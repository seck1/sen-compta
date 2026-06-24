<?php
class RapprochementController {

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
        $db = getDB();

        $stmt = $db->prepare("SELECT r.*, u.prenom, u.nom FROM rapprochements_bancaires r JOIN users u ON u.id=r.user_id WHERE r.entreprise_id=? ORDER BY r.periode_annee DESC, r.periode_mois DESC");
        $stmt->execute([$id]);
        $rapprochements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Rapprochements bancaires';
        $activeTab = 'rapprochement';
        ob_start();
        require APP_ROOT . '/views/dossier/rapprochement/index.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function creer(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();

        // Comptes banque 51x/52x
        $stmt = $db->prepare("SELECT numero, intitule FROM comptes WHERE entreprise_id=? AND (numero LIKE '51%' OR numero LIKE '52%') AND actif=1 ORDER BY numero");
        $stmt->execute([$id]);
        $comptesBanque = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Nouveau rapprochement';
        $activeTab = 'rapprochement';
        ob_start();
        require APP_ROOT . '/views/dossier/rapprochement/form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $mois = (int)$_POST['periode_mois'];
        $annee = (int)$_POST['periode_annee'];
        $compte = trim($_POST['compte_banque']);
        $solde_releve = (float)$_POST['solde_releve'];

        $db = getDB();

        // Calculer le solde comptable
        $stmt = $db->prepare("SELECT COALESCE(SUM(l.debit),0)-COALESCE(SUM(l.credit),0) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND c.numero LIKE ? AND (YEAR(e.date_ecriture) < ? OR (YEAR(e.date_ecriture) = ? AND MONTH(e.date_ecriture) <= ?))");
        $stmt->execute([$id, $compte.'%', $annee, $annee, $mois]);
        $solde_comptable = (float)$stmt->fetchColumn();
        $ecart = $solde_releve - $solde_comptable;

        $ins = $db->prepare("INSERT INTO rapprochements_bancaires (entreprise_id, user_id, compte_banque, periode_mois, periode_annee, solde_releve, solde_comptable, ecart, statut) VALUES (?,?,?,?,?,?,?,?,'en_cours')");
        $ins->execute([$id, auth()['id'], $compte, $mois, $annee, $solde_releve, $solde_comptable, $ecart]);
        $rapId = $db->lastInsertId();

        redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rapId");
    }

    public function voir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $rap_id = (int)($_GET['rap_id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM rapprochements_bancaires WHERE id=? AND entreprise_id=?");
        $stmt->execute([$rap_id, $id]);
        $rapprochement = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$rapprochement) { http_response_code(404); echo "Rapprochement introuvable"; exit; }

        // Lignes déjà rapprochées
        $stmt = $db->prepare("SELECT rl.ligne_ecriture_id FROM rapprochements_lignes rl WHERE rl.rapprochement_id=? AND rl.rapproche=1");
        $stmt->execute([$rap_id]);
        $rapIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ligne_ecriture_id');

        // Écritures comptables du compte pour cette période
        $mois = $rapprochement['periode_mois'];
        $annee = $rapprochement['periode_annee'];
        $compte = $rapprochement['compte_banque'];
        $stmt = $db->prepare("
            SELECT l.id as ligne_id, l.debit, l.credit, l.libelle as ligne_libelle,
                   e.date_ecriture, e.libelle as ecriture_libelle, e.numero_piece as reference
            FROM lignes_ecritures l
            JOIN comptes c ON c.id=l.compte_id
            JOIN ecritures e ON e.id=l.ecriture_id
            WHERE e.entreprise_id=? AND c.numero LIKE ?
              AND MONTH(e.date_ecriture)=? AND YEAR(e.date_ecriture)=?
            ORDER BY e.date_ecriture
        ");
        $stmt->execute([$id, $compte.'%', $mois, $annee]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $solde_comptable_rap = 0;
        foreach ($lignes as $l) {
            if (in_array($l['ligne_id'], $rapIds)) {
                $solde_comptable_rap += $l['debit'] - $l['credit'];
            }
        }

        $pageTitle = 'Rapprochement bancaire';
        $activeTab = 'rapprochement';
        ob_start();
        require APP_ROOT . '/views/dossier/rapprochement/voir.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function importCsv(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $rap_id = (int)($_POST['rap_id'] ?? 0);

        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM rapprochements_bancaires WHERE id=? AND entreprise_id=?");
        $stmt->execute([$rap_id, $id]);
        if (!$stmt->fetch()) redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id&error=not_found");

        if (empty($_FILES['csv_file']['tmp_name'])) {
            redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id&error=no_file");
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id&error=file_error");

        // Supprimer les lignes existantes du relevé pour ce rapprochement
        $db->prepare("DELETE FROM releve_lignes WHERE rapprochement_id=?")->execute([$rap_id]);

        $inserted = 0;
        $firstRow = true;
        $ins = $db->prepare("INSERT INTO releve_lignes (rapprochement_id, date_operation, libelle, debit, credit) VALUES (?,?,?,?,?)");

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            // Essayer aussi avec virgule si point-virgule ne donne pas assez de colonnes
            if (count($row) < 3) {
                $row = str_getcsv(implode(';', $row), ',');
            }
            // Ignorer l'en-tête
            if ($firstRow) {
                $firstRow = false;
                // Si la première colonne ressemble à une date, ce n'est pas un header
                if (!preg_match('/^\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4}$/', trim($row[0] ?? ''))) continue;
            }
            if (count($row) < 3) continue;

            // Parser date (formats: dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd)
            $dateRaw = trim($row[0] ?? '');
            $date = null;
            if (preg_match('/^(\d{2})[\/\-\.](\d{2})[\/\-\.](\d{4})$/', $dateRaw, $m)) {
                $date = $m[3] . '-' . $m[2] . '-' . $m[1];
            } elseif (preg_match('/^(\d{4})[\/\-\.](\d{2})[\/\-\.](\d{2})$/', $dateRaw, $m)) {
                $date = $m[1] . '-' . $m[2] . '-' . $m[3];
            }
            if (!$date) continue;

            $libelle = trim($row[1] ?? '');
            // Colonnes possibles: debit;credit ou montant seul (négatif = credit)
            $col2 = str_replace([' ', "\xc2\xa0"], '', trim($row[2] ?? ''));
            $col2 = str_replace(',', '.', $col2);
            $col3 = isset($row[3]) ? str_replace([' ', "\xc2\xa0", ','], ['', '', '.'], trim($row[3])) : '';

            if ($col3 !== '' && is_numeric($col3)) {
                // Format: date;libelle;debit;credit
                $debit  = max(0, (float)$col2);
                $credit = max(0, (float)$col3);
            } else {
                // Format: date;libelle;montant (positif=débit, négatif=crédit)
                $montant = (float)$col2;
                $debit  = $montant > 0 ? $montant : 0;
                $credit = $montant < 0 ? abs($montant) : 0;
            }

            if ($debit == 0 && $credit == 0) continue;

            $ins->execute([$rap_id, $date, $libelle, $debit, $credit]);
            $inserted++;
        }
        fclose($handle);

        redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id&imported=$inserted");
    }

    public function lettrerAuto(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $rap_id = (int)($_POST['rap_id'] ?? 0);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM rapprochements_bancaires WHERE id=? AND entreprise_id=?");
        $stmt->execute([$rap_id, $id]);
        $rap = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$rap) redirect("/dossier/rapprochement?id=$id");

        // Récupérer les lignes relevé non lettrées
        $stmt = $db->prepare("SELECT * FROM releve_lignes WHERE rapprochement_id=? AND rapproche=0");
        $stmt->execute([$rap_id]);
        $releveLignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les écritures comptables non rapprochées pour cette période/compte
        $mois  = $rap['periode_mois'];
        $annee = $rap['periode_annee'];
        $compte = $rap['compte_banque'];
        $stmt = $db->prepare("
            SELECT l.id as ligne_id, l.debit, l.credit, l.libelle as libelle,
                   e.date_ecriture
            FROM lignes_ecritures l
            JOIN comptes c ON c.id=l.compte_id
            JOIN ecritures e ON e.id=l.ecriture_id
            WHERE e.entreprise_id=? AND c.numero LIKE ?
              AND MONTH(e.date_ecriture)=? AND YEAR(e.date_ecriture)=?
              AND l.id NOT IN (
                  SELECT ligne_ecriture_id FROM rapprochements_lignes
                  WHERE rapprochement_id=? AND rapproche=1 AND ligne_ecriture_id IS NOT NULL
              )
            ORDER BY e.date_ecriture
        ");
        $stmt->execute([$id, $compte.'%', $mois, $annee, $rap_id]);
        $ecritures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $matched = 0;
        $insRap = $db->prepare("INSERT INTO rapprochements_lignes (rapprochement_id, ligne_ecriture_id, type, date_operation, libelle, debit, credit, rapproche, releve_ligne_id) VALUES (?,?,'comptable',?,?,?,?,1,?) ON DUPLICATE KEY UPDATE rapproche=1, releve_ligne_id=?");
        $updReleve = $db->prepare("UPDATE releve_lignes SET rapproche=1, ligne_ecriture_id=? WHERE id=?");

        foreach ($releveLignes as $rl) {
            $montantReleve = $rl['debit'] > 0 ? $rl['debit'] : $rl['credit'];
            $dateReleve    = strtotime($rl['date_operation']);

            foreach ($ecritures as $idx => $ec) {
                $montantEc = $ec['debit'] > 0 ? $ec['debit'] : $ec['credit'];
                $dateEc    = strtotime($ec['date_ecriture']);

                // Match: même montant (tolérance 1 FCFA) + date ±3 jours
                $memeType   = ($rl['debit'] > 0 && $ec['debit'] > 0) || ($rl['credit'] > 0 && $ec['credit'] > 0);
                $memeMontant = abs($montantReleve - $montantEc) <= 1;
                $memeDate   = abs($dateReleve - $dateEc) <= 3 * 86400;

                if ($memeType && $memeMontant && $memeDate) {
                    $insRap->execute([$rap_id, $ec['ligne_id'], $ec['date_ecriture'], $ec['libelle'], $ec['debit'], $ec['credit'], $rl['id'], $rl['id']]);
                    $updReleve->execute([$ec['ligne_id'], $rl['id']]);
                    unset($ecritures[$idx]); // ne plus matcher cette écriture
                    $matched++;
                    break;
                }
            }
        }

        // Recalculer solde
        $stmt2 = $db->prepare("SELECT COALESCE(SUM(l.debit),0)-COALESCE(SUM(l.credit),0) FROM rapprochements_lignes rl JOIN lignes_ecritures l ON l.id=rl.ligne_ecriture_id WHERE rl.rapprochement_id=? AND rl.rapproche=1");
        $stmt2->execute([$rap_id]);
        $sc = (float)$stmt2->fetchColumn();
        $ecart = (float)$rap['solde_releve'] - $sc;
        $statut = abs($ecart) < 0.01 ? 'rapproche' : 'en_cours';
        $db->prepare("UPDATE rapprochements_bancaires SET solde_comptable=?, ecart=?, statut=? WHERE id=?")->execute([$sc, $ecart, $statut, $rap_id]);

        redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id&auto=$matched");
    }

    public function marquer(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $rap_id = (int)$_POST['rap_id'];
        $ligne_id = (int)$_POST['ligne_id'];
        $rapproche = (int)($_POST['rapproche'] ?? 0);

        $db = getDB();

        // Vérifier que le rapprochement appartient à cet entreprise
        $stmt = $db->prepare("SELECT id FROM rapprochements_bancaires WHERE id=? AND entreprise_id=?");
        $stmt->execute([$rap_id, $id]);
        if (!$stmt->fetch()) redirect('/dashboard');

        if ($rapproche) {
            // Récupérer les infos de la ligne
            $stmt = $db->prepare("SELECT l.debit, l.credit, e.date_ecriture, l.libelle FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id WHERE l.id=?");
            $stmt->execute([$ligne_id]);
            $l = $stmt->fetch(PDO::FETCH_ASSOC);

            $ins = $db->prepare("INSERT INTO rapprochements_lignes (rapprochement_id, ligne_ecriture_id, type, date_operation, libelle, debit, credit, rapproche) VALUES (?,?,'comptable',?,?,?,?,1) ON DUPLICATE KEY UPDATE rapproche=1");
            $ins->execute([$rap_id, $ligne_id, $l['date_ecriture'], $l['libelle'], $l['debit'], $l['credit']]);
        } else {
            $del = $db->prepare("DELETE FROM rapprochements_lignes WHERE rapprochement_id=? AND ligne_ecriture_id=?");
            $del->execute([$rap_id, $ligne_id]);
        }

        // Recalculer solde comptable rapproché
        $stmt = $db->prepare("SELECT COALESCE(SUM(l.debit),0)-COALESCE(SUM(l.credit),0) FROM rapprochements_lignes rl JOIN lignes_ecritures l ON l.id=rl.ligne_ecriture_id WHERE rl.rapprochement_id=? AND rl.rapproche=1");
        $stmt->execute([$rap_id]);
        $sc = (float)$stmt->fetchColumn();

        $stmt2 = $db->prepare("SELECT solde_releve FROM rapprochements_bancaires WHERE id=?");
        $stmt2->execute([$rap_id]);
        $sr = (float)$stmt2->fetchColumn();
        $ecart = $sr - $sc;

        $statut = abs($ecart) < 0.01 ? 'rapproche' : 'en_cours';
        $upd = $db->prepare("UPDATE rapprochements_bancaires SET solde_comptable=?, ecart=?, statut=? WHERE id=?");
        $upd->execute([$sc, $ecart, $statut, $rap_id]);

        redirect("/dossier/rapprochement/voir?id=$id&rap_id=$rap_id");
    }
}
