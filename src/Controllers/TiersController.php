<?php
require_once APP_ROOT . '/config/app.php';

class TiersController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    public function index(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $filtre_type = $_GET['type'] ?? '';
        $filtre_q    = trim($_GET['q'] ?? '');

        // Stats globales (indépendantes du filtre)
        $stmtStats = $db->prepare("SELECT type, COUNT(*) as nb FROM tiers WHERE entreprise_id = ? AND actif = 1 GROUP BY type");
        $stmtStats->execute([$id]);
        $stats = ['fournisseur' => 0, 'client' => 0, 'les_deux' => 0];
        foreach ($stmtStats->fetchAll() as $row) $stats[$row['type']] = (int)$row['nb'];

        // Liste filtrée
        $sql = "SELECT * FROM tiers WHERE entreprise_id = ? AND actif = 1";
        $params = [$id];
        if ($filtre_type) { $sql .= " AND type = ?"; $params[] = $filtre_type; }
        if ($filtre_q)    { $sql .= " AND nom LIKE ?"; $params[] = '%'.$filtre_q.'%'; }
        $sql .= " ORDER BY nom";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require APP_ROOT . '/views/dossier/tiers/index.php';
        $content = ob_get_clean();
        $pageTitle = $filtre_type === 'client' ? 'Clients' : ($filtre_type === 'fournisseur' ? 'Fournisseurs' : 'Tiers');
        $activeTab = $filtre_type === 'client' ? 'clients' : ($filtre_type === 'fournisseur' ? 'fournisseurs' : 'fournisseurs');
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function form(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $tiers_id = (int)($_GET['tiers_id'] ?? 0);
        $tiers    = null;
        if ($tiers_id) {
            $stmt = $db->prepare("SELECT * FROM tiers WHERE id = ? AND entreprise_id = ?");
            $stmt->execute([$tiers_id, $id]);
            $tiers = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        ob_start();
        require APP_ROOT . '/views/dossier/tiers/form.php';
        $content = ob_get_clean();
        $pageTitle = $tiers ? 'Modifier tiers' : 'Nouveau tiers';
        $activeTab = 'tiers';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $tiers_id = (int)($_POST['tiers_id'] ?? 0);
        $nom      = trim($_POST['nom'] ?? '');
        $type     = $_POST['type'] ?? 'fournisseur';
        $ninea    = trim($_POST['ninea'] ?? '');
        $tel      = trim($_POST['telephone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $adresse  = trim($_POST['adresse'] ?? '');

        if (!$nom) redirect('/dossier/tiers?id='.$id);

        if ($tiers_id) {
            $stmt = $db->prepare("UPDATE tiers SET nom=?, type=?, ninea=?, telephone=?, email=?, adresse=? WHERE id=? AND entreprise_id=?");
            $stmt->execute([$nom, $type, $ninea ?: null, $tel ?: null, $email ?: null, $adresse ?: null, $tiers_id, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO tiers (entreprise_id, nom, type, ninea, telephone, email, adresse) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$id, $nom, $type, $ninea ?: null, $tel ?: null, $email ?: null, $adresse ?: null]);
        }

        redirect('/dossier/tiers?id='.$id);
    }

    public function voir(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $tiers_id   = (int)($_GET['tiers_id'] ?? 0);
        $filtre_ex  = $_GET['exercice'] ?? '';
        $date_debut = $_GET['date_debut'] ?? '';
        $date_fin   = $_GET['date_fin']   ?? '';

        $stmt = $db->prepare("SELECT * FROM tiers WHERE id = ? AND entreprise_id = ? AND actif = 1");
        $stmt->execute([$tiers_id, $id]);
        $tiers = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tiers) redirect('/dossier/tiers?id='.$id);

        // Exercices disponibles pour le filtre (tous les exercices du dossier)
        $stmtEx = $db->prepare("SELECT DISTINCT exercice as annee FROM ecritures WHERE entreprise_id=? UNION SELECT annee FROM exercices WHERE entreprise_id=? ORDER BY annee DESC");
        $stmtEx->execute([$id, $id]);
        $exercices_dispo = $stmtEx->fetchAll(PDO::FETCH_COLUMN);

        // Requête avec filtres optionnels
        $sql = "
            SELECT le.debit, le.credit, le.libelle,
                   e.date_ecriture, e.numero_piece, e.statut, e.libelle as libelle_ecriture,
                   e.exercice, j.code as journal_code, c.numero as numero_compte
            FROM lignes_ecritures le
            JOIN ecritures e ON e.id = le.ecriture_id
            JOIN journaux j ON j.id = e.journal_id
            JOIN comptes c ON c.id = le.compte_id
            WHERE e.entreprise_id = ?
              AND (le.tiers_id = ? OR le.tiers = ?)
        ";
        $params = [$id, $tiers_id, $tiers['nom']];

        if ($filtre_ex !== '') {
            $sql .= " AND e.exercice = ?";
            $params[] = (int)$filtre_ex;
        }
        if ($date_debut) {
            $sql .= " AND e.date_ecriture >= ?";
            $params[] = $date_debut;
        }
        if ($date_fin) {
            $sql .= " AND e.date_ecriture <= ?";
            $params[] = $date_fin;
        }

        $sql .= " ORDER BY e.date_ecriture DESC, e.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require APP_ROOT . '/views/dossier/tiers/voir.php';
        $content = ob_get_clean();
        $pageTitle = e($tiers['nom']);
        $activeTab = $tiers['type'] === 'client' ? 'clients' : 'fournisseurs';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function supprimer(): void {
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $tiers_id = (int)($_POST['tiers_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $db->prepare("UPDATE tiers SET actif=0 WHERE id=? AND entreprise_id=?")->execute([$tiers_id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function json(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if (!userHasAccess($id)) { echo json_encode([]); exit; }
        $db   = getDB();
        $type = $_GET['type'] ?? '';
        $sql  = "SELECT id, nom, type, ninea, telephone FROM tiers WHERE entreprise_id=? AND actif=1";
        $params = [$id];
        if ($type && $type !== 'tous') {
            $sql .= " AND (type=? OR type='les_deux')";
            $params[] = $type;
        }
        $sql .= " ORDER BY nom";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        header('Content-Type: application/json');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Création rapide d'un tiers en AJAX (depuis la saisie d'écriture / le scan IA). */
    public function quickCreate(): void {
        requireAuth();
        header('Content-Type: application/json');
        $id  = (int)($_POST['entreprise_id'] ?? 0);
        if (!userHasAccess($id)) { echo json_encode(['error' => 'Accès refusé']); exit; }
        $nom  = trim($_POST['nom'] ?? '');
        $type = $_POST['type'] ?? 'client';
        if (!in_array($type, ['client','fournisseur','les_deux'], true)) $type = 'client';
        if ($nom === '') { echo json_encode(['error' => 'Nom requis']); exit; }

        $db = getDB();
        // Réutiliser un tiers existant du même nom/type au lieu de dupliquer
        $chk = $db->prepare("SELECT id, nom, type FROM tiers WHERE entreprise_id=? AND nom=? AND actif=1 LIMIT 1");
        $chk->execute([$id, $nom]);
        if ($t = $chk->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['ok' => true, 'id' => (int)$t['id'], 'nom' => $t['nom'], 'type' => $t['type'], 'existant' => true]);
            exit;
        }
        $ins = $db->prepare("INSERT INTO tiers (entreprise_id, nom, type) VALUES (?,?,?)");
        $ins->execute([$id, $nom, $type]);
        echo json_encode(['ok' => true, 'id' => (int)$db->lastInsertId(), 'nom' => $nom, 'type' => $type]);
    }
}
