<?php
require_once APP_ROOT . '/config/app.php';

class DashboardController {

    public function index(): void {
        requireAuth();
        $db   = getDB();
        $user = auth();

        $cabinetId = $user['cabinet_id'] ?? null;
        $cabFilter = $cabinetId ? " AND cabinet_id = $cabinetId" : "";
        $cabFilterE = $cabinetId ? " AND e.cabinet_id = $cabinetId" : "";
        $cabFilterPlain = $cabinetId ? " AND cabinet_id = $cabinetId" : "";

        // Stats globales (admin) ou filtrées (collaborateur)
        if (isAdmin()) {
            $nbEntreprises = $db->query("SELECT COUNT(*) FROM entreprises WHERE statut = 'actif'$cabFilter")->fetchColumn();
            $nbUsers       = $db->query("SELECT COUNT(*) FROM users WHERE actif = 1 AND role != 'admin'$cabFilterPlain")->fetchColumn();
            $entreprises   = $db->query("SELECT e.*, (SELECT COUNT(*) FROM ecritures ec WHERE ec.entreprise_id=e.id) as nb_ecritures, (SELECT MAX(ec2.date_ecriture) FROM ecritures ec2 WHERE ec2.entreprise_id=e.id) as last_activity FROM entreprises e WHERE 1=1$cabFilterE ORDER BY e.raison_sociale")->fetchAll();

            // Honoraires du mois (admin) — filtrés par cabinet via entreprises
            $honMois = $db->query("SELECT COALESCE(SUM(h.montant_ht),0) FROM honoraires h JOIN entreprises e ON e.id=h.entreprise_id WHERE DATE_FORMAT(h.date_facture,'%Y-%m')='".date('Y-m')."'$cabFilterE")->fetchColumn();

            // Missions en cours
            $nbMissions = $db->query("SELECT COUNT(*) FROM missions m JOIN entreprises e ON e.id=m.entreprise_id WHERE m.statut='en_cours'$cabFilterE")->fetchColumn();

            // Dossiers sans activité depuis 30j
            $inactifs = $db->query("SELECT e.raison_sociale, MAX(ec.date_ecriture) as last FROM entreprises e LEFT JOIN ecritures ec ON ec.entreprise_id=e.id WHERE e.statut='actif'$cabFilterE GROUP BY e.id HAVING last IS NULL OR last < DATE_SUB(CURDATE(),INTERVAL 30 DAY) ORDER BY last ASC LIMIT 5")->fetchAll();

        } else {
            $stmtNb = $db->prepare("SELECT COUNT(*) FROM user_entreprises ue JOIN entreprises e ON e.id = ue.entreprise_id WHERE ue.user_id = ? AND ue.actif = 1 AND e.statut = 'actif'" . ($cabinetId ? " AND e.cabinet_id = $cabinetId" : ""));
            $stmtNb->execute([$user['id']]);
            $nbEntreprises = $stmtNb->fetchColumn();
            $nbUsers = null;
            $honMois = null;
            $nbMissions = null;
            $inactifs = [];

            $stmt = $db->prepare("SELECT e.*, (SELECT COUNT(*) FROM ecritures ec WHERE ec.entreprise_id=e.id) as nb_ecritures, (SELECT MAX(ec2.date_ecriture) FROM ecritures ec2 WHERE ec2.entreprise_id=e.id) as last_activity FROM entreprises e JOIN user_entreprises ue ON ue.entreprise_id = e.id WHERE ue.user_id = ? AND ue.actif = 1" . ($cabinetId ? " AND e.cabinet_id = $cabinetId" : "") . " ORDER BY e.raison_sociale");
            $stmt->execute([$user['id']]);
            $entreprises = $stmt->fetchAll();
        }

        // Bulletins en retard (tous dossiers)
        $bulletinsRetard = [];
        foreach ($entreprises as $ent) {
            $stmtEmp = $db->prepare("SELECT COUNT(*) FROM employes WHERE entreprise_id=? AND statut='actif'");
            $stmtEmp->execute([$ent['id']]);
            $nbEmp = (int)$stmtEmp->fetchColumn();
            if ($nbEmp > 0) {
                $stmtBul = $db->prepare("SELECT COUNT(*) FROM bulletins_paie WHERE entreprise_id=? AND CONCAT(periode_annee,'-',LPAD(periode_mois,2,'0'))=?");
                $stmtBul->execute([$ent['id'], date('Y-m')]);
                $nbBul = (int)$stmtBul->fetchColumn();
                if ($nbBul < $nbEmp) {
                    $bulletinsRetard[] = ['raison_sociale'=>$ent['raison_sociale'],'id'=>$ent['id'],'nb_emp'=>$nbEmp,'nb_bul'=>$nbBul];
                }
            }
        }

        // Écritures en brouillon par dossier
        $brouillons = [];
        foreach ($entreprises as $ent) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND statut='brouillon'");
            $stmt->execute([$ent['id']]);
            $nb = (int)$stmt->fetchColumn();
            if ($nb > 0) $brouillons[] = ['id'=>$ent['id'],'raison_sociale'=>$ent['raison_sociale'],'nb'=>$nb];
        }

        // TVA en retard (déclaration du mois précédent non faite)
        $tva_retard = [];
        $mois_tva = date('n') == 1 ? 12 : date('n') - 1;
        $annee_tva = date('n') == 1 ? date('Y') - 1 : date('Y');
        foreach ($entreprises as $ent) {
            // Vérifier si déclaration TVA existe pour le mois précédent
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM declarations_tva WHERE entreprise_id=? AND mois=? AND annee=?");
                $stmt->execute([$ent['id'], $mois_tva, $annee_tva]);
                $nb = (int)$stmt->fetchColumn();
                // Vérifier si l'entreprise a des mouvements TVA (4431/4441) ce mois
                $stmtTva = $db->prepare("SELECT COUNT(*) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND (c.numero LIKE '4431%' OR c.numero LIKE '4441%') AND MONTH(e.date_ecriture)=? AND YEAR(e.date_ecriture)=?");
                $stmtTva->execute([$ent['id'], $mois_tva, $annee_tva]);
                $hasTva = (int)$stmtTva->fetchColumn();
                if ($nb == 0 && $hasTva > 0) {
                    $tva_retard[] = ['id'=>$ent['id'],'raison_sociale'=>$ent['raison_sociale']];
                }
            } catch (\Exception $e) {}
        }

        // Exercices non clôturés depuis plus d'un an
        $exercices_ouverts = [];
        foreach ($entreprises as $ent) {
            $exCourant = (int)($ent['exercice_courant'] ?? date('Y'));
            if ($exCourant < date('Y') - 1) {
                $exercices_ouverts[] = ['id'=>$ent['id'],'raison_sociale'=>$ent['raison_sociale'],'exercice'=>$exCourant];
            }
        }

        try {
            $nbEcheances = $db->query("SELECT COUNT(*) FROM echeances ec JOIN entreprises e ON e.id=ec.entreprise_id WHERE ec.statut = 'en_retard'$cabFilterE")->fetchColumn();
        } catch (\Exception $e) {
            $nbEcheances = 0;
        }

        ob_start();
        require_once APP_ROOT . '/views/dashboard/index.php';
        $content = ob_get_clean();

        $pageTitle  = 'Tableau de bord';
        $activePage = 'dashboard';
        require_once APP_ROOT . '/views/layouts/main.php';
    }
}
