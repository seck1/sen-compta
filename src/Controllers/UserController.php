<?php
require_once APP_ROOT . '/config/app.php';

class UserController {

    public function index(): void {
        requireAdmin();
        $db        = getDB();
        $user      = auth();
        $cabinetId = $user['cabinet_id'] ?? null;

        if ($cabinetId) {
            $stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM user_entreprises ue WHERE ue.user_id = u.id AND ue.actif = 1) as nb_dossiers FROM users u WHERE u.cabinet_id = ? AND u.id != ? ORDER BY u.nom, u.prenom");
            $stmt->execute([$cabinetId, $user['id']]);
            $users = $stmt->fetchAll();
        } else {
            $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM user_entreprises ue WHERE ue.user_id = u.id AND ue.actif = 1) as nb_dossiers FROM users u WHERE u.role != 'admin' ORDER BY u.nom, u.prenom")->fetchAll();
        }

        ob_start();
        require_once APP_ROOT . '/views/users/index.php';
        $content = ob_get_clean();
        $pageTitle  = 'Collaborateurs';
        $activePage = 'users';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function create(): void {
        requireAdmin();
        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);
        $editMode = false;
        ob_start();
        require_once APP_ROOT . '/views/users/form.php';
        $content = ob_get_clean();
        $pageTitle  = 'Nouveau collaborateur';
        $activePage = 'users';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function store(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/users');

        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'collaborateur';

        if (!$nom || !$prenom || !$email || !$password) {
            $_SESSION['form_error'] = 'Tous les champs sont obligatoires.';
            redirect('/users/create');
        }

        // Politique mot de passe : 8 caractères min, 1 majuscule, 1 chiffre
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $_SESSION['form_error'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.';
            redirect('/users/create');
        }

        try {
            $telephone = trim($_POST['telephone'] ?? '') ?: null;
            $db        = getDB();
            $currentUser = auth();
            $cabinetId   = $currentUser['cabinet_id'] ?? null;
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (cabinet_id, nom, prenom, email, password, role, role_saas, telephone) VALUES (?,?,?,?,?,?,'collaborateur',?)");
            $stmt->execute([$cabinetId, $nom, $prenom, $email, $hash, $role, $telephone]);
            redirect('/users');
        } catch (Exception $e) {
            $_SESSION['form_error'] = 'Cet email est déjà utilisé.';
            redirect('/users/create');
        }
    }

    public function edit(): void {
        requireAdmin();
        $id   = (int)($_GET['id'] ?? 0);
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) redirect('/users');

        // Dossiers assignés
        $stmt2 = $db->prepare("SELECT ue.*, e.raison_sociale, e.couleur FROM user_entreprises ue JOIN entreprises e ON e.id = ue.entreprise_id WHERE ue.user_id = ?");
        $stmt2->execute([$id]);
        $assignments = $stmt2->fetchAll();

        // Tous les dossiers du cabinet pour assignation
        $currentUser = auth();
        $cabinetId   = $currentUser['cabinet_id'] ?? null;
        if ($cabinetId) {
            $eStmt = $db->prepare("SELECT * FROM entreprises WHERE statut='actif' AND cabinet_id = ? ORDER BY raison_sociale");
            $eStmt->execute([$cabinetId]);
            $entreprises = $eStmt->fetchAll();
        } else {
            $entreprises = $db->query("SELECT * FROM entreprises WHERE statut='actif' ORDER BY raison_sociale")->fetchAll();
        }

        // Stats d'activité
        $stats = [];

        // Nombre d'écritures saisies
        $s = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE user_id = ?");
        $s->execute([$id]);
        $stats['nb_ecritures'] = (int)$s->fetchColumn();

        // Nombre de missions
        $s = $db->prepare("SELECT COUNT(*) FROM missions WHERE user_id = ?");
        $s->execute([$id]);
        $stats['nb_missions'] = (int)$s->fetchColumn();

        // Nombre d'actions audit
        $s = $db->prepare("SELECT COUNT(*) FROM audit_logs WHERE user_id = ?");
        $s->execute([$id]);
        $stats['nb_actions'] = (int)$s->fetchColumn();

        // 10 dernières actions
        $s = $db->prepare("
            SELECT al.*, e.raison_sociale
            FROM audit_logs al
            LEFT JOIN entreprises e ON e.id = al.entreprise_id
            WHERE al.user_id = ?
            ORDER BY al.created_at DESC
            LIMIT 10
        ");
        $s->execute([$id]);
        $activites = $s->fetchAll();

        $error    = $_SESSION['form_error'] ?? null;
        $editMode = true;
        unset($_SESSION['form_error']);

        ob_start();
        require_once APP_ROOT . '/views/users/form.php';
        $content = ob_get_clean();
        $pageTitle  = 'Modifier — ' . $user['prenom'] . ' ' . $user['nom'];
        $activePage = 'users';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function update(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/users');
        $id = (int)($_POST['id'] ?? 0);

        $allowedRoles = ['admin', 'superviseur', 'collaborateur'];
        $role = $_POST['role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            $_SESSION['form_error'] = 'Rôle invalide.';
            redirect('/users/edit?id=' . $id);
        }

        $db   = getDB();
        $stmt = $db->prepare("UPDATE users SET nom=?, prenom=?, email=?, role=?, actif=?, telephone=? WHERE id=?");
        $stmt->execute([
            trim($_POST['nom'] ?? ''),
            trim($_POST['prenom'] ?? ''),
            trim($_POST['email'] ?? ''),
            $role,
            isset($_POST['actif']) ? 1 : 0,
            trim($_POST['telephone'] ?? '') ?: null,
            $id,
        ]);

        if (!empty($_POST['password'])) {
            $pwd = $_POST['password'];
            if (strlen($pwd) < 8 || !preg_match('/[A-Z]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
                $_SESSION['form_error'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.';
                redirect('/users/edit?id=' . $id);
            }
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($pwd, PASSWORD_BCRYPT), $id]);
        }

        redirect('/users');
    }

    public function delete(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/users');
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id'] ?? 0);
        getDB()->prepare("UPDATE users SET actif = 0 WHERE id = ?")->execute([$id]);
        redirect('/users');
    }

    public function assign(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/users');
        $userId       = (int)($_POST['user_id'] ?? 0);
        $entrepriseId = (int)($_POST['entreprise_id'] ?? 0);
        $role         = $_POST['role'] ?? 'saisie';

        $db   = getDB();
        $stmt = $db->prepare("INSERT INTO user_entreprises (user_id, entreprise_id, role) VALUES (?,?,?) ON DUPLICATE KEY UPDATE role=?, actif=1");
        $stmt->execute([$userId, $entrepriseId, $role, $role]);
        redirect('/users/edit?id=' . $userId);
    }
}
