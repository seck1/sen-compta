<?php
require_once APP_ROOT . '/src/Services/NotificationService.php';

class CloturController {

    private function getEntrepriseAccess(int $id): array {
        requireAuth();
        $entreprise = getEntreprise($id);
        if (empty($entreprise)) { http_response_code(404); echo "Dossier introuvable"; exit; }
        if (!userHasAccess($id)) { redirect('/dashboard'); }
        return $entreprise;
    }

    public function index(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice = $entreprise['exercice_courant'];

        $db = getDB();

        // Check balance of all ecritures
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(l.debit),0) as total_debit, COALESCE(SUM(l.credit),0) as total_credit
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ? AND e.statut IN ('validee','cloturee')
        ");
        $stmt->execute([$id, $exercice]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        $equilibre = abs((float)$balance['total_debit'] - (float)$balance['total_credit']) < 1;

        // Clôture history
        $stmt = $db->prepare("SELECT * FROM clotures WHERE entreprise_id = ? ORDER BY exercice DESC");
        $stmt->execute([$id]);
        $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Current exercice status
        $stmt = $db->prepare("SELECT * FROM clotures WHERE entreprise_id = ? AND exercice = ?");
        $stmt->execute([$id, $exercice]);
        $cloture_actuelle = $stmt->fetch(PDO::FETCH_ASSOC);

        $message = $_GET['message'] ?? null;
        $error   = $_GET['error'] ?? null;

        $pageTitle = 'Clôture exercice';
        $activeTab = 'cloture';
        ob_start();
        require APP_ROOT . '/views/dossier/cloture.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function checklist(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();
        $exercice = $entreprise['exercice_courant'];

        $checks = [];

        // 1. Balance équilibrée
        $stmt = $db->prepare("SELECT COALESCE(SUM(le.debit),0) as d, COALESCE(SUM(le.credit),0) as c FROM lignes_ecritures le JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=?");
        $stmt->execute([$id, $exercice]);
        $bal = $stmt->fetch(PDO::FETCH_ASSOC);
        $diff = abs($bal['d'] - $bal['c']);
        $checks[] = [
            'label'  => 'Balance équilibrée (Σ Débits = Σ Crédits)',
            'status' => $diff < 1 ? 'ok' : 'error',
            'detail' => $diff < 1 ? 'Débits = Crédits' : 'Écart : ' . number_format($diff, 0, ',', ' ') . ' FCFA',
            'link'   => $diff >= 1 ? APP_URL.'/dossier/balance?id='.$id : null,
        ];

        // 2. Écritures en brouillon
        $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND exercice=? AND statut='brouillon'");
        $stmt->execute([$id, $exercice]);
        $nb = (int)$stmt->fetchColumn();
        $checks[] = [
            'label'  => 'Aucune écriture en brouillon',
            'status' => $nb === 0 ? 'ok' : 'error',
            'detail' => $nb === 0 ? 'Toutes les écritures sont validées' : $nb.' écriture(s) en brouillon à valider',
            'link'   => $nb > 0 ? APP_URL.'/dossier/ecritures?id='.$id.'&statut=brouillon' : null,
        ];

        // 3. TVA déclarée pour tous les mois de l'exercice
        try {
            $stmt = $db->prepare("SELECT COUNT(DISTINCT CONCAT(periode_annee,'-',periode_mois)) FROM declarations_tva WHERE entreprise_id=? AND periode_annee=?");
            $stmt->execute([$id, $exercice]);
            $nb_decl = (int)$stmt->fetchColumn();
            // Nb de mois avec mouvements TVA
            $stmt2 = $db->prepare("SELECT COUNT(DISTINCT MONTH(e.date_ecriture)) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND (c.numero LIKE '4431%' OR c.numero LIKE '4441%')");
            $stmt2->execute([$id, $exercice]);
            $nb_mois_tva = (int)$stmt2->fetchColumn();
            $tva_ok = $nb_mois_tva === 0 || $nb_decl >= $nb_mois_tva;
            $checks[] = [
                'label'  => 'Déclarations TVA complètes',
                'status' => $tva_ok ? 'ok' : 'warning',
                'detail' => $tva_ok ? $nb_decl.' déclaration(s) enregistrée(s)' : $nb_decl.'/'.$nb_mois_tva.' mois déclarés',
                'link'   => !$tva_ok ? APP_URL.'/dossier/tva?id='.$id : null,
            ];
        } catch (\Exception $e) {
            $checks[] = ['label'=>'Déclarations TVA','status'=>'warning','detail'=>'Impossible de vérifier','link'=>null];
        }

        // 4. Amortissements calculés
        $stmt = $db->prepare("SELECT COUNT(*) FROM immobilisations WHERE entreprise_id=? AND statut='actif'");
        $stmt->execute([$id]);
        $nb_immo = (int)$stmt->fetchColumn();
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM immobilisations WHERE entreprise_id=? AND statut='actif' AND amort_cumule > 0");
        $stmt2->execute([$id]);
        $nb_amort = (int)$stmt2->fetchColumn();
        $checks[] = [
            'label'  => 'Amortissements calculés',
            'status' => $nb_immo === 0 ? 'ok' : ($nb_amort > 0 ? 'ok' : 'warning'),
            'detail' => $nb_immo === 0 ? 'Aucune immobilisation active' : $nb_amort.'/'.$nb_immo.' immobilisation(s) amorties',
            'link'   => ($nb_immo > 0 && $nb_amort === 0) ? APP_URL.'/dossier/immo?id='.$id : null,
        ];

        // 5. Lettrage complet (comptes 401/411)
        $stmt = $db->prepare("SELECT COUNT(*) FROM lignes_ecritures le JOIN comptes c ON c.id=le.compte_id JOIN ecritures e ON e.id=le.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND (c.numero LIKE '401%' OR c.numero LIKE '411%') AND (le.code_lettrage IS NULL OR le.code_lettrage='')");
        $stmt->execute([$id, $exercice]);
        $nb_nl = (int)$stmt->fetchColumn();
        $checks[] = [
            'label'  => 'Lettrage clients/fournisseurs complet',
            'status' => $nb_nl === 0 ? 'ok' : 'warning',
            'detail' => $nb_nl === 0 ? 'Tous les comptes 401/411 sont lettrés' : $nb_nl.' ligne(s) non lettrée(s)',
            'link'   => $nb_nl > 0 ? APP_URL.'/dossier/lettrage?id='.$id : null,
        ];

        // 6. Rapprochement bancaire fait
        $stmt = $db->prepare("SELECT COUNT(*) FROM rapprochements_bancaires WHERE entreprise_id=? AND periode_annee=? AND statut='rapproche'");
        $stmt->execute([$id, $exercice]);
        $nb_rap = (int)$stmt->fetchColumn();
        $checks[] = [
            'label'  => 'Rapprochement bancaire effectué',
            'status' => $nb_rap > 0 ? 'ok' : 'warning',
            'detail' => $nb_rap > 0 ? $nb_rap.' rapprochement(s) validé(s)' : 'Aucun rapprochement clôturé pour cet exercice',
            'link'   => $nb_rap === 0 ? APP_URL.'/dossier/rapprochement?id='.$id : null,
        ];

        // 7. Notes de frais traitées
        $stmt = $db->prepare("SELECT COUNT(*) FROM notes_frais WHERE entreprise_id=? AND exercice=? AND statut='soumise'");
        $stmt->execute([$id, $exercice]);
        $nb_nf = (int)$stmt->fetchColumn();
        $checks[] = [
            'label'  => 'Notes de frais traitées',
            'status' => $nb_nf === 0 ? 'ok' : 'warning',
            'detail' => $nb_nf === 0 ? 'Aucune note en attente' : $nb_nf.' note(s) en attente d\'approbation',
            'link'   => $nb_nf > 0 ? APP_URL.'/dossier/notes-frais?id='.$id : null,
        ];

        $activeTab = 'cloture-checklist';
        $pageTitle = 'Checklist de clôture';
        ob_start();
        require APP_ROOT . '/views/dossier/cloture-checklist.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function cloturer(): void {
        $id = (int)($_POST['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        if (!isAdmin()) { redirect("/dossier/cloture?id=$id&error=forbidden"); }

        $exercice = (int)($_POST['exercice'] ?? 0);
        if ($exercice !== (int)$entreprise['exercice_courant']) {
            redirect("/dossier/cloture?id=$id&error=exercice_mismatch");
        }

        $db = getDB();

        // Check if already closed
        $stmt = $db->prepare("SELECT id FROM clotures WHERE entreprise_id = ? AND exercice = ?");
        $stmt->execute([$id, $exercice]);
        if ($stmt->fetchColumn()) {
            redirect("/dossier/cloture?id=$id&error=already_closed");
        }

        // Verify balance
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(l.debit),0) as td, COALESCE(SUM(l.credit),0) as tc
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ? AND e.statut IN ('validee','cloturee')
        ");
        $stmt->execute([$id, $exercice]);
        $bal = $stmt->fetch(PDO::FETCH_ASSOC);
        if (abs((float)$bal['td'] - (float)$bal['tc']) > 1) {
            redirect("/dossier/cloture?id=$id&error=desequilibre");
        }

        $db->beginTransaction();
        try {
            // Résultat net
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(CASE WHEN c.numero LIKE '7%' THEN l.credit - l.debit ELSE 0 END),0) -
                       COALESCE(SUM(CASE WHEN c.numero LIKE '6%' THEN l.debit - l.credit ELSE 0 END),0) as resultat
                FROM lignes_ecritures l
                JOIN comptes c ON c.id = l.compte_id
                JOIN ecritures e ON e.id = l.ecriture_id
                WHERE e.entreprise_id = ? AND e.exercice = ? AND e.statut IN ('validee','cloturee')
            ");
            $stmt->execute([$id, $exercice]);
            $resultat = (float)$stmt->fetchColumn();

            // Save clôture record
            $stmt = $db->prepare("INSERT INTO clotures (entreprise_id, exercice, resultat_net, date_cloture, user_id, statut)
                                   VALUES (?,?,?,CURDATE(),?,'cloture')");
            $stmt->execute([$id, $exercice, $resultat, auth()['id']]);

            // Generate RAN (Report à Nouveau) entries for next exercice
            $nouvel_exercice = $exercice + 1;

            // Find journal OD
            $stmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id = ? AND code = 'OD' LIMIT 1");
            $stmt->execute([$id]);
            $journal_od = $stmt->fetchColumn();
            if (!$journal_od) {
                $stmt = $db->prepare("INSERT INTO journaux (entreprise_id, code, libelle) VALUES (?, 'OD', 'Opérations diverses')");
                $stmt->execute([$id]);
                $journal_od = $db->lastInsertId();
            }

            // Create RAN ecriture
            $stmt = $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, date_ecriture, libelle, exercice, periode, statut)
                                   VALUES (?,?,?,?,?,1,'validee')");
            $stmt->execute([$id, $journal_od, $nouvel_exercice . '-01-01',
                           "Report à nouveau exercice $exercice", $nouvel_exercice]);
            $ecriture_id = $db->lastInsertId();

            // Get all bilan accounts balances
            $stmt = $db->prepare("
                SELECT c.id as compte_id, c.numero,
                       COALESCE(SUM(l.debit),0) as total_debit,
                       COALESCE(SUM(l.credit),0) as total_credit
                FROM comptes c
                JOIN lignes_ecritures l ON l.compte_id = c.id
                JOIN ecritures e ON e.id = l.ecriture_id
                WHERE e.entreprise_id = ? AND e.exercice = ? AND e.statut IN ('validee','cloturee')
                AND (c.numero LIKE '1%' OR c.numero LIKE '2%' OR c.numero LIKE '3%'
                     OR c.numero LIKE '4%' OR c.numero LIKE '5%')
                GROUP BY c.id, c.numero
                HAVING ABS(total_debit - total_credit) > 0.01
            ");
            $stmt->execute([$id, $exercice]);
            $comptes_bilan = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comptes_bilan as $compte) {
                $solde = (float)$compte['total_debit'] - (float)$compte['total_credit'];
                $debit  = $solde > 0 ? $solde : 0;
                $credit = $solde < 0 ? abs($solde) : 0;

                $stmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit)
                                      VALUES (?,?,?,?,?)");
                $stmt->execute([$ecriture_id, $compte['compte_id'],
                               "RAN " . $compte['numero'], $debit, $credit]);
            }

            // ── Report du RESULTAT de l'exercice au passif (SYSCOHADA) ──
            // Sans cette ligne, le bilan d'ouverture N+1 est desequilibre (Actif != Passif).
            // Benefice (resultat > 0) -> compte 131 au credit. Perte -> compte 139 au debit.
            if (abs($resultat) > 0.005) {
                $compte_resultat_num = $resultat > 0 ? '131' : '139';
                // Resoudre (ou creer) le compte de resultat dans le plan comptable du dossier
                $cr = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $cr->execute([$id, $compte_resultat_num]);
                $compte_resultat_id = $cr->fetchColumn();
                if (!$compte_resultat_id) {
                    $libelleCpt = $resultat > 0 ? 'Résultat net : Bénéfice' : 'Résultat net : Perte';
                    $db->prepare("INSERT INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,'passif',1)")
                       ->execute([$id, $compte_resultat_num, $libelleCpt]);
                    $compte_resultat_id = $db->lastInsertId();
                }
                $debitR  = $resultat < 0 ? abs($resultat) : 0;   // perte -> debit
                $creditR = $resultat > 0 ? $resultat : 0;        // benefice -> credit
                $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)")
                   ->execute([$ecriture_id, $compte_resultat_id,
                              "RAN résultat exercice $exercice", $debitR, $creditR]);
            }

            // Controle : le RAN genere doit etre equilibre (Σdebit = Σcredit), sinon on annule.
            $ctrl = $db->prepare("SELECT COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c FROM lignes_ecritures WHERE ecriture_id=?");
            $ctrl->execute([$ecriture_id]);
            $ran = $ctrl->fetch(PDO::FETCH_ASSOC);
            if (abs((float)$ran['d'] - (float)$ran['c']) > 0.01) {
                $db->rollBack();
                redirect("/dossier/cloture?id=$id&error=ran_desequilibre");
                return;
            }

            // Update entreprise exercice_courant
            $stmt = $db->prepare("UPDATE entreprises SET exercice_courant = ? WHERE id = ?");
            $stmt->execute([$nouvel_exercice, $id]);

            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
            redirect("/dossier/cloture?id=$id&error=system");
            return;
        }

        // Hors du try/catch : log et redirect après commit réussi
        NotificationService::log(
            auth()['id'],
            'CLOTURE_EXERCICE',
            $id,
            'clotures',
            null,
            "Clôture exercice $exercice → $nouvel_exercice pour dossier #$id"
        );

        redirect("/dossier/cloture?id=$id&message=cloture_ok&new_exercice=$nouvel_exercice");
    }
}
