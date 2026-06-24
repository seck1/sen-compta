<?php
require_once APP_ROOT . '/config/app.php';

class ImportBancaireController {

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

        // Lignes importées non rapprochées
        $stmt = $db->prepare("SELECT * FROM releve_bancaire_lignes WHERE entreprise_id=? AND exercice=? ORDER BY date_operation DESC");
        $stmt->execute([$id, $exercice]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nb_total        = count($lignes);
        $nb_rapprochees  = count(array_filter($lignes, fn($l) => $l['rapprochee']));
        $nb_en_attente   = $nb_total - $nb_rapprochees;
        $solde_importe   = array_sum(array_map(fn($l) => $l['sens']==='credit' ? $l['montant'] : -$l['montant'], $lignes));

        $stats = compact('nb_total', 'nb_rapprochees', 'nb_en_attente', 'solde_importe');

        // Écritures disponibles pour rapprochement (journal BNQ/CAI, non encore liées)
        $stmtEc = $db->prepare("
            SELECT e.id, e.date_ecriture, e.libelle, e.numero_piece,
                   SUM(CASE WHEN le.debit > 0 THEN le.debit ELSE 0 END) as total_debit,
                   SUM(CASE WHEN le.credit > 0 THEN le.credit ELSE 0 END) as total_credit
            FROM ecritures e
            JOIN lignes_ecriture le ON le.ecriture_id = e.id
            WHERE e.entreprise_id = ?
              AND e.exercice = ?
              AND e.statut = 'validee'
              AND e.id NOT IN (
                  SELECT ecriture_id FROM releve_bancaire_lignes
                  WHERE entreprise_id = ? AND exercice = ? AND ecriture_id IS NOT NULL
              )
            GROUP BY e.id
            ORDER BY e.date_ecriture DESC
            LIMIT 200
        ");
        $stmtEc->execute([$id, $exercice, $id, $exercice]);
        $ecritures_dispo = $stmtEc->fetchAll(PDO::FETCH_ASSOC);

        // Messages flash
        $flash_imported = isset($_GET['imported']) ? (int)$_GET['imported'] : null;
        $flash_error    = $_GET['error'] ?? null;

        ob_start();
        require APP_ROOT . '/views/dossier/import-bancaire.php';
        $content = ob_get_clean();
        $pageTitle = 'Import relevé bancaire';
        $activeTab = 'import-bancaire';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function importCSV(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $exercice   = $entreprise['exercice_courant'];
        $db = getDB();

        if (empty($_FILES['csv']['tmp_name'])) redirect("/dossier/import-bancaire?id=$id&error=nofile");

        $format    = $_POST['format'] ?? 'auto';
        $separateur = $_POST['separateur'] ?? ';';
        $handle    = fopen($_FILES['csv']['tmp_name'], 'r');
        if (!$handle) redirect("/dossier/import-bancaire?id=$id&error=badfile");

        // Détecter encodage et convertir
        $content = file_get_contents($_FILES['csv']['tmp_name']);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }

        $lines   = explode("\n", $content);
        $headers = null;
        $imported = 0;
        $import_ref = 'IMP-' . date('YmdHis');

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            $cols = str_getcsv($line, $separateur);

            if (!$headers) {
                $headers = array_map('strtolower', array_map('trim', $cols));
                continue;
            }

            // Mapping auto des colonnes
            $row = array_combine(array_slice($headers, 0, count($cols)), $cols) ?: [];

            // Chercher date
            $date_val = $row['date'] ?? $row['date operation'] ?? $row['date_operation'] ?? $row['date valeur'] ?? null;
            if (!$date_val) continue;
            // Convertir format dd/mm/yyyy
            if (preg_match('#(\d{2})/(\d{2})/(\d{4})#', $date_val, $m)) {
                $date_val = "$m[3]-$m[2]-$m[1]";
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_val)) continue;

            // Chercher libellé
            $libelle = $row['libelle'] ?? $row['libellé'] ?? $row['description'] ?? $row['motif'] ?? $row['label'] ?? '';
            $libelle = trim($libelle);

            // Chercher montants
            $debit  = (float)str_replace([' ',',','€','F','CFA'], ['','.','',' ',''], $row['debit'] ?? $row['débit'] ?? $row['montant debit'] ?? '0');
            $credit = (float)str_replace([' ',',','€','F','CFA'], ['','.','',' ',''], $row['credit'] ?? $row['crédit'] ?? $row['montant credit'] ?? '0');

            // Si colonne "montant" unique avec signe
            if ($debit == 0 && $credit == 0) {
                $montant_raw = $row['montant'] ?? $row['amount'] ?? '0';
                $montant_val = (float)str_replace([' ',',','€','F','CFA'], ['','.','',' ',''], $montant_raw);
                if ($montant_val < 0) $debit  = abs($montant_val);
                else                  $credit = $montant_val;
            }

            if ($debit == 0 && $credit == 0) continue;

            $sens    = $debit > 0 ? 'debit' : 'credit';
            $montant = $debit > 0 ? $debit : $credit;

            $db->prepare("INSERT IGNORE INTO releve_bancaire_lignes (entreprise_id, exercice, date_operation, libelle, montant, sens, import_ref)
                VALUES (?,?,?,?,?,?,?)")->execute([$id, $exercice, $date_val, $libelle ?: 'Opération', $montant, $sens, $import_ref]);
            $imported++;
        }

        redirect("/dossier/import-bancaire?id=$id&imported=$imported");
    }

    public function rapprocher(): void {
        requireAuth();
        $id      = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db      = getDB();
        $ligne_id = (int)($_POST['ligne_id'] ?? 0);
        $ecriture_id = (int)($_POST['ecriture_id'] ?? 0);

        $db->prepare("UPDATE releve_bancaire_lignes SET rapprochee=1, ecriture_id=?, match_type='manuel' WHERE id=? AND entreprise_id=?")
           ->execute([$ecriture_id ?: null, $ligne_id, $id]);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function rapprochementAuto(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $exercice   = $entreprise['exercice_courant'];
        $db = getDB();

        header('Content-Type: application/json');

        // Récupérer les lignes relevé non rapprochées
        $stmtLignes = $db->prepare("
            SELECT * FROM releve_bancaire_lignes
            WHERE entreprise_id=? AND exercice=? AND rapprochee=0
            ORDER BY date_operation ASC
        ");
        $stmtLignes->execute([$id, $exercice]);
        $lignes = $stmtLignes->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les écritures disponibles (non encore liées à un relevé)
        $stmtEc = $db->prepare("
            SELECT e.id, e.date_ecriture, e.libelle,
                   SUM(CASE WHEN le.debit  > 0 THEN le.debit  ELSE 0 END) as total_debit,
                   SUM(CASE WHEN le.credit > 0 THEN le.credit ELSE 0 END) as total_credit
            FROM ecritures e
            JOIN lignes_ecriture le ON le.ecriture_id = e.id
            WHERE e.entreprise_id = ?
              AND e.exercice = ?
              AND e.statut = 'validee'
              AND e.id NOT IN (
                  SELECT ecriture_id FROM releve_bancaire_lignes
                  WHERE entreprise_id = ? AND exercice = ? AND ecriture_id IS NOT NULL
              )
            GROUP BY e.id
        ");
        $stmtEc->execute([$id, $exercice, $id, $exercice]);
        $ecritures = $stmtEc->fetchAll(PDO::FETCH_ASSOC);

        // IDs déjà utilisés dans cette session (pour éviter double-attribution)
        $ecUsees = [];

        $nb_auto      = 0;
        $nb_suggestions = 0;
        $nb_non_trouvees = 0;
        $details      = [];

        foreach ($lignes as $ligne) {
            $lMontant = (float)$ligne['montant'];
            $lSens    = $ligne['sens']; // 'debit' ou 'credit'
            $lDate    = strtotime($ligne['date_operation']);
            $lLib     = strtolower(trim($ligne['libelle']));

            // Sens opposé : débit relevé = crédit écriture (argent sorti du compte)
            $sensCible = ($lSens === 'debit') ? 'credit' : 'debit';

            $bestScore    = null;  // 'exact', 'montant_date', 'montant'
            $bestEcriture = null;
            $bestPriority = 99;

            foreach ($ecritures as $ec) {
                if (in_array($ec['id'], $ecUsees)) continue;

                $ecMontant = ($sensCible === 'credit')
                    ? (float)$ec['total_credit']
                    : (float)$ec['total_debit'];

                // Tolérance montant ±0.01
                if (abs($ecMontant - $lMontant) > 0.01) continue;

                $ecDate = strtotime($ec['date_ecriture']);
                $diffJours = abs($lDate - $ecDate) / 86400;
                $ecLib = strtolower(trim($ec['libelle']));

                // Calcul similarité libellé
                similar_text($lLib, $ecLib, $pct);

                // Priorité 1 : montant + date ±3j + libellé >40%
                if ($diffJours <= 3 && $pct > 40) {
                    if ($bestPriority > 1) {
                        $bestPriority = 1;
                        $bestScore    = 'exact';
                        $bestEcriture = $ec;
                    }
                // Priorité 2 : montant + date ±5j
                } elseif ($diffJours <= 5) {
                    if ($bestPriority > 2) {
                        $bestPriority = 2;
                        $bestScore    = 'montant_date';
                        $bestEcriture = $ec;
                    }
                // Priorité 3 : montant seul + même sens
                } elseif ($bestPriority > 3) {
                    $bestPriority = 3;
                    $bestScore    = 'montant';
                    $bestEcriture = $ec;
                }
            }

            if ($bestEcriture !== null && in_array($bestScore, ['exact', 'montant_date'])) {
                // Auto-rapprochement
                $db->prepare("
                    UPDATE releve_bancaire_lignes
                    SET rapprochee=1, ecriture_id=?, match_type='auto'
                    WHERE id=? AND entreprise_id=?
                ")->execute([$bestEcriture['id'], $ligne['id'], $id]);

                $ecUsees[] = $bestEcriture['id'];
                $nb_auto++;
                $details[] = [
                    'ligne_id'    => (int)$ligne['id'],
                    'ecriture_id' => (int)$bestEcriture['id'],
                    'score'       => $bestScore,
                    'montant'     => $lMontant,
                ];
            } elseif ($bestEcriture !== null && $bestScore === 'montant') {
                // Suggestion uniquement (match montant seul = trop risqué d'auto-confirmer)
                $nb_suggestions++;
                $details[] = [
                    'ligne_id'    => (int)$ligne['id'],
                    'ecriture_id' => (int)$bestEcriture['id'],
                    'score'       => 'suggestion',
                    'montant'     => $lMontant,
                ];
            } else {
                $nb_non_trouvees++;
            }
        }

        echo json_encode([
            'ok'           => true,
            'auto'         => $nb_auto,
            'suggestions'  => $nb_suggestions,
            'non_trouvees' => $nb_non_trouvees,
            'details'      => $details,
        ]);
    }

    public function supprimerLigne(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $ligne_id = (int)($_POST['ligne_id'] ?? 0);
        getDB()->prepare("DELETE FROM releve_bancaire_lignes WHERE id=? AND entreprise_id=?")->execute([$ligne_id, $id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
