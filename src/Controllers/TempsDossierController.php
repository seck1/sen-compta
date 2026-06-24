<?php
require_once APP_ROOT . '/config/app.php';

class TempsDossierController {

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

        $mois    = (int)($_GET['mois'] ?? date('n'));
        $annee   = (int)($_GET['annee'] ?? date('Y'));
        $mois    = max(1, min(12, $mois));

        // Saisies du mois
        $stmt = $db->prepare("
            SELECT t.*, u.prenom, u.nom as user_nom
            FROM temps_dossier t
            JOIN users u ON u.id = t.user_id
            WHERE t.entreprise_id=? AND MONTH(t.date_travail)=? AND YEAR(t.date_travail)=?
            ORDER BY t.date_travail DESC, t.created_at DESC
        ");
        $stmt->execute([$id, $mois, $annee]);
        $saisies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totaux par collaborateur ce mois
        $stmt = $db->prepare("
            SELECT u.prenom, u.nom, SUM(t.duree_minutes) as total_min,
                   SUM(CASE WHEN t.facturable=1 THEN t.duree_minutes ELSE 0 END) as fact_min,
                   COUNT(*) as nb_saisies
            FROM temps_dossier t JOIN users u ON u.id=t.user_id
            WHERE t.entreprise_id=? AND MONTH(t.date_travail)=? AND YEAR(t.date_travail)=?
            GROUP BY t.user_id ORDER BY total_min DESC
        ");
        $stmt->execute([$id, $mois, $annee]);
        $par_collab = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totaux par catégorie ce mois
        $stmt = $db->prepare("
            SELECT categorie, SUM(duree_minutes) as total_min, COUNT(*) as nb
            FROM temps_dossier
            WHERE entreprise_id=? AND MONTH(date_travail)=? AND YEAR(date_travail)=?
            GROUP BY categorie ORDER BY total_min DESC
        ");
        $stmt->execute([$id, $mois, $annee]);
        $par_categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total général mois
        $total_minutes  = array_sum(array_column($saisies, 'duree_minutes'));
        $total_fact_min = array_sum(array_map(fn($s) => $s['facturable'] ? $s['duree_minutes'] : 0, $saisies));

        // Cumul annuel
        $stmt = $db->prepare("SELECT COALESCE(SUM(duree_minutes),0) FROM temps_dossier WHERE entreprise_id=? AND YEAR(date_travail)=?");
        $stmt->execute([$id, $annee]);
        $total_annee_min = (int)$stmt->fetchColumn();

        $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $pageTitle = 'Suivi du temps';
        $activeTab = 'temps';
        ob_start();
        require APP_ROOT . '/views/dossier/temps.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $date        = $_POST['date_travail'] ?? date('Y-m-d');
        $heures      = (int)($_POST['heures'] ?? 0);
        $minutes_sup = (int)($_POST['minutes'] ?? 0);
        $duree       = $heures * 60 + $minutes_sup;
        $categorie   = $_POST['categorie'] ?? 'saisie';
        $description = trim($_POST['description'] ?? '');
        $facturable  = isset($_POST['facturable']) ? 1 : 0;

        $allowed = ['saisie','revision','declaration','reunion','rapport','autre'];
        if (!in_array($categorie, $allowed)) $categorie = 'saisie';
        if ($duree <= 0) redirect("/dossier/temps?id=$id&error=duree");

        $stmt = $db->prepare("INSERT INTO temps_dossier (entreprise_id, user_id, date_travail, duree_minutes, categorie, description, facturable) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$id, auth()['id'], $date, $duree, $categorie, $description, $facturable]);

        redirect("/dossier/temps?id=$id&ok=1");
    }

    public function supprimer(): void {
        requireAuth();
        $id      = (int)($_POST['entreprise_id'] ?? 0);
        $saisie_id = (int)($_POST['saisie_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $where = isAdmin() ? "id=? AND entreprise_id=?" : "id=? AND entreprise_id=? AND user_id=".auth()['id'];
        $db->prepare("DELETE FROM temps_dossier WHERE $where")->execute([$saisie_id, $id]);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function marquerFacture(): void {
        requireAuth();
        if (!isSuperviseur()) { echo json_encode(['ok'=>false]); return; }
        $id      = (int)($_POST['entreprise_id'] ?? 0);
        $saisie_id = (int)($_POST['saisie_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $db->prepare("UPDATE temps_dossier SET facture=1 WHERE id=? AND entreprise_id=?")->execute([$saisie_id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    // API : total heures non facturées pour un dossier (utilisé dans honoraires)
    public function totalNonFacture(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $stmt = $db->prepare("SELECT COALESCE(SUM(duree_minutes),0) FROM temps_dossier WHERE entreprise_id=? AND facturable=1 AND facture=0");
        $stmt->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['minutes' => (int)$stmt->fetchColumn()]);
    }
}
