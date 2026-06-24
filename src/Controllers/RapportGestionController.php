<?php
require_once APP_ROOT . '/config/app.php';

class RapportGestionController {

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

        $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $mois_courant = (int)($_GET['mois'] ?? date('n'));
        $mois_courant = max(1, min(12, $mois_courant));

        ob_start();
        require APP_ROOT . '/views/dossier/rapport-gestion.php';
        $content = ob_get_clean();
        $pageTitle = 'Rapport de gestion';
        $activeTab = 'rapport-gestion';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function export(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();
        $exercice   = $entreprise['exercice_courant'];

        $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $mois_courant = (int)($_GET['mois'] ?? date('n'));
        $mois_courant = max(1, min(12, $mois_courant));

        // === Données CA et charges ===
        // CA cumulé (comptes 7x) jusqu'au mois courant
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(le.credit - le.debit), 0) as ca
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%'
              AND MONTH(e.date_ecriture) <= ?
        ");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $ca_cumule = (float)$stmt->fetchColumn();

        // Charges cumulées (comptes 6x)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(le.debit - le.credit), 0) as charges
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%'
              AND MONTH(e.date_ecriture) <= ?
        ");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $charges_cumulees = (float)$stmt->fetchColumn();

        $resultat_cumule = $ca_cumule - $charges_cumulees;

        // Mois précédent pour comparaison
        $mois_prec = $mois_courant - 1;
        $ca_prec = 0; $charges_prec = 0;
        if ($mois_prec >= 1) {
            $stmt = $db->prepare("SELECT COALESCE(SUM(le.credit - le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)<=?");
            $stmt->execute([$id, $exercice, $mois_prec]);
            $ca_prec = (float)$stmt->fetchColumn();
            $stmt = $db->prepare("SELECT COALESCE(SUM(le.debit - le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)<=?");
            $stmt->execute([$id, $exercice, $mois_prec]);
            $charges_prec = (float)$stmt->fetchColumn();
        }
        $ca_mois        = $ca_cumule - $ca_prec;
        $charges_mois   = $charges_cumulees - $charges_prec;
        $resultat_mois  = $ca_mois - $charges_mois;

        // Trésorerie (comptes 5x)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(le.debit - le.credit), 0) as tresorerie
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '5%'
              AND MONTH(e.date_ecriture) <= ?
        ");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $tresorerie = (float)$stmt->fetchColumn();

        // Créances clients (411x non lettrées)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(le.debit - le.credit), 0) as creances
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND c.numero LIKE '411%'
              AND (le.code_lettrage IS NULL OR le.code_lettrage = '')
        ");
        $stmt->execute([$id]);
        $creances_clients = (float)$stmt->fetchColumn();

        // Dettes fournisseurs (401x non lettrées)
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(le.credit - le.debit), 0) as dettes
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND c.numero LIKE '401%'
              AND (le.code_lettrage IS NULL OR le.code_lettrage = '')
        ");
        $stmt->execute([$id]);
        $dettes_fournisseurs = (float)$stmt->fetchColumn();

        // Évolution mensuelle CA et charges
        $evolution = [];
        for ($m = 1; $m <= $mois_courant; $m++) {
            $stmt = $db->prepare("SELECT COALESCE(SUM(le.credit-le.debit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%' AND MONTH(e.date_ecriture)=?");
            $stmt->execute([$id, $exercice, $m]);
            $ca_m = (float)$stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COALESCE(SUM(le.debit-le.credit),0) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' AND MONTH(e.date_ecriture)=?");
            $stmt->execute([$id, $exercice, $m]);
            $ch_m = (float)$stmt->fetchColumn();

            $evolution[$m] = ['ca' => $ca_m, 'charges' => $ch_m, 'resultat' => $ca_m - $ch_m];
        }

        // Top 5 postes de charges
        $stmt = $db->prepare("
            SELECT c.numero, c.intitule,
                   COALESCE(SUM(le.debit - le.credit), 0) as total
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%'
              AND MONTH(e.date_ecriture) <= ? AND LENGTH(c.numero) <= 4
            GROUP BY c.id
            HAVING total > 0
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $top_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top 5 postes de produits
        $stmt = $db->prepare("
            SELECT c.numero, c.intitule,
                   COALESCE(SUM(le.credit - le.debit), 0) as total
            FROM lignes_ecritures le
            JOIN comptes c ON c.id = le.compte_id
            JOIN ecritures e ON e.id = le.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%'
              AND MONTH(e.date_ecriture) <= ? AND LENGTH(c.numero) <= 4
            GROUP BY c.id
            HAVING total > 0
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $top_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nb écritures du mois
        $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND exercice=? AND MONTH(date_ecriture)=?");
        $stmt->execute([$id, $exercice, $mois_courant]);
        $nb_ecritures = (int)$stmt->fetchColumn();

        require APP_ROOT . '/views/dossier/exports/rapport-gestion-print.php';
        exit;
    }
}
