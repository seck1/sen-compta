<?php
require_once APP_ROOT . '/config/app.php';

class ExportController {

    private function csvHeaders(string $filename): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
    }

    private function csvRow(array $row): string {
        return implode(';', array_map(function($v) {
            $v = str_replace('"', '""', $v ?? '');
            return '"' . $v . '"';
        }, $row)) . "\r\n";
    }

    public function ecritures(): void {
        requireAuth();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id || !userHasAccess($id)) { http_response_code(403); exit; }

        $entreprise = getEntreprise($id);
        $exercice   = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $stmt = $db->prepare("
            SELECT e.date_ecriture, e.numero_piece, j.code as journal, j.libelle as journal_libelle,
                   e.libelle, c.numero as compte, c.intitule as compte_intitule,
                   l.debit, l.credit, l.code_lettrage, e.statut
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            JOIN journaux j ON j.id = e.journal_id
            JOIN comptes c ON c.id = l.compte_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            ORDER BY e.date_ecriture, e.id, l.id
        ");
        $stmt->execute([$id, $exercice]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->csvHeaders('ecritures_' . $entreprise['code_dossier'] . '_' . $exercice . '.csv');

        echo $this->csvRow(['Date', 'N° Pièce', 'Journal', 'Libellé journal', 'Libellé écriture', 'Compte', 'Intitulé compte', 'Débit', 'Crédit', 'Lettrage', 'Statut']);
        foreach ($rows as $r) {
            echo $this->csvRow([
                $r['date_ecriture'], $r['numero_piece'], $r['journal'],
                $r['journal_libelle'], $r['libelle'], $r['compte'],
                $r['compte_intitule'],
                number_format($r['debit'], 2, ',', ''),
                number_format($r['credit'], 2, ',', ''),
                $r['code_lettrage'] ?? '', $r['statut']
            ]);
        }
        exit;
    }

    public function balance(): void {
        requireAuth();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id || !userHasAccess($id)) { http_response_code(403); exit; }

        $entreprise = getEntreprise($id);
        $exercice   = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $stmt = $db->prepare("
            SELECT c.numero, c.intitule, c.type_compte,
                   COALESCE(SUM(l.debit),0) as total_debit,
                   COALESCE(SUM(l.credit),0) as total_credit,
                   COALESCE(SUM(l.debit),0) - COALESCE(SUM(l.credit),0) as solde_debiteur,
                   COALESCE(SUM(l.credit),0) - COALESCE(SUM(l.debit),0) as solde_crediteur
            FROM comptes c
            LEFT JOIN lignes_ecritures l ON l.compte_id = c.id
            LEFT JOIN ecritures e ON e.id = l.ecriture_id AND e.exercice = ?
            WHERE c.entreprise_id = ?
            GROUP BY c.id
            HAVING total_debit > 0 OR total_credit > 0
            ORDER BY c.numero
        ");
        $stmt->execute([$exercice, $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->csvHeaders('balance_' . $entreprise['code_dossier'] . '_' . $exercice . '.csv');

        echo $this->csvRow(['Compte', 'Intitulé', 'Type', 'Total Débit', 'Total Crédit', 'Solde Débiteur', 'Solde Créditeur']);
        $totD = $totC = 0;
        foreach ($rows as $r) {
            $sd = max(0, (float)$r['solde_debiteur']);
            $sc = max(0, (float)$r['solde_crediteur']);
            $totD += $r['total_debit'];
            $totC += $r['total_credit'];
            echo $this->csvRow([
                $r['numero'], $r['intitule'], $r['type_compte'],
                number_format($r['total_debit'], 2, ',', ''),
                number_format($r['total_credit'], 2, ',', ''),
                number_format($sd, 2, ',', ''),
                number_format($sc, 2, ',', ''),
            ]);
        }
        echo $this->csvRow(['TOTAUX', '', '', number_format($totD,2,',',''), number_format($totC,2,',',''), '', '']);
        exit;
    }

    public function grandLivre(): void {
        requireAuth();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id || !userHasAccess($id)) { http_response_code(403); exit; }

        $entreprise = getEntreprise($id);
        $exercice   = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        $stmt = $db->prepare("
            SELECT c.numero, c.intitule, e.date_ecriture, e.numero_piece,
                   j.code as journal, e.libelle, l.debit, l.credit, l.code_lettrage
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            JOIN journaux j ON j.id = e.journal_id
            JOIN comptes c ON c.id = l.compte_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            ORDER BY c.numero, e.date_ecriture, e.id
        ");
        $stmt->execute([$id, $exercice]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->csvHeaders('grand-livre_' . $entreprise['code_dossier'] . '_' . $exercice . '.csv');

        echo $this->csvRow(['Compte', 'Intitulé', 'Date', 'N° Pièce', 'Journal', 'Libellé', 'Débit', 'Crédit', 'Lettrage']);
        foreach ($rows as $r) {
            echo $this->csvRow([
                $r['numero'], $r['intitule'], $r['date_ecriture'], $r['numero_piece'],
                $r['journal'], $r['libelle'],
                number_format($r['debit'], 2, ',', ''),
                number_format($r['credit'], 2, ',', ''),
                $r['code_lettrage'] ?? ''
            ]);
        }
        exit;
    }
}
