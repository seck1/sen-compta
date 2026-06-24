<?php
require_once APP_ROOT . '/config/app.php';

class AuthController {

    public function loginPage(): void {
        if (auth()) redirect('/dashboard');
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require_once APP_ROOT . '/views/auth/login.php';
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/login');

        // BUG-01 : vérification CSRF
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $_SESSION['login_error'] = 'Veuillez remplir tous les champs.';
            redirect('/login');
        }

        // BUG-02 : rate limiting — max 5 tentatives par IP, blocage 15 min
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'login_attempts_' . md5($ip);
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

        if ($attempts['until'] > time()) {
            $reste = ceil(($attempts['until'] - time()) / 60);
            $_SESSION['login_error'] = "Trop de tentatives. Réessayez dans {$reste} minute(s).";
            redirect('/login');
        }

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND actif = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts['count']++;
            if ($attempts['count'] >= 5) {
                $attempts['until'] = time() + 900; // blocage 15 min
                $attempts['count'] = 0;
            }
            $_SESSION[$key] = $attempts;
            $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
            redirect('/login');
        }

        // Réinitialiser le compteur après succès
        unset($_SESSION[$key]);

        // Vérification 2FA si activé
        if (!empty($user['totp_actif']) && !empty($user['totp_secret'])) {
            $_SESSION['totp_user_pending_id'] = $user['id']; // ne stocker que l'ID, pas les données sensibles
            redirect('/login/2fa');
        }

        // Mise à jour dernière connexion
        $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = ?")->execute([$user['id']]);

        // Régénérer l'ID de session pour prévenir la session fixation
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => $user['id'],
            'nom'        => $user['nom'],
            'prenom'     => $user['prenom'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'role_saas'  => $user['role_saas'] ?? 'collaborateur',
            'cabinet_id' => $user['cabinet_id'] ?? null,
            'totp_actif' => $user['totp_actif'],
        ];

        redirect('/dashboard');
    }

    public function verify2fa(): void {
        $userId = $_SESSION['totp_user_pending_id'] ?? null;
        if (!$userId) redirect('/login');

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) redirect('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate limiting 2FA — max 5 tentatives
            $attempts = $_SESSION['2fa_attempts'][$userId] ?? 0;
            if ($attempts >= 5) {
                unset($_SESSION['totp_user_pending_id'], $_SESSION['2fa_attempts'][$userId]);
                redirect('/login?error=too_many_attempts');
                return;
            }
            $_SESSION['2fa_attempts'][$userId] = $attempts + 1;

            require_once APP_ROOT . '/src/Services/TOTPService.php';
            $code = trim($_POST['code'] ?? '');
            if (TOTPService::verify($user['totp_secret'], $code)) {
                unset($_SESSION['2fa_attempts'][$userId]);
                unset($_SESSION['totp_user_pending_id']);
                $db->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id = ?")->execute([$user['id']]);
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'], 'nom' => $user['nom'],
                    'prenom' => $user['prenom'], 'email' => $user['email'],
                    'role' => $user['role'], 'totp_actif' => 1,
                ];
                redirect('/dashboard');
            }
            $error = 'Code invalide.';
        }
        $error = $error ?? null;
        require_once APP_ROOT . '/views/auth/2fa.php';
    }

    public function logout(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login'); return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        redirect('/login');
    }

    public function forgotPage(): void {
        $sent = $_GET['sent'] ?? false;
        $error = $_GET['error'] ?? null;
        require APP_ROOT . '/views/auth/forgot.php';
    }

    public function forgotPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/mot-de-passe-oublie');
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirect('/mot-de-passe-oublie?error=invalid');
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id, prenom, nom FROM users WHERE email=? AND actif=1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // On redirige toujours vers "envoyé" pour ne pas révéler si l'email existe
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            // Stocker le token (table password_resets si elle existe, sinon en session)
            try {
                $ins = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?) ON DUPLICATE KEY UPDATE token=?, expires_at=?");
                $ins->execute([$user['id'], $token, $expires, $token, $expires]);
            } catch (Exception $e) {
                // Table inexistante : stocker en session pour démo
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_user_id'] = $user['id'];
            }
            // En production : envoyer un email avec le lien
            // mail($email, 'Réinitialisation mot de passe', APP_URL.'/reset-password?token='.$token);
        }

        redirect('/mot-de-passe-oublie?sent=1');
    }
}
