<?php
class NotificationService {

    public static function creer(int $userId, string $titre, string $message, string $type = 'info', ?string $lien = null): void {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO notifications (user_id, titre, message, type, lien) VALUES (?,?,?,?,?)");
            $stmt->execute([$userId, $titre, $message, $type, $lien]);
        } catch (\Exception $e) {}
    }

    public static function creerPourTous(string $titre, string $message, string $type = 'info', ?string $lien = null): void {
        try {
            $db = getDB();
            $users = $db->query("SELECT id FROM users WHERE actif=1")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($users as $uid) {
                self::creer($uid, $titre, $message, $type, $lien);
            }
        } catch (\Exception $e) {}
    }

    public static function getNonLues(int $userId): array {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? AND lu=0 ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) { return []; }
    }

    public static function countNonLues(int $userId): int {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND lu=0");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) { return 0; }
    }

    public static function marquerLues(int $userId): void {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE notifications SET lu=1 WHERE user_id=?");
            $stmt->execute([$userId]);
        } catch (\Exception $e) {}
    }

    public static function log(int $userId, string $action, ?int $entrepriseId = null, ?string $table = null, ?int $recordId = null, ?string $details = null): void {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, entreprise_id, action, table_cible, enregistrement_id, details, ip_address) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$userId, $entrepriseId, $action, $table, $recordId, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
        } catch (\Exception $e) {}
    }
}
