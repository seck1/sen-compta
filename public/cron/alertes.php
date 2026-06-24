<?php
/**
 * Script cron — Génération automatique des alertes fiscales et RH
 *
 * Exécution en ligne de commande :
 *   php /path/to/public/cron/alertes.php --token=VOTRE_TOKEN
 *
 * Exécution via URL (CronController) :
 *   https://votre-domaine/cron/alertes?token=VOTRE_TOKEN
 *
 * Entrée crontab (8h chaque jour) :
 *   0 8 * * * php /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/public/cron/alertes.php --token=VOTRE_TOKEN >> /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log 2>&1
 */

// ── Ce script n'est exécutable qu'en CLI ─────────────────────────────────────
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo json_encode(['erreur' => 'Accès refusé. Utilisez la route /cron/alertes pour un accès HTTP.']);
    exit(1);
}

define('CRON_START', microtime(true));

// ── Répertoires de base ───────────────────────────────────────────────────────
$rootDir = dirname(__DIR__, 2); // cabinet-smc/
$logFile = $rootDir . '/logs/cron.log';
$lockFile = sys_get_temp_dir() . '/cabinet-smc-cron-alertes.lock';

// ── Helpers de log ───────────────────────────────────────────────────────────
function cronLog(string $msg): void {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo $line; // aussi vers stdout (redirigé par crontab)
}

// ── Protection contre les exécutions parallèles ──────────────────────────────
if (file_exists($lockFile)) {
    $pid = (int)file_get_contents($lockFile);
    // Vérifie si le processus tourne encore (Unix uniquement)
    if ($pid > 0 && function_exists('posix_kill') && posix_kill($pid, 0)) {
        cronLog("SKIP — Cron déjà en cours (PID $pid). Abandon.");
        exit(0);
    }
    // Lock orphelin : on le supprime
    unlink($lockFile);
}
file_put_contents($lockFile, getmypid());

// ── Lecture du token depuis les arguments CLI ─────────────────────────────────
$token = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--token=')) {
        $token = substr($arg, strlen('--token='));
    }
}
// Fallback : variable d'environnement
if (!$token) {
    $token = getenv('CRON_TOKEN') ?: getenv('BACKUP_TOKEN') ?: null;
}

// ── Chargement de la config (définit BACKUP_TOKEN, getDB(), autoload) ────────
// On neutralise les vérifications HTTP qui ne fonctionnent pas en CLI
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['HTTPS']     = $_SERVER['HTTPS']     ?? '';

require_once $rootDir . '/config/app.php';

// ── Vérification du token ─────────────────────────────────────────────────────
$expectedToken = getenv('CRON_TOKEN') ?: BACKUP_TOKEN;

if (!$token || !hash_equals($expectedToken, $token)) {
    cronLog("ERREUR — Token invalide. Accès refusé.");
    unlink($lockFile);
    exit(1);
}

// ── Chargement des services ───────────────────────────────────────────────────
require_once $rootDir . '/src/Services/AlerteService.php';
require_once $rootDir . '/src/Services/NotificationService.php';

// ── Récupération de toutes les entreprises actives ────────────────────────────
try {
    $db = getDB();
} catch (\Exception $e) {
    cronLog("ERREUR BDD — " . $e->getMessage());
    unlink($lockFile);
    exit(1);
}

cronLog("=== Démarrage cron alertes fiscales ===");

// ── Récupération des entreprises actives avec leurs utilisateurs assignés ─────
$stmt = $db->query("
    SELECT DISTINCT e.id AS entreprise_id, e.nom,
           u.id AS user_id
    FROM entreprises e
    JOIN user_entreprises ue ON ue.entreprise_id = e.id AND ue.actif = 1
    JOIN users u ON u.id = ue.user_id AND u.actif = 1
    ORDER BY e.id, u.id
");
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupe par entreprise
$entreprises = [];
foreach ($lignes as $l) {
    $eid = $l['entreprise_id'];
    if (!isset($entreprises[$eid])) {
        $entreprises[$eid] = ['nom' => $l['nom'], 'users' => []];
    }
    $entreprises[$eid]['users'][] = (int)$l['user_id'];
}

// Ajouter les entreprises sans utilisateur assigné (pour les admins)
// Les admins (tous les users actifs) reçoivent les alertes
$stmtAdmins = $db->query("SELECT id FROM users WHERE actif=1 AND role IN ('admin','superviseur')");
$admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

// Entreprises sans affectation explicite → on les associe aux admins
$stmtEnt = $db->query("
    SELECT e.id, e.nom FROM entreprises e
    WHERE e.id NOT IN (SELECT DISTINCT entreprise_id FROM user_entreprises WHERE actif=1)
");
foreach ($stmtEnt->fetchAll(PDO::FETCH_ASSOC) as $ent) {
    $eid = $ent['id'];
    if (!isset($entreprises[$eid])) {
        $entreprises[$eid] = ['nom' => $ent['nom'], 'users' => $admins];
    }
}

if (empty($entreprises)) {
    cronLog("INFO — Aucune entreprise active trouvée.");
    unlink($lockFile);
    exit(0);
}

$nbEntreprises = 0;
$nbAlertes     = 0;

// ── Boucle principale ─────────────────────────────────────────────────────────
foreach ($entreprises as $entreprise_id => $info) {
    $nom   = $info['nom'];
    $users = $info['users'];

    if (empty($users)) {
        cronLog("  [$nom] Aucun utilisateur assigné — ignoré.");
        continue;
    }

    $nbEntreprises++;
    cronLog("  [$nom] Traitement pour " . count($users) . " utilisateur(s)...");

    foreach ($users as $user_id) {
        try {
            // Compte les notifications avant
            $avant = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$user_id")->fetchColumn();

            AlerteService::genererAlertes((int)$entreprise_id, $user_id);

            // Compte les nouvelles notifications créées
            $apres  = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$user_id")->fetchColumn();
            $nouvelles = $apres - $avant;
            $nbAlertes += $nouvelles;

            if ($nouvelles > 0) {
                cronLog("    → user #$user_id : $nouvelles alerte(s) générée(s)");
            }
        } catch (\Exception $e) {
            cronLog("    → ERREUR user #$user_id : " . $e->getMessage());
        }
    }
}

// ── Résumé ───────────────────────────────────────────────────────────────────
$duree = round(microtime(true) - CRON_START, 2);
cronLog("=== Terminé en {$duree}s — {$nbEntreprises} entreprise(s), {$nbAlertes} alerte(s) générée(s) ===");

unlink($lockFile);
exit(0);
