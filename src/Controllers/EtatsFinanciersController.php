<?php
require_once APP_ROOT . '/src/Services/BilanService.php';
require_once APP_ROOT . '/src/Services/CompteResultatService.php';
require_once APP_ROOT . '/src/Services/PaieService.php';
require_once APP_ROOT . '/src/Services/RegimeFiscalService.php';

class EtatsFinanciersController {

    private function getEntrepriseAccess(int $id): array {
        requireAuth();
        $entreprise = getEntreprise($id);
        if (empty($entreprise)) { http_response_code(404); echo "Dossier introuvable"; exit; }
        if (!userHasAccess($id)) { redirect('/dashboard'); }
        return $entreprise;
    }

    public function bilan(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $service = new BilanService($id, $exercice);
        $bilan = $service->calculer();

        // Comparaison N-1 — vérifier que l'exercice N-1 existe avant de calculer
        $stmtExN1 = getDB()->prepare("SELECT COUNT(*) FROM exercices WHERE entreprise_id=? AND annee=?");
        $stmtExN1->execute([$id, $exercice - 1]);
        $premierExercice = $stmtExN1->fetchColumn() == 0;
        $serviceN1 = new BilanService($id, $exercice - 1);
        $bilanN1 = $premierExercice ? null : $serviceN1->calculer();

        $pageTitle = 'Bilan';
        $activeTab = 'bilan';
        ob_start();
        require APP_ROOT . '/views/dossier/bilan.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function compteResultat(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $service = new CompteResultatService($id, $exercice);
        $cr = $service->calculer();

        // Comparaison N-1
        $serviceN1 = new CompteResultatService($id, $exercice - 1);
        $crN1 = $serviceN1->calculer();

        $pageTitle = 'Compte de résultat';
        $activeTab = 'compte-resultat';
        ob_start();
        require APP_ROOT . '/views/dossier/compte-resultat.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function tafire(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exerciceN = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);
        $exerciceN1 = $exerciceN - 1;

        $serviceN  = new BilanService($id, $exerciceN);
        $serviceN1 = new BilanService($id, $exerciceN1);
        $crServiceN = new CompteResultatService($id, $exerciceN);

        $bilanN  = $serviceN->calculer();
        $bilanN1 = $serviceN1->calculer();
        $crN     = $crServiceN->calculer();

        // TAFIRE calculations
        $resultat_net    = $bilanN['resultat_net'];
        $dotations_amort = $crN['charges']['exploitation']['dot_amort'];

        // Dettes financières variation
        $dettes_fin_n  = $bilanN['passif']['dettes_fin']['total'];
        $dettes_fin_n1 = $bilanN1['passif']['dettes_fin']['total'];
        $aug_dettes_fin = max(0, $dettes_fin_n - $dettes_fin_n1);
        $remb_dettes    = max(0, $dettes_fin_n1 - $dettes_fin_n);

        // Cessions immobilisations: produits HAO (77x)
        $cessions_immo = $crN['produits']['hao'] ?? 0;

        // Acquisitions immobilisations: variation actif immobilisé brut
        $immo_n_brut  = $bilanN['actif']['immobilise']['corporelles']['brut']
                       + $bilanN['actif']['immobilise']['incorporelles']['brut'];
        $immo_n1_brut = $bilanN1['actif']['immobilise']['corporelles']['brut']
                       + $bilanN1['actif']['immobilise']['incorporelles']['brut'];
        $acquisitions_immo = max(0, $immo_n_brut - $immo_n1_brut);

        // Dividendes = report bénéficiaire N vs N-1 (simplifié)
        $dividendes = 0; // requires assembly declaration data

        $ressources_total = $resultat_net + $dotations_amort + $aug_dettes_fin + $cessions_immo;
        $emplois_total    = $acquisitions_immo + $remb_dettes + $dividendes;
        $variation_frn    = $ressources_total - $emplois_total;

        // BFR variation
        $actif_circ_n  = $bilanN['actif']['circulant']['total'];
        $actif_circ_n1 = $bilanN1['actif']['circulant']['total'];
        $passif_circ_n  = $bilanN['passif']['passif_circulant']['total'];
        $passif_circ_n1 = $bilanN1['passif']['passif_circulant']['total'];

        $var_actif_circ  = $actif_circ_n - $actif_circ_n1;
        $var_passif_circ = $passif_circ_n - $passif_circ_n1;
        $variation_bfr   = $var_actif_circ - $var_passif_circ;

        // Trésorerie variation
        $trezo_n  = $bilanN['actif']['tresorerie'] - $bilanN['passif']['tresorerie_passive'];
        $trezo_n1 = $bilanN1['actif']['tresorerie'] - $bilanN1['passif']['tresorerie_passive'];
        $variation_tresorerie = $variation_frn - $variation_bfr;
        $var_trezo_directe = $trezo_n - $trezo_n1;

        $tafire = compact(
            'resultat_net','dotations_amort','aug_dettes_fin','cessions_immo',
            'acquisitions_immo','remb_dettes','dividendes',
            'ressources_total','emplois_total','variation_frn',
            'var_actif_circ','var_passif_circ','variation_bfr',
            'variation_tresorerie','var_trezo_directe',
            'exerciceN','exerciceN1'
        );

        $pageTitle = 'TAFIRE';
        $activeTab = 'tafire';
        ob_start();
        require APP_ROOT . '/views/dossier/tafire.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function tva(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $mois     = (int)($_GET['mois'] ?? date('n'));
        $mois_fin = (int)($_GET['mois_fin'] ?? $mois);
        $annee    = (int)($_GET['annee'] ?? $entreprise['exercice_courant']);
        $tva      = null;

        if (isset($_GET['calculer'])) {
            $credit_ant = (float)($_GET['credit_anterieur'] ?? 0);
            $tva = PaieService::calculerTVA($id, $mois, $annee, $credit_ant, $mois_fin);
        }

        // Check if already declared
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM declarations_tva WHERE entreprise_id = ? AND periode_mois = ? AND periode_annee = ?");
        $stmt->execute([$id, $mois, $annee]);
        $declaration_existante = $stmt->fetch(PDO::FETCH_ASSOC);

        // Historique des déclarations
        $stmt_hist = $db->prepare("SELECT * FROM declarations_tva WHERE entreprise_id = ? ORDER BY periode_annee DESC, periode_mois DESC LIMIT 24");
        $stmt_hist->execute([$id]);
        $historique_tva = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

        // Report automatique du crédit depuis la déclaration précédente
        $credit_auto = 0;
        if (!isset($_GET['calculer']) && !isset($_GET['credit_anterieur'])) {
            $mois_prec = $mois === 1 ? 12 : $mois - 1;
            $annee_prec = $mois === 1 ? $annee - 1 : $annee;
            $stmt_prec = $db->prepare("SELECT credit_reportable FROM declarations_tva WHERE entreprise_id=? AND periode_mois=? AND periode_annee=?");
            $stmt_prec->execute([$id, $mois_prec, $annee_prec]);
            $prec = $stmt_prec->fetch();
            if ($prec && $prec['credit_reportable'] > 0) {
                $credit_auto = (float)$prec['credit_reportable'];
            }
        }

        $pageTitle = 'Déclaration TVA';
        $activeTab = 'tva';
        ob_start();
        require APP_ROOT . '/views/dossier/tva.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeTVA(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $mois   = (int)$_POST['mois'];
        $annee  = (int)$_POST['annee'];
        $credit_ant = (float)($_POST['credit_anterieur'] ?? 0);

        $tva = PaieService::calculerTVA($id, $mois, $annee, $credit_ant);

        $db = getDB();
        // Fix N — Bloquer l'écrasement d'une déclaration TVA déjà payée
        $stmtChk = $db->prepare("SELECT statut FROM declarations_tva WHERE entreprise_id=? AND periode_mois=? AND periode_annee=?");
        $stmtChk->execute([$id, $mois, $annee]);
        $existingDecl = $stmtChk->fetch();
        if ($existingDecl && $existingDecl['statut'] === 'paye') {
            redirect("/dossier/tva?id=$id&error=already_paid"); return;
        }

        // Upsert
        $stmt = $db->prepare("INSERT INTO declarations_tva
            (entreprise_id, user_id, periode_mois, periode_annee, tva_collectee, tva_deductible_biens, tva_deductible_immo,
             tva_nette, credit_tva_anterieur, tva_a_payer, credit_reportable, statut, date_depot)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,'depose', CURDATE())
            ON DUPLICATE KEY UPDATE
            tva_collectee=VALUES(tva_collectee), tva_deductible_biens=VALUES(tva_deductible_biens),
            tva_deductible_immo=VALUES(tva_deductible_immo), tva_nette=VALUES(tva_nette),
            credit_tva_anterieur=VALUES(credit_tva_anterieur), tva_a_payer=VALUES(tva_a_payer),
            credit_reportable=VALUES(credit_reportable), statut='depose', date_depot=CURDATE()");

        $stmt->execute([
            $id, auth()['id'], $mois, $annee,
            $tva['tva_collectee'], $tva['tva_ded_biens'], $tva['tva_ded_immo'],
            $tva['tva_nette'], $tva['credit_anterieur'],
            $tva['tva_a_payer'], $tva['credit_reportable']
        ]);

        redirect("/dossier/tva?id=$id&mois=$mois&annee=$annee&saved=1");
    }

    public function payerTVA(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $decl_id    = (int)($_POST['declaration_id'] ?? 0);
        $mois       = (int)($_POST['mois'] ?? 0);
        $annee      = (int)($_POST['annee'] ?? 0);
        $date_paiement     = $_POST['date_paiement'] ?? date('Y-m-d');
        $reference_paiement = trim($_POST['reference_paiement'] ?? '');

        $this->getEntrepriseAccess($id);

        // Fix O — Réservé aux admins
        if (!isAdmin()) { redirect('/dashboard'); return; }

        $db = getDB();
        $chk = $db->prepare("SELECT statut FROM declarations_tva WHERE id=? AND entreprise_id=?");
        $chk->execute([$decl_id, $id]);
        $row = $chk->fetch();
        if ($row && $row['statut'] === 'paye') {
            redirect("/dossier/tva?id=$id&error=already_paid"); return;
        }
        $stmt = $db->prepare("UPDATE declarations_tva SET statut='paye', date_paiement=?, reference_paiement=? WHERE id=? AND entreprise_id=?");
        $stmt->execute([$date_paiement, $reference_paiement ?: null, $decl_id, $id]);

        // Fix O — Audit paiement TVA
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log(auth()['id'], 'TVA_PAYEE', $id, 'declarations_tva', $decl_id, "TVA payée — période $mois/$annee — ref: $reference_paiement");

        redirect("/dossier/tva?id=$id&mois=$mois&annee=$annee&paid=1");
    }

    // ----------------------------------------------------------------
    // Fiche régime fiscal (lecture seule)
    // ----------------------------------------------------------------
    public function regime(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $regime  = $entreprise['regime_fiscal'] ?? 'CGI';
        $modules = RegimeFiscalService::getModulesDisponibles($regime);
        $label   = RegimeFiscalService::getLabel($regime);
        $color   = RegimeFiscalService::getBadgeColor($regime);
        $exercice = (int)($entreprise['exercice_courant'] ?? date('Y'));
        $echeances = RegimeFiscalService::getEcheances($regime, $exercice);
        $caHt    = RegimeFiscalService::getCaHtFromEcritures($id, $exercice);

        $pageTitle = 'Fiche régime fiscal';
        $activeTab = 'regime';
        ob_start();
        require APP_ROOT . '/views/dossier/fiscalite/regime.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ----------------------------------------------------------------
    // CGU Declaration
    // ----------------------------------------------------------------
    public function cgu(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $regime = $entreprise['regime_fiscal'] ?? 'CGI';
        // Also allow MICRO (impôt libératoire shares the same route)
        if (!in_array($regime, ['CGU', 'MICRO'])) {
            redirect("/dossier/fiscalite/regime?id=$id");
        }

        $annee   = (int)($_GET['annee'] ?? date('Y'));
        $exercice = (int)($entreprise['exercice_courant'] ?? date('Y'));
        $secteur  = $entreprise['secteur_activite_detail'] ?? 'commerce';
        $caHt    = RegimeFiscalService::getCaHtFromEcritures($id, $exercice);
        $caTtc   = $caHt * 1.18;

        $calcul   = null;
        $liberatoire = null;

        if ($regime === 'CGU') {
            $calcul = RegimeFiscalService::calculerCGU($caTtc, $secteur);
        } else {
            // MICRO — afficher tous les trimestres
            $liberatoire = [];
            for ($t = 1; $t <= 4; $t++) {
                $liberatoire[$t] = RegimeFiscalService::calculerImpotLiberatoire($secteur, $t, $annee);
            }
        }

        // Load existing declaration if any
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM declarations_cgu WHERE entreprise_id = ? AND annee = ?");
        $stmt->execute([$id, $annee]);
        $declaration_existante = $stmt->fetch(PDO::FETCH_ASSOC);

        $pageTitle = $regime === 'CGU' ? 'Déclaration CGU' : 'Impôt Libératoire';
        $activeTab = 'cgu';
        ob_start();
        require APP_ROOT . '/views/dossier/fiscalite/cgu.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ----------------------------------------------------------------
    // IS — Impôt sur les Sociétés
    // ----------------------------------------------------------------
    public function is(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $crService = new CompteResultatService($id, $exercice);
        $cr = $crService->calculer();
        $resultat_comptable = $cr['resultats']['net'];

        $db = getDB();

        // CA HT : comptes 70x à 75x (produits d'exploitation SYSCOHADA)
        // SUM(credit) - SUM(debit) car les produits sont normalement créditeurs
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(l.credit) - SUM(l.debit), 0)
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
              AND c.numero REGEXP '^7[0-5]'
        ");
        $stmt->execute([$id, $exercice]);
        $ca_ht = max(0, (float)$stmt->fetchColumn());

        // Déclaration IS exercice courant (si déjà enregistrée)
        $stmt = $db->prepare("SELECT * FROM declarations_is WHERE entreprise_id=? AND exercice=?");
        $stmt->execute([$id, $exercice]);
        $declaration = $stmt->fetch(PDO::FETCH_ASSOC);

        // IS N-1 pour le calcul des acomptes provisionnels
        $stmt = $db->prepare("SELECT COALESCE(is_du, 0) FROM declarations_is WHERE entreprise_id=? AND exercice=?");
        $stmt->execute([$id, $exercice - 1]);
        $is_du_n1 = (float)$stmt->fetchColumn();

        // Acomptes versés (IS déjà payé via échéances fiscales)
        $stmt = $db->prepare("SELECT COALESCE(SUM(montant_reel),0) FROM echeances_fiscales WHERE entreprise_id=? AND type='IS' AND YEAR(date_echeance)=? AND statut='regle'");
        $stmt->execute([$id, $exercice]);
        $acomptes_verses = (float)$stmt->fetchColumn();

        $regime  = $entreprise['regime_fiscal'] ?? 'CGI';
        $saved   = isset($_GET['saved']);
        $pageTitle = 'Déclaration IS';
        $activeTab = 'is';
        ob_start();
        require APP_ROOT . '/views/dossier/fiscalite/is.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeIS(): void {
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $exercice = (int)$_POST['exercice'];

        $resultat_comptable = (float)$_POST['resultat_comptable'];
        $reintegrations = (float)($_POST['reintegrations'] ?? 0);
        $deductions = (float)($_POST['deductions'] ?? 0);
        $resultat_fiscal = $resultat_comptable + $reintegrations - $deductions;

        $ca_ht = (float)$_POST['ca_ht'];
        $taux_is = 0.30;
        $is_theorique = max(0, $resultat_fiscal) * $taux_is;
        $minimum_is = max(500000, $ca_ht * 0.005);
        $is_du = $resultat_fiscal > 0 ? max($minimum_is, $is_theorique) : $minimum_is;
        $acomptes_verses = (float)($_POST['acomptes_verses'] ?? 0);
        $is_net = max(0, $is_du - $acomptes_verses);

        $user = auth();
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO declarations_is (entreprise_id, user_id, exercice, resultat_comptable, reintegrations, deductions, resultat_fiscal, is_du, acomptes_verses, is_net, statut, date_depot) VALUES (?,?,?,?,?,?,?,?,?,?,'brouillon', CURDATE()) ON DUPLICATE KEY UPDATE resultat_comptable=VALUES(resultat_comptable), reintegrations=VALUES(reintegrations), deductions=VALUES(deductions), resultat_fiscal=VALUES(resultat_fiscal), is_du=VALUES(is_du), acomptes_verses=VALUES(acomptes_verses), is_net=VALUES(is_net), date_depot=CURDATE()");
        $stmt->execute([$id, $user['id'], $exercice, $resultat_comptable, $reintegrations, $deductions, $resultat_fiscal, $is_du, $acomptes_verses, $is_net]);

        redirect("/dossier/fiscalite/is?id=$id&exercice=$exercice&saved=1");
    }

    // ----------------------------------------------------------------
    // Déclarations Sociales
    // ----------------------------------------------------------------
    public function declarationSociale(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $mois = (int)($_GET['mois'] ?? date('n'));
        $annee = (int)($_GET['annee'] ?? date('Y'));

        $db = getDB();
        $stmt = $db->prepare("SELECT b.*, e.nom, e.prenom FROM bulletins_paie b JOIN employes e ON e.id=b.employe_id WHERE b.entreprise_id=? AND b.periode_mois=? AND b.periode_annee=? ORDER BY e.nom");
        $stmt->execute([$id, $mois, $annee]);
        $bulletins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totaux = [
            'nb_salaries'    => count($bulletins),
            'masse_salariale'=> array_sum(array_column($bulletins, 'salaire_brut')),
            'ipres_salarie'  => array_sum(array_column($bulletins, 'ipres_salarie')),
            'ipres_patronal' => array_sum(array_column($bulletins, 'ipres_patronal')),
            'css_accidents'  => array_sum(array_column($bulletins, 'css_accident')),
            'css_prestations'=> array_sum(array_column($bulletins, 'css_prestation')),
            'ipm_salarie'    => array_sum(array_column($bulletins, 'ipm_salarie')),
            'ipm_patronal'   => array_sum(array_column($bulletins, 'ipm_patronal')),
            'cfce'           => array_sum(array_column($bulletins, 'cfce')),
        ];
        $totaux['ipres_total']   = $totaux['ipres_salarie'] + $totaux['ipres_patronal'];
        $totaux['css_total']     = $totaux['css_accidents'] + $totaux['css_prestations'];
        $totaux['ipm_total']     = $totaux['ipm_salarie'] + $totaux['ipm_patronal'];
        $totaux['total_a_payer'] = $totaux['ipres_total'] + $totaux['css_total'] + $totaux['ipm_total'] + $totaux['cfce'];

        $stmt = $db->prepare("SELECT * FROM declarations_sociales WHERE entreprise_id=? AND type='GLOBAL' AND periode_mois=? AND periode_annee=?");
        $stmt->execute([$id, $mois, $annee]);
        $declaration_existante = $stmt->fetch(PDO::FETCH_ASSOC);

        $saved = isset($_GET['saved']);
        $pageTitle = 'Déclarations Sociales';
        $activeTab = 'decl-sociale';
        ob_start();
        require APP_ROOT . '/views/dossier/fiscalite/declaration-sociale.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeDeclarationSociale(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $mois  = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];

        $db = getDB();
        // Fix N — Bloquer l'écrasement d'une déclaration sociale déjà payée
        $stmtChkS = $db->prepare("SELECT statut FROM declarations_sociales WHERE entreprise_id=? AND type='GLOBAL' AND periode_mois=? AND periode_annee=?");
        $stmtChkS->execute([$id, $mois, $annee]);
        $existingDeclS = $stmtChkS->fetch();
        if ($existingDeclS && $existingDeclS['statut'] === 'paye') {
            redirect("/dossier/fiscalite/declaration-sociale?id=$id&mois=$mois&annee=$annee&error=already_paid"); return;
        }

        $stmt = $db->prepare("INSERT INTO declarations_sociales (entreprise_id, user_id, type, periode_mois, periode_annee, nb_salaries, masse_salariale, ipres_salarie, ipres_patronal, ipres_total, css_accidents, css_prestations, css_total, ipm_salarie, ipm_patronal, ipm_total, cfce, total_a_payer, statut) VALUES (?,?,'GLOBAL',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'depose') ON DUPLICATE KEY UPDATE statut='depose', nb_salaries=VALUES(nb_salaries), masse_salariale=VALUES(masse_salariale), ipres_salarie=VALUES(ipres_salarie), ipres_patronal=VALUES(ipres_patronal), ipres_total=VALUES(ipres_total), css_accidents=VALUES(css_accidents), css_prestations=VALUES(css_prestations), css_total=VALUES(css_total), ipm_salarie=VALUES(ipm_salarie), ipm_patronal=VALUES(ipm_patronal), ipm_total=VALUES(ipm_total), cfce=VALUES(cfce), total_a_payer=VALUES(total_a_payer)");
        $stmt->execute([
            $id, auth()['id'], $mois, $annee,
            (int)$_POST['nb_salaries'], (float)$_POST['masse_salariale'],
            (float)$_POST['ipres_salarie'], (float)$_POST['ipres_patronal'], (float)$_POST['ipres_total'],
            (float)$_POST['css_accidents'], (float)$_POST['css_prestations'], (float)$_POST['css_total'],
            (float)$_POST['ipm_salarie'], (float)$_POST['ipm_patronal'], (float)$_POST['ipm_total'],
            (float)$_POST['cfce'], (float)$_POST['total_a_payer'],
        ]);
        redirect("/dossier/fiscalite/declaration-sociale?id=$id&mois=$mois&annee=$annee&saved=1");
    }

    // ----------------------------------------------------------------
    // Calendrier Fiscal
    // ----------------------------------------------------------------
    public function calendrierFiscal(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $annee = (int)($_GET['annee'] ?? date('Y'));
        $regime = $entreprise['regime_fiscal'] ?? 'CGI';

        $echeances = RegimeFiscalService::getEcheancesAnnuelles($regime, $annee, $id);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM echeances_fiscales WHERE entreprise_id=? AND YEAR(date_echeance)=? ORDER BY date_echeance");
        $stmt->execute([$id, $annee]);
        $echeances_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $echeances_map = [];
        foreach ($echeances_db as $e) {
            $echeances_map[$e['type'].'_'.$e['date_echeance']] = $e;
        }

        $saved = isset($_GET['saved']);
        $pageTitle = 'Calendrier Fiscal';
        $activeTab = 'calendrier';
        ob_start();
        require APP_ROOT . '/views/dossier/fiscalite/calendrier.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function marquerEcheance(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $echeance_id   = (int)$_POST['echeance_id'];
        $statut        = $_POST['statut'];
        $montant_reel  = (float)($_POST['montant_reel'] ?? 0);
        $date_reglement = $_POST['date_reglement'] ?? null;

        $db = getDB();
        $stmt = $db->prepare("UPDATE echeances_fiscales SET statut=?, montant_reel=?, date_reglement=? WHERE id=? AND entreprise_id=?");
        $stmt->execute([$statut, $montant_reel, $date_reglement ?: null, $echeance_id, $id]);
        redirect("/dossier/fiscalite/calendrier?id=$id");
    }

    public function initEcheances(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $annee  = (int)$_POST['annee'];
        $regime = $entreprise['regime_fiscal'] ?? 'CGI';

        $echeances = RegimeFiscalService::getEcheancesAnnuelles($regime, $annee, $id);
        $db = getDB();

        foreach ($echeances as $ech) {
            $stmt = $db->prepare("INSERT IGNORE INTO echeances_fiscales (entreprise_id, type, libelle, date_echeance, montant_estime, statut) VALUES (?,?,?,?,?,'a_venir')");
            $stmt->execute([$id, $ech['type'], $ech['libelle'], $ech['date'], $ech['montant_estime'] ?? null]);
        }
        redirect("/dossier/fiscalite/calendrier?id=$id&annee=$annee&saved=1");
    }

    public function genererEcheances(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $annee = (int)($_POST['annee'] ?? date('Y'));
        require_once APP_ROOT . '/src/Services/AlerteService.php';
        AlerteService::genererEcheancesAnnee($id, $annee);
        redirect("/dossier/fiscalite/calendrier?id=$id&annee=$annee&saved=1");
    }

    public function storeCGU(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $annee   = (int)$_POST['annee'];
        $ca_ttc  = (float)($_POST['ca_ttc'] ?? 0);
        $secteur = $_POST['secteur'] ?? 'commerce';

        $calcul  = RegimeFiscalService::calculerCGU($ca_ttc, $secteur);

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO declarations_cgu
            (entreprise_id, user_id, annee, ca_ttc, ca_ht, secteur, cgu_base, minimum_secteur, cgu_due,
             acompte_t1, acompte_t2, acompte_t3, solde, statut, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
             ca_ttc=VALUES(ca_ttc), ca_ht=VALUES(ca_ht), secteur=VALUES(secteur),
             cgu_base=VALUES(cgu_base), minimum_secteur=VALUES(minimum_secteur), cgu_due=VALUES(cgu_due),
             acompte_t1=VALUES(acompte_t1), acompte_t2=VALUES(acompte_t2),
             acompte_t3=VALUES(acompte_t3), solde=VALUES(solde),
             statut=VALUES(statut), notes=VALUES(notes)");

        $stmt->execute([
            $id, auth()['id'], $annee,
            $calcul['ca_ttc'], $calcul['ca_ht'], $secteur,
            $calcul['cgu_base'], $calcul['minimum_secteur'], $calcul['cgu_due'],
            $calcul['acompte_t1'], $calcul['acompte_t2'], $calcul['acompte_t3'], $calcul['solde'],
            $_POST['statut'] ?? 'brouillon',
            trim($_POST['notes'] ?? ''),
        ]);

        redirect("/dossier/fiscalite/cgu?id=$id&annee=$annee&saved=1");
    }

    // ----------------------------------------------------------------
    // Export PDF — Bilan (standalone printable HTML)
    // ----------------------------------------------------------------
    public function exportBilan(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $service = new BilanService($id, $exercice);
        $bilan = $service->calculer();

        require APP_ROOT . '/views/dossier/exports/bilan-print.php';
        exit;
    }

    // ----------------------------------------------------------------
    // Export PDF — Compte de résultat (standalone printable HTML)
    // ----------------------------------------------------------------
    public function exportCompteResultat(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $service = new CompteResultatService($id, $exercice);
        $cr = $service->calculer();

        require APP_ROOT . '/views/dossier/exports/cr-print.php';
        exit;
    }

    // ----------------------------------------------------------------
    // État Financier DGID (SYSCOHADA) — page d'accueil + téléchargement
    // ----------------------------------------------------------------
    public function etatDGID(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        // Liste des exercices disponibles
        $db = getDB();
        $stmt = $db->prepare("SELECT DISTINCT YEAR(date_ecriture) as annee FROM ecritures WHERE entreprise_id = ? ORDER BY annee DESC");
        $stmt->execute([$id]);
        $exercicesDispos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($exercicesDispos)) $exercicesDispos = [$exercice];

        $pageTitle  = 'État Financier DGID';
        $activeTab  = 'bilan';
        ob_start();
        require APP_ROOT . '/views/dossier/etat-financier-dgid.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function downloadDGID(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        require_once APP_ROOT . '/src/Services/EtatFinancierDGID.php';
        $db = getDB();
        $service = new EtatFinancierDGID($db, $entreprise, $exercice);

        // Fix V — Chemin temporaire portable (non lié à XAMPP)
        $tmpFile = sys_get_temp_dir() . '/dgid_' . $id . '_' . $exercice . '.xlsx';
        $service->generer($tmpFile);

        $filename = 'EtatFinancier_DGID_' . preg_replace('/[^A-Za-z0-9]/', '_', $entreprise['raison_sociale']) . '_' . $exercice . '.xlsx';

        // Archive obligatoire : OHADA 10 ans / CGI Art. 750 6 ans
        // Non bloquant : un échec d'archivage ne doit jamais empêcher le téléchargement.
        $archiveDir = APP_ROOT . '/archives/dgid/';
        if (!is_dir($archiveDir)) {
            @mkdir($archiveDir, 0775, true);
        }
        if (is_dir($archiveDir) && is_writable($archiveDir)) {
            $archiveFile = $archiveDir . date('Y-m-d_His') . '_' . $filename;
            if (!@copy($tmpFile, $archiveFile)) {
                error_log("DGID archive failed for: $archiveFile");
            }
        } else {
            error_log("DGID archive dir non inscriptible: $archiveDir");
        }
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        // log(userId, action, entrepriseId, table, recordId, details)
        NotificationService::log(
            (int)(auth()['id']),
            'EXPORT_DGID',
            (int)$id,
            'entreprises',
            (int)$id,
            "Export DGID $exercice — " . $entreprise['raison_sociale']
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }
}
