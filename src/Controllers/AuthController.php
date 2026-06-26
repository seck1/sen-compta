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
        // On récupère l'utilisateur SANS filtrer sur actif=1, afin de distinguer
        // un mauvais mot de passe d'un compte non encore validé par email.
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
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

        // Identifiants corrects, mais compte non encore validé par email :
        // on régénère un code, on l'envoie, et on renvoie l'utilisateur sur la
        // page de saisie du code (au lieu de "mot de passe incorrect").
        if ((int)($user['email_verifie'] ?? 1) === 0) {
            unset($_SESSION[$key]); // identifiants bons : pas de pénalité brute-force
            $code = $this->regenererCodeVerification((int)$user['id']);
            $_SESSION['verif_user_id'] = (int)$user['id'];
            $_SESSION['verif_email']   = $user['email'];
            $_SESSION['verif_info']    = "Votre compte n'est pas encore validé. Un nouveau code vient de vous être envoyé par email.";
            redirect('/inscription/verifier');
        }

        // Compte désactivé par un administrateur (et non un simple défaut de validation)
        if ((int)($user['actif'] ?? 1) === 0) {
            $_SESSION['login_error'] = "Votre compte est désactivé. Contactez le support.";
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

    /**
     * Régénère un code de vérification email pour un user non validé et l'envoie.
     * Retourne le code (4 chiffres) ou null. Utilisé quand un compte non validé
     * tente de se connecter.
     */
    private function regenererCodeVerification(int $userId): ?string {
        $db = getDB();
        $u = $db->prepare("SELECT u.email, c.nom AS cabinet_nom, u.cabinet_id FROM users u LEFT JOIN cabinets c ON c.id=u.cabinet_id WHERE u.id=?");
        $u->execute([$userId]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 1800);
        $db->prepare("UPDATE email_verifications SET verified_at=NOW() WHERE user_id=? AND verified_at IS NULL")->execute([$userId]);
        $db->prepare("INSERT INTO email_verifications (user_id, cabinet_id, email, code, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$userId, $row['cabinet_id'], $row['email'], $code, $expires]);
        require_once APP_ROOT . '/config/mail.php';
        @mailVerificationCode($row['email'], $row['cabinet_nom'] ?? 'votre cabinet', $code);
        return $code;
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
            $token = bin2hex(random_bytes(32));          // envoyé dans l'email (clair)
            $tokenHash = hash('sha256', $token);          // stocké en base (jamais le token clair)
            $expires = date('Y-m-d H:i:s', time() + 3600);
            try {
                // Invalider les anciennes demandes en cours pour cet utilisateur
                $db->prepare("UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL")->execute([$user['id']]);
                $ins = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)");
                $ins->execute([$user['id'], $tokenHash, $expires]);
            } catch (Exception $e) {
                error_log('forgotPost insert token: '.$e->getMessage());
                redirect('/mot-de-passe-oublie?sent=1');
            }
            // Envoyer le lien de réinitialisation par email
            $lien = APP_URL . '/reset-password?token=' . $token;
            require_once APP_ROOT . '/config/mail.php';
            @mailResetPassword($email, $user['prenom'] ?? '', $lien);
        }

        redirect('/mot-de-passe-oublie?sent=1');
    }

    /** Page de saisie du nouveau mot de passe (depuis le lien email). */
    public function resetPasswordPage(): void {
        $token = trim($_GET['token'] ?? '');
        $valid = $this->tokenResetValide($token) !== null;
        $error = $_SESSION['reset_error'] ?? null;
        unset($_SESSION['reset_error']);
        require APP_ROOT . '/views/auth/reset-password.php';
    }

    /** Enregistre le nouveau mot de passe après vérification du token. */
    public function resetPasswordPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/login');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $token = trim($_POST['token'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        $userId = $this->tokenResetValide($token);
        if ($userId === null) {
            $_SESSION['reset_error'] = "Lien invalide ou expiré. Veuillez refaire une demande.";
            redirect('/reset-password?token=' . urlencode($token));
        }
        if (strlen($pass) < 8) {
            $_SESSION['reset_error'] = "Le mot de passe doit contenir au moins 8 caractères.";
            redirect('/reset-password?token=' . urlencode($token));
        }
        if ($pass !== $pass2) {
            $_SESSION['reset_error'] = "Les deux mots de passe ne correspondent pas.";
            redirect('/reset-password?token=' . urlencode($token));
        }

        $db = getDB();
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);
        // Invalider le token (et tous les autres de cet utilisateur)
        $db->prepare("UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL")->execute([$userId]);

        // Invalider toute session active (un attaquant éventuellement connecté est déconnecté)
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['inscription_success'] = "Mot de passe réinitialisé. Vous pouvez vous connecter.";
        redirect('/login');
    }

    /** Retourne l'user_id si le token est valide (existe, non utilisé, non expiré), sinon null. */
    private function tokenResetValide(string $token): ?int {
        if ($token === '') return null;
        try {
            $db = getDB();
            $tokenHash = hash('sha256', $token);
            $stmt = $db->prepare("SELECT user_id FROM password_resets WHERE token=? AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$tokenHash]);
            $id = $stmt->fetchColumn();
            return $id !== false ? (int)$id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
