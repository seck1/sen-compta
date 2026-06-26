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

        // Config SMTP (réservée au super-admin) + flash
        $isSuperAdmin = (($user['role_saas'] ?? '') === 'super_admin');
        $smtp = $this->getSmtpSettings();
        $smtpFlash = $_SESSION['smtp_flash'] ?? null;
        unset($_SESSION['smtp_flash']);

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

    /* ---------- RGPD : droit d'acces & portabilite ---------- */
    // Telecharge les donnees personnelles de l'utilisateur connecte (JSON).
    public function exporterDonnees(): void {
        requireAuth();
        $db = getDB();
        $uid = (int)auth()['id'];

        $u = $db->prepare("SELECT id, cabinet_id, nom, prenom, email, role, role_saas, telephone,
                                  derniere_connexion, created_at, totp_actif
                           FROM users WHERE id = ?");
        $u->execute([$uid]);
        $compte = $u->fetch(PDO::FETCH_ASSOC);

        // Dossiers auxquels l'utilisateur est rattache
        $a = $db->prepare("SELECT ue.entreprise_id, e.raison_sociale, ue.role, ue.date_assignation
                           FROM user_entreprises ue JOIN entreprises e ON e.id = ue.entreprise_id
                           WHERE ue.user_id = ?");
        $a->execute([$uid]);
        $acces = $a->fetchAll(PDO::FETCH_ASSOC);

        // Journaliser la demande (preuve de conformite)
        $this->logDemande($uid, null, $compte['email'] ?? '', 'export', 'Export automatique des donnees du compte.');

        $export = [
            'genere_le'    => date('c'),
            'application'  => 'SenCompta',
            'mention'      => 'Donnees personnelles du compte, fournies au titre du droit d\'acces et de portabilite (RGPD art. 15 & 20 / loi 2008-12).',
            'compte'       => $compte,
            'acces_dossiers' => $acces,
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="mes-donnees-sencompta-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ---------- RGPD : droit a l'effacement ---------- */
    public function demanderSuppression(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/profil');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $user = auth();
        $this->logDemande((int)$user['id'], null, $user['email'],
            'suppression', trim($_POST['message'] ?? '') ?: null);
        redirect('/profil?rgpd=demande');
    }

    private function logDemande(?int $userId, ?int $clientId, string $email, string $type, ?string $message): void {
        getDB()->prepare("INSERT INTO rgpd_demandes (user_id, client_id, email, type, message, ip)
                          VALUES (?,?,?,?,?,?)")
            ->execute([$userId, $clientId, $email, $type, $message,
                       $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    // ── Paramétrage SMTP (super-admin uniquement) ─────────────────────
    private function requireSuperAdmin(): void {
        requireAuth();
        $u = auth();
        if (($u['role_saas'] ?? '') !== 'super_admin') redirect('/profil');
    }

    /** Lit la config SMTP depuis app_settings (valeurs vides si non définies). */
    private function getSmtpSettings(): array {
        $keys = ['smtp_host','smtp_port','smtp_user','smtp_pass','mail_from','mail_from_name'];
        $out = array_fill_keys($keys, '');
        try {
            $db = getDB();
            $rows = $db->query("SELECT cle, valeur FROM app_settings WHERE cle IN ('".implode("','",$keys)."')")->fetchAll(PDO::FETCH_KEY_PAIR);
            foreach ($rows as $k=>$v) $out[$k] = $v;
        } catch (\Throwable $e) { /* table absente */ }
        if ($out['smtp_port']==='') $out['smtp_port']='587';
        return $out;
    }

    public function sauverSmtp(): void {
        $this->requireSuperAdmin();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $map = [
            'smtp_host'      => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'      => trim($_POST['smtp_port'] ?? '587'),
            'smtp_user'      => trim($_POST['smtp_user'] ?? ''),
            'mail_from'      => trim($_POST['mail_from'] ?? ''),
            'mail_from_name' => trim($_POST['mail_from_name'] ?? 'SenCompta'),
        ];
        // Le mot de passe n'est mis à jour que s'il est fourni (on ne l'écrase pas par vide)
        $pass = $_POST['smtp_pass'] ?? '';
        if ($pass !== '') $map['smtp_pass'] = $pass;

        $st = $db->prepare("INSERT INTO app_settings (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=VALUES(valeur)");
        foreach ($map as $k=>$v) $st->execute([$k, $v]);

        $_SESSION['smtp_flash'] = ['ok'=>true,'msg'=>'Configuration SMTP enregistrée.'];
        redirect('/profil#email');
    }

    public function testerSmtp(): void {
        $this->requireSuperAdmin();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $u = auth();
        require_once APP_ROOT . '/config/mail.php';
        $to = $u['email'] ?? trim($_POST['test_email'] ?? '');
        if (!$to) { $_SESSION['smtp_flash']=['ok'=>false,'msg'=>'Aucune adresse de test.']; redirect('/profil#email'); }
        $html = '<div style="font-family:Arial;padding:20px"><h2 style="color:#1e3a5f">Test SMTP SenCompta</h2><p>Si vous recevez cet email, votre configuration SMTP fonctionne correctement.</p></div>';
        $ok = @sendMail($to, $u['prenom'] ?? 'Admin', 'Test SMTP — SenCompta', $html);
        $_SESSION['smtp_flash'] = $ok
            ? ['ok'=>true,'msg'=>"Email de test envoyé à $to. Vérifiez votre boîte (et les spams)."]
            : ['ok'=>false,'msg'=>"Échec de l'envoi. Vérifiez l'hôte, le port, l'utilisateur et le mot de passe d'application."];
        redirect('/profil#email');
    }
}
