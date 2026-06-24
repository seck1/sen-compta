<?php
require_once APP_ROOT . '/config/app.php';

class PlanningController {

    public function index(): void {
        requireAuth();
        $db = getDB();
        $u = auth();

        $filtre_statut = $_GET['statut'] ?? '';
        $filtre_user   = (int)($_GET['user_id'] ?? 0);
        $filtre_type   = $_GET['type'] ?? '';

        $cabinetId = $u['cabinet_id'] ?? null;

        $sql = "SELECT m.*, e.raison_sociale, u.nom, u.prenom,
                       DATEDIFF(m.date_fin_prevue, CURDATE()) as jours_restants
                FROM missions m
                JOIN entreprises e ON e.id = m.entreprise_id
                JOIN users u ON u.id = m.user_id
                WHERE 1=1";
        $params = [];

        if ($cabinetId) { $sql .= " AND e.cabinet_id = ?"; $params[] = $cabinetId; }

        if (!isSuperviseur()) {
            $sql .= " AND m.user_id = ?";
            $params[] = $u['id'];
        } elseif ($filtre_user) {
            $sql .= " AND m.user_id = ?";
            $params[] = $filtre_user;
        }
        if ($filtre_statut) { $sql .= " AND m.statut = ?"; $params[] = $filtre_statut; }
        if ($filtre_type)   { $sql .= " AND m.type = ?"; $params[] = $filtre_type; }
        $sql .= " ORDER BY m.date_fin_prevue ASC, m.statut ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Superviseur et admin filtrent par collaborateur du même cabinet
        if (isSuperviseur()) {
            if ($cabinetId) {
                $uStmt = $db->prepare("SELECT id, nom, prenom FROM users WHERE actif=1 AND cabinet_id = ? ORDER BY nom");
                $uStmt->execute([$cabinetId]);
                $users = $uStmt->fetchAll();
            } else {
                $users = $db->query("SELECT id, nom, prenom FROM users WHERE actif=1 ORDER BY nom")->fetchAll();
            }
        } else {
            $users = [];
        }

        // Stats
        $stats = [
            'en_cours'  => count(array_filter($missions, fn($m) => $m['statut'] === 'en_cours')),
            'planifiee' => count(array_filter($missions, fn($m) => $m['statut'] === 'planifiee')),
            'retard'    => count(array_filter($missions, fn($m) => in_array($m['statut'], ['planifiee','en_cours']) && ($m['jours_restants'] ?? 1) < 0)),
            'termine'   => count(array_filter($missions, fn($m) => $m['statut'] === 'terminee')),
        ];

        ob_start();
        require APP_ROOT . '/views/planning/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Planning missions';
        $activePage = 'planning';
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function creer(): void {
        requireAuth();
        $db = getDB();
        $cabinetId = auth()['cabinet_id'] ?? null;
        if ($cabinetId) {
            $eStmt = $db->prepare("SELECT id, raison_sociale FROM entreprises WHERE statut='actif' AND cabinet_id = ? ORDER BY raison_sociale");
            $eStmt->execute([$cabinetId]);
            $entreprises = $eStmt->fetchAll();
            $uStmt = $db->prepare("SELECT id, nom, prenom FROM users WHERE actif=1 AND cabinet_id = ? ORDER BY nom");
            $uStmt->execute([$cabinetId]);
            $users = $uStmt->fetchAll();
        } else {
            $entreprises = $db->query("SELECT id, raison_sociale FROM entreprises WHERE statut='actif' ORDER BY raison_sociale")->fetchAll();
            $users = $db->query("SELECT id, nom, prenom FROM users WHERE actif=1 ORDER BY nom")->fetchAll();
        }

        ob_start();
        require APP_ROOT . '/views/planning/creer.php';
        $content = ob_get_clean();
        $pageTitle = 'Nouvelle mission';
        $activePage = 'planning';
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function store(): void {
        requireAuth();
        $db = getDB();
        $u = auth();

        $prefix = 'MIS-' . date('Ym') . '-';
        $last = $db->query("SELECT MAX(SUBSTRING(reference, " . (strlen($prefix)+1) . ")) FROM missions WHERE reference LIKE '$prefix%'")->fetchColumn();
        $num = str_pad(((int)$last) + 1, 3, '0', STR_PAD_LEFT);
        $ref = $prefix . $num;

        $stmt = $db->prepare("INSERT INTO missions (entreprise_id, user_id, reference, libelle, type, date_debut, date_fin_prevue, budget_heures, taux_horaire, montant_forfait, statut, note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$_POST['entreprise_id'],
            (int)($_POST['user_id'] ?? $u['id']),
            $ref,
            $_POST['libelle'],
            $_POST['type'] ?? 'comptabilite',
            $_POST['date_debut'],
            $_POST['date_fin_prevue'] ?: null,
            $_POST['budget_heures'] ?: null,
            $_POST['taux_horaire'] ?: 0,
            $_POST['montant_forfait'] ?: null,
            $_POST['statut'] ?? 'planifiee',
            $_POST['note'] ?? null,
        ]);

        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log($u['id'], 'MISSION_CREE', (int)$_POST['entreprise_id'], 'missions', $db->lastInsertId(), $_POST['libelle']);

        redirect('/planning');
    }

    public function updateStatut(): void {
        requireAuth();
        $id     = (int)($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $db = getDB();
        $stmt = $db->prepare("UPDATE missions SET statut=?, date_fin_reelle=CASE WHEN ? IN ('terminee','facturee') THEN CURDATE() ELSE date_fin_reelle END WHERE id=?");
        $stmt->execute([$statut, $statut, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
