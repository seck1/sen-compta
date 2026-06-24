<?php
require_once APP_ROOT . '/config/app.php';

class RelanceController {

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

        // Créances clients en retard : lignes 411x avec solde débiteur non lettré
        $sql = "
            SELECT
                t.id as tiers_id, t.nom as tiers_nom, t.telephone, t.email,
                COALESCE(SUM(le.debit) - SUM(le.credit), 0) as solde,
                COUNT(DISTINCT e.id) as nb_ecritures,
                MIN(e.date_ecriture) as premiere_ecriture,
                MAX(e.date_ecriture) as derniere_ecriture,
                DATEDIFF(CURDATE(), MIN(e.date_ecriture)) as jours_retard,
                r.id as relance_id, r.statut as relance_statut, r.niveau as relance_niveau,
                r.date_relance, r.notes as relance_notes
            FROM tiers t
            JOIN lignes_ecritures le ON le.tiers_id = t.id
            JOIN ecritures e ON e.id = le.ecriture_id
            JOIN comptes c ON c.id = le.compte_id
            LEFT JOIN relances r ON r.tiers_id = t.id AND r.entreprise_id = ? AND r.statut != 'reglee'
            WHERE t.entreprise_id = ? AND t.actif = 1
              AND (t.type = 'client' OR t.type = 'les_deux')
              AND c.numero LIKE '411%'
              AND e.entreprise_id = ?
              AND (le.code_lettrage IS NULL OR le.code_lettrage = '')
            GROUP BY t.id, r.id
            HAVING solde > 0
            ORDER BY jours_retard DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $id, $id]);
        $creances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Historique relances
        $stmtHist = $db->prepare("
            SELECT r.*, t.nom as tiers_nom, u.prenom, u.nom as user_nom
            FROM relances r
            JOIN tiers t ON t.id = r.tiers_id
            JOIN users u ON u.id = r.user_id
            WHERE r.entreprise_id = ?
            ORDER BY r.created_at DESC
            LIMIT 50
        ");
        $stmtHist->execute([$id]);
        $historique = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

        // KPIs
        $total_creances = array_sum(array_column($creances, 'solde'));
        $nb_retard_30   = count(array_filter($creances, fn($r) => $r['jours_retard'] > 30));
        $nb_retard_60   = count(array_filter($creances, fn($r) => $r['jours_retard'] > 60));
        $nb_retard_90   = count(array_filter($creances, fn($r) => $r['jours_retard'] > 90));

        ob_start();
        require APP_ROOT . '/views/dossier/relances.php';
        $content = ob_get_clean();
        $pageTitle = 'Relances clients';
        $activeTab = 'relances';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function enregistrer(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $tiers_id  = (int)($_POST['tiers_id'] ?? 0);
        $niveau    = (int)($_POST['niveau'] ?? 1);
        $notes     = trim($_POST['notes'] ?? '');
        $montant   = (float)($_POST['montant'] ?? 0);

        $stmt = $db->prepare("INSERT INTO relances (entreprise_id, tiers_id, montant, date_echeance, date_relance, niveau, statut, notes, user_id)
            VALUES (?, ?, ?, CURDATE(), CURDATE(), ?, 'relancee', ?, ?)
            ON DUPLICATE KEY UPDATE date_relance=CURDATE(), niveau=VALUES(niveau), notes=VALUES(notes), statut='relancee'");
        $stmt->execute([$id, $tiers_id, $montant, $niveau, $notes, auth()['id']]);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function envoyerEmail(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();

        $tiers_id = (int)($_POST['tiers_id'] ?? 0);
        $montant  = (float)($_POST['montant'] ?? 0);
        $niveau   = (int)($_POST['niveau'] ?? 1);
        $notes    = trim($_POST['notes'] ?? '');

        // Récupérer l'email du tiers
        $stmt = $db->prepare("SELECT nom, email FROM tiers WHERE id=? AND entreprise_id=?");
        $stmt->execute([$tiers_id, $id]);
        $tiers = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');

        if (!$tiers || empty($tiers['email'])) {
            echo json_encode(['ok' => false, 'error' => 'Email du client introuvable']);
            return;
        }

        $ok = mailRelanceClient(
            $tiers['email'],
            $tiers['nom'],
            $entreprise['raison_sociale'],
            $montant,
            $niveau,
            $notes
        );

        if ($ok) {
            // Enregistrer aussi la relance
            $stmt = $db->prepare("INSERT INTO relances (entreprise_id, tiers_id, montant, date_echeance, date_relance, niveau, statut, notes, user_id)
                VALUES (?, ?, ?, CURDATE(), CURDATE(), ?, 'relancee', ?, ?)
                ON DUPLICATE KEY UPDATE date_relance=CURDATE(), niveau=VALUES(niveau), notes=VALUES(notes), statut='relancee'");
            $stmt->execute([$id, $tiers_id, $montant, $niveau, 'Email envoyé. '.$notes, auth()['id']]);
            echo json_encode(['ok' => true, 'message' => 'Email envoyé à '.$tiers['email']]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Échec de l\'envoi. Vérifiez la config SMTP.']);
        }
    }

    public function marquerReglee(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $tiers_id = (int)($_POST['tiers_id'] ?? 0);
        $db->prepare("UPDATE relances SET statut='reglee' WHERE entreprise_id=? AND tiers_id=?")->execute([$id, $tiers_id]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
