<?php
class HonorairesController {

    private function requireAdmin(): void {
        requireAuth();
        if (!isAdmin()) { redirect('/dashboard'); }
    }

    public function index(): void {
        $this->requireAdmin();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cabJoin = $cabinetId ? " AND e.cabinet_id = $cabinetId" : "";

        // Stats filtrées par cabinet
        $mois_courant = date('Y-m');
        $annee = date('Y');

        $stmt = $db->prepare("SELECT COALESCE(SUM(h.montant_ttc),0) FROM honoraires h JOIN entreprises e ON e.id=h.entreprise_id WHERE DATE_FORMAT(h.date_facture,'%Y-%m') = ? $cabJoin");
        $stmt->execute([$mois_courant]);
        $ca_mois = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(h.montant_ttc),0) FROM honoraires h JOIN entreprises e ON e.id=h.entreprise_id WHERE YEAR(h.date_facture) = ? $cabJoin");
        $stmt->execute([$annee]);
        $ca_annee = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(h.montant_ttc),0) FROM honoraires h JOIN entreprises e ON e.id=h.entreprise_id WHERE h.statut = 'emise' AND h.date_echeance < CURDATE() $cabJoin");
        $stmt->execute();
        $impayes = $stmt->fetchColumn();

        // Filters
        $filtre_statut = $_GET['statut'] ?? '';
        $filtre_entreprise = (int)($_GET['entreprise_id'] ?? 0);
        $filtre_mois = $_GET['periode'] ?? '';

        $sql = "SELECT h.*, e.raison_sociale FROM honoraires h JOIN entreprises e ON e.id = h.entreprise_id WHERE 1=1 $cabJoin";
        $params = [];
        if ($filtre_statut) { $sql .= " AND h.statut = ?"; $params[] = $filtre_statut; }
        if ($filtre_entreprise) { $sql .= " AND h.entreprise_id = ?"; $params[] = $filtre_entreprise; }
        if ($filtre_mois) { $sql .= " AND DATE_FORMAT(h.date_facture,'%Y-%m') = ?"; $params[] = $filtre_mois; }
        $sql .= " ORDER BY h.date_facture DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $honoraires = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Entreprises filtrées par cabinet
        if ($cabinetId) {
            $stmt = $db->prepare("SELECT id, raison_sociale FROM entreprises WHERE cabinet_id = ? ORDER BY raison_sociale");
            $stmt->execute([$cabinetId]);
        } else {
            $stmt = $db->query("SELECT id, raison_sociale FROM entreprises ORDER BY raison_sociale");
        }
        $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Honoraires';
        ob_start();
        require APP_ROOT . '/views/honoraires/index.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function creer(): void {
        $this->requireAdmin();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        if ($cabinetId) {
            $stmt = $db->prepare("SELECT id, raison_sociale FROM entreprises WHERE cabinet_id = ? ORDER BY raison_sociale");
            $stmt->execute([$cabinetId]);
        } else {
            $stmt = $db->query("SELECT id, raison_sociale FROM entreprises ORDER BY raison_sociale");
        }
        $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Auto numero
        $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(numero_facture, 5) AS UNSIGNED)) FROM honoraires WHERE numero_facture LIKE 'FAC-%'");
        $last_num = (int)$stmt->fetchColumn();
        $numero_auto = 'FAC-' . str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);

        $pageTitle = 'Nouvelle facture';
        ob_start();
        require APP_ROOT . '/views/honoraires/form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function store(): void {
        $this->requireAdmin();
        $db = getDB();

        $entreprise_id = (int)$_POST['entreprise_id'];
        $montant_ht    = (float)$_POST['montant_ht'];
        $taux_tva      = (float)($_POST['taux_tva'] ?? 18);
        $montant_tva   = round($montant_ht * $taux_tva / 100, 2);
        $montant_ttc   = round($montant_ht + $montant_tva, 2);

        $date_echeance = $_POST['date_echeance'] ?: date('Y-m-d', strtotime('+30 days'));

        $db->beginTransaction();
        try {
            // Générer le numéro de facture de façon atomique sous verrou
            $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(numero_facture, 5) AS UNSIGNED)) FROM honoraires WHERE numero_facture LIKE 'FAC-%' FOR UPDATE");
            $last_num = (int)$stmt->fetchColumn();
            $numero_facture = 'FAC-' . str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("INSERT INTO honoraires
                (entreprise_id, user_id, numero_facture, date_facture, date_echeance,
                 libelle, montant_ht, taux_tva, montant_tva, montant_ttc, statut)
                VALUES (?,?,?,?,?,?,?,?,?,?,'emise')");
            $stmt->execute([
                $entreprise_id,
                auth()['id'],
                $numero_facture,
                $_POST['date_facture'],
                $date_echeance,
                $_POST['description'] ?? $_POST['libelle'] ?? 'Honoraires',
                $montant_ht, $taux_tva, $montant_tva, $montant_ttc
            ]);
        // Save lignes
            $honoraire_id = $db->lastInsertId();

            $libelles   = $_POST['ligne_libelle'] ?? [];
            $quantites  = $_POST['ligne_qte'] ?? [];
            $prix_units = $_POST['ligne_pu'] ?? [];

            foreach ($libelles as $i => $libelle) {
                if (empty($libelle)) continue;
                $qte   = (float)($quantites[$i] ?? 1);
                $pu    = (float)($prix_units[$i] ?? 0);
                $total = $qte * $pu;
                $stmt  = $db->prepare("INSERT INTO honoraires_lignes (honoraire_id, designation, quantite, prix_unitaire, montant) VALUES (?,?,?,?,?)");
                $stmt->execute([$honoraire_id, $libelle, $qte, $pu, $total]);
            }

            // Écritures comptables SYSCOHADA
            $this->genererEcrituresHonoraires($db, $entreprise_id, $honoraire_id, $numero_facture, $_POST['date_facture'], $montant_ht, $montant_tva, $montant_ttc, $taux_tva);

            $db->commit();
            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'HONORAIRE_CREE', $entreprise_id, 'honoraires', $honoraire_id, "Facture $numero_facture — " . number_format($montant_ttc, 0, ',', ' ') . " FCFA");
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['form_error'] = 'Erreur lors de la création de la facture.';
            redirect('/honoraires/creer');
            return;
        }

        redirect("/honoraires/voir?id=$honoraire_id");
    }

    public function voir(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT h.*, e.raison_sociale, e.adresse, e.telephone, e.email, e.ninea, e.rccm
                               FROM honoraires h JOIN entreprises e ON e.id = h.entreprise_id WHERE h.id = ?");
        $stmt->execute([$id]);
        $honoraire = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$honoraire) { http_response_code(404); echo "Facture introuvable"; exit; }

        $stmt = $db->prepare("SELECT * FROM honoraires_lignes WHERE honoraire_id = ? ORDER BY id");
        $stmt->execute([$id]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Facture ' . $honoraire['numero_facture'];
        ob_start();
        require APP_ROOT . '/views/honoraires/voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function pdf(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT h.*, e.raison_sociale, e.adresse, e.telephone, e.email, e.ninea, e.rccm, e.forme_juridique
                               FROM honoraires h JOIN entreprises e ON e.id = h.entreprise_id WHERE h.id = ?");
        $stmt->execute([$id]);
        $honoraire = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$honoraire) { http_response_code(404); echo "Facture introuvable"; exit; }

        $stmt = $db->prepare("SELECT * FROM honoraires_lignes WHERE honoraire_id = ? ORDER BY id");
        $stmt->execute([$id]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Render print-optimized HTML directly (no layout)
        require APP_ROOT . '/views/honoraires/pdf.php';
    }

    public function missions(): void {
        $this->requireAdmin();
        $db = getDB();

        $stmt = $db->query("SELECT m.*, e.raison_sociale FROM missions m JOIN entreprises e ON e.id = m.entreprise_id ORDER BY m.date_debut DESC");
        $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Missions';
        ob_start();
        require APP_ROOT . '/views/honoraires/missions.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function creerMission(): void {
        $this->requireAdmin();
        $db = getDB();
        $stmt = $db->query("SELECT id, raison_sociale FROM entreprises ORDER BY raison_sociale");
        $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Nouvelle mission';
        ob_start();
        require APP_ROOT . '/views/honoraires/mission-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function storeMission(): void {
        $this->requireAdmin();
        $db = getDB();

        $stmt = $db->prepare("INSERT INTO missions
            (entreprise_id, user_id, reference, libelle, type, date_debut, date_fin_prevue,
             budget_heures, taux_horaire, montant_forfait, note, statut)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,'en_cours')");
        $stmt->execute([
            (int)$_POST['entreprise_id'],
            auth()['id'],
            $_POST['reference'],
            $_POST['libelle'] ?? $_POST['description'] ?? '',
            $_POST['type'] ?? $_POST['type_mission'] ?? 'comptabilite',
            $_POST['date_debut'],
            $_POST['date_fin_prevue'] ?: null,
            (float)($_POST['budget_heures'] ?? $_POST['heures_estimees'] ?? 0),
            (float)($_POST['taux_horaire'] ?? 0),
            (float)($_POST['montant_forfait'] ?? 0) ?: null,
            $_POST['note'] ?? null,
        ]);

        redirect('/honoraires/missions');
    }

    public function lettreMission(): void {
        requireAuth();
        $mission_id = (int)($_GET['mission_id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT m.*, e.raison_sociale, e.adresse, e.ninea, e.rccm, e.logo, e.telephone as ent_tel, e.email as ent_email
            FROM missions m JOIN entreprises e ON e.id = m.entreprise_id
            WHERE m.id = ?");
        $stmt->execute([$mission_id]);
        $mission = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mission) { http_response_code(404); echo "Mission introuvable"; exit; }

        // Cabinet info
        $cabinet = [
            'nom'      => 'Cabinet SMC',
            'adresse'  => 'Dakar, Sénégal',
            'telephone'=> '',
            'email'    => '',
        ];

        require APP_ROOT . '/views/honoraires/lettre-mission-print.php';
        exit;
    }

    private function genererEcrituresHonoraires($db, int $entreprise_id, int $honoraire_id, string $numero, string $date, float $ht, float $tva, float $ttc, float $taux_tva): void {
        try {
            $stmt = $db->prepare("SELECT exercice_courant FROM entreprises WHERE id=?");
            $stmt->execute([$entreprise_id]);
            $exercice = (int)($stmt->fetchColumn() ?: date('Y'));
            $mois = (int)date('n', strtotime($date));

            // Journal VTE
            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='VTE' LIMIT 1");
            $jStmt->execute([$entreprise_id]);
            $journal = $jStmt->fetch(PDO::FETCH_ASSOC);
            if (!$journal) return;
            $journal_id = $journal['id'];

            $getCompte = function($numero_compte) use ($db, $entreprise_id) {
                $s = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $s->execute([$entreprise_id, $numero_compte]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                return $r ? (int)$r['id'] : null;
            };

            $user_id = auth()['id'];
            $libelle = "Honoraires $numero";

            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'brouillon')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $numero, $date, $libelle, $exercice, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");

            // DÉBIT 411 Clients (TTC)
            if ($c = $getCompte('411')) {
                $lStmt->execute([$ecriture_id, $c, $libelle, $ttc, 0]);
            }
            // CRÉDIT 706 Prestations de services (HT)
            if ($c = $getCompte('706')) {
                $lStmt->execute([$ecriture_id, $c, $libelle, 0, $ht]);
            }
            // CRÉDIT 4431 TVA facturée (si TVA > 0)
            if ($tva > 0 && $c = $getCompte('4431')) {
                $lStmt->execute([$ecriture_id, $c, "TVA $numero", 0, $tva]);
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la création de la facture si les écritures échouent
        }
    }

    public function marquerPaye(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $mode = $_POST['mode_paiement'] ?? 'virement';
        $date = $_POST['date_paiement'] ?? date('Y-m-d');
        $db = getDB();
        $stmtEnt = $db->prepare("SELECT entreprise_id, statut FROM honoraires WHERE id=?");
        $stmtEnt->execute([$id]);
        $hon = $stmtEnt->fetch();
        if (!$hon || $hon['statut'] === 'payee') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Facture déjà payée ou introuvable']); exit;
        }
        $ent_id = (int)$hon['entreprise_id'];
        $stmt = $db->prepare("UPDATE honoraires SET statut='payee', date_paiement=?, mode_paiement=? WHERE id=? AND statut != 'payee'");
        $stmt->execute([$date, $mode, $id]);
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log(auth()['id'], 'HONORAIRE_PAYE', $ent_id, 'honoraires', $id, "Facture #$id marquée payée");
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function tableau(): void {
        $this->requireAdmin();
        $db = getDB();
        $cabinetId = (int)(auth()['cabinet_id'] ?? 0) ?: null;
        $cabJoin = $cabinetId ? " AND e.cabinet_id = $cabinetId" : "";
        $cabFilter = $cabinetId ? " AND h.entreprise_id IN (SELECT id FROM entreprises WHERE cabinet_id = $cabinetId)" : "";

        $impayes = $db->query("
            SELECT h.*, e.raison_sociale,
                   DATEDIFF(CURDATE(), h.date_echeance) as jours_retard
            FROM honoraires h
            JOIN entreprises e ON e.id = h.entreprise_id
            WHERE h.statut IN ('emise','brouillon') $cabJoin
            ORDER BY h.date_echeance ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $stats = $db->query("
            SELECT
                SUM(CASE WHEN h.statut='payee' AND YEAR(h.date_facture)=".date('Y')." THEN h.montant_ttc ELSE 0 END) as encaisse,
                SUM(CASE WHEN h.statut IN ('emise','brouillon') THEN h.montant_ttc ELSE 0 END) as en_attente,
                SUM(CASE WHEN h.statut IN ('emise','brouillon') AND h.date_echeance < CURDATE() THEN h.montant_ttc ELSE 0 END) as en_retard,
                COUNT(CASE WHEN h.statut IN ('emise','brouillon') AND h.date_echeance < CURDATE() THEN 1 END) as nb_retard
            FROM honoraires h
            JOIN entreprises e ON e.id = h.entreprise_id
            WHERE 1=1 $cabJoin
        ")->fetch(PDO::FETCH_ASSOC);

        ob_start();
        require APP_ROOT . '/views/honoraires/tableau.php';
        $content = ob_get_clean();
        $pageTitle = 'Suivi des paiements';
        $activePage = 'honoraires';
        require APP_ROOT . '/views/layouts/main.php';
    }
}
