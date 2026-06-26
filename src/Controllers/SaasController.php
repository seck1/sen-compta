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

        // Créer le user admin du cabinet — INACTIF tant que l'email n'est pas vérifié
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userIns = $db->prepare("INSERT INTO users (cabinet_id, nom, prenom, email, password, role, role_saas, actif, email_verifie)
                                 VALUES (?,?,?,?,?,'admin','admin_cabinet',0,0)");
        $parts = explode(' ', $responsable, 2);
        $prenom = $parts[0];
        $nomUser = $parts[1] ?? '';
        $userIns->execute([$cabinetId, $nomUser, $prenom, $email, $hash]);
        $userId = (int)$db->lastInsertId();

        // Générer un code à 4 chiffres + l'enregistrer (valable 30 min)
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 1800);
        $db->prepare("INSERT INTO email_verifications (user_id, cabinet_id, email, code, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$userId, $cabinetId, $email, $code, $expires]);

        // Envoyer l'email de bienvenue avec le code + notifier le super-admin
        require_once APP_ROOT . '/config/mail.php';
        @mailVerificationCode($email, $nom, $code);
        $this->notifierAdminInscription($nom, $email, $responsable, $plan['nom'] ?? '');

        // Page de saisie du code
        $_SESSION['verif_user_id'] = $userId;
        $_SESSION['verif_email']   = $email;
        redirect('/inscription/verifier');
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

        $stmt = $db->prepare("SELECT * FROM email_verifications WHERE user_id=? AND verified_at IS NULL ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $verif = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verif) { $_SESSION['verif_error'] = "Aucun code en attente. Demandez-en un nouveau."; redirect('/inscription/verifier'); }
        if ((int)$verif['tentatives'] >= 6) { $_SESSION['verif_error'] = "Trop de tentatives. Demandez un nouveau code."; redirect('/inscription/verifier'); }
        if (strtotime($verif['expires_at']) < time()) { $_SESSION['verif_error'] = "Code expiré. Demandez un nouveau code."; redirect('/inscription/verifier'); }

        if ($code !== $verif['code']) {
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
        unset($_SESSION['verif_user_id'], $_SESSION['verif_email']);
        $_SESSION['inscription_success'] = "Email vérifié ! Votre compte est activé. Connectez-vous pour démarrer vos 14 jours d'essai.";
        redirect('/login');
    }

    /** L'utilisateur redemande un code depuis la page de vérification. */
    public function renvoyerCodeInscription(): void {
        $userId = (int)($_SESSION['verif_user_id'] ?? 0);
        if (!$userId) redirect('/inscription');
        $this->genererEtEnvoyerCode($userId);
        $_SESSION['verif_info'] = "Un nouveau code vous a été envoyé par email.";
        redirect('/inscription/verifier');
    }

    /** Génère un nouveau code pour un user et l'envoie par email (réutilisé par l'admin). */
    private function genererEtEnvoyerCode(int $userId): bool {
        $db = getDB();
        $u = $db->prepare("SELECT u.email, c.nom AS cabinet_nom, u.cabinet_id FROM users u LEFT JOIN cabinets c ON c.id=u.cabinet_id WHERE u.id=?");
        $u->execute([$userId]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + 1800);
        // invalider les anciens codes non utilisés
        $db->prepare("UPDATE email_verifications SET verified_at=NOW() WHERE user_id=? AND verified_at IS NULL")->execute([$userId]);
        $db->prepare("INSERT INTO email_verifications (user_id, cabinet_id, email, code, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$userId, $row['cabinet_id'], $row['email'], $code, $expires]);
        require_once APP_ROOT . '/config/mail.php';
        return (bool)@mailVerificationCode($row['email'], $row['cabinet_nom'] ?? 'votre cabinet', $code);
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
        if ($userId && $this->genererEtEnvoyerCode($userId)) {
            $_SESSION['flash_success'] = "Code de vérification renvoyé.";
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
}
