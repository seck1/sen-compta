<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Gestion des acces du Portail Client — COTE CABINET.
 * Le cabinet cree/gere les comptes clients lies a un dossier (entreprise).
 */
class PortailAdminController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) { redirect('/dashboard'); }
        $stmt = getDB()->prepare("SELECT * FROM entreprises WHERE id = ?");
        $stmt->execute([$id]);
        $ent = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ent) { http_response_code(404); echo "Dossier introuvable"; exit; }
        return $ent;
    }

    // Liste des acces client d'un dossier + pieces deposees
    public function index(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();

        $clients = $db->prepare("SELECT * FROM portail_clients WHERE entreprise_id = ? ORDER BY created_at DESC");
        $clients->execute([$id]);
        $clients = $clients->fetchAll(PDO::FETCH_ASSOC);

        $depots = $db->prepare("SELECT d.*, c.nom AS client_nom FROM portail_depots d JOIN portail_clients c ON c.id = d.client_id WHERE d.entreprise_id = ? ORDER BY d.created_at DESC LIMIT 50");
        $depots->execute([$id]);
        $depots = $depots->fetchAll(PDO::FETCH_ASSOC);

        $message = $_GET['message'] ?? null;
        $error   = $_GET['error'] ?? null;
        $pageTitle = 'Portail client';
        $activeTab = 'portail';
        ob_start();
        require APP_ROOT . '/views/dossier/portail.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // Creer un acces client
    public function creer(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect("/dossier/portail?id=$id");
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $nom   = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pwd   = $_POST['password'] ?? '';
        if (!$nom || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pwd) < 8) {
            redirect("/dossier/portail?id=$id&error=champs");
        }

        $db = getDB();
        // email unique
        $chk = $db->prepare("SELECT COUNT(*) FROM portail_clients WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn()) redirect("/dossier/portail?id=$id&error=email");

        $db->prepare("INSERT INTO portail_clients (entreprise_id, nom, email, password, telephone, voir_etats, voir_honoraires, permet_depot, cree_par)
                      VALUES (?,?,?,?,?,?,?,?,?)")
           ->execute([
               $id, $nom, $email, password_hash($pwd, PASSWORD_BCRYPT),
               trim($_POST['telephone'] ?? '') ?: null,
               isset($_POST['voir_etats']) ? 1 : 0,
               isset($_POST['voir_honoraires']) ? 1 : 0,
               isset($_POST['permet_depot']) ? 1 : 0,
               auth()['id'] ?? null,
           ]);
        redirect("/dossier/portail?id=$id&message=cree");
    }

    // Activer/desactiver ou mettre a jour les permissions
    public function update(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect("/dossier/portail?id=$id");
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $clientId = (int)($_POST['client_id'] ?? 0);
        $action   = $_POST['action'] ?? '';
        $db = getDB();

        // securite : le client doit appartenir a CE dossier
        $own = $db->prepare("SELECT COUNT(*) FROM portail_clients WHERE id = ? AND entreprise_id = ?");
        $own->execute([$clientId, $id]);
        if (!$own->fetchColumn()) redirect("/dossier/portail?id=$id&error=introuvable");

        if ($action === 'toggle') {
            $db->prepare("UPDATE portail_clients SET actif = 1 - actif WHERE id = ?")->execute([$clientId]);
        } elseif ($action === 'permissions') {
            $db->prepare("UPDATE portail_clients SET voir_etats=?, voir_honoraires=?, permet_depot=? WHERE id=?")
               ->execute([
                   isset($_POST['voir_etats']) ? 1 : 0,
                   isset($_POST['voir_honoraires']) ? 1 : 0,
                   isset($_POST['permet_depot']) ? 1 : 0,
                   $clientId,
               ]);
        } elseif ($action === 'reset_password') {
            $new = $_POST['new_password'] ?? '';
            if (strlen($new) >= 8) {
                $db->prepare("UPDATE portail_clients SET password=? WHERE id=?")
                   ->execute([password_hash($new, PASSWORD_BCRYPT), $clientId]);
            }
        }
        redirect("/dossier/portail?id=$id&message=maj");
    }

    // Traiter une piece deposee (valider/rejeter)
    public function traiterDepot(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect("/dossier/portail?id=$id");
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $depotId = (int)($_POST['depot_id'] ?? 0);
        $statut  = in_array($_POST['statut'] ?? '', ['traite','rejete'], true) ? $_POST['statut'] : 'traite';
        $db = getDB();
        $db->prepare("UPDATE portail_depots SET statut=?, note_cabinet=? WHERE id=? AND entreprise_id=?")
           ->execute([$statut, trim($_POST['note'] ?? '') ?: null, $depotId, $id]);
        redirect("/dossier/portail?id=$id&message=depot");
    }
}
