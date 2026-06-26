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

        // Créer le user admin du cabinet
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $userIns = $db->prepare("INSERT INTO users (cabinet_id, nom, prenom, email, password, role, role_saas, actif)
                                 VALUES (?,?,?,?,?,'admin','admin_cabinet',1)");
        $parts = explode(' ', $responsable, 2);
        $prenom = $parts[0];
        $nomUser = $parts[1] ?? '';
        $userIns->execute([$cabinetId, $nomUser, $prenom, $email, $hash]);

        $_SESSION['inscription_success'] = "Compte créé ! Vous avez 14 jours d'essai gratuit. Connectez-vous maintenant.";
        redirect('/login');
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
