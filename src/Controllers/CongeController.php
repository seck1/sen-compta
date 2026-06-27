<?php
require_once APP_ROOT . '/config/app.php';

class CongeController {

    private function getEntrepriseAccess(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    private function getParams(int $entreprise_id): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM parametres_conges WHERE entreprise_id=?");
        $stmt->execute([$entreprise_id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            return ['jours_par_mois'=>2.0,'plafond_report_n1'=>15,'expiration_report_mois'=>3,'calcul_automatique'=>1];
        }
        return $p;
    }

    public function index(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();

        $annee = (int)($_GET['annee'] ?? date('Y'));

        // Liste des congés avec infos employé
        $stmt = $db->prepare("
            SELECT c.*, e.nom, e.prenom, e.poste
            FROM conges c
            JOIN employes e ON e.id = c.employe_id
            WHERE c.entreprise_id = ?
            AND (YEAR(c.date_debut) = ? OR YEAR(c.date_fin) = ?)
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$id, $annee, $annee]);
        $conges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Soldes par employé
        $stmt = $db->prepare("
            SELECT s.*, e.nom, e.prenom, e.poste
            FROM soldes_conges s
            JOIN employes e ON e.id = s.employe_id
            WHERE s.entreprise_id = ? AND s.annee = ?
            ORDER BY e.nom, e.prenom
        ");
        $stmt->execute([$id, $annee]);
        $soldes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Employés sans solde cette année
        $stmt = $db->prepare("
            SELECT e.id, e.nom, e.prenom, e.poste
            FROM employes e
            WHERE e.entreprise_id = ? AND e.statut = 'actif'
            AND e.id NOT IN (SELECT employe_id FROM soldes_conges WHERE entreprise_id = ? AND annee = ?)
            ORDER BY e.nom
        ");
        $stmt->execute([$id, $id, $annee]);
        $employes_sans_solde = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tous les employés actifs pour le formulaire
        $stmt = $db->prepare("SELECT id, nom, prenom, poste FROM employes WHERE entreprise_id = ? AND statut = 'actif' ORDER BY nom");
        $stmt->execute([$id]);
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats rapides
        $en_attente = count(array_filter($conges, fn($c) => $c['statut'] === 'en_attente'));
        $approuves   = count(array_filter($conges, fn($c) => $c['statut'] === 'approuve'));

        $params_conges = $this->getParams($id);

        // Calcul automatique des soldes si activé
        if ($params_conges['calcul_automatique']) {
            foreach ($employes as $emp) {
                // Vérifier si solde existe déjà pour cet employé/année
                $stmtCheck = $db->prepare("SELECT id, jours_acquis FROM soldes_conges WHERE employe_id=? AND annee=? AND entreprise_id=?");
                $stmtCheck->execute([$emp['id'], $annee, $id]);
                $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if ($existing && (float)$existing['jours_acquis'] > 0) continue; // déjà défini manuellement avec une valeur

                // Calcul prorata selon date d'embauche
                $date_embauche = $emp['date_embauche'] ?? null;
                if (!$date_embauche) continue;
                $debut_calcul = max(
                    new DateTime("$annee-01-01"),
                    new DateTime($date_embauche)
                );
                $fin_calcul = new DateTime("$annee-12-31");
                if ($debut_calcul > $fin_calcul) continue;

                $mois_travailles = ($fin_calcul->format('Y') - $debut_calcul->format('Y')) * 12
                    + $fin_calcul->format('n') - $debut_calcul->format('n') + 1;
                $mois_travailles = min(12, max(0, $mois_travailles));
                $jours_acquis = round($mois_travailles * (float)$params_conges['jours_par_mois'], 1);

                // Report N-1 : jours restants de l'année précédente (plafonné)
                $stmtN1 = $db->prepare("SELECT jours_restants FROM soldes_conges WHERE employe_id=? AND annee=? AND entreprise_id=?");
                $stmtN1->execute([$emp['id'], $annee - 1, $id]);
                $restant_n1 = (float)($stmtN1->fetchColumn() ?: 0);
                $report_n1 = min($restant_n1, (float)$params_conges['plafond_report_n1']);

                $db->prepare("INSERT INTO soldes_conges (entreprise_id,employe_id,annee,jours_acquis,jours_reportes_n1,jours_pris)
                    VALUES (?,?,?,?,?,0)
                    ON DUPLICATE KEY UPDATE jours_acquis=VALUES(jours_acquis), jours_reportes_n1=VALUES(jours_reportes_n1)"
                )->execute([$id,$emp['id'],$annee,$jours_acquis,$report_n1]);
            }
            // Recharger les soldes
            $stmt = $db->prepare("SELECT s.*, e.nom, e.prenom, e.poste FROM soldes_conges s JOIN employes e ON e.id=s.employe_id WHERE s.entreprise_id=? AND s.annee=? ORDER BY e.nom, e.prenom");
            $stmt->execute([$id, $annee]);
            $soldes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT e.id, e.nom, e.prenom, e.poste FROM employes e WHERE e.entreprise_id=? AND e.statut='actif' AND e.id NOT IN (SELECT employe_id FROM soldes_conges WHERE entreprise_id=? AND annee=?) ORDER BY e.nom");
            $stmt->execute([$id, $id, $annee]);
            $employes_sans_solde = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageTitle = 'Gestion des congés';
        $activeTab = 'conges';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/conges.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function parametres(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        if (!isSuperviseur()) redirect("/dossier/rh/conges?id=$id");
        $params_conges = $this->getParams($id);
        $pageTitle = 'Paramètres congés';
        $activeTab = 'conges';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/conges-params.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeParametres(): void {
        requireAuth();
        if (!isSuperviseur()) redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();

        $jours_par_mois         = (float)($_POST['jours_par_mois'] ?? 2.0);
        $plafond_report_n1      = (float)($_POST['plafond_report_n1'] ?? 15);
        $expiration_report_mois = (int)($_POST['expiration_report_mois'] ?? 3);
        $calcul_automatique     = isset($_POST['calcul_automatique']) ? 1 : 0;

        $db->prepare("INSERT INTO parametres_conges (entreprise_id,jours_par_mois,plafond_report_n1,expiration_report_mois,calcul_automatique)
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE jours_par_mois=?,plafond_report_n1=?,expiration_report_mois=?,calcul_automatique=?")
           ->execute([$id,$jours_par_mois,$plafond_report_n1,$expiration_report_mois,$calcul_automatique,
                      $jours_par_mois,$plafond_report_n1,$expiration_report_mois,$calcul_automatique]);

        redirect("/dossier/rh/conges/parametres?id=$id&ok=1");
    }

    public function store(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();

        $employe_id  = (int)($_POST['employe_id'] ?? 0);
        $type        = $_POST['type_conge'] ?? 'conge_paye';
        $date_debut  = $_POST['date_debut'] ?? '';
        $date_fin    = $_POST['date_fin'] ?? '';
        $motif       = trim($_POST['motif'] ?? '');

        if (!$employe_id || !$date_debut || !$date_fin) redirect("/dossier/rh/conges?id=$id&error=1");

        // Calcul jours ouvrés (lun-sam)
        $d1 = new DateTime($date_debut);
        $d2 = new DateTime($date_fin);
        $nb_jours = 0;
        $cur = clone $d1;
        while ($cur <= $d2) {
            if ($cur->format('N') != 7) $nb_jours++; // exclude dimanche
            $cur->modify('+1 day');
        }

        $conge_id = (int)($_POST['conge_id'] ?? 0);
        if ($conge_id) {
            $stmt = $db->prepare("UPDATE conges SET employe_id=?,type_conge=?,date_debut=?,date_fin=?,nb_jours=?,motif=?,statut='en_attente' WHERE id=? AND entreprise_id=?");
            $stmt->execute([$employe_id,$type,$date_debut,$date_fin,$nb_jours,$motif,$conge_id,$id]);
        } else {
            $stmt = $db->prepare("INSERT INTO conges (entreprise_id,employe_id,type_conge,date_debut,date_fin,nb_jours,motif,demande_par) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$id,$employe_id,$type,$date_debut,$date_fin,$nb_jours,$motif,auth()['id']]);
        }
        redirect("/dossier/rh/conges?id=$id&ok=1");
    }

    public function traiter(): void {
        requireAuth();
        if (!isSuperviseur()) redirect('/dashboard');
        $id        = (int)($_POST['entreprise_id'] ?? 0);
        $conge_id  = (int)($_POST['conge_id'] ?? 0);
        $statut    = $_POST['statut'] ?? '';
        $commentaire = trim($_POST['commentaire'] ?? '');
        $this->getEntrepriseAccess($id);

        if (!in_array($statut, ['approuve','refuse','annule'])) redirect("/dossier/rh/conges?id=$id");

        $db = getDB();

        // Récupérer le congé
        $stmt = $db->prepare("SELECT * FROM conges WHERE id=? AND entreprise_id=?");
        $stmt->execute([$conge_id, $id]);
        $conge = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conge) redirect("/dossier/rh/conges?id=$id");

        $stmt = $db->prepare("UPDATE conges SET statut=?,traite_par=?,commentaire_rh=? WHERE id=? AND entreprise_id=?");
        $stmt->execute([$statut, auth()['id'], $commentaire, $conge_id, $id]);

        // Mettre à jour solde si congé payé approuvé
        if ($statut === 'approuve' && $conge['statut'] !== 'approuve' && $conge['type_conge'] === 'conge_paye') {
            $annee = date('Y', strtotime($conge['date_debut']));
            // Upsert solde
            $stmt = $db->prepare("INSERT INTO soldes_conges (entreprise_id,employe_id,annee,jours_acquis,jours_pris)
                VALUES (?,?,?,0,?) ON DUPLICATE KEY UPDATE jours_pris = jours_pris + ?");
            $stmt->execute([$id, $conge['employe_id'], $annee, $conge['nb_jours'], $conge['nb_jours']]);
        }
        // Si refus après approbation, on annule la déduction
        if ($statut === 'refuse' && $conge['statut'] === 'approuve' && $conge['type_conge'] === 'conge_paye') {
            $annee = date('Y', strtotime($conge['date_debut']));
            $stmt = $db->prepare("UPDATE soldes_conges SET jours_pris = GREATEST(0, jours_pris - ?) WHERE employe_id=? AND annee=?");
            $stmt->execute([$conge['nb_jours'], $conge['employe_id'], $annee]);
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function solde(): void {
        requireAuth();
        if (!isSuperviseur()) redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();

        $employe_id        = (int)($_POST['employe_id'] ?? 0);
        $annee             = (int)($_POST['annee'] ?? date('Y'));
        $jours_acquis      = (float)($_POST['jours_acquis'] ?? 0);
        $jours_reportes_n1 = (float)($_POST['jours_reportes_n1'] ?? 0);

        $stmt = $db->prepare("INSERT INTO soldes_conges (entreprise_id,employe_id,annee,jours_acquis,jours_reportes_n1,jours_pris)
            VALUES (?,?,?,?,?,0) ON DUPLICATE KEY UPDATE jours_acquis=?, jours_reportes_n1=?");
        $stmt->execute([$id,$employe_id,$annee,$jours_acquis,$jours_reportes_n1,$jours_acquis,$jours_reportes_n1]);

        redirect("/dossier/rh/conges?id=$id&ok=1");
    }

    public function api(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $mois       = (int)($_GET['mois'] ?? 0);
        $annee      = (int)($_GET['annee'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();

        $date_debut_mois = sprintf('%04d-%02d-01', $annee, $mois);
        $date_fin_mois   = date('Y-m-t', strtotime($date_debut_mois));

        $jours_ouvres = 0;
        $cur = new DateTime($date_debut_mois);
        $fin = new DateTime($date_fin_mois);
        while ($cur <= $fin) {
            if ($cur->format('N') != 7) $jours_ouvres++;
            $cur->modify('+1 day');
        }

        $stmt = $db->prepare("SELECT e.salaire_base FROM employes e WHERE e.id=? AND e.entreprise_id=?");
        $stmt->execute([$employe_id, $id]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        $salaire_base = (float)($emp['salaire_base'] ?? 0);

        $type_labels = [
            'conge_paye'=>'Congé payé','maladie'=>'Maladie','maternite'=>'Maternité',
            'paternite'=>'Paternité','sans_solde'=>'Sans solde','autre'=>'Autre'
        ];

        $stmt = $db->prepare("
            SELECT type_conge, SUM(nb_jours) as nb_jours,
                   MIN(date_debut) as date_debut, MAX(date_fin) as date_fin
            FROM conges
            WHERE employe_id=? AND entreprise_id=? AND statut='approuve'
              AND date_debut<=? AND date_fin>=?
            GROUP BY type_conge
        ");
        $stmt->execute([$employe_id, $id, $date_fin_mois, $date_debut_mois]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conges = [];
        $deduction_totale = 0;
        foreach ($rows as $r) {
            $deduction = 0;
            if ($r['type_conge'] === 'sans_solde' && $jours_ouvres > 0) {
                $deduction = round(($salaire_base / $jours_ouvres) * $r['nb_jours']);
                $deduction_totale += $deduction;
            }
            $conges[] = [
                'type'       => $r['type_conge'],
                'type_label' => $type_labels[$r['type_conge']] ?? $r['type_conge'],
                'nb_jours'   => (int)$r['nb_jours'],
                'date_debut' => date('d/m/Y', strtotime($r['date_debut'])),
                'date_fin'   => date('d/m/Y', strtotime($r['date_fin'])),
                'deduction'  => number_format($deduction, 0, ',', ' '),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['ok'=>true,'conges'=>$conges,'deduction_totale'=>number_format($deduction_totale,0,',',' ')]);
    }

    public function supprimer(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $conge_id = (int)($_POST['conge_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();
        $db->prepare("DELETE FROM conges WHERE id=? AND entreprise_id=? AND statut='en_attente'")->execute([$conge_id,$id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
