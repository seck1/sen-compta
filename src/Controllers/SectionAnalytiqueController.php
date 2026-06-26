<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Comptabilité analytique — gestion des sections analytiques
 * (centres de coûts / activités / projets) d'un dossier.
 *
 * Calqué sur TiersController : index (liste + formulaire), store (create/update),
 * supprimer (soft delete), json (pour la saisie d'écriture).
 */
class SectionAnalytiqueController {

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

        // Section en cours d'édition ?
        $edit = null;
        $editId = (int)($_GET['edit'] ?? 0);
        if ($editId) {
            $stmt = $db->prepare("SELECT * FROM sections_analytiques WHERE id=? AND entreprise_id=?");
            $stmt->execute([$editId, $id]);
            $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // Liste des sections actives + nombre de lignes ventilées sur chacune
        $stmt = $db->prepare("
            SELECT s.*,
                   (SELECT COUNT(*) FROM lignes_ecritures l WHERE l.section_analytique_id = s.id) AS nb_lignes
            FROM sections_analytiques s
            WHERE s.entreprise_id = ? AND s.actif = 1
            ORDER BY s.code ASC");
        $stmt->execute([$id]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activeTab = 'sections-analytiques';
        $pageTitle = 'Sections analytiques';
        ob_start();
        require APP_ROOT . '/views/dossier/sections-analytiques.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        $code      = strtoupper(trim($_POST['code'] ?? ''));
        $libelle   = trim($_POST['libelle'] ?? '');
        $desc      = trim($_POST['description'] ?? '');

        if ($code === '' || $libelle === '') {
            $_SESSION['flash_error'] = "Le code et le libellé sont obligatoires.";
            redirect('/dossier/sections-analytiques?id=' . $id);
        }

        try {
            if ($sectionId) {
                $stmt = $db->prepare("UPDATE sections_analytiques SET code=?, libelle=?, description=? WHERE id=? AND entreprise_id=?");
                $stmt->execute([$code, $libelle, $desc ?: null, $sectionId, $id]);
                $_SESSION['flash_success'] = "Section « $code » modifiée.";
            } else {
                $stmt = $db->prepare("INSERT INTO sections_analytiques (entreprise_id, code, libelle, description) VALUES (?,?,?,?)");
                $stmt->execute([$id, $code, $libelle, $desc ?: null]);
                $_SESSION['flash_success'] = "Section « $code » créée.";
            }
        } catch (\PDOException $e) {
            // Violation de la contrainte d'unicité (entreprise_id, code)
            if ($e->getCode() === '23000') {
                $_SESSION['flash_error'] = "Le code « $code » existe déjà.";
            } else {
                $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
            }
        }

        redirect('/dossier/sections-analytiques?id=' . $id);
    }

    public function supprimer(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        // Soft delete : on archive (les lignes déjà ventilées gardent leur référence).
        $db->prepare("UPDATE sections_analytiques SET actif=0 WHERE id=? AND entreprise_id=?")
           ->execute([$sectionId, $id]);

        $_SESSION['flash_success'] = "Section archivée.";
        redirect('/dossier/sections-analytiques?id=' . $id);
    }

    /** Liste JSON des sections actives (pour le select de saisie d'écriture). */
    public function json(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $stmt = $db->prepare("SELECT id, code, libelle FROM sections_analytiques WHERE entreprise_id=? AND actif=1 ORDER BY code");
        $stmt->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
