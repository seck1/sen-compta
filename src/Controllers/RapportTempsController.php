<?php
require_once APP_ROOT . '/config/app.php';

class RapportTempsController {

    public function index(): void {
        requireAuth();
        if (!isSuperviseur()) redirect('/dashboard');
        $db = getDB();
        $cabinetId = auth()['cabinet_id'] ?? null;
        $cabJoin = $cabinetId ? " AND e.cabinet_id = $cabinetId" : "";
        $cabJoinU = $cabinetId ? " AND u.cabinet_id = $cabinetId" : "";

        $mois  = (int)($_GET['mois'] ?? date('n'));
        $annee = (int)($_GET['annee'] ?? date('Y'));
        $mois  = max(1, min(12, $mois));

        $stmt = $db->prepare("
            SELECT u.id as user_id, u.prenom, u.nom,
                   e.id as entreprise_id, e.raison_sociale, e.couleur,
                   SUM(t.duree_minutes) as total_min,
                   SUM(CASE WHEN t.facturable=1 THEN t.duree_minutes ELSE 0 END) as fact_min,
                   SUM(CASE WHEN t.facture=1 THEN t.duree_minutes ELSE 0 END) as facture_min,
                   COUNT(*) as nb_saisies
            FROM temps_dossier t
            JOIN users u ON u.id = t.user_id
            JOIN entreprises e ON e.id = t.entreprise_id
            WHERE MONTH(t.date_travail)=? AND YEAR(t.date_travail)=? $cabJoin $cabJoinU
            GROUP BY u.id, e.id
            ORDER BY u.nom, u.prenom, total_min DESC
        ");
        $stmt->execute([$mois, $annee]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
            SELECT u.id, u.prenom, u.nom,
                   SUM(t.duree_minutes) as total_min,
                   SUM(CASE WHEN t.facturable=1 THEN t.duree_minutes ELSE 0 END) as fact_min,
                   COUNT(DISTINCT t.entreprise_id) as nb_dossiers
            FROM temps_dossier t
            JOIN users u ON u.id = t.user_id
            JOIN entreprises e ON e.id = t.entreprise_id
            WHERE MONTH(t.date_travail)=? AND YEAR(t.date_travail)=? $cabJoin $cabJoinU
            GROUP BY u.id ORDER BY total_min DESC
        ");
        $stmt->execute([$mois, $annee]);
        $par_collab = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
            SELECT e.id, e.raison_sociale, e.couleur,
                   SUM(t.duree_minutes) as total_min,
                   SUM(CASE WHEN t.facturable=1 THEN t.duree_minutes ELSE 0 END) as fact_min,
                   COUNT(DISTINCT t.user_id) as nb_collabs
            FROM temps_dossier t
            JOIN entreprises e ON e.id = t.entreprise_id
            WHERE MONTH(t.date_travail)=? AND YEAR(t.date_travail)=? $cabJoin
            GROUP BY e.id ORDER BY total_min DESC
        ");
        $stmt->execute([$mois, $annee]);
        $par_dossier = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
            SELECT WEEK(t.date_travail, 1) as semaine,
                   SUM(t.duree_minutes) as total_min
            FROM temps_dossier t
            JOIN entreprises e ON e.id = t.entreprise_id
            WHERE MONTH(t.date_travail)=? AND YEAR(t.date_travail)=? $cabJoin
            GROUP BY semaine ORDER BY semaine
        ");
        $stmt->execute([$mois, $annee]);
        $par_semaine = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Grand total
        $total_min_global = array_sum(array_column($par_collab, 'total_min'));
        $total_fact_global = array_sum(array_column($par_collab, 'fact_min'));

        $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $activePage = 'rapport-temps';
        $pageTitle  = 'Rapport temps global';
        ob_start();
        require APP_ROOT . '/views/rapport-temps.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }
}
