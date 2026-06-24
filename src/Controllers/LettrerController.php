<?php
class LettrerController {

    private function getEntrepriseAccess(int $id): array {
        requireAuth();
        $entreprise = getEntreprise($id);
        if (empty($entreprise)) { http_response_code(404); echo "Dossier introuvable"; exit; }
        if (!userHasAccess($id)) { redirect('/dashboard'); }
        return $entreprise;
    }

    // [#13] Valider $type sur liste blanche stricte
    private function sanitizeType(string $raw): string {
        return in_array($raw, ['clients', 'fournisseurs'], true) ? $raw : 'clients';
    }

    public function index(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $type = $this->sanitizeType($_GET['type'] ?? 'clients');
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $exercice = $entreprise['exercice_courant'];
        $prefix   = ($type === 'fournisseurs') ? '40%' : '41%';

        $stmt = $db->prepare("
            SELECT l.id, l.debit, l.credit, l.libelle, l.code_lettrage,
                   c.numero as compte_numero, c.intitule as compte_libelle,
                   e.date_ecriture, e.libelle as ecriture_libelle, e.numero_facture,
                   j.code as journal_code,
                   t.nom as nom_tiers
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            LEFT JOIN journaux j ON j.id = e.journal_id
            LEFT JOIN tiers t ON t.id = l.tiers_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            AND c.numero LIKE ?
            ORDER BY c.numero, e.date_ecriture
        ");
        $stmt->execute([$id, $exercice, $prefix]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comptes = [];
        foreach ($lignes as $l) {
            $comptes[$l['compte_numero']]['libelle'] = $l['compte_libelle'];
            $comptes[$l['compte_numero']]['lignes'][] = $l;
        }

        $pageTitle = 'Lettrage';
        $activeTab = 'lettrage';
        ob_start();
        require APP_ROOT . '/views/dossier/lettrage/index.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function lettrer(): void {
        // [#1] Vérification CSRF
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id   = (int)($_POST['id'] ?? 0);
        $type = $this->sanitizeType($_POST['type'] ?? 'clients');
        $entreprise = $this->getEntrepriseAccess($id);
        $exercice   = $entreprise['exercice_courant'];
        $prefix     = ($type === 'fournisseurs') ? '40%' : '41%';

        $lignes_ids = array_map('intval', (array)($_POST['lignes'] ?? []));
        // Dédoublonner pour éviter les doublons volontaires
        $lignes_ids = array_values(array_unique($lignes_ids));

        if (count($lignes_ids) < 2) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=no_selection");
        }

        $db = getDB();
        $placeholders = implode(',', array_fill(0, count($lignes_ids), '?'));

        // [#3] Vérifier appartenance, exercice courant, compte correct ET non déjà lettré
        $stmt = $db->prepare("
            SELECT l.id, l.debit, l.credit, l.code_lettrage,
                   c.numero as compte_numero
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE l.id IN ($placeholders)
              AND e.entreprise_id = ?
              AND e.exercice = ?
              AND c.numero LIKE ?
        ");
        $stmt->execute(array_merge($lignes_ids, [$id, $exercice, $prefix]));
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($lignes)) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=invalid");
        }

        // [#11] Vérifier que toutes les lignes demandées ont été trouvées
        if (count($lignes) !== count($lignes_ids)) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=invalid");
        }

        // [#4] Vérifier qu'aucune ligne n'est déjà lettrée
        foreach ($lignes as $l) {
            if (!empty($l['code_lettrage'])) {
                redirect("/dossier/lettrage?id=$id&type=$type&error=already_lettered");
            }
        }

        // Vérifier même compte
        $comptes_nums = array_unique(array_column($lignes, 'compte_numero'));
        if (count($comptes_nums) > 1) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=multi_compte");
        }

        // [#12] Arrondi pour éviter erreurs flottantes
        $total_debit  = round(array_sum(array_column($lignes, 'debit')),  2);
        $total_credit = round(array_sum(array_column($lignes, 'credit')), 2);
        if (abs($total_debit - $total_credit) > 0.01) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=desequilibre");
        }

        $compte_numero = reset($comptes_nums);
        $stmtCId = $db->prepare("SELECT id FROM comptes WHERE entreprise_id = ? AND numero = ?");
        $stmtCId->execute([$id, $compte_numero]);
        $compteRow = $stmtCId->fetch();
        $compteId  = $compteRow ? (int)$compteRow['id'] : 0;
        if (!$compteId) {
            redirect("/dossier/lettrage?id=$id&type=$type&error=invalid");
        }

        // [#2] Transaction + [#5] verrou SELECT FOR UPDATE pour éviter race condition
        $db->beginTransaction();
        try {
            // Verrouiller la table lettrages pour ce compte le temps de générer le code
            $stmtLock = $db->prepare("
                SELECT MAX(code_lettrage) FROM lettrages
                WHERE entreprise_id = ? AND compte_id = ?
                FOR UPDATE
            ");
            $stmtLock->execute([$id, $compteId]);
            $last = $stmtLock->fetchColumn();
            $code = $last ? $this->nextLettrageCode($last) : 'A';

            $user = auth();

            $stmtIns = $db->prepare("
                INSERT INTO lettrages (entreprise_id, compte_id, code_lettrage, date_lettrage, user_id)
                VALUES (?, ?, ?, CURDATE(), ?)
            ");
            $stmtIns->execute([$id, $compteId, $code, $user['id']]);

            $stmtUpd = $db->prepare("
                UPDATE lignes_ecritures SET code_lettrage = ?
                WHERE id IN ($placeholders)
            ");
            $stmtUpd->execute(array_merge([$code], $lignes_ids));

            // Vérifier que toutes les lignes ont bien été mises à jour
            if ($stmtUpd->rowCount() !== count($lignes_ids)) {
                throw new Exception("Mise à jour partielle des lignes");
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            redirect("/dossier/lettrage?id=$id&type=$type&error=db_error");
        }

        redirect("/dossier/lettrage?id=$id&type=$type&lettre=$code");
    }

    // [#10] delettrer passe en POST
    public function delettrer(): void {
        // [#1] Vérification CSRF
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id   = (int)($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $type = $this->sanitizeType($_POST['type'] ?? 'clients');
        $this->getEntrepriseAccess($id);

        if (!$code || !preg_match('/^[A-Z]+$/', $code)) {
            redirect("/dossier/lettrage?id=$id&type=$type");
        }

        $db = getDB();

        // [#2] Transaction pour garantir cohérence
        $db->beginTransaction();
        try {
            $stmtUpd = $db->prepare("
                UPDATE lignes_ecritures l
                JOIN ecritures e ON e.id = l.ecriture_id
                SET l.code_lettrage = NULL
                WHERE l.code_lettrage = ? AND e.entreprise_id = ?
            ");
            $stmtUpd->execute([$code, $id]);

            $stmtDel = $db->prepare("
                DELETE FROM lettrages WHERE entreprise_id = ? AND code_lettrage = ?
            ");
            $stmtDel->execute([$id, $code]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            redirect("/dossier/lettrage?id=$id&type=$type&error=db_error");
        }

        redirect("/dossier/lettrage?id=$id&type=$type&delettre=$code");
    }

    private function nextLettrageCode(string $last): string {
        $last  = strtoupper(preg_replace('/[^A-Z]/', '', $last));
        if (empty($last)) return 'A';
        $len   = strlen($last);
        $chars = str_split($last);
        $i     = $len - 1;
        while ($i >= 0) {
            if ($chars[$i] < 'Z') {
                $chars[$i] = chr(ord($chars[$i]) + 1);
                return implode('', $chars);
            }
            $chars[$i] = 'A';
            $i--;
        }
        return str_repeat('A', $len + 1);
    }
}
