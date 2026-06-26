<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Avoirs commerciaux (notes de crédit).
 * Un avoir annule tout ou partie d'une facture : il réduit le reste dû.
 * Lignes pré-remplies depuis la facture d'origine, modifiables.
 */
class AvoirController {

    private function cabinetId(): ?int {
        requireAuth();
        return (int)(auth()['cabinet_id'] ?? 0) ?: null;
    }

    private function nextRef(): string {
        $db = getDB();
        $year = date('Y');
        $stmt = $db->prepare("SELECT COUNT(*) FROM avoirs_commercial WHERE numero LIKE ?");
        $stmt->execute(["AVOIR-$year-%"]);
        $n = (int)$stmt->fetchColumn() + 1;
        return "AVOIR-$year-" . str_pad($n, 3, '0', STR_PAD_LEFT);
    }

    /** Récupère une facture en vérifiant l'accès cabinet. */
    private function getFacture(int $factureId): ?array {
        $db = getDB();
        $cabinetId = $this->cabinetId();
        $sql = "SELECT f.*, p.raison_sociale AS prospect_nom FROM factures_commercial f
                JOIN prospects p ON p.id=f.prospect_id WHERE f.id=?";
        $params = [$factureId];
        if ($cabinetId) { $sql .= " AND p.cabinet_id=?"; $params[] = $cabinetId; }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ── Liste des avoirs ──────────────────────────────────────────────────────
    public function index(): void {
        $cabinetId = $this->cabinetId();
        $db = getDB();

        $sql = "SELECT a.*, f.numero AS facture_numero, p.raison_sociale AS client
                FROM avoirs_commercial a
                JOIN factures_commercial f ON f.id=a.facture_id
                JOIN prospects p ON p.id=a.prospect_id";
        $params = [];
        if ($cabinetId) { $sql .= " WHERE p.cabinet_id=?"; $params[] = $cabinetId; }
        $sql .= " ORDER BY a.date_avoir DESC, a.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $avoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activePage = 'commercial-avoirs';
        $pageTitle  = 'Avoirs';
        ob_start();
        require APP_ROOT . '/views/commercial/avoirs-liste.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    // ── Formulaire de création (pré-rempli depuis la facture) ─────────────────
    public function form(): void {
        $factureId = (int)($_GET['facture_id'] ?? 0);
        $facture   = $this->getFacture($factureId);
        if (!$facture) { http_response_code(404); echo "Facture introuvable"; exit; }

        $db = getDB();
        $lstmt = $db->prepare("SELECT * FROM factures_commercial_lignes WHERE facture_id=? ORDER BY ordre");
        $lstmt->execute([$factureId]);
        $lignes = $lstmt->fetchAll(PDO::FETCH_ASSOC);

        $activePage = 'commercial-avoirs';
        $pageTitle  = 'Nouvel avoir';
        ob_start();
        require APP_ROOT . '/views/commercial/avoir-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    // ── Enregistrement ────────────────────────────────────────────────────────
    public function store(): void {
        requireAuth();
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $factureId = (int)($_POST['facture_id'] ?? 0);
        $facture   = $this->getFacture($factureId);
        if (!$facture) { redirect('/commercial/factures'); }

        $motif  = $_POST['motif'] ?? 'autre';
        if (!in_array($motif, ['retour','remboursement','geste_commercial','erreur','autre'], true)) $motif = 'autre';
        $raison = trim($_POST['raison'] ?? '');
        $date   = $_POST['date_avoir'] ?? date('Y-m-d');
        $tauxTva = (float)($facture['taux_tva'] ?? 18);

        // Lignes
        $lignesPost = $_POST['lignes'] ?? [];
        $lignes = []; $totalHt = 0.0;
        foreach ($lignesPost as $i => $l) {
            $des = trim($l['designation'] ?? '');
            if ($des === '') continue;
            $qte = (float)($l['quantite'] ?? 1);
            $pu  = (float)($l['prix_unitaire'] ?? 0);
            $rem = (float)($l['remise'] ?? 0);
            $tva = (float)($l['tva_taux'] ?? $tauxTva);
            $ht  = $qte * $pu * (1 - $rem / 100);
            if (abs($ht) < 0.001) continue;
            $lignes[] = compact('des','qte','pu','rem','tva','ht') + ['desc' => trim($l['description'] ?? ''), 'ordre' => $i];
            $totalHt += $ht;
        }

        if (empty($lignes)) {
            $_SESSION['flash_error'] = "L'avoir doit contenir au moins une ligne avec un montant.";
            redirect('/commercial/avoirs/creer?facture_id=' . $factureId);
        }

        $montantTva = round($totalHt * $tauxTva / 100, 2);
        $montantTtc = round($totalHt + $montantTva, 2);

        // Garde-fou : l'avoir ne peut pas dépasser le TTC de la facture
        if ($montantTtc > (float)$facture['montant_ttc'] + 0.01) {
            $_SESSION['flash_error'] = sprintf(
                "Le montant de l'avoir (%s) dépasse le TTC de la facture (%s).",
                number_format($montantTtc,0,',',' '), number_format($facture['montant_ttc'],0,',',' ')
            );
            redirect('/commercial/avoirs/creer?facture_id=' . $factureId);
        }

        $db->beginTransaction();
        try {
            $numero = $this->nextRef();
            $db->prepare("INSERT INTO avoirs_commercial
                (numero, facture_id, prospect_id, user_id, date_avoir, motif, raison, montant_ht, taux_tva, montant_tva, montant_ttc, statut)
                VALUES (?,?,?,?,?,?,?,?,?,?,?, 'emis')")
               ->execute([$numero, $factureId, $facture['prospect_id'], auth()['id'], $date, $motif, $raison ?: null,
                          $totalHt, $tauxTva, $montantTva, $montantTtc]);
            $avoirId = (int)$db->lastInsertId();

            $insL = $db->prepare("INSERT INTO avoirs_commercial_lignes
                (avoir_id, designation, description, quantite, unite, prix_unitaire, remise, tva_taux, montant_ht, ordre)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            foreach ($lignes as $l) {
                $insL->execute([$avoirId, $l['des'], $l['desc'] ?: null, $l['qte'], 'forfait', $l['pu'], $l['rem'], $l['tva'], $l['ht'], $l['ordre']]);
            }

            // Effet sur la facture : réduit le reste dû. On augmente montant_paye d'un
            // équivalent "avoir" pour diminuer le restant ; si avoir = TTC -> facture soldée.
            $nouveauPaye = min((float)$facture['montant_ttc'], (float)$facture['montant_paye'] + $montantTtc);
            $resteDu = (float)$facture['montant_ttc'] - $nouveauPaye;
            if ($resteDu <= 0.01 && $montantTtc >= (float)$facture['montant_ttc'] - 0.01) {
                $statut = 'annulee'; // avoir total
            } elseif ($resteDu <= 0.01) {
                $statut = 'payee';
            } else {
                $statut = 'partiellement_payee';
            }
            $db->prepare("UPDATE factures_commercial SET montant_paye=?, statut=? WHERE id=?")
               ->execute([$nouveauPaye, $statut, $factureId]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur lors de la création de l'avoir : " . $e->getMessage();
            redirect('/commercial/avoirs/creer?facture_id=' . $factureId);
        }

        $_SESSION['flash_success'] = "Avoir $numero créé (" . number_format($montantTtc,0,',',' ') . " FCFA).";
        redirect('/commercial/avoirs/voir?id=' . $avoirId);
    }

    // ── Détail d'un avoir ─────────────────────────────────────────────────────
    public function voir(): void {
        $cabinetId = $this->cabinetId();
        $db = getDB();
        $id = (int)($_GET['id'] ?? 0);

        $sql = "SELECT a.*, f.numero AS facture_numero, p.raison_sociale AS client, p.adresse, p.ville, p.ninea AS client_ninea
                FROM avoirs_commercial a
                JOIN factures_commercial f ON f.id=a.facture_id
                JOIN prospects p ON p.id=a.prospect_id
                WHERE a.id=?";
        $params = [$id];
        if ($cabinetId) { $sql .= " AND p.cabinet_id=?"; $params[] = $cabinetId; }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $avoir = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$avoir) { http_response_code(404); echo "Avoir introuvable"; exit; }

        $lstmt = $db->prepare("SELECT * FROM avoirs_commercial_lignes WHERE avoir_id=? ORDER BY ordre");
        $lstmt->execute([$id]);
        $lignes = $lstmt->fetchAll(PDO::FETCH_ASSOC);

        $activePage = 'commercial-avoirs';
        $pageTitle  = 'Avoir ' . $avoir['numero'];
        ob_start();
        require APP_ROOT . '/views/commercial/avoir-voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }
}
