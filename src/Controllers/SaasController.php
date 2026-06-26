<?php
require_once APP_ROOT . '/config/app.php';

class SaasController {

    // ── Page publique « Fonctionnalités » (vitrine) ───────────────────
    public function fonctionnalites(): void {
        require_once APP_ROOT . '/views/saas/fonctionnalites.php';
    }

    // ── Page inscription cabinet ──────────────────────────────────────
    public function inscriptionPage(): void {
        if (auth()) redirect('/dashboard');
        $plans = $this->getPlans();
        $error = $_SESSION['inscription_error'] ?? null;
        $success = $_SESSION['inscription_success'] ?? null;
        unset($_SESSION['inscription_error'], $_SESSION['inscription_success']);
        require_once APP_ROOT . '/views/saas/inscription.php';
    }

    public function inscriptionPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/inscription');
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        // RGPD : consentement CGU + confidentialite obligatoire
        if (empty($_POST['accept_cgu'])) {
            $_SESSION['inscription_error'] = "Vous devez accepter les CGU et la politique de confidentialité.";
            redirect('/inscription');
        }

        $nom         = trim($_POST['nom_cabinet'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $telephone   = trim($_POST['telephone'] ?? '');
        $responsable = trim($_POST['responsable'] ?? '');
        $password    = $_POST['password'] ?? '';
        $password2   = $_POST['password2'] ?? '';
        $plan_code   = $_POST['plan'] ?? 'solo';

        // Validations
        if (!$nom || !$email || !$responsable || !$password) {
            $_SESSION['inscription_error'] = 'Veuillez remplir tous les champs obligatoires.';
            redirect('/inscription');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['inscription_error'] = 'Email invalide.';
            redirect('/inscription');
        }
        if ($password !== $password2) {
            $_SESSION['inscription_error'] = 'Les mots de passe ne correspondent pas.';
            redirect('/inscription');
        }
        if (strlen($password) < 8) {
            $_SESSION['inscription_error'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            redirect('/inscription');
        }

        $db = getDB();

        // Vérifier email unique
        $check = $db->prepare("SELECT id FROM cabinets WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $_SESSION['inscription_error'] = 'Cet email est déjà utilisé.';
            redirect('/inscription');
        }

        // Récupérer le plan
        $planStmt = $db->prepare("SELECT * FROM plans WHERE code = ?");
        $planStmt->execute([$plan_code]);
        $plan = $planStmt->fetch();
        if (!$plan) $plan = $db->query("SELECT * FROM plans WHERE code='solo'")->fetch();

        // Créer le slug unique
        $slug = $this->makeSlug($nom);
        $slugCheck = $db->prepare("SELECT id FROM cabinets WHERE slug = ?");
        $slugCheck->execute([$slug]);
        if ($slugCheck->fetch()) $slug .= '-' . rand(100, 999);

        // Créer le cabinet
        $essaiFin = date('Y-m-d', strtotime('+14 days'));
        $ins = $db->prepare("INSERT INTO cabinets (nom, slug, email, telephone, responsable_nom, plan_id, statut, essai_fin)
                             VALUES (?,?,?,?,?,?,?,?)");
        $ins->execute([$nom, $slug, $email, $telephone, $responsable, $plan['id'], 'essai', $essaiFin]);
        $cabinetId = $db->lastInsertId();

        // La vérification email est-elle activée ? (interrupteur global app_settings)
        // OFF par défaut tant que l'envoi d'emails n'est pas configuré -> inscription directe.
        $verifActive = $this->verificationEmailActive();

        // Créer le user admin du cabinet
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $actif = $verifActive ? 0 : 1;      // actif direct si la vérif est désactivée
        $emailVerifie = $verifActive ? 0 : 1;
        $userIns = $db->prepare("INSERT INTO users (cabinet_id, nom, prenom, email, password, role, role_saas, actif, email_verifie)
                                 VALUES (?,?,?,?,?,'admin','admin_cabinet',?,?)");
        $parts = explode(' ', $responsable, 2);
        $prenom = $parts[0];
        $nomUser = $parts[1] ?? '';
        $userIns->execute([$cabinetId, $nomUser, $prenom, $email, $hash, $actif, $emailVerifie]);
        $userId = (int)$db->lastInsertId();

        require_once APP_ROOT . '/config/mail.php';
        // Notifier le super-admin dans tous les cas
        $this->notifierAdminInscription($nom, $email, $responsable, $plan['nom'] ?? '');

        if (!$verifActive) {
            // Pas de vérification : connexion directe, le compte est déjà actif
            $_SESSION['user_id'] = $userId;
            $_SESSION['flash_success'] = "Bienvenue ! Votre compte a été créé avec succès.";
            redirect('/dashboard');
        }

        // Vérification active : générer un code à 4 chiffres (valable 30 min) + l'envoyer
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 1800);
        $db->prepare("INSERT INTO email_verifications (user_id, cabinet_id, email, code, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$userId, $cabinetId, $email, $code, $expires]);
        @mailVerificationCode($email, $nom, $code);

        // Page de saisie du code
        $_SESSION['verif_user_id'] = $userId;
        $_SESSION['verif_email']   = $email;
        redirect('/inscription/verifier');
    }

    /** La vérification email à l'inscription est-elle activée ? (réglage app_settings, OFF par défaut) */
    private function verificationEmailActive(): bool {
        try {
            $v = getDB()->query("SELECT valeur FROM app_settings WHERE cle='email_verification_active'")->fetchColumn();
            return $v === '1';
        } catch (\Throwable $e) {
            return false; // table absente ou non configurée -> vérif désactivée
        }
    }

    /** Envoie une notification email à tous les super-admins lors d'une inscription. */
    private function notifierAdminInscription(string $cabinetNom, string $email, string $responsable, string $planNom): void {
        try {
            $db = getDB();
            $admins = $db->query("SELECT email FROM users WHERE role_saas='super_admin' AND email IS NOT NULL AND email<>''")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($admins)) $admins = ['sencompta1@gmail.com']; // fallback
            require_once APP_ROOT . '/config/mail.php';
            foreach (array_unique($admins) as $a) {
                @mailNouvelleInscriptionAdmin($a, $cabinetNom, $email, $responsable, $planNom);
            }
        } catch (\Throwable $e) { error_log('notif admin inscription: '.$e->getMessage()); }
    }

    // ── Page de vérification du code (4 chiffres) ─────────────────────
    public function verificationPage(): void {
        if (empty($_SESSION['verif_user_id'])) redirect('/inscription');
        $email = $_SESSION['verif_email'] ?? '';
        $error = $_SESSION['verif_error'] ?? null;
        $info  = $_SESSION['verif_info'] ?? null;
        unset($_SESSION['verif_error'], $_SESSION['verif_info']);
        require_once APP_ROOT . '/views/saas/inscription-verifier.php';
    }

    public function verificationPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/inscription/verifier');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $userId = (int)($_SESSION['verif_user_id'] ?? 0);
        if (!$userId) redirect('/inscription');
        $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
        $db = getDB();

        // Anti-brute-force global : plafond de tentatives par session (contournement
        // du compteur par code via renvois répétés). 12 max -> ~0,12% de chance sur 10000.
        $_SESSION['verif_attempts'] = ($_SESSION['verif_attempts'] ?? 0) + 1;
        if ($_SESSION['verif_attempts'] > 12) {
            $_SESSION['verif_error'] = "Trop de tentatives. Réessayez plus tard ou contactez le support.";
            redirect('/inscription/verifier');
        }

        $stmt = $db->prepare("SELECT * FROM email_verifications WHERE user_id=? AND verified_at IS NULL ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $verif = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verif) { $_SESSION['verif_error'] = "Aucun code en attente. Demandez-en un nouveau."; redirect('/inscription/verifier'); }
        if ((int)$verif['tentatives'] >= 6) { $_SESSION['verif_error'] = "Trop de tentatives. Demandez un nouveau code."; redirect('/inscription/verifier'); }
        if (strtotime($verif['expires_at']) < time()) { $_SESSION['verif_error'] = "Code expiré. Demandez un nouveau code."; redirect('/inscription/verifier'); }

        if (!hash_equals($verif['code'], $code)) {
            $db->prepare("UPDATE email_verifications SET tentatives=tentatives+1 WHERE id=?")->execute([$verif['id']]);
            $_SESSION['verif_error'] = "Code incorrect. Réessayez.";
            redirect('/inscription/verifier');
        }

        // Code OK → activer le compte
        $db->prepare("UPDATE email_verifications SET verified_at=NOW() WHERE id=?")->execute([$verif['id']]);
        $db->prepare("UPDATE users SET actif=1, email_verifie=1 WHERE id=?")->execute([$userId]);
        if (!empty($verif['cabinet_id'])) {
            $db->prepare("UPDATE cabinets SET statut='essai' WHERE id=?")->execute([$verif['cabinet_id']]);
        }
        unset($_SESSION['verif_user_id'], $_SESSION['verif_email'], $_SESSION['verif_attempts'], $_SESSION['verif_last_resend']);
        $_SESSION['inscription_success'] = "Email vérifié ! Votre compte est activé. Connectez-vous pour démarrer vos 14 jours d'essai.";
        redirect('/login');
    }

    /** L'utilisateur redemande un code depuis la page de vérification. */
    public function renvoyerCodeInscription(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/inscription/verifier');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $userId = (int)($_SESSION['verif_user_id'] ?? 0);
        if (!$userId) redirect('/inscription');

        // Anti-spam : un renvoi toutes les 60 s max par session
        $last = $_SESSION['verif_last_resend'] ?? 0;
        if (time() - $last < 60) {
            $_SESSION['verif_error'] = "Veuillez patienter une minute avant de redemander un code.";
            redirect('/inscription/verifier');
        }
        $_SESSION['verif_last_resend'] = time();

        $this->genererEtEnvoyerCode($userId);
        $_SESSION['verif_info'] = "Un nouveau code vous a été envoyé par email.";
        redirect('/inscription/verifier');
    }

    /**
     * Génère un nouveau code pour un user et l'envoie par email (réutilisé par l'admin).
     * Retourne le code généré (4 chiffres) si tout s'est bien passé, sinon null.
     */
    private function genererEtEnvoyerCode(int $userId): ?string {
        $db = getDB();
        $u = $db->prepare("SELECT u.email, c.nom AS cabinet_nom, u.cabinet_id FROM users u LEFT JOIN cabinets c ON c.id=u.cabinet_id WHERE u.id=?");
        $u->execute([$userId]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 1800);
        // invalider les anciens codes non utilisés
        $db->prepare("UPDATE email_verifications SET verified_at=NOW() WHERE user_id=? AND verified_at IS NULL")->execute([$userId]);
        $db->prepare("INSERT INTO email_verifications (user_id, cabinet_id, email, code, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$userId, $row['cabinet_id'], $row['email'], $code, $expires]);
        require_once APP_ROOT . '/config/mail.php';
        @mailVerificationCode($row['email'], $row['cabinet_nom'] ?? 'votre cabinet', $code);
        // On retourne le code même si l'email échoue : le super-admin peut alors
        // le communiquer manuellement au cabinet.
        return $code;
    }

    /** Admin : renvoyer le code de vérification à un cabinet non encore activé. */
    public function adminRenvoyerCode(): void {
        $this->requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/superadmin/cabinets');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $cabinetId = (int)($_POST['cabinet_id'] ?? 0);
        $db = getDB();
        $u = $db->prepare("SELECT id FROM users WHERE cabinet_id=? AND role_saas='admin_cabinet' ORDER BY id LIMIT 1");
        $u->execute([$cabinetId]);
        $userId = (int)$u->fetchColumn();
        $code = $userId ? $this->genererEtEnvoyerCode($userId) : null;
        if ($code !== null) {
            $_SESSION['flash_success'] = "Code de vérification renvoyé par email. Code : $code (valable 30 min).";
        } else {
            $_SESSION['flash_error'] = "Impossible de renvoyer le code.";
        }
        redirect('/superadmin/cabinets');
    }

    // ── SUPER ADMIN ───────────────────────────────────────────────────
    public function adminDashboard(): void {
        $this->requireSuperAdmin();
        $db = getDB();

        $stats = [
            'total_cabinets' => $db->query("SELECT COUNT(*) FROM cabinets WHERE id > 1")->fetchColumn(),
            'actifs'         => $db->query("SELECT COUNT(*) FROM cabinets WHERE statut='actif' AND id > 1")->fetchColumn(),
            'essai'          => $db->query("SELECT COUNT(*) FROM cabinets WHERE statut='essai' AND id > 1")->fetchColumn(),
            'suspendus'      => $db->query("SELECT COUNT(*) FROM cabinets WHERE statut='suspendu' AND id > 1")->fetchColumn(),
            'mrr'            => $db->query("SELECT COALESCE(SUM(p.prix_mois),0) FROM cabinets c JOIN plans p ON c.plan_id=p.id WHERE c.statut='actif' AND c.id > 1")->fetchColumn(),
            'paiements_attente' => $db->query("SELECT COUNT(*) FROM paiements_abonnement WHERE statut='en_attente'")->fetchColumn(),
            'demandes_attente'  => $db->query("SELECT COUNT(*) FROM demandes_cabinets WHERE statut='en_attente'")->fetchColumn(),
        ];

        $cabinets = $db->query("
            SELECT c.*, p.nom as plan_nom, p.prix_mois,
                   (SELECT COUNT(*) FROM entreprises e WHERE e.cabinet_id = c.id) as nb_entreprises,
                   (SELECT COUNT(*) FROM users u WHERE u.cabinet_id = c.id) as nb_users
            FROM cabinets c
            JOIN plans p ON c.plan_id = p.id
            WHERE c.id > 1
            ORDER BY c.created_at DESC
            LIMIT 50
        ")->fetchAll();

        require_once APP_ROOT . '/views/saas/admin-dashboard.php';
    }

    public function adminCabinets(): void {
        $this->requireSuperAdmin();
        $db = getDB();
        $statut = $_GET['statut'] ?? '';
        $search = $_GET['q'] ?? '';

        $where = 'WHERE c.id > 1';
        $params = [];
        if ($statut) { $where .= ' AND c.statut = ?'; $params[] = $statut; }
        if ($search) { $where .= ' AND (c.nom LIKE ? OR c.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $stmt = $db->prepare("
            SELECT c.*, p.nom as plan_nom, p.prix_mois,
                   (SELECT COUNT(*) FROM entreprises e WHERE e.cabinet_id = c.id) as nb_entreprises,
                   (SELECT COUNT(*) FROM users u WHERE u.cabinet_id = c.id) as nb_users
            FROM cabinets c JOIN plans p ON c.plan_id = p.id
            $where ORDER BY c.created_at DESC
        ");
        $stmt->execute($params);
        $cabinets = $stmt->fetchAll();
        $plans = $this->getPlans();

        require_once APP_ROOT . '/views/saas/admin-cabinets.php';
    }

    public function adminCabinetAction(): void {
        $this->requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/superadmin/cabinets');
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $db = getDB();
        $id = (int)($_POST['cabinet_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'activer':
                $db->prepare("UPDATE cabinets SET statut='actif', abonnement_debut=NOW(), abonnement_fin=DATE_ADD(NOW(), INTERVAL 1 MONTH) WHERE id=?")->execute([$id]);
                break;
            case 'suspendre':
                $db->prepare("UPDATE cabinets SET statut='suspendu' WHERE id=?")->execute([$id]);
                break;
            case 'reactiver':
                $db->prepare("UPDATE cabinets SET statut='actif' WHERE id=?")->execute([$id]);
                break;
            case 'changer_plan':
                $plan_id = (int)($_POST['plan_id'] ?? 1);
                $db->prepare("UPDATE cabinets SET plan_id=? WHERE id=?")->execute([$plan_id, $id]);
                break;
        }
        redirect('/superadmin/cabinets');
    }

    public function adminPaiements(): void {
        $this->requireSuperAdmin();
        $db = getDB();

        $paiements = $db->query("
            SELECT pa.*, c.nom as cabinet_nom, c.email as cabinet_email, p.nom as plan_nom
            FROM paiements_abonnement pa
            JOIN cabinets c ON pa.cabinet_id = c.id
            JOIN plans p ON pa.plan_id = p.id
            ORDER BY pa.created_at DESC
            LIMIT 100
        ")->fetchAll();

        require_once APP_ROOT . '/views/saas/admin-paiements.php';
    }

    public function adminValiderPaiement(): void {
        $this->requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/superadmin/paiements');
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $db = getDB();
        $id = (int)($_POST['paiement_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'valider') {
            $db->prepare("UPDATE paiements_abonnement SET statut='valide', valide_par=?, valide_le=NOW() WHERE id=?")->execute([auth()['id'], $id]);
            // Activer le cabinet
            $pay = $db->prepare("SELECT * FROM paiements_abonnement WHERE id=?");
            $pay->execute([$id]);
            $p = $pay->fetch();
            if ($p) {
                $fin = $p['periodicite'] === 'annuel'
                    ? date('Y-m-d', strtotime('+1 year'))
                    : date('Y-m-d', strtotime('+1 month'));
                $db->prepare("UPDATE cabinets SET statut='actif', abonnement_debut=NOW(), abonnement_fin=? WHERE id=?")->execute([$fin, $p['cabinet_id']]);
            }
        } elseif ($action === 'refuser') {
            $db->prepare("UPDATE paiements_abonnement SET statut='refuse', valide_par=?, valide_le=NOW(), notes=? WHERE id=?")->execute([auth()['id'], $_POST['notes'] ?? '', $id]);
        }

        redirect('/superadmin/paiements');
    }

    public function adminDemandes(): void {
        $this->requireSuperAdmin();
        $db = getDB();

        $demandes = $db->query("
            SELECT d.*, c.nom as cabinet_nom, c.email as cabinet_email,
                   p.nom as plan_demande_nom
            FROM demandes_cabinets d
            JOIN cabinets c ON d.cabinet_id = c.id
            LEFT JOIN plans p ON d.plan_demande_id = p.id
            ORDER BY d.created_at DESC
        ")->fetchAll();

        require_once APP_ROOT . '/views/saas/admin-demandes.php';
    }

    // ── ESPACE CABINET ────────────────────────────────────────────────
    public function monCompte(): void {
        $this->requireCabinet();
        $db = getDB();
        $user = auth();
        $cabinet = $db->prepare("SELECT c.*, p.nom as plan_nom, p.max_entreprises, p.max_users, p.prix_mois FROM cabinets c JOIN plans p ON c.plan_id=p.id WHERE c.id=?");
        $cabinet->execute([$user['cabinet_id'] ?? 1]);
        $cabinet = $cabinet->fetch();

        $nb_entreprises = $db->prepare("SELECT COUNT(*) FROM entreprises WHERE cabinet_id=?");
        $nb_entreprises->execute([$user['cabinet_id'] ?? 1]);
        $nb_entreprises = $nb_entreprises->fetchColumn();

        $nb_users = $db->prepare("SELECT COUNT(*) FROM users WHERE cabinet_id=?");
        $nb_users->execute([$user['cabinet_id'] ?? 1]);
        $nb_users = $nb_users->fetchColumn();

        $plans = $this->getPlans();
        require_once APP_ROOT . '/views/saas/mon-compte.php';
    }

    public function demandeUpgrade(): void {
        $this->requireCabinet();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/mon-compte');
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $db = getDB();
        $user = auth();
        $plan_id = (int)($_POST['plan_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        $db->prepare("INSERT INTO demandes_cabinets (cabinet_id, type, plan_demande_id, message) VALUES (?,?,?,?)")
           ->execute([$user['cabinet_id'] ?? 1, 'upgrade', $plan_id ?: null, $message]);

        $_SESSION['flash_success'] = 'Demande envoyée. Nous vous répondrons sous 24h.';
        redirect('/mon-compte');
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function getPlans(): array {
        return getDB()->query("SELECT * FROM plans WHERE actif=1 ORDER BY prix_mois")->fetchAll();
    }

    private function makeSlug(string $nom): string {
        $slug = strtolower($nom);
        $slug = preg_replace('/[àáâãäå]/u', 'a', $slug);
        $slug = preg_replace('/[éèêë]/u', 'e', $slug);
        $slug = preg_replace('/[îï]/u', 'i', $slug);
        $slug = preg_replace('/[ôö]/u', 'o', $slug);
        $slug = preg_replace('/[ùûü]/u', 'u', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    private function requireSuperAdmin(): void {
        $user = auth();
        if (!$user || ($user['role_saas'] ?? '') !== 'super_admin') {
            redirect('/dashboard');
        }
    }

    private function requireCabinet(): void {
        if (!auth()) redirect('/login');
    }

    // ═══════════════ UTILISATEURS EN LIGNE (supervision temps réel) ═══════════════

    /** Seuils (secondes) : en ligne ≤5min, inactif ≤15min, sinon hors-ligne. */
    private const SEUIL_ONLINE = 300;
    private const SEUIL_IDLE    = 900;

    /** Page de supervision des sessions utilisateurs (tous cabinets). */
    public function adminUsersOnline(): void {
        $this->requireSuperAdmin();
        $periode = in_array($_GET['p'] ?? '24h', ['24h','7j','30j','tout']) ? ($_GET['p'] ?? '24h') : '24h';
        $search  = trim($_GET['q'] ?? '');

        $data = $this->getUsersActivite($periode, $search);
        $users   = $data['users'];
        $stats   = $data['stats'];

        $pageTitle  = 'Utilisateurs en ligne';
        $activePage = 'superadmin-online';
        require_once APP_ROOT . '/views/saas/admin-users-online.php';
    }

    /** Endpoint JSON pour l'auto-refresh (mêmes données, sans le layout). */
    public function adminUsersOnlineData(): void {
        $this->requireSuperAdmin();
        $periode = in_array($_GET['p'] ?? '24h', ['24h','7j','30j','tout']) ? ($_GET['p'] ?? '24h') : '24h';
        $search  = trim($_GET['q'] ?? '');
        header('Content-Type: application/json');
        echo json_encode($this->getUsersActivite($periode, $search));
        exit;
    }

    /** Calcule la liste des users avec statut + sparkline + stats agrégées. */
    private function getUsersActivite(string $periode, string $search): array {
        $db = getDB();

        // Fenêtre de la période pour le filtrage des sessions/actions
        $since = match ($periode) {
            '7j'   => date('Y-m-d H:i:s', time() - 7*86400),
            '30j'  => date('Y-m-d H:i:s', time() - 30*86400),
            'tout' => '1970-01-01 00:00:00',
            default => date('Y-m-d H:i:s', time() - 86400),  // 24h
        };

        $where = "WHERE u.id IS NOT NULL";
        $params = [':since' => $since];
        if ($search !== '') {
            $where .= " AND (u.nom LIKE :s OR u.prenom LIKE :s OR u.email LIKE :s OR c.nom LIKE :s)";
            $params[':s'] = "%$search%";
        }

        // Activité de référence = la plus récente entre derniere_activite et derniere_connexion
        $sql = "
            SELECT u.id, u.nom, u.prenom, u.email, u.role, u.role_saas, u.actif,
                   u.derniere_connexion, u.derniere_activite,
                   c.nom AS cabinet_nom,
                   GREATEST(COALESCE(u.derniere_activite,'1970-01-01'),
                            COALESCE(u.derniere_connexion,'1970-01-01')) AS last_seen,
                   (SELECT COUNT(*) FROM audit_logs a
                      WHERE a.user_id = u.id AND a.created_at >= :since) AS nb_actions
            FROM users u
            LEFT JOIN cabinets c ON c.id = u.cabinet_id
            $where
            ORDER BY last_seen DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = time();
        $count = ['online'=>0,'idle'=>0,'offline'=>0];
        $users = [];
        foreach ($rows as $r) {
            $ls = ($r['last_seen'] && $r['last_seen'] !== '1970-01-01 00:00:00')
                  ? strtotime($r['last_seen']) : 0;
            $delta = $ls ? $now - $ls : PHP_INT_MAX;
            if ($delta <= self::SEUIL_ONLINE)      { $statut = 'online';  $count['online']++; }
            elseif ($delta <= self::SEUIL_IDLE)    { $statut = 'idle';    $count['idle']++; }
            else                                   { $statut = 'offline'; $count['offline']++; }

            $users[] = [
                'id'         => (int)$r['id'],
                'nom'        => trim(($r['prenom'] ?? '').' '.($r['nom'] ?? '')) ?: 'Utilisateur',
                'email'      => $r['email'],
                'role'       => $r['role_saas'] === 'super_admin' ? 'Super-admin' : ucfirst($r['role'] ?? ''),
                'cabinet'    => $r['cabinet_nom'] ?? '—',
                'statut'     => $statut,
                'nb_actions' => (int)$r['nb_actions'],
                'last_seen'  => $ls ? $r['last_seen'] : null,
                'last_human' => $ls ? $this->tempsRelatif($delta) : 'jamais',
                'spark'      => $this->sparkline14j($db, (int)$r['id']),
            ];
        }

        $total = count($users);
        $stats = [
            'online'  => $count['online'],
            'idle'    => $count['idle'],
            'offline' => $count['offline'],
            'total'   => $total,
            'pct_online'  => $total ? round($count['online']*100/$total) : 0,
            'pct_idle'    => $total ? round($count['idle']*100/$total) : 0,
            'pct_offline' => $total ? round($count['offline']*100/$total) : 0,
        ];
        return ['users'=>$users, 'stats'=>$stats, 'periode'=>$periode];
    }

    /** Renvoie 14 entiers : nb d'actions par jour sur les 14 derniers jours. */
    private function sparkline14j(PDO $db, int $userId): array {
        static $stmt = null;
        if ($stmt === null) {
            $stmt = $db->prepare("
                SELECT DATE(created_at) d, COUNT(*) n
                FROM audit_logs
                WHERE user_id = ? AND created_at >= (CURDATE() - INTERVAL 13 DAY)
                GROUP BY DATE(created_at)
            ");
        }
        $stmt->execute([$userId]);
        $byDay = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $byDay[$r['d']] = (int)$r['n'];
        $out = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', time() - $i*86400);
            $out[] = $byDay[$day] ?? 0;
        }
        return $out;
    }

    private function tempsRelatif(int $sec): string {
        if ($sec < 60)    return "à l'instant";
        if ($sec < 3600)  return floor($sec/60).'min';
        if ($sec < 86400) return floor($sec/3600).'h';
        return floor($sec/86400).'j';
    }
}
