<?php
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/src/Services/NotificationService.php';

class NotificationController {

    public function liste(): void {
        requireAuth();
        $u = auth();
        header('Content-Type: application/json');
        echo json_encode([
            'count' => NotificationService::countNonLues($u['id']),
            'items' => NotificationService::getNonLues($u['id']),
        ]);
    }

    public function marquerLues(): void {
        requireAuth();
        NotificationService::marquerLues(auth()['id']);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function page(): void {
        requireAuth();
        $db = getDB();
        $u = auth();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100");
        $stmt->execute([$u['id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marquer toutes comme lues
        NotificationService::marquerLues($u['id']);

        ob_start();
        require_once APP_ROOT . '/views/notifications/index.php';
        $content = ob_get_clean();
        $pageTitle = 'Notifications';
        $activePage = 'notifications';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function auditLog(): void {
        requireAdmin();
        $db = getDB();
        $cabinetId = auth()['cabinet_id'] ?? null;
        if ($cabinetId) {
            $stmt = $db->prepare("SELECT al.*, u.prenom, u.nom, e.raison_sociale FROM audit_logs al JOIN users u ON u.id=al.user_id LEFT JOIN entreprises e ON e.id=al.entreprise_id WHERE u.cabinet_id=? ORDER BY al.created_at DESC LIMIT 200");
            $stmt->execute([$cabinetId]);
        } else {
            $stmt = $db->query("SELECT al.*, u.prenom, u.nom, e.raison_sociale FROM audit_logs al JOIN users u ON u.id=al.user_id LEFT JOIN entreprises e ON e.id=al.entreprise_id ORDER BY al.created_at DESC LIMIT 200");
        }
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        require_once APP_ROOT . '/views/notifications/audit.php';
        $content = ob_get_clean();
        $pageTitle = 'Journal des actions';
        $activePage = 'audit';
        require_once APP_ROOT . '/views/layouts/main.php';
    }
}
