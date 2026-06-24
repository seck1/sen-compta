<?php
require_once APP_ROOT . '/config/app.php';

class CommentaireController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    public function liste(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $stmt = $db->prepare("
            SELECT c.*, u.prenom, u.nom as user_nom, u.role
            FROM commentaires_dossier c
            JOIN users u ON u.id = c.user_id
            WHERE c.entreprise_id = ?
            ORDER BY c.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$id]);
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($commentaires);
    }

    public function store(): void {
        requireAuth();
        $id      = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $message  = trim($_POST['message'] ?? '');
        $type     = $_POST['type'] ?? 'note';
        $priorite = $_POST['priorite'] ?? 'normale';

        if (empty($message)) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Message vide']);
            return;
        }

        $allowed_types    = ['note', 'alerte', 'info', 'tache'];
        $allowed_priorites = ['normale', 'haute', 'urgente'];
        if (!in_array($type, $allowed_types))     $type     = 'note';
        if (!in_array($priorite, $allowed_priorites)) $priorite = 'normale';

        $stmt = $db->prepare("INSERT INTO commentaires_dossier (entreprise_id, user_id, message, type, priorite) VALUES (?,?,?,?,?)");
        $stmt->execute([$id, auth()['id'], $message, $type, $priorite]);
        $newId = $db->lastInsertId();

        $stmt = $db->prepare("SELECT c.*, u.prenom, u.nom as user_nom, u.role FROM commentaires_dossier c JOIN users u ON u.id=c.user_id WHERE c.id=?");
        $stmt->execute([$newId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'comment' => $comment]);
    }

    public function resoudre(): void {
        requireAuth();
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $db->prepare("UPDATE commentaires_dossier SET resolu=1 WHERE id=? AND entreprise_id=?")->execute([$comment_id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function supprimer(): void {
        requireAuth();
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $comment_id = (int)($_POST['comment_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        // Seul l'auteur ou un admin peut supprimer
        $where = isAdmin() ? "id=? AND entreprise_id=?" : "id=? AND entreprise_id=? AND user_id=".auth()['id'];
        $db->prepare("DELETE FROM commentaires_dossier WHERE $where")->execute([$comment_id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function nb_non_lus(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $stmt = $db->prepare("SELECT COUNT(*) FROM commentaires_dossier WHERE entreprise_id=? AND resolu=0 AND user_id != ?");
        $stmt->execute([$id, auth()['id']]);
        header('Content-Type: application/json');
        echo json_encode(['nb' => (int)$stmt->fetchColumn()]);
    }
}
