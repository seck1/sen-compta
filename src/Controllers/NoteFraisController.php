<?php
require_once APP_ROOT . '/config/app.php';

class NoteFraisController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    public function index(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();
        $exercice   = $entreprise['exercice_courant'];

        $filtre_statut = $_GET['statut'] ?? '';
        $filtre_mois   = (int)($_GET['mois'] ?? 0);

        $sql = "SELECT nf.*, u.prenom, u.nom as user_nom,
                       CONCAT(emp.prenom, ' ', emp.nom) as employe_nom
                FROM notes_frais nf
                JOIN users u ON u.id = nf.user_id
                LEFT JOIN employes emp ON emp.id = nf.employe_id
                WHERE nf.entreprise_id = ? AND nf.exercice = ?";
        $params = [$id, $exercice];

        if ($filtre_statut) { $sql .= " AND nf.statut = ?"; $params[] = $filtre_statut; }
        if ($filtre_mois)   { $sql .= " AND nf.mois = ?";   $params[] = $filtre_mois; }
        $sql .= " ORDER BY nf.date_depense DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // KPIs
        $total_soumis   = array_sum(array_column(array_filter($notes, fn($n) => $n['statut'] === 'soumise'),   'montant'));
        $total_approuve = array_sum(array_column(array_filter($notes, fn($n) => $n['statut'] === 'approuvee'), 'montant'));
        $total_rembourse = array_sum(array_column(array_filter($notes, fn($n) => $n['statut'] === 'remboursee'), 'montant'));

        // Employés pour le select
        $stmtEmp = $db->prepare("SELECT id, CONCAT(prenom,' ',nom) as nom_complet FROM employes WHERE entreprise_id=? AND statut='actif' ORDER BY nom");
        $stmtEmp->execute([$id]);
        $employes = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

        $categories = ['transport'=>'Transport','repas'=>'Repas','hebergement'=>'Hébergement',
                       'fournitures'=>'Fournitures','communication'=>'Communication','autre'=>'Autre'];
        $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        ob_start();
        require APP_ROOT . '/views/dossier/notes-frais.php';
        $content = ob_get_clean();
        $pageTitle = 'Notes de frais';
        $activeTab = 'notes-frais';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $exercice = $entreprise['exercice_courant'];
        $db = getDB();

        $date     = $_POST['date_depense'] ?? date('Y-m-d');
        $cat      = $_POST['categorie'] ?? 'autre';
        $libelle  = trim($_POST['libelle'] ?? '');
        $montant  = (float)str_replace([' ',','],['','.'], $_POST['montant'] ?? '0');
        $emp_id   = (int)($_POST['employe_id'] ?? 0) ?: null;
        $notes    = trim($_POST['notes'] ?? '');
        $mois     = (int)date('n', strtotime($date));

        // Upload justificatif
        $justificatif = null;
        if (!empty($_FILES['justificatif']['name'])) {
            $ext = pathinfo($_FILES['justificatif']['name'], PATHINFO_EXTENSION);
            $fname = 'nf_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest = APP_ROOT . '/public/uploads/justificatifs/' . $fname;
            if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $dest)) {
                $justificatif = $fname;
            }
        }

        if (!$libelle || $montant <= 0) redirect("/dossier/notes-frais?id=$id");

        $stmt = $db->prepare("INSERT INTO notes_frais (entreprise_id, user_id, employe_id, exercice, mois, date_depense, categorie, libelle, montant, justificatif, statut, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,'soumise',?)");
        $stmt->execute([$id, auth()['id'], $emp_id, $exercice, $mois, $date, $cat, $libelle, $montant, $justificatif, $notes]);

        redirect("/dossier/notes-frais?id=$id&saved=1");
    }

    public function updateStatut(): void {
        requireAuth();
        $id    = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db    = getDB();
        $nf_id = (int)($_POST['note_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $allowed = ['soumise','approuvee','rejetee','remboursee'];
        if (in_array($statut, $allowed)) {
            // Récupérer la note avant mise à jour
            $stmt = $db->prepare("SELECT * FROM notes_frais WHERE id=? AND entreprise_id=?");
            $stmt->execute([$nf_id, $id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);

            $db->prepare("UPDATE notes_frais SET statut=? WHERE id=? AND entreprise_id=?")->execute([$statut, $nf_id, $id]);

            // Générer écriture comptable à l'approbation
            if ($note && $statut === 'approuvee') {
                $this->genererEcrituresNoteFrais($db, $id, $note, $entreprise['exercice_courant']);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    private function genererEcrituresNoteFrais($db, int $entreprise_id, array $note, string $exercice): void {
        try {
            $comptes_charge = [
                'transport'     => '6251',
                'repas'         => '6252',
                'hebergement'   => '6253',
                'fournitures'   => '6064',
                'communication' => '6262',
                'autre'         => '6258',
            ];

            $cat     = $note['categorie'] ?? 'autre';
            $num_charge = $comptes_charge[$cat] ?? '6258';
            $montant = (float)$note['montant'];
            $date    = $note['date_depense'];
            $mois    = (int)$note['mois'];
            $libelle = 'Note de frais : ' . $note['libelle'];
            $piece   = 'NDF-' . $note['id'];

            // Journal OD
            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='OD' LIMIT 1");
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
            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'brouillon')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $piece, $date, $libelle, (int)$exercice, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");

            // DÉBIT compte de charge (625x, 6064...)
            if ($c = $getCompte($num_charge)) {
                $lStmt->execute([$ecriture_id, $c, $libelle, $montant, 0]);
            }
            // CRÉDIT 467 Autres créditeurs (à rembourser à l'employé)
            if ($c = $getCompte('467')) {
                $lStmt->execute([$ecriture_id, $c, $libelle, 0, $montant]);
            }
        } catch (\Exception $e) {
            // Ne pas bloquer l'approbation si les écritures échouent
        }
    }
}
