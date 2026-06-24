<?php
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/src/Services/TOTPService.php';

class ProfilController {

    public function index(): void {
        requireAuth();
        $db = getDB();
        $u = auth();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$u['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $saved = isset($_GET['saved']);

        ob_start();
        require APP_ROOT . '/views/profil/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Mon profil';
        $activePage = 'profil';
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function update(): void {
        requireAuth();
        $db = getDB();
        $u = auth();
        $stmt = $db->prepare("UPDATE users SET nom=?, prenom=?, telephone=? WHERE id=?");
        $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['telephone'], $u['id']]);

        if (!empty($_POST['password'])) {
            $pwd = $_POST['password'];
            if (strlen($pwd) < 8 || !preg_match('/[A-Z]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
                redirect('/profil?error=password');
            }
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $u['id']]);
        }

        // Refresh session — on exclut volontairement password et totp_secret
        $stmt = $db->prepare("SELECT id, nom, prenom, email, role, avatar, telephone, actif, derniere_connexion, totp_actif FROM users WHERE id=?");
        $stmt->execute([$u['id']]);
        $_SESSION['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

        redirect('/profil?saved=1');
    }

    public function setup2fa(): void {
        requireAuth();
        $u = auth();
        $secret = TOTPService::generateSecret();
        $_SESSION['totp_pending'] = $secret;
        $db = getDB();
        $stmtU = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmtU->execute([$u['id']]);
        $user = $stmtU->fetch();
        $otpauthUri = TOTPService::getOtpauthUri($secret, $user['email']);

        ob_start();
        require APP_ROOT . '/views/profil/setup2fa.php';
        $content = ob_get_clean();
        $pageTitle = 'Configurer 2FA';
        $activePage = 'profil';
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function confirm2fa(): void {
        requireAuth();
        $secret = $_SESSION['totp_pending'] ?? '';
        $code = trim($_POST['code'] ?? '');
        if (!$secret || !TOTPService::verify($secret, $code)) {
            redirect('/profil/setup-2fa?error=1');
        }
        $db = getDB();
        $db->prepare("UPDATE users SET totp_secret=?, totp_actif=1 WHERE id=?")->execute([$secret, auth()['id']]);
        unset($_SESSION['totp_pending']);
        redirect('/profil?saved=1&2fa=1');
    }

    public function disable2fa(): void {
        requireAuth();
        $db = getDB();
        $db->prepare("UPDATE users SET totp_secret=NULL, totp_actif=0 WHERE id=?")->execute([auth()['id']]);
        redirect('/profil?saved=1');
    }
}
