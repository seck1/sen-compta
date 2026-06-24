<?php
require_once APP_ROOT . '/config/app.php';

class BudgetController {

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

        // Comptes de charges (6x) et produits (7x) avec budget et réalisé
        $mois_labels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

        $stmt = $db->prepare("
            SELECT c.id, c.numero, c.intitule,
                COALESCE(SUM(CASE WHEN SUBSTR(c.numero,1,1)='6' THEN le.debit - le.credit
                               WHEN SUBSTR(c.numero,1,1)='7' THEN le.credit - le.debit ELSE 0 END), 0) as realise_total,
                COALESCE((SELECT SUM(b.montant) FROM budgets b WHERE b.compte_id=c.id AND b.entreprise_id=? AND b.exercice=?), 0) as budget_total
            FROM comptes c
            LEFT JOIN lignes_ecritures le ON le.compte_id = c.id
            LEFT JOIN ecritures e ON e.id = le.ecriture_id AND e.entreprise_id = ? AND e.exercice = ?
            WHERE c.entreprise_id = ? AND (c.numero LIKE '6%' OR c.numero LIKE '7%')
              AND LENGTH(c.numero) <= 4
            GROUP BY c.id
            HAVING (realise_total != 0 OR budget_total != 0)
            ORDER BY c.numero
        ");
        $stmt->execute([$id, $exercice, $id, $exercice, $id]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Réalisé par mois (tous comptes 6x et 7x)
        $realise_mois = [];
        $budget_mois  = [];
        for ($m = 1; $m <= 12; $m++) {
            $stmtM = $db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN c.numero LIKE '7%' THEN le.credit - le.debit ELSE 0 END), 0) as produits,
                    COALESCE(SUM(CASE WHEN c.numero LIKE '6%' THEN le.debit - le.credit ELSE 0 END), 0) as charges
                FROM lignes_ecritures le
                JOIN comptes c ON c.id = le.compte_id
                JOIN ecritures e ON e.id = le.ecriture_id
                WHERE e.entreprise_id=? AND e.exercice=? AND MONTH(e.date_ecriture)=?
            ");
            $stmtM->execute([$id, $exercice, $m]);
            $row = $stmtM->fetch(PDO::FETCH_ASSOC);
            $realise_mois[$m] = $row;

            $stmtBM = $db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN c.numero LIKE '7%' THEN b.montant ELSE 0 END), 0) as produits,
                    COALESCE(SUM(CASE WHEN c.numero LIKE '6%' THEN b.montant ELSE 0 END), 0) as charges
                FROM budgets b
                JOIN comptes c ON c.id = b.compte_id
                WHERE b.entreprise_id=? AND b.exercice=? AND b.mois=?
            ");
            $stmtBM->execute([$id, $exercice, $m]);
            $budget_mois[$m] = $stmtBM->fetch(PDO::FETCH_ASSOC);
        }

        $total_realise_produits = array_sum(array_column($realise_mois, 'produits'));
        $total_realise_charges  = array_sum(array_column($realise_mois, 'charges'));
        $total_budget_produits  = array_sum(array_column($budget_mois, 'produits'));
        $total_budget_charges   = array_sum(array_column($budget_mois, 'charges'));

        ob_start();
        require APP_ROOT . '/views/dossier/budget.php';
        $content = ob_get_clean();
        $pageTitle = 'Budget vs Réalisé';
        $activeTab = 'budget';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $exercice = $entreprise['exercice_courant'];
        $db = getDB();

        $compte_id = (int)($_POST['compte_id'] ?? 0);
        $mois      = (int)($_POST['mois'] ?? 0);
        $montant   = (float)str_replace([' ', ','], ['', '.'], $_POST['montant'] ?? '0');

        if ($compte_id && $mois >= 1 && $mois <= 12) {
            $stmt = $db->prepare("INSERT INTO budgets (entreprise_id, exercice, compte_id, mois, montant)
                VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE montant=VALUES(montant)");
            $stmt->execute([$id, $exercice, $compte_id, $mois, $montant]);
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
