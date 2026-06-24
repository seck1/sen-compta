<?php
class CommercialController {

    private function requireCabinet(): void {
        requireAuth();
    }

    private function nextRef(string $prefix, string $table, string $col): string {
        $db = getDB();
        $year = date('Y');
        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $col LIKE ?");
        $stmt->execute(["$prefix-$year-%"]);
        $n = (int)$stmt->fetchColumn() + 1;
        return "$prefix-$year-" . str_pad($n, 3, '0', STR_PAD_LEFT);
    }

    // =====================================================
    // DASHBOARD COMMERCIAL
    // =====================================================
    public function dashboard(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cf = $cabinetId ? " AND cabinet_id = $cabinetId" : "";
        $cfp = $cabinetId ? " AND p.cabinet_id = $cabinetId" : "";

        $annee = (int)($_GET['annee'] ?? date('Y'));

        // KPIs filtrés par cabinet
        $caFacture = $db->prepare("SELECT COALESCE(SUM(montant_ttc),0) FROM factures_commercial WHERE statut IN ('payee','partiellement_payee','envoyee') AND YEAR(date_facture)=? $cf");
        $caFacture->execute([$annee]); $caFacture = (float)$caFacture->fetchColumn();

        $caEncaisse = $db->prepare("SELECT COALESCE(SUM(montant_paye),0) FROM factures_commercial WHERE YEAR(date_facture)=? $cf");
        $caEncaisse->execute([$annee]); $caEncaisse = (float)$caEncaisse->fetchColumn();

        $caAttente = $db->prepare("SELECT COALESCE(SUM(montant_ttc - montant_paye),0) FROM factures_commercial WHERE statut IN ('envoyee','partiellement_payee','en_retard') AND YEAR(date_facture)=? $cf");
        $caAttente->execute([$annee]); $caAttente = (float)$caAttente->fetchColumn();

        $nbDevisEnvoyes = $db->prepare("SELECT COUNT(*) FROM devis WHERE statut='envoye' AND YEAR(date_devis)=? $cf");
        $nbDevisEnvoyes->execute([$annee]); $nbDevisEnvoyes = (int)$nbDevisEnvoyes->fetchColumn();

        $nbDevisAcceptes = $db->prepare("SELECT COUNT(*) FROM devis WHERE statut IN ('accepte','converti') AND YEAR(date_devis)=? $cf");
        $nbDevisAcceptes->execute([$annee]); $nbDevisAcceptes = (int)$nbDevisAcceptes->fetchColumn();

        $tauxConversion = $nbDevisEnvoyes > 0 ? round(($nbDevisAcceptes / ($nbDevisEnvoyes + $nbDevisAcceptes)) * 100) : 0;

        $nbProspects = $db->query("SELECT COUNT(*) FROM prospects WHERE type_contact='prospect' $cf")->fetchColumn();
        $nbClients   = $db->query("SELECT COUNT(*) FROM prospects WHERE type_contact='client' $cf")->fetchColumn();

        // Pipeline
        $pipeline = $db->query("SELECT pipeline_stage, COUNT(*) as nb, COALESCE(SUM(ca_potentiel),0) as total FROM prospects WHERE 1=1 $cf GROUP BY pipeline_stage")->fetchAll();
        $pipelineMap = [];
        foreach ($pipeline as $p) $pipelineMap[$p['pipeline_stage']] = $p;

        // Factures en retard
        $db->query("UPDATE factures_commercial SET statut='en_retard' WHERE statut='envoyee' AND date_echeance < CURDATE() AND montant_paye < montant_ttc $cf");

        $facturesRetard = $db->prepare("SELECT f.*, p.raison_sociale FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE f.statut='en_retard' $cfp ORDER BY f.date_echeance ASC LIMIT 5");
        $facturesRetard->execute(); $facturesRetard = $facturesRetard->fetchAll();

        $dernieresFactures = $db->prepare("SELECT f.*, p.raison_sociale FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE 1=1 $cfp ORDER BY f.created_at DESC LIMIT 6");
        $dernieresFactures->execute(); $dernieresFactures = $dernieresFactures->fetchAll();

        // CA mensuel
        $caParMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $stmt = $db->prepare("SELECT COALESCE(SUM(montant_ttc),0) FROM factures_commercial WHERE YEAR(date_facture)=? AND MONTH(date_facture)=? AND statut != 'annulee' $cf");
            $stmt->execute([$annee, $m]);
            $caParMois[] = (float)$stmt->fetchColumn();
        }

        $activePage = 'commercial-dashboard';
        $pageTitle = 'Commercial';
        ob_start();
        require APP_ROOT . '/views/commercial/dashboard.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    // =====================================================
    // PROSPECTS
    // =====================================================
    public function prospects(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;

        $stage = $_GET['stage'] ?? '';
        $search = trim($_GET['q'] ?? '');

        $where = '1=1';
        $params = [];
        if ($cabinetId) { $where .= ' AND cabinet_id=?'; $params[] = $cabinetId; }
        if ($stage) { $where .= ' AND pipeline_stage=?'; $params[] = $stage; }
        if ($search) { $where .= ' AND (raison_sociale LIKE ? OR contact_nom LIKE ? OR email LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }

        $stmt = $db->prepare("SELECT * FROM prospects WHERE $where ORDER BY updated_at DESC");
        $stmt->execute($params);
        $prospects = $stmt->fetchAll();

        $cfCount = $cabinetId ? "WHERE cabinet_id=$cabinetId" : "";
        $counts = $db->query("SELECT pipeline_stage, COUNT(*) as nb FROM prospects $cfCount GROUP BY pipeline_stage")->fetchAll(PDO::FETCH_KEY_PAIR);

        $activePage = 'commercial-prospects';
        $pageTitle = 'Prospects &amp; Clients';
        ob_start();
        require APP_ROOT . '/views/commercial/prospects.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function prospectForm(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $id = (int)($_GET['id'] ?? 0);
        $prospect = null;
        if ($id) {
            $s = $cabinetId
                ? $db->prepare("SELECT * FROM prospects WHERE id=? AND cabinet_id=?")
                : $db->prepare("SELECT * FROM prospects WHERE id=?");
            $cabinetId ? $s->execute([$id, $cabinetId]) : $s->execute([$id]);
            $prospect = $s->fetch() ?: null;
            if (!$prospect) redirect('/commercial/prospects');
        }

        $activePage = 'commercial-prospects';
        $pageTitle = $id ? 'Modifier prospect' : 'Nouveau prospect';
        ob_start();
        require APP_ROOT . '/views/commercial/prospect-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function storeProspect(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'raison_sociale'   => trim($_POST['raison_sociale'] ?? ''),
            'type_contact'     => $_POST['type_contact'] ?? 'prospect',
            'secteur'          => trim($_POST['secteur'] ?? ''),
            'forme_juridique'  => $_POST['forme_juridique'] ?? 'Autre',
            'ninea'            => trim($_POST['ninea'] ?? ''),
            'adresse'          => trim($_POST['adresse'] ?? ''),
            'ville'            => trim($_POST['ville'] ?? 'Dakar'),
            'telephone'        => trim($_POST['telephone'] ?? ''),
            'email'            => trim($_POST['email'] ?? ''),
            'site_web'         => trim($_POST['site_web'] ?? ''),
            'contact_nom'      => trim($_POST['contact_nom'] ?? ''),
            'contact_prenom'   => trim($_POST['contact_prenom'] ?? ''),
            'contact_poste'    => trim($_POST['contact_poste'] ?? ''),
            'contact_telephone'=> trim($_POST['contact_telephone'] ?? ''),
            'contact_email'    => trim($_POST['contact_email'] ?? ''),
            'pipeline_stage'   => $_POST['pipeline_stage'] ?? 'nouveau',
            'source'           => $_POST['source'] ?? 'autre',
            'ca_potentiel'     => (float)str_replace([' ','.'],['',' '], $_POST['ca_potentiel'] ?? 0),
            'notes'            => trim($_POST['notes'] ?? ''),
            'user_id'          => auth()['id'],
        ];

        if ($id) {
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            $stmt = $db->prepare("UPDATE prospects SET $set WHERE id=?");
            $stmt->execute(array_merge(array_values($data), [$id]));
        } else {
            $data['cabinet_id'] = (int)(auth()['cabinet_id'] ?? 0) ?: null;
            $data['reference'] = $this->nextRef('PROS', 'prospects', 'reference');
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $stmt = $db->prepare("INSERT INTO prospects ($cols) VALUES ($vals)");
            $stmt->execute(array_values($data));
            $id = $db->lastInsertId();
        }
        redirect('/commercial/prospect?id=' . $id);
    }

    public function prospectVoir(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $id = (int)($_GET['id'] ?? 0);
        $s = $cabinetId
            ? $db->prepare("SELECT * FROM prospects WHERE id=? AND cabinet_id=?")
            : $db->prepare("SELECT * FROM prospects WHERE id=?");
        $cabinetId ? $s->execute([$id, $cabinetId]) : $s->execute([$id]);
        $prospect = $s->fetch();
        if (!$prospect) { http_response_code(404); echo "Prospect introuvable"; exit; }

        $devis = $db->prepare("SELECT * FROM devis WHERE prospect_id=? ORDER BY date_devis DESC");
        $devis->execute([$id]); $devis = $devis->fetchAll();

        $factures = $db->prepare("SELECT * FROM factures_commercial WHERE prospect_id=? ORDER BY date_facture DESC");
        $factures->execute([$id]); $factures = $factures->fetchAll();

        $activePage = 'commercial-prospects';
        $pageTitle = $prospect['raison_sociale'];
        ob_start();
        require APP_ROOT . '/views/commercial/prospect-voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function updateStage(): void {
        $this->requireCabinet();
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $body['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        verifyCsrfToken($token);
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $id    = (int)($body['id']    ?? $_POST['id']    ?? 0);
        $stage = $body['stage']       ?? $_POST['stage'] ?? '';
        $allowed = ['nouveau','qualifie','devis_envoye','negociation','client','perdu'];
        if (!$id || !in_array($stage, $allowed)) { http_response_code(400); echo json_encode(['error'=>'Paramètres invalides']); exit; }
        // Vérifier appartenance cabinet avant update
        if ($cabinetId) {
            $chk = $db->prepare("SELECT COUNT(*) FROM prospects WHERE id=? AND cabinet_id=?");
            $chk->execute([$id, $cabinetId]);
            if (!$chk->fetchColumn()) { http_response_code(403); echo json_encode(['error'=>'Accès refusé']); exit; }
        }
        $db->prepare("UPDATE prospects SET pipeline_stage=? WHERE id=?")->execute([$stage, $id]);
        if ($stage === 'client') $db->prepare("UPDATE prospects SET type_contact='client' WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]);
    }

    // =====================================================
    // CATALOGUE PRESTATIONS
    // =====================================================
    public function catalogue(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfCat = $cabinetId ? "WHERE (cabinet_id = $cabinetId OR cabinet_id IS NULL)" : "";
        $catalogue = $db->query("SELECT * FROM prestations_catalogue $cfCat ORDER BY categorie, designation")->fetchAll();
        $activePage = 'commercial-catalogue';
        $pageTitle = 'Catalogue prestations';
        ob_start();
        require APP_ROOT . '/views/commercial/catalogue.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function storeCatalogue(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'code'           => strtoupper(trim($_POST['code'] ?? '')),
            'designation'    => trim($_POST['designation'] ?? ''),
            'description'    => trim($_POST['description'] ?? ''),
            'categorie'      => $_POST['categorie'] ?? 'autre',
            'unite'          => trim($_POST['unite'] ?? 'forfait'),
            'prix_unitaire'  => (float)($_POST['prix_unitaire'] ?? 0),
            'tva_taux'       => (float)($_POST['tva_taux'] ?? 18),
            'actif'          => 1,
        ];
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        if ($id) {
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            // Modifier uniquement les prestations du cabinet courant
            $db->prepare("UPDATE prestations_catalogue SET $set WHERE id=? AND (cabinet_id=? OR cabinet_id IS NULL)")->execute(array_merge(array_values($data), [$id, $cabinetId]));
        } else {
            $data['cabinet_id'] = $cabinetId;
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO prestations_catalogue ($cols) VALUES ($vals)")->execute(array_values($data));
        }
        redirect('/commercial/catalogue');
    }

    public function catalogueJson(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfCat = $cabinetId ? "AND (cabinet_id = $cabinetId OR cabinet_id IS NULL)" : "";
        $prestations = $db->query("SELECT * FROM prestations_catalogue WHERE actif=1 $cfCat ORDER BY categorie, designation")->fetchAll();
        header('Content-Type: application/json');
        echo json_encode($prestations);
    }

    // =====================================================
    // DEVIS
    // =====================================================
    public function devis(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        $statut = $_GET['statut'] ?? '';
        $where = 'WHERE 1=1' . $cfp;
        $params = [];
        if ($statut) { $where .= ' AND d.statut=?'; $params[] = $statut; }
        $stmt = $db->prepare("SELECT d.*, p.raison_sociale, p.ville FROM devis d JOIN prospects p ON p.id=d.prospect_id $where ORDER BY d.date_devis DESC");
        $stmt->execute($params); $devisList = $stmt->fetchAll();

        $stats = $db->query("SELECT d.statut, COUNT(*) as nb, COALESCE(SUM(d.montant_ttc),0) as total FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE 1=1 $cfp GROUP BY d.statut")->fetchAll();
        $statsMap = [];
        foreach ($stats as $s) $statsMap[$s['statut']] = $s;

        $activePage = 'commercial-devis';
        $pageTitle = 'Devis';
        ob_start();
        require APP_ROOT . '/views/commercial/devis-liste.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function devisForm(): void {
        $this->requireCabinet();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        $devis = null; $lignes = [];

        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        if ($id) {
            $stmt = $db->prepare("SELECT d.*, p.raison_sociale FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE d.id=? $cfp");
            $stmt->execute([$id]); $devis = $stmt->fetch();
            if (!$devis) redirect('/commercial/devis');
            $lstmt = $db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre");
            $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();
        }

        $prospects = $cabinetId
            ? $db->query("SELECT id, raison_sociale, ville FROM prospects WHERE type_contact != 'perdu' AND cabinet_id=$cabinetId ORDER BY raison_sociale")->fetchAll()
            : $db->query("SELECT id, raison_sociale, ville FROM prospects WHERE type_contact != 'perdu' ORDER BY raison_sociale")->fetchAll();
        $cfCat = isset($cabinetId) && $cabinetId ? "AND (cabinet_id = $cabinetId OR cabinet_id IS NULL)" : "";
        $prestations = $db->query("SELECT * FROM prestations_catalogue WHERE actif=1 $cfCat ORDER BY categorie, designation")->fetchAll();
        $prospectId = (int)($_GET['prospect_id'] ?? $devis['prospect_id'] ?? 0);

        $activePage = 'commercial-devis';
        $pageTitle = $id ? 'Modifier devis' : 'Nouveau devis';
        ob_start();
        require APP_ROOT . '/views/commercial/devis-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function storeDevis(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $id = (int)($_POST['id'] ?? 0);

        $montantHt  = (float)($_POST['montant_ht'] ?? 0);
        $montantTva = (float)($_POST['montant_tva'] ?? 0);
        $montantTtc = (float)($_POST['montant_ttc'] ?? 0);

        $data = [
            'prospect_id'         => (int)($_POST['prospect_id'] ?? 0),
            'date_devis'          => $_POST['date_devis'] ?? date('Y-m-d'),
            'date_validite'       => $_POST['date_validite'] ?? null,
            'objet'               => trim($_POST['objet'] ?? ''),
            'statut'              => $_POST['statut'] ?? 'brouillon',
            'montant_ht'          => $montantHt,
            'montant_tva'         => $montantTva,
            'montant_ttc'         => $montantTtc,
            'remise_globale'      => (float)($_POST['remise_globale'] ?? 0),
            'conditions_paiement' => trim($_POST['conditions_paiement'] ?? ''),
            'notes_client'        => trim($_POST['notes_client'] ?? ''),
            'notes_internes'      => trim($_POST['notes_internes'] ?? ''),
            'user_id'             => auth()['id'],
        ];

        if ($id) {
            // Vérifier appartenance cabinet
            $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
            if ($cabinetId) {
                $chk = $db->prepare("SELECT COUNT(*) FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE d.id=? AND p.cabinet_id=?");
                $chk->execute([$id, $cabinetId]);
                if (!$chk->fetchColumn()) redirect('/commercial/devis');
            }
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            $db->prepare("UPDATE devis SET $set WHERE id=?")->execute(array_merge(array_values($data), [$id]));
            $db->prepare("DELETE FROM devis_lignes WHERE devis_id=?")->execute([$id]);
        } else {
            $data['cabinet_id'] = (int)(auth()['cabinet_id'] ?? 0) ?: null;
            $data['numero'] = $this->nextRef('DEV', 'devis', 'numero');
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO devis ($cols) VALUES ($vals)")->execute(array_values($data));
            $id = $db->lastInsertId();
        }

        // Lignes
        $designations = $_POST['ligne_designation'] ?? [];
        foreach ($designations as $i => $des) {
            if (empty(trim($des))) continue;
            $qte  = (float)($_POST['ligne_qte'][$i] ?? 1);
            $pu   = (float)($_POST['ligne_pu'][$i] ?? 0);
            $rem  = (float)($_POST['ligne_remise'][$i] ?? 0);
            $tva  = (float)($_POST['ligne_tva'][$i] ?? 18);
            $ht   = $qte * $pu * (1 - $rem/100);
            $prestId = (int)($_POST['ligne_prestation_id'][$i] ?? 0) ?: null;
            $db->prepare("INSERT INTO devis_lignes (devis_id, prestation_id, designation, description, quantite, unite, prix_unitaire, remise, tva_taux, montant_ht, ordre) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$id, $prestId, trim($des), trim($_POST['ligne_desc'][$i] ?? ''), $qte, trim($_POST['ligne_unite'][$i] ?? 'forfait'), $pu, $rem, $tva, $ht, $i]);
        }

        // Mettre à jour stage prospect si devis envoyé
        if ($data['statut'] === 'envoye') {
            $db->prepare("UPDATE prospects SET pipeline_stage='devis_envoye' WHERE id=? AND pipeline_stage NOT IN ('client')")->execute([$data['prospect_id']]);
        }

        redirect('/commercial/devis/voir?id=' . $id);
    }

    public function devisVoir(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $id = (int)($_GET['id'] ?? 0);
        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        $stmt = $db->prepare("SELECT d.*, p.raison_sociale AS prospect_nom, p.adresse, p.ville AS prospect_ville, p.telephone, p.email, p.ninea, p.contact_nom, p.contact_prenom, p.forme_juridique AS prospect_forme FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE d.id=? $cfp");
        $stmt->execute([$id]); $devis = $stmt->fetch();
        if (!$devis) { http_response_code(404); echo "Devis introuvable"; exit; }
        $lstmt = $db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre");
        $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();

        $activePage = 'commercial-devis';
        $pageTitle = 'Devis ' . $devis['numero'];
        ob_start();
        require APP_ROOT . '/views/commercial/devis-voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function convertirDevisEnFacture(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $devisId = (int)($_POST['devis_id'] ?? 0);

        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        $stmt = $db->prepare("SELECT d.*, p.raison_sociale FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE d.id=? $cfp");
        $stmt->execute([$devisId]); $devis = $stmt->fetch();
        if (!$devis) { redirect('/commercial/devis'); }

        $lignes = $db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre");
        $lignes->execute([$devisId]); $lignes = $lignes->fetchAll();

        $numero = $this->nextRef('FAC', 'factures_commercial', 'numero');
        $db->prepare("INSERT INTO factures_commercial (cabinet_id, numero, prospect_id, devis_id, user_id, date_facture, date_echeance, objet, montant_ht, montant_tva, montant_ttc, remise_globale, conditions_paiement, notes_client) VALUES (?,?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 30 DAY),?,?,?,?,?,?,?)")
           ->execute([$cabinetId, $numero, $devis['prospect_id'], $devisId, auth()['id'], $devis['objet'], $devis['montant_ht'], $devis['montant_tva'], $devis['montant_ttc'], $devis['remise_globale'], $devis['conditions_paiement'], $devis['notes_client']]);
        $factureId = $db->lastInsertId();

        foreach ($lignes as $l) {
            $db->prepare("INSERT INTO factures_commercial_lignes (facture_id, prestation_id, designation, description, quantite, unite, prix_unitaire, remise, tva_taux, montant_ht, ordre) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$factureId, $l['prestation_id'], $l['designation'], $l['description'], $l['quantite'], $l['unite'], $l['prix_unitaire'], $l['remise'], $l['tva_taux'], $l['montant_ht'], $l['ordre']]);
        }

        $db->prepare("UPDATE devis SET statut='converti', facture_id=? WHERE id=?")->execute([$factureId, $devisId]);
        $db->prepare("UPDATE prospects SET pipeline_stage='client', type_contact='client' WHERE id=?")->execute([$devis['prospect_id']]);

        redirect('/commercial/factures/voir?id=' . $factureId);
    }

    // =====================================================
    // FACTURES
    // =====================================================
    public function factures(): void {
        $this->requireCabinet();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        $cf = $cabinetId ? " AND cabinet_id=$cabinetId" : "";

        // Mettre à jour les retards pour ce cabinet
        $db->query("UPDATE factures_commercial SET statut='en_retard' WHERE statut='envoyee' AND date_echeance < CURDATE() AND montant_paye < montant_ttc $cf");

        $statut = $_GET['statut'] ?? '';
        $where = 'WHERE 1=1' . $cfp;
        $params = [];
        if ($statut) { $where .= ' AND f.statut=?'; $params[] = $statut; }
        $stmt = $db->prepare("SELECT f.*, p.raison_sociale FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id $where ORDER BY f.date_facture DESC");
        $stmt->execute($params); $factures = $stmt->fetchAll();

        $stats = $db->query("SELECT f.statut, COUNT(*) as nb, COALESCE(SUM(f.montant_ttc),0) as total FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE 1=1 $cfp GROUP BY f.statut")->fetchAll();
        $statsMap = [];
        foreach ($stats as $s) $statsMap[$s['statut']] = $s;

        $activePage = 'commercial-factures';
        $pageTitle = 'Factures';
        ob_start();
        require APP_ROOT . '/views/commercial/factures-liste.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function factureForm(): void {
        $this->requireCabinet();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        $facture = null; $lignes = [];

        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cfp = $cabinetId ? " AND p.cabinet_id=$cabinetId" : "";
        if ($id) {
            $stmt = $db->prepare("SELECT f.*, p.raison_sociale FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE f.id=? $cfp");
            $stmt->execute([$id]); $facture = $stmt->fetch();
            if (!$facture) redirect('/commercial/factures');
            $lstmt = $db->prepare("SELECT * FROM factures_commercial_lignes WHERE facture_id=? ORDER BY ordre");
            $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();
        }

        $prospects = $cabinetId
            ? $db->query("SELECT id, raison_sociale, ville FROM prospects WHERE cabinet_id=$cabinetId ORDER BY raison_sociale")->fetchAll()
            : $db->query("SELECT id, raison_sociale, ville FROM prospects ORDER BY raison_sociale")->fetchAll();
        $cfCat = isset($cabinetId) && $cabinetId ? "AND (cabinet_id = $cabinetId OR cabinet_id IS NULL)" : "";
        $prestations = $db->query("SELECT * FROM prestations_catalogue WHERE actif=1 $cfCat ORDER BY categorie, designation")->fetchAll();
        $prospectId = (int)($_GET['prospect_id'] ?? $facture['prospect_id'] ?? 0);

        $activePage = 'commercial-factures';
        $pageTitle = $id ? 'Modifier facture' : 'Nouvelle facture';
        ob_start();
        require APP_ROOT . '/views/commercial/facture-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function storeFacture(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'prospect_id'         => (int)($_POST['prospect_id'] ?? 0),
            'date_facture'        => $_POST['date_facture'] ?? date('Y-m-d'),
            'date_echeance'       => $_POST['date_echeance'] ?? null,
            'objet'               => trim($_POST['objet'] ?? ''),
            'statut'              => $_POST['statut'] ?? 'brouillon',
            'montant_ht'          => (float)($_POST['montant_ht'] ?? 0),
            'montant_tva'         => (float)($_POST['montant_tva'] ?? 0),
            'montant_ttc'         => (float)($_POST['montant_ttc'] ?? 0),
            'remise_globale'      => (float)($_POST['remise_globale'] ?? 0),
            'conditions_paiement' => trim($_POST['conditions_paiement'] ?? 'Paiement à 30 jours'),
            'notes_client'        => trim($_POST['notes_client'] ?? ''),
            'user_id'             => auth()['id'],
        ];

        if ($id) {
            $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
            if ($cabinetId) {
                $chk = $db->prepare("SELECT COUNT(*) FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE f.id=? AND p.cabinet_id=?");
                $chk->execute([$id, $cabinetId]);
                if (!$chk->fetchColumn()) redirect('/commercial/factures');
            }
            $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
            $db->prepare("UPDATE factures_commercial SET $set WHERE id=?")->execute(array_merge(array_values($data), [$id]));
            $db->prepare("DELETE FROM factures_commercial_lignes WHERE facture_id=?")->execute([$id]);
        } else {
            $data['cabinet_id'] = (int)(auth()['cabinet_id'] ?? 0) ?: null;
            $data['numero'] = $this->nextRef('FAC', 'factures_commercial', 'numero');
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO factures_commercial ($cols) VALUES ($vals)")->execute(array_values($data));
            $id = $db->lastInsertId();
        }

        $lignes = $_POST['lignes'] ?? [];
        foreach ($lignes as $i => $l) {
            $des = trim($l['designation'] ?? '');
            if (empty($des)) continue;
            $qte     = (float)($l['quantite']     ?? 1);
            $pu      = (float)($l['prix_unitaire'] ?? 0);
            $rem     = (float)($l['remise']        ?? 0);
            $tva     = (float)($l['tva_taux']      ?? 18);
            $ht      = $qte * $pu * (1 - $rem / 100);
            $prestId = (int)($l['prestation_id']   ?? 0) ?: null;
            $db->prepare("INSERT INTO factures_commercial_lignes (facture_id, prestation_id, designation, description, quantite, unite, prix_unitaire, remise, tva_taux, montant_ht, ordre) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$id, $prestId, $des, trim($l['description'] ?? ''), $qte, trim($l['unite'] ?? 'forfait'), $pu, $rem, $tva, $ht, $i]);
        }

        redirect('/commercial/factures/voir?id=' . $id);
    }

    public function factureVoir(): void {
        $this->requireCabinet();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT f.*, p.raison_sociale AS prospect_nom, p.adresse, p.ville AS prospect_ville, p.telephone, p.email, p.ninea AS prospect_ninea, p.contact_nom, p.contact_prenom, p.forme_juridique AS prospect_forme FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE f.id=?");
        $stmt->execute([$id]); $facture = $stmt->fetch();
        if (!$facture) { http_response_code(404); echo "Facture introuvable"; exit; }

        $lstmt = $db->prepare("SELECT * FROM factures_commercial_lignes WHERE facture_id=? ORDER BY ordre");
        $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();

        $paiements = $db->prepare("SELECT * FROM paiements_commercial WHERE facture_id=? ORDER BY date_paiement DESC");
        $paiements->execute([$id]); $paiements = $paiements->fetchAll();

        $activePage = 'commercial-factures';
        $pageTitle = 'Facture ' . $facture['numero'];
        ob_start();
        require APP_ROOT . '/views/commercial/facture-voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function enregistrerPaiement(): void {
        $this->requireCabinet();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();
        $factureId = (int)($_POST['facture_id'] ?? 0);
        $montant   = (float)($_POST['montant'] ?? 0);

        $db->prepare("INSERT INTO paiements_commercial (facture_id, date_paiement, montant, moyen, reference, notes) VALUES (?,?,?,?,?,?)")
           ->execute([$factureId, $_POST['date_paiement'] ?? date('Y-m-d'), $montant, $_POST['moyen'] ?? 'virement', trim($_POST['reference'] ?? ''), trim($_POST['notes'] ?? '')]);

        // Recalcul montant payé
        $totalPaye = $db->prepare("SELECT COALESCE(SUM(montant),0) FROM paiements_commercial WHERE facture_id=?");
        $totalPaye->execute([$factureId]); $totalPaye = (float)$totalPaye->fetchColumn();

        $facture = $db->prepare("SELECT montant_ttc FROM factures_commercial WHERE id=?");
        $facture->execute([$factureId]); $ttc = (float)$facture->fetchColumn();

        $statut = $totalPaye >= $ttc ? 'payee' : ($totalPaye > 0 ? 'partiellement_payee' : 'envoyee');
        $db->prepare("UPDATE factures_commercial SET montant_paye=?, statut=? WHERE id=?")->execute([$totalPaye, $statut, $factureId]);

        redirect('/commercial/factures/voir?id=' . $factureId);
    }

    public function factureExport(): void {
        $this->requireCabinet();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT f.*, p.raison_sociale AS prospect_nom, p.adresse, p.ville AS prospect_ville, p.telephone, p.email, p.ninea AS prospect_ninea, p.contact_nom, p.contact_prenom, p.forme_juridique AS prospect_forme FROM factures_commercial f JOIN prospects p ON p.id=f.prospect_id WHERE f.id=?");
        $stmt->execute([$id]); $facture = $stmt->fetch();
        if (!$facture) { http_response_code(404); exit; }
        $lstmt = $db->prepare("SELECT * FROM factures_commercial_lignes WHERE facture_id=? ORDER BY ordre");
        $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();
        require APP_ROOT . '/views/commercial/facture-pdf.php';
    }

    public function devisExport(): void {
        $this->requireCabinet();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT d.*, p.raison_sociale AS prospect_nom, p.adresse, p.ville AS prospect_ville, p.telephone, p.email, p.ninea, p.contact_nom, p.contact_prenom, p.forme_juridique AS prospect_forme FROM devis d JOIN prospects p ON p.id=d.prospect_id WHERE d.id=?");
        $stmt->execute([$id]); $devis = $stmt->fetch();
        if (!$devis) { http_response_code(404); exit; }
        $lstmt = $db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre");
        $lstmt->execute([$id]); $lignes = $lstmt->fetchAll();
        require APP_ROOT . '/views/commercial/devis-pdf.php';
    }
}
