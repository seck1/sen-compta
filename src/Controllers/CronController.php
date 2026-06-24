<?php
/**
 * CronController — Point d'entrée HTTP sécurisé pour le cron d'alertes fiscales.
 *
 * Route : GET /cron/alertes?token=VOTRE_TOKEN
 * Header alternatif : X-Cron-Token: VOTRE_TOKEN
 *
 * Retourne du JSON, pas de session nécessaire.
 */
class CronController {

    public function alertes(): void {
        // ── Pas de session, pas de CSRF — auth par token uniquement ────────
        // Lecture du token (GET ou header HTTP)
        $token = $_GET['token']
            ?? $_SERVER['HTTP_X_CRON_TOKEN']
            ?? null;

        $expectedToken = getenv('CRON_TOKEN') ?: BACKUP_TOKEN;

        if (!$token || !hash_equals($expectedToken, $token)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'     => false,
                'erreur' => 'Token invalide ou manquant.',
            ]);
            return;
        }

        // ── Chargement des services ─────────────────────────────────────────
        require_once APP_ROOT . '/src/Services/AlerteService.php';
        require_once APP_ROOT . '/src/Services/NotificationService.php';

        $debut = microtime(true);

        try {
            $db = getDB();
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'erreur' => 'Connexion BDD échouée : ' . $e->getMessage()]);
            return;
        }

        // ── Entreprises actives + utilisateurs assignés ─────────────────────
        $stmt = $db->query("
            SELECT DISTINCT e.id AS entreprise_id, e.nom,
                   u.id AS user_id
            FROM entreprises e
            JOIN user_entreprises ue ON ue.entreprise_id = e.id AND ue.actif = 1
            JOIN users u ON u.id = ue.user_id AND u.actif = 1
            ORDER BY e.id, u.id
        ");
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regroupement par entreprise
        $entreprises = [];
        foreach ($lignes as $l) {
            $eid = $l['entreprise_id'];
            if (!isset($entreprises[$eid])) {
                $entreprises[$eid] = ['nom' => $l['nom'], 'users' => []];
            }
            $entreprises[$eid]['users'][] = (int)$l['user_id'];
        }

        // Entreprises sans affectation → admins/superviseurs
        $admins = $db->query("SELECT id FROM users WHERE actif=1 AND role IN ('admin','superviseur')")
                     ->fetchAll(PDO::FETCH_COLUMN);

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

        // ── Génération des alertes ──────────────────────────────────────────
        $nbEntreprises = 0;
        $nbAlertes     = 0;
        $erreurs       = [];

        foreach ($entreprises as $entreprise_id => $info) {
            if (empty($info['users'])) continue;
            $nbEntreprises++;

            foreach ($info['users'] as $user_id) {
                try {
                    $avant = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$user_id")->fetchColumn();
                    AlerteService::genererAlertes((int)$entreprise_id, $user_id);
                    $apres      = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE user_id=$user_id")->fetchColumn();
                    $nbAlertes += max(0, $apres - $avant);
                } catch (\Exception $e) {
                    $erreurs[] = "Entreprise #{$entreprise_id} / user #$user_id : " . $e->getMessage();
                }
            }
        }

        // ── Log fichier ─────────────────────────────────────────────────────
        $logFile = APP_ROOT . '/logs/cron.log';
        if (!is_dir(dirname($logFile))) {
            @mkdir(dirname($logFile), 0755, true);
        }
        $duree = round(microtime(true) - $debut, 2);
        $ligne = '[' . date('Y-m-d H:i:s') . '] [HTTP] '
               . "{$nbEntreprises} entreprise(s), {$nbAlertes} alerte(s) en {$duree}s"
               . (count($erreurs) ? ' | ERREURS: ' . implode('; ', $erreurs) : '')
               . PHP_EOL;
        @file_put_contents($logFile, $ligne, FILE_APPEND | LOCK_EX);

        // ── Réponse JSON ────────────────────────────────────────────────────
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'                    => true,
            'nb_alertes_generees'   => $nbAlertes,
            'nb_entreprises_traitees' => $nbEntreprises,
            'duree_secondes'        => $duree,
            'erreurs'               => $erreurs,
            'timestamp'             => date('c'),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
