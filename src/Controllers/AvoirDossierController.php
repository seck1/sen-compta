<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Avoirs de vente côté DOSSIER (entreprise gérée).
 * Un avoir = écriture d'EXTOURNE (inverse) d'une facture de vente (journal VTE) :
 * on crédite le client (411) et on débite les produits (70x) + TVA collectée (443x).
 * Total (100 %) ou partiel (% de la facture).
 */
class AvoirDossierController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    private function exercice(array $ent): int {
        return (int)($_SESSION['exercice'][$ent['id']] ?? $ent['exercice_courant'] ?? date('Y'));
    }

    private function nextNumero(int $entId): string {
        $db = getDB();
        $year = date('Y');
        $stmt = $db->prepare("SELECT COUNT(*) FROM avoirs_dossier WHERE entreprise_id=? AND numero LIKE ?");
        $stmt->execute([$entId, "AV-$year-%"]);
        $n = (int)$stmt->fetchColumn() + 1;
        return "AV-$year-" . str_pad($n, 3, '0', STR_PAD_LEFT);
    }

    // ── Liste des avoirs + factures de vente extournables ─────────────────────
    public function index(): void {
        $id  = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db  = getDB();
        $exercice = $this->exercice($entreprise);

        // Avoirs déjà émis
        $stmt = $db->prepare("SELECT * FROM avoirs_dossier WHERE entreprise_id=? AND exercice=? ORDER BY date_avoir DESC, id DESC");
        $stmt->execute([$id, $exercice]);
        $avoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Factures de vente (écritures journal VTE ayant une ligne client 411)
        $stmtF = $db->prepare("
            SELECT e.id, e.numero_piece, e.numero_facture, e.date_ecriture, e.libelle,
                   (SELECT SUM(l.debit) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id
                     WHERE l.ecriture_id=e.id AND c.numero LIKE '411%') AS montant_client
            FROM ecritures e
            JOIN journaux j ON j.id=e.journal_id
            WHERE e.entreprise_id=? AND e.exercice=? AND j.code='VTE'
              AND (e.numero_facture IS NULL OR e.numero_facture NOT LIKE 'AV-%')  -- exclure les avoirs eux-mêmes
              AND e.libelle NOT LIKE 'Avoir %'
              AND EXISTS (SELECT 1 FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id
                          WHERE l.ecriture_id=e.id AND c.numero LIKE '411%')
              -- exclure les factures déjà entièrement extournées (un avoir 100% existe)
              AND NOT EXISTS (SELECT 1 FROM avoirs_dossier ad
                              WHERE ad.ecriture_origine_id=e.id AND ad.taux >= 100 AND ad.statut='emis')
            ORDER BY e.date_ecriture DESC, e.id DESC");
        $stmtF->execute([$id, $exercice]);
        $factures = $stmtF->fetchAll(PDO::FETCH_ASSOC);

        $activeTab = 'avoirs-dossier';
        $pageTitle = 'Avoirs de vente';
        ob_start();
        require APP_ROOT . '/views/dossier/avoirs-dossier.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ── Formulaire d'extourne pour une facture donnée ─────────────────────────
    public function form(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();
        $ecritureId = (int)($_GET['ecriture'] ?? 0);

        $ecr = $this->chargerFactureVente($id, $ecritureId);
        if (!$ecr) { $_SESSION['flash_error'] = "Facture de vente introuvable."; redirect("/dossier/avoirs?id=$id"); }

        $activeTab = 'avoirs-dossier';
        $pageTitle = 'Nouvel avoir';
        $facture   = $ecr['entete'];
        $lignes    = $ecr['lignes'];
        ob_start();
        require APP_ROOT . '/views/dossier/avoir-dossier-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ── Création de l'avoir (écriture d'extourne) ─────────────────────────────
    public function store(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $ecritureId = (int)($_POST['ecriture_id'] ?? 0);
        $ecr = $this->chargerFactureVente($id, $ecritureId);
        if (!$ecr) { $_SESSION['flash_error'] = "Facture introuvable."; redirect("/dossier/avoirs?id=$id"); }

        $taux = (float)($_POST['taux'] ?? 100);
        if ($taux <= 0 || $taux > 100) $taux = 100;
        $coef = $taux / 100;

        $motif = $_POST['motif'] ?? 'autre';
        if (!in_array($motif, ['retour','remboursement','erreur','geste_commercial','autre'], true)) $motif = 'autre';
        $raison = trim($_POST['raison'] ?? '');
        $date   = $_POST['date_avoir'] ?? date('Y-m-d');
        $exercice = (int)$ecr['entete']['exercice'];

        // Journal VTE
        $jstmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='VTE' LIMIT 1");
        $jstmt->execute([$id]);
        $journalVte = (int)$jstmt->fetchColumn();
        if (!$journalVte) { $_SESSION['flash_error'] = "Journal VTE introuvable."; redirect("/dossier/avoirs?id=$id"); }

        $db->beginTransaction();
        try {
            $numeroAvoir = $this->nextNumero($id);
            $libelle = "Avoir $numeroAvoir sur " . ($ecr['entete']['numero_facture'] ?: $ecr['entete']['numero_piece']);
            if ($taux < 100) $libelle .= sprintf(' (%.0f%%)', $taux);

            // Écriture d'extourne : on INVERSE débit/crédit de chaque ligne, * coef
            $numeroPiece = $this->genererNumeroPieceVte($id, $journalVte, $date);
            $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, user_id, date_ecriture, libelle, exercice, periode, statut, numero_piece, numero_facture)
                          VALUES (?,?,?,?,?,?,?, 'validee', ?, ?)")
               ->execute([$id, $journalVte, auth()['id'], $date, $libelle, $exercice, (int)date('m', strtotime($date)),
                          $numeroPiece, $numeroAvoir]);
            $avoirEcrId = (int)$db->lastInsertId();

            $insL = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit, tiers_id) VALUES (?,?,?,?,?,?)");
            $montantTtc = 0.0;
            foreach ($ecr['lignes'] as $l) {
                $debit  = round((float)$l['credit'] * $coef, 2); // inversion
                $credit = round((float)$l['debit']  * $coef, 2);
                if (abs($debit) < 0.005 && abs($credit) < 0.005) continue;
                $insL->execute([$avoirEcrId, (int)$l['compte_id'], 'Avoir — ' . $l['libelle'], $debit, $credit,
                                $l['tiers_id'] ? (int)$l['tiers_id'] : null]);
                // Montant TTC extourné = montant client (411) de la facture d'origine (au débit dans la facture),
                // ramené au % extourné.
                if (strpos((string)$l['numero'], '411') === 0) {
                    $montantTtc += round((float)$l['debit'] * $coef, 2);
                }
            }

            // Traçabilité
            $db->prepare("INSERT INTO avoirs_dossier
                (entreprise_id, numero, ecriture_origine_id, ecriture_avoir_id, numero_facture_origine, exercice, date_avoir, motif, raison, taux, montant, statut, user_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?, 'emis', ?)")
               ->execute([$id, $numeroAvoir, $ecritureId, $avoirEcrId,
                          $ecr['entete']['numero_facture'] ?: $ecr['entete']['numero_piece'],
                          $exercice, $date, $motif, $raison ?: null, $taux, $montantTtc, auth()['id']]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur lors de la création de l'avoir : " . $e->getMessage();
            redirect("/dossier/avoirs/creer?id=$id&ecriture=$ecritureId");
        }

        $_SESSION['flash_success'] = "Avoir $numeroAvoir créé : écriture d'extourne enregistrée dans le journal VTE.";
        redirect("/dossier/avoirs?id=$id");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Charge une facture de vente (entête + lignes) en vérifiant que c'est bien du VTE. */
    private function chargerFactureVente(int $entId, int $ecritureId): ?array {
        $db = getDB();
        $stmt = $db->prepare("SELECT e.*, j.code AS journal_code FROM ecritures e
                              JOIN journaux j ON j.id=e.journal_id
                              WHERE e.id=? AND e.entreprise_id=? AND j.code='VTE'");
        $stmt->execute([$ecritureId, $entId]);
        $entete = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$entete) return null;

        $lstmt = $db->prepare("SELECT l.*, c.numero, c.intitule FROM lignes_ecritures l
                               JOIN comptes c ON c.id=l.compte_id WHERE l.ecriture_id=? ORDER BY l.id");
        $lstmt->execute([$ecritureId]);
        $lignes = $lstmt->fetchAll(PDO::FETCH_ASSOC);
        return ['entete' => $entete, 'lignes' => $lignes];
    }

    /** Numéro de pièce VTE (séquence par journal/exercice). */
    private function genererNumeroPieceVte(int $entId, int $journalId, string $date): string {
        $db = getDB();
        $an = date('Y', strtotime($date));
        $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND journal_id=? AND exercice=?");
        $stmt->execute([$entId, $journalId, (int)$an]);
        $seq = ((int)$stmt->fetchColumn()) + 1;
        return 'VTE-' . $an . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
