<?php
class BackupController {

    private string $backupDir;

    public function __construct() {
        $this->backupDir = APP_ROOT . '/backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    private function requireAdmin(): void {
        requireAuth();
        if (!isAdmin()) { redirect('/dashboard'); }
    }

    public function index(): void {
        $this->requireAdmin();

        $fichiers = [];
        foreach (glob($this->backupDir . '/*.sql.gz') as $f) {
            $fichiers[] = [
                'nom'    => basename($f),
                'taille' => filesize($f),
                'date'   => filemtime($f),
            ];
        }
        // Aussi les .sql non compressés
        foreach (glob($this->backupDir . '/*.sql') as $f) {
            $fichiers[] = [
                'nom'    => basename($f),
                'taille' => filesize($f),
                'date'   => filemtime($f),
            ];
        }
        usort($fichiers, fn($a,$b) => $b['date'] - $a['date']);

        $pageTitle = 'Sauvegardes';
        $activePage = 'backups';
        ob_start();
        require APP_ROOT . '/views/admin/backups.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function creer(): void {
        $this->requireAdmin();

        $filename = 'backup_' . DB_NAME . '_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        $result = $this->exportSQL($filepath);

        if ($result['ok']) {
            // Log notification
            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'BACKUP_CREATED', null, 'backup', null, "Sauvegarde créée : $filename");
            redirect('/admin/backups?msg=ok&fichier=' . urlencode($filename));
        } else {
            redirect('/admin/backups?msg=erreur&detail=' . urlencode($result['error']));
        }
    }

    public function telecharger(): void {
        $this->requireAdmin();
        $nom = basename($_GET['fichier'] ?? '');
        if (!$nom || !preg_match('/^backup_[\w\-]+\.(sql|sql\.gz)$/', $nom)) {
            http_response_code(400); echo "Fichier invalide"; exit;
        }
        $path = $this->backupDir . '/' . $nom;
        if (!file_exists($path)) { http_response_code(404); echo "Fichier introuvable"; exit; }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function supprimer(): void {
        $this->requireAdmin();
        $nom = basename($_POST['fichier'] ?? '');
        if (!$nom || !preg_match('/^backup_[\w\-]+\.(sql|sql\.gz)$/', $nom)) {
            http_response_code(400); echo "Fichier invalide"; exit;
        }
        $path = $this->backupDir . '/' . $nom;
        if (file_exists($path)) { unlink($path); }
        redirect('/admin/backups?msg=supprime');
    }

    // Appelé par le cron automatique (script CLI ou endpoint protégé)
    public function auto(): void {
        // Sécurité : en CLI on autorise ; sinon il FAUT un token non vide ET egal au secret.
        // Un BACKUP_TOKEN vide/absent => endpoint desactive (jamais accessible via le web).
        if (PHP_SAPI !== 'cli') {
            $token  = $_GET['token'] ?? '';
            $secret = defined('BACKUP_TOKEN') ? BACKUP_TOKEN : '';
            if ($secret === '' || $token === '' || !hash_equals($secret, $token)) {
                http_response_code(403); echo "Accès refusé"; exit;
            }
        }

        $filename = 'backup_' . DB_NAME . '_' . date('Y-m-d_His') . '_auto.sql';
        $filepath = $this->backupDir . '/' . $filename;
        $result   = $this->exportSQL($filepath);

        // Rotation : garde les 30 derniers fichiers
        $this->rotation(30);

        header('Content-Type: application/json');
        echo json_encode($result + ['fichier' => $filename]);
        exit;
    }

    private function exportSQL(string $filepath): array {
        $tables = [];
        try {
            $db = getDB();
            $rows = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tables = $rows;
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $sql  = "-- Cabinet SMC — Sauvegarde base de données\n";
        $sql .= "-- Générée le " . date('d/m/Y H:i:s') . "\n";
        $sql .= "-- Base : " . DB_NAME . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Structure
            $create = $db->query("SHOW CREATE TABLE `$table`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create['Create Table'] . ";\n\n";

            // Données
            $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $cols = '`' . implode('`,`', array_keys($rows[0])) . '`';
                $sql .= "INSERT INTO `$table` ($cols) VALUES\n";
                $vals = [];
                foreach ($rows as $row) {
                    $escaped = array_map(function($v) use ($db) {
                        if ($v === null) return 'NULL';
                        return $db->quote($v);
                    }, array_values($row));
                    $vals[] = '(' . implode(',', $escaped) . ')';
                }
                $sql .= implode(",\n", $vals) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $written = file_put_contents($filepath, $sql);
        if ($written === false) {
            return ['ok' => false, 'error' => 'Impossible d\'écrire le fichier. Vérifiez les permissions du dossier /backups'];
        }

        return ['ok' => true, 'taille' => $written];
    }

    private function rotation(int $max): void {
        $files = glob($this->backupDir . '/*.sql');
        if (count($files) <= $max) return;
        usort($files, fn($a,$b) => filemtime($a) - filemtime($b));
        $toDelete = array_slice($files, 0, count($files) - $max);
        foreach ($toDelete as $f) { unlink($f); }
    }
}
