<?php
require_once APP_ROOT . '/config/app.php';

class NotificationsEmailController {

    public function index(): void {
        requireAuth();
        if (!isAdmin()) redirect('/dashboard');
        $db = getDB();

        // Récupérer tous les dossiers actifs
        $stmt = $db->query("SELECT * FROM entreprises WHERE statut='actif' ORDER BY raison_sociale");
        $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Collaborateurs avec email
        $stmt = $db->query("SELECT id, prenom, nom, email, role FROM users WHERE actif=1 AND email IS NOT NULL AND email!='' ORDER BY prenom");
        $collaborateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Alertes TVA en retard par dossier
        $alertes_tva = [];
        $moisPrec = date('n') == 1 ? 12 : date('n') - 1;
        $anneePrec = date('n') == 1 ? (int)date('Y') - 1 : (int)date('Y');
        foreach ($entreprises as $ent) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM declarations_tva WHERE entreprise_id=? AND periode_mois=? AND periode_annee=?");
            $stmt->execute([$ent['id'], $moisPrec, $anneePrec]);
            if ((int)$stmt->fetchColumn() === 0) {
                // Vérifier s'il y a eu des mouvements TVA ce mois
                $stmt2 = $db->prepare("SELECT COUNT(*) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND MONTH(e.date_ecriture)=? AND YEAR(e.date_ecriture)=? AND (c.numero LIKE '4431%' OR c.numero LIKE '4441%')");
                $stmt2->execute([$ent['id'], $moisPrec, $anneePrec]);
                if ((int)$stmt2->fetchColumn() > 0) {
                    $alertes_tva[] = $ent;
                }
            }
        }

        // Échéances fiscales proches (calendrier)
        $stmt = $db->prepare("
            SELECT ef.*, e.raison_sociale, e.id as entreprise_id
            FROM echeances_fiscales ef
            JOIN entreprises e ON e.id = ef.entreprise_id
            WHERE ef.statut = 'a_venir' AND ef.date_echeance <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
            ORDER BY ef.date_echeance ASC
        ");
        $stmt->execute();
        $echeances_proches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Écritures en brouillon
        $brouillons = [];
        foreach ($entreprises as $ent) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND statut='brouillon'");
            $stmt->execute([$ent['id']]);
            $nb = (int)$stmt->fetchColumn();
            if ($nb > 0) $brouillons[] = array_merge($ent, ['nb_brouillons' => $nb]);
        }

        $message = $_GET['message'] ?? '';

        $pageTitle = 'Notifications email';
        $activeTab = 'notifications-email';
        ob_start();
        require APP_ROOT . '/views/notifications-email.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function envoyer(): void {
        requireAuth();
        if (!isAdmin()) { echo json_encode(['ok'=>false,'error'=>'Accès refusé']); return; }
        $db = getDB();

        $type      = $_POST['type'] ?? '';
        $user_ids  = array_map('intval', $_POST['user_ids'] ?? []);
        $ent_ids   = array_map('intval', $_POST['entreprise_ids'] ?? []);

        if (empty($user_ids)) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Aucun destinataire sélectionné']);
            return;
        }

        $stmt = $db->prepare("SELECT prenom, nom, email FROM users WHERE id=? AND email!=''");
        $nb_ok = 0; $nb_err = 0;

        if ($type === 'tva') {
            $moisPrec  = date('n') == 1 ? 12 : date('n') - 1;
            $anneePrec = date('n') == 1 ? (int)date('Y') - 1 : (int)date('Y');
            $stmtEnt   = $db->prepare("SELECT raison_sociale FROM entreprises WHERE id=?");

            foreach ($user_ids as $uid) {
                $stmt->execute([$uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user) continue;
                foreach ($ent_ids as $eid) {
                    $stmtEnt->execute([$eid]);
                    $ent = $stmtEnt->fetchColumn();
                    $ok = mailNotificationTVA($user['email'], $user['prenom'].' '.$user['nom'], $ent, $moisPrec, $anneePrec);
                    $ok ? $nb_ok++ : $nb_err++;
                }
            }
        } elseif ($type === 'brouillon') {
            $stmtEnt = $db->prepare("SELECT raison_sociale FROM entreprises WHERE id=?");
            foreach ($user_ids as $uid) {
                $stmt->execute([$uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user) continue;
                foreach ($ent_ids as $eid) {
                    $stmtEnt->execute([$eid]);
                    $ent = $stmtEnt->fetchColumn();
                    $ok = sendMail(
                        $user['email'],
                        $user['prenom'].' '.$user['nom'],
                        "Écritures en brouillon — $ent",
                        "<p>Bonjour <strong>{$user['prenom']}</strong>,</p><p>Des écritures en brouillon nécessitent votre validation pour le dossier <strong>$ent</strong>.</p><p><a href='".APP_URL."/dossier/ecritures?id=$eid'>Accéder aux écritures →</a></p>"
                    );
                    $ok ? $nb_ok++ : $nb_err++;
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['ok'=>true,'nb_ok'=>$nb_ok,'nb_err'=>$nb_err]);
    }
}
