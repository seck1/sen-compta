<?php
require_once APP_ROOT . '/config/app.php';

class ReglementController {

    public function regler(): void {
        requireAuth();
        header('Content-Type: application/json');

        // Accepte FormData (multipart) ou JSON
        if (!empty($_POST)) {
            $ecritureId    = (int)($_POST['ecriture_id']     ?? 0);
            $entId         = (int)($_POST['entreprise_id']    ?? 0);
            $montant       = (float)($_POST['montant']        ?? 0);
            $date          = $_POST['date']                   ?? date('Y-m-d');
            $mode          = $_POST['mode']                   ?? 'virement';
            $compteReglt   = $_POST['compte_reglement']       ?? '521';
            $libelle       = trim($_POST['libelle']           ?? '');
            $moyenPaiement = trim($_POST['mode']              ?? '') ?: null;
        } else {
            $data          = json_decode(file_get_contents('php://input'), true) ?? [];
            $ecritureId    = (int)($data['ecriture_id']      ?? 0);
            $entId         = (int)($data['entreprise_id']     ?? 0);
            $montant       = (float)($data['montant']         ?? 0);
            $date          = $data['date']                    ?? date('Y-m-d');
            $mode          = $data['mode']                    ?? 'virement';
            $compteReglt   = $data['compte_reglement']        ?? '521';
            $libelle       = trim($data['libelle']            ?? '');
            $moyenPaiement = trim($data['mode']               ?? '') ?: null;
        }

        // Gérer l'upload du justificatif
        $pieceJointe = null;
        if (!empty($_FILES['justificatif']['tmp_name'])) {
            $file = $_FILES['justificatif'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','pdf']) && $file['size'] <= 5242880) {
                $uploadDir = APP_ROOT . '/public/uploads/justificatifs/';
                $filename  = uniqid('regl_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $pieceJointe = $filename;
                }
            }
        }

        if (!$ecritureId || !$entId || $montant <= 0) {
            echo json_encode(['error' => 'Données invalides']); exit;
        }
        if (!userHasAccess($entId)) {
            echo json_encode(['error' => 'Accès refusé']); exit;
        }

        $db         = getDB();
        $user       = auth();
        $entreprise = getEntreprise($entId);
        $exercice   = $entreprise['exercice_courant'];
        $periode    = (int)date('m', strtotime($date));

        // Charger l'écriture originale
        $stmt = $db->prepare("SELECT e.*, j.code as journal_code FROM ecritures e JOIN journaux j ON j.id=e.journal_id WHERE e.id=? AND e.entreprise_id=?");
        $stmt->execute([$ecritureId, $entId]);
        $ecriture = $stmt->fetch();
        if (!$ecriture) { echo json_encode(['error' => 'Écriture introuvable']); exit; }

        // Trouver les lignes 401 (fournisseur) ou 411 (client) non lettrées
        $stmt = $db->prepare("
            SELECT l.id, l.debit, l.credit, l.compte_id, l.tiers_id, c.numero
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            WHERE l.ecriture_id = ? AND (c.numero LIKE '40%' OR c.numero LIKE '41%')
            AND (l.code_lettrage IS NULL OR l.code_lettrage = '')
        ");
        $stmt->execute([$ecritureId]);
        $lignesTiers = $stmt->fetchAll();
        if (empty($lignesTiers)) {
            echo json_encode(['error' => 'Aucune ligne fournisseur/client à régler (déjà lettrées ?)']); exit;
        }

        // Calculer le solde restant dû (montant facture - règlements déjà passés sur ce compte/tiers)
        $montantFacture = 0;
        foreach ($lignesTiers as $lt) {
            // Pour fournisseur: solde = credit; pour client: solde = debit (on le détermine après)
            $montantFacture += $lt['credit'] + $lt['debit'];
        }
        // Vérifier les règlements existants liés à cette facture (même numero_facture, même tiers, journaux BNQ/CAI)
        $stmtDeja = $db->prepare("
            SELECT COALESCE(SUM(l.debit), 0) as total_regle
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            JOIN journaux j ON j.id = e.journal_id
            JOIN comptes c ON c.id = l.compte_id
            WHERE e.entreprise_id = ? AND e.numero_facture = ? AND e.id != ?
            AND j.code IN ('BNQ','CAI','MOB')
            AND (c.numero LIKE '40%' OR c.numero LIKE '41%')
        ");
        $stmtDeja->execute([$entId, $ecriture['numero_facture'], $ecritureId]);
        $dejaRegle = (float)$stmtDeja->fetchColumn();
        $soldeRestant = $montantFacture - $dejaRegle;

        if ($montant > $soldeRestant + 0.01) {
            echo json_encode(['error' => sprintf('Montant trop élevé. Solde restant dû : %s (déjà réglé : %s)', number_format($soldeRestant, 0, ',', ' '), number_format($dejaRegle, 0, ',', ' '))]); exit;
        }

        // Déterminer type (achat=401→fournisseur, vente=411→client)
        $premierNumero = $lignesTiers[0]['numero'];
        $isFournisseur = str_starts_with($premierNumero, '40');
        $comptetiersId = $lignesTiers[0]['compte_id'];
        $compteTiersNum = $premierNumero;
        $tiersId = $lignesTiers[0]['tiers_id'] ?? null;

        // Mapping moyen de paiement → journal + compte par défaut (normes SYSCOHADA)
        $modeJournalMap = [
            'especes'      => ['journal' => 'CAI', 'compte' => '531'],
            'virement'     => ['journal' => 'BNQ', 'compte' => '5211'],
            'cheque'       => ['journal' => 'BNQ', 'compte' => '5211'],
            'carte'        => ['journal' => 'BNQ', 'compte' => '5211'],
            'orange_money' => ['journal' => 'MOB', 'compte' => '5212'],
            'wave'         => ['journal' => 'MOB', 'compte' => '5213'],
            'free_money'   => ['journal' => 'MOB', 'compte' => '5214'],
            'autre'        => ['journal' => 'BNQ', 'compte' => '5211'],
        ];
        $modeConfig  = $modeJournalMap[$mode] ?? ['journal' => 'BNQ', 'compte' => '521'];
        $journalCode = $modeConfig['journal'];

        $stmtJ = $db->prepare("SELECT id, code FROM journaux WHERE entreprise_id=? AND code=?");
        $stmtJ->execute([$entId, $journalCode]);
        $journal = $stmtJ->fetch();
        if (!$journal) {
            // fallback MOB, BNQ puis OD
            foreach (['MOB', 'BNQ', 'OD'] as $fallback) {
                $stmtJ = $db->prepare("SELECT id, code FROM journaux WHERE entreprise_id=? AND code=?");
                $stmtJ->execute([$entId, $fallback]);
                $journal = $stmtJ->fetch();
                if ($journal) { $journalCode = $fallback; break; }
            }
        }
        if (!$journal) { echo json_encode(['error' => 'Journal de règlement introuvable']); exit; }

        // Compte de règlement : priorité au compte envoyé par le client, sinon défaut du mode
        // Pour espèces et monnaies mobiles, forcer le compte correct même si le client envoie 521
        $comptesForcés = ['especes' => '531', 'orange_money' => '5212', 'wave' => '5213', 'free_money' => '5214'];
        $compteReglNum = isset($comptesForcés[$mode]) ? $comptesForcés[$mode] : $compteReglt;
        $stmtC = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=?");
        $stmtC->execute([$entId, $compteReglNum]);
        $compteRegl = $stmtC->fetch();
        if (!$compteRegl) {
            $stmtC = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero LIKE ? LIMIT 1");
            $stmtC->execute([$entId, substr($compteReglNum, 0, 3) . '%']);
            $compteRegl = $stmtC->fetch();
        }
        if (!$compteRegl) { echo json_encode(['error' => 'Compte de règlement introuvable']); exit; }

        if (!$libelle) {
            $libelle = 'Règlement ' . ($isFournisseur ? 'fournisseur' : 'client') . ' — ' . $ecriture['libelle'];
        }

        $db->beginTransaction();
        try {
            // Générer N° pièce règlement
            $an  = date('Y', strtotime($date));
            $stmtSeq = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND journal_id=? AND exercice=?");
            $stmtSeq->execute([$entId, $journal['id'], (int)$an]);
            $seq = ((int)$stmtSeq->fetchColumn()) + 1;
            $numeroPiece = $journalCode . '-' . $an . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Créer l'écriture de règlement (copie le N° facture de la facture originale)
            $stmtE = $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, user_id, numero_piece, numero_facture, date_ecriture, date_valeur, libelle, piece_jointe, moyen_paiement, exercice, periode, statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'validee')");
            $stmtE->execute([$entId, $journal['id'], $user['id'], $numeroPiece, $ecriture['numero_facture'] ?? null, $date, $date, $libelle, $pieceJointe, $moyenPaiement, $exercice, $periode]);
            $newEcritureId = $db->lastInsertId();

            // Lignes de règlement
            $stmtL = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit, tiers_id) VALUES (?,?,?,?,?,?)");
            if ($isFournisseur) {
                // Fournisseur : on paie → débit 401, crédit banque
                $stmtL->execute([$newEcritureId, $comptetiersId, $libelle, $montant, 0, $tiersId]);
                $stmtL->execute([$newEcritureId, $compteRegl['id'], $libelle, 0, $montant, null]);
            } else {
                // Client : on encaisse → débit banque, crédit 411
                $stmtL->execute([$newEcritureId, $compteRegl['id'], $libelle, $montant, 0, null]);
                $stmtL->execute([$newEcritureId, $comptetiersId, $libelle, 0, $montant, $tiersId]);
            }

            // Récupérer les IDs des lignes créées pour le tiers
            $stmtLignes = $db->prepare("SELECT id FROM lignes_ecritures WHERE ecriture_id=? AND compte_id=?");
            $stmtLignes->execute([$newEcritureId, $comptetiersId]);
            $nouvelleLigne = $stmtLignes->fetch();

            // Lettrage automatique : trouver les lignes originales correspondant au montant
            $lignesALettrer = [];
            $totalLettrage  = 0;
            foreach ($lignesTiers as $lt) {
                $montantLigne = $isFournisseur ? $lt['credit'] : $lt['debit'];
                if ($totalLettrage + $montantLigne <= $montant + 0.01) {
                    $lignesALettrer[] = $lt['id'];
                    $totalLettrage   += $montantLigne;
                }
                if (abs($totalLettrage - $montant) < 0.01) break;
            }

            $newCode = null;
            if (!empty($lignesALettrer) && $nouvelleLigne && abs($totalLettrage - $montant) < 0.01) {
                $lignesALettrer[] = $nouvelleLigne['id'];

                // Générer le prochain code lettrage
                $stmtCode = $db->prepare("SELECT MAX(code_lettrage) FROM lettrages WHERE entreprise_id=? AND compte_id=?");
                $stmtCode->execute([$entId, $comptetiersId]);
                $lastCode = $stmtCode->fetchColumn();
                $newCode  = $lastCode ? $this->nextLettrageCode($lastCode) : 'A';

                // Enregistrer le lettrage (une ligne par ligne lettrée)
                $stmtLt = $db->prepare("INSERT INTO lettrages (entreprise_id, compte_id, code_lettrage, date_lettrage, user_id) VALUES (?,?,?,?,?)");
                $stmtLt->execute([$entId, $comptetiersId, $newCode, $date, $user['id']]);

                // Appliquer le code lettrage sur les lignes
                $placeholders = implode(',', array_fill(0, count($lignesALettrer), '?'));
                // Sécurité : ne mettre à jour que les lignes appartenant à l'entreprise courante
                $stmtUpd = $db->prepare("UPDATE lignes_ecritures SET code_lettrage=? WHERE id IN ($placeholders) AND ecriture_id IN (SELECT id FROM ecritures WHERE entreprise_id=?)");
                $stmtUpd->execute(array_merge([$newCode], $lignesALettrer, [$entId]));
            }

            $db->commit();

            // Log d'audit — règlement financier
            NotificationService::log(
                $user['id'],
                'REGLEMENT',
                $entId,
                'ecritures',
                $ecritureId,
                "Règlement de " . number_format($montant, 0, ',', ' ') . " FCFA — écriture #$ecritureId"
            );

            echo json_encode([
                'success'      => true,
                'ecriture_id'  => $newEcritureId,
                'numero_piece' => $numeroPiece,
                'lettre'       => $newCode ?? null,
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    private function nextLettrageCode(string $code): string {
        $len = strlen($code);
        for ($i = $len - 1; $i >= 0; $i--) {
            if ($code[$i] < 'Z') {
                $code[$i] = chr(ord($code[$i]) + 1);
                return $code;
            }
            $code[$i] = 'A';
        }
        return 'A' . $code;
    }
}
