<?php
require_once APP_ROOT . '/config/app.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Module d'import de données dans un dossier :
 *  - Clients / Fournisseurs (table tiers)
 *  - Plan comptable (table comptes)
 *  - Balance d'ouverture N-1 (écriture "Report à nouveau" en brouillon, journal OD)
 *
 * Accepte CSV (séparateur auto , ou ;) et Excel (.xlsx via PhpSpreadsheet).
 */
class ImportDonneesController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    // ── Page principale ───────────────────────────────────────────────────────
    public function index(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $activeTab  = 'import';
        $pageTitle  = 'Importer des données';
        $exercice   = $this->exerciceCourant($entreprise);

        ob_start();
        require APP_ROOT . '/views/dossier/import.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ── Téléchargement des modèles ────────────────────────────────────────────
    public function modele(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $this->getEntreprise($id);
        $type = $_GET['type'] ?? '';

        $modeles = [
            'tiers'   => ['nom_fichier' => 'modele_tiers.csv',
                          'entetes' => ['nom','type','ninea','telephone','email','adresse'],
                          'exemple' => ['SARL Exemple','client','SN1234567','+221770000000','contact@exemple.sn','Dakar']],
            'comptes' => ['nom_fichier' => 'modele_plan_comptable.csv',
                          'entetes' => ['numero','intitule','type_compte'],
                          'exemple' => ['401000','Fournisseurs','passif']],
            'balance' => ['nom_fichier' => 'modele_balance_ouverture.csv',
                          'entetes' => ['numero','intitule','debit','credit'],
                          'exemple' => ['521000','Banque','1500000','0']],
        ];
        if (!isset($modeles[$type])) redirect("/dossier/import?id=$id");
        $m = $modeles[$type];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $m['nom_fichier'] . '"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
        $out = fopen('php://output', 'w');
        fputcsv($out, $m['entetes'], ';');
        fputcsv($out, $m['exemple'], ';');
        fclose($out);
        exit;
    }

    // ── Import Clients ────────────────────────────────────────────────────────
    public function importClients(): void {
        $this->importTiersType('client', 'IMPORT_CLIENTS', 'clients');
    }

    // ── Import Fournisseurs ───────────────────────────────────────────────────
    public function importFournisseurs(): void {
        $this->importTiersType('fournisseur', 'IMPORT_FOURNISSEURS', 'fournisseurs');
    }

    /** Logique commune d'import de tiers pour un type donné (client|fournisseur). */
    private function importTiersType(string $type, string $action, string $libelle): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $mode = ($_POST['mode'] ?? 'fusion') === 'remplacement' ? 'remplacement' : 'fusion';
        $rows = $this->lireFichier('fichier', "/dossier/import?id=$id");
        if ($rows === null) redirect("/dossier/import?id=$id");

        $ok = 0; $ignore = 0; $archives = 0;
        $db->beginTransaction();
        try {
            // Remplacement : soft-delete (actif=0) des tiers existants du même type.
            // On ne supprime pas en dur car les tiers peuvent être référencés par des écritures.
            if ($mode === 'remplacement') {
                $del = $db->prepare("UPDATE tiers SET actif=0 WHERE entreprise_id=? AND type IN (?, 'les_deux') AND actif=1");
                $del->execute([$id, $type]);
                $archives = $del->rowCount();
            }

            $stmt = $db->prepare("INSERT INTO tiers (entreprise_id, nom, type, ninea, telephone, email, adresse) VALUES (?,?,?,?,?,?,?)");
            foreach ($rows as $r) {
                $nom = trim($this->col($r, ['nom','raison_sociale','raison sociale','client','fournisseur','denomination']) ?? '');
                if ($nom === '') { $ignore++; continue; }

                $ninea = trim($this->col($r, ['ninea','nina']) ?? '');
                $tel   = trim($this->col($r, ['telephone','téléphone','tel','tél','phone']) ?? '');
                $email = trim($this->col($r, ['email','mail','e-mail']) ?? '');
                $adr   = trim($this->col($r, ['adresse','address','ville']) ?? '');

                $stmt->execute([$id, $nom, $type, $ninea ?: null, $tel ?: null, $email ?: null, $adr ?: null]);
                $ok++;
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur import $libelle : " . $e->getMessage();
            redirect("/dossier/import?id=$id");
        }

        $this->logImport($id, $action, "$ok $libelle importés (mode $mode)");
        $_SESSION['flash_success'] = ucfirst($libelle) . " : $ok importé(s)."
            . ($mode === 'remplacement' && $archives ? " $archives ancien(s) archivé(s)." : "")
            . ($ignore ? " $ignore ligne(s) ignorée(s) (nom vide)." : "");
        redirect("/dossier/import?id=$id");
    }

    // ── Import Plan comptable ─────────────────────────────────────────────────
    public function importPlanComptable(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $mode = ($_POST['mode'] ?? 'fusion') === 'remplacement' ? 'remplacement' : 'fusion';
        $rows = $this->lireFichier('fichier', "/dossier/import?id=$id");
        if ($rows === null) redirect("/dossier/import?id=$id");

        $ok = 0; $ignore = 0; $supprimes = 0; $proteges = 0;
        $db->beginTransaction();
        try {
            // Remplacement : supprimer les comptes NON utilisés dans des écritures
            // (ceux utilisés sont protégés par la FK lignes_ecritures -> on les garde).
            if ($mode === 'remplacement') {
                $utilises = $db->prepare(
                    "SELECT COUNT(*) FROM comptes c WHERE c.entreprise_id=?
                     AND EXISTS (SELECT 1 FROM lignes_ecritures l WHERE l.compte_id=c.id)");
                $utilises->execute([$id]);
                $proteges = (int)$utilises->fetchColumn();

                $delC = $db->prepare(
                    "DELETE FROM comptes WHERE entreprise_id=?
                     AND id NOT IN (SELECT DISTINCT compte_id FROM lignes_ecritures
                                    WHERE compte_id IS NOT NULL)");
                $delC->execute([$id]);
                $supprimes = $delC->rowCount();
            }

            // Comptes restants pour éviter les doublons (contrainte UNIQUE)
            $existants = [];
            $q = $db->prepare("SELECT numero FROM comptes WHERE entreprise_id=?");
            $q->execute([$id]);
            foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $num) $existants[(string)$num] = true;

            $stmt = $db->prepare("INSERT INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,?,?)");
            foreach ($rows as $r) {
                $numero   = strtoupper(trim($this->col($r, ['numero','numéro','compte','n°','code']) ?? ''));
                $intitule = trim($this->col($r, ['intitule','intitulé','libelle','libellé','nom','designation']) ?? '');
                if ($numero === '' || $intitule === '') { $ignore++; continue; }
                if (isset($existants[$numero])) { $ignore++; continue; }

                $classe = (int)substr($numero, 0, 1);
                if ($classe < 1 || $classe > 9) { $ignore++; continue; }

                // type_compte : depuis le fichier sinon déduit de la classe
                $type = strtolower(trim($this->col($r, ['type_compte','type','nature']) ?? ''));
                if (!in_array($type, ['actif','passif','charge','produit','bilan'], true)) {
                    $type = $this->typeDepuisClasse($classe);
                }

                $stmt->execute([$id, $numero, $intitule, $type, $classe]);
                $existants[$numero] = true;
                $ok++;
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur import plan comptable : " . $e->getMessage();
            redirect("/dossier/import?id=$id");
        }

        $this->logImport($id, 'IMPORT_PLAN', "$ok comptes importés (mode $mode)");
        $msg = "$ok compte(s) importé(s).";
        if ($mode === 'remplacement') {
            $msg .= " $supprimes ancien(s) supprimé(s).";
            if ($proteges) $msg .= " $proteges compte(s) utilisé(s) conservé(s) (présents dans des écritures).";
        }
        if ($ignore) $msg .= " $ignore ligne(s) ignorée(s) (vide ou déjà existant).";
        $_SESSION['flash_success'] = $msg;
        redirect("/dossier/import?id=$id");
    }

    // ── Import Balance d'ouverture N-1 ────────────────────────────────────────
    public function importBalance(): void {
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        verifyCsrfToken($_POST['csrf_token'] ?? '');
        $db = getDB();

        $mode     = ($_POST['mode'] ?? 'fusion') === 'remplacement' ? 'remplacement' : 'fusion';
        $exercice = (int)($_POST['exercice'] ?? $this->exerciceCourant($entreprise));
        if ($exercice < 2000) { $_SESSION['flash_error'] = "Exercice invalide."; redirect("/dossier/import?id=$id"); }

        $rows = $this->lireFichier('fichier', "/dossier/import?id=$id");
        if ($rows === null) redirect("/dossier/import?id=$id");

        // Report à nouveau déjà présent ?
        $check = $db->prepare("SELECT id FROM ecritures WHERE entreprise_id=? AND exercice=? AND libelle LIKE 'Report à nouveau%'");
        $check->execute([$id, $exercice]);
        $anciensAN = $check->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($anciensAN)) {
            if ($mode === 'fusion') {
                $_SESSION['flash_error'] = "Un report à nouveau existe déjà pour l'exercice $exercice. Utilisez le mode « Remplacement » pour l'écraser.";
                redirect("/dossier/import?id=$id");
            }
            // Remplacement : supprimer l'ancienne écriture AN + ses lignes
            $in = implode(',', array_fill(0, count($anciensAN), '?'));
            $db->prepare("DELETE FROM lignes_ecritures WHERE ecriture_id IN ($in)")->execute($anciensAN);
            $db->prepare("DELETE FROM ecritures WHERE id IN ($in)")->execute($anciensAN);
        }

        // Journal OD
        $stmtJ = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='OD' LIMIT 1");
        $stmtJ->execute([$id]);
        $journalId = $stmtJ->fetchColumn();
        if (!$journalId) { $_SESSION['flash_error'] = "Journal OD introuvable pour ce dossier."; redirect("/dossier/import?id=$id"); }

        // Index des comptes existants : numero => id
        $comptes = [];
        $q = $db->prepare("SELECT id, numero FROM comptes WHERE entreprise_id=?");
        $q->execute([$id]);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $c) $comptes[(string)$c['numero']] = (int)$c['id'];

        // Préparer les lignes + équilibre
        $lignes = []; $totalD = 0.0; $totalC = 0.0; $ignore = 0; $comptesCrees = 0;

        $db->beginTransaction();
        try {
            $insCompte = $db->prepare("INSERT INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,?,?)");

            foreach ($rows as $r) {
                $numero = strtoupper(trim($this->col($r, ['numero','numéro','compte','code']) ?? ''));
                if ($numero === '') { $ignore++; continue; }
                $debit  = $this->montant($this->col($r, ['debit','débit','solde_debit','solde debit']) ?? '0');
                $credit = $this->montant($this->col($r, ['credit','crédit','solde_credit','solde credit']) ?? '0');
                if (abs($debit) < 0.01 && abs($credit) < 0.01) { $ignore++; continue; }

                // Créer le compte s'il n'existe pas
                if (!isset($comptes[$numero])) {
                    $classe = (int)substr($numero, 0, 1);
                    if ($classe < 1 || $classe > 9) { $ignore++; continue; }
                    $intitule = trim($this->col($r, ['intitule','intitulé','libelle','libellé','nom']) ?? ('Compte ' . $numero));
                    $insCompte->execute([$id, $numero, $intitule, $this->typeDepuisClasse($classe), $classe]);
                    $comptes[$numero] = (int)$db->lastInsertId();
                    $comptesCrees++;
                }

                $lignes[] = ['compte_id' => $comptes[$numero], 'numero' => $numero, 'debit' => $debit, 'credit' => $credit];
                $totalD += $debit; $totalC += $credit;
            }

            if (empty($lignes)) {
                $db->rollBack();
                $_SESSION['flash_error'] = "Aucune ligne valide trouvée dans le fichier.";
                redirect("/dossier/import?id=$id");
            }

            // Contrôle d'équilibre (tolérance 1 FCFA)
            if (abs($totalD - $totalC) > 1.0) {
                $db->rollBack();
                $_SESSION['flash_error'] = sprintf(
                    "Balance déséquilibrée : débit %s ≠ crédit %s (écart %s). Import annulé.",
                    number_format($totalD, 0, ',', ' '),
                    number_format($totalC, 0, ',', ' '),
                    number_format(abs($totalD - $totalC), 0, ',', ' ')
                );
                redirect("/dossier/import?id=$id");
            }

            // Écriture "Report à nouveau" en brouillon (cohérent avec reportANouveaux)
            $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, user_id, date_ecriture, libelle, exercice, periode, statut, numero_piece)
                          VALUES (?, ?, ?, ?, 'Report à nouveau exercice $exercice (import)', ?, 1, 'brouillon', 'AN-$exercice')")
               ->execute([$id, $journalId, auth()['id'], "$exercice-01-01", $exercice]);
            $ecritureId = (int)$db->lastInsertId();

            $insLigne = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");
            foreach ($lignes as $l) {
                $insLigne->execute([$ecritureId, $l['compte_id'], 'AN ' . $l['numero'], $l['debit'], $l['credit']]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur import balance : " . $e->getMessage();
            redirect("/dossier/import?id=$id");
        }

        $this->logImport($id, 'IMPORT_BALANCE', count($lignes) . " lignes (exercice $exercice)");
        $_SESSION['flash_success'] = sprintf(
            "Balance d'ouverture %s importée : %d lignes, écriture AN en brouillon.%s À valider dans les écritures.",
            $exercice, count($lignes),
            $comptesCrees ? " $comptesCrees compte(s) créé(s)." : ""
        );
        redirect("/dossier/ecritures?id=$id&exercice=$exercice");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Année de l'exercice courant du dossier (session prioritaire). */
    private function exerciceCourant(array $entreprise): int {
        $sess = $_SESSION['exercice'][$entreprise['id']] ?? null;
        return (int)($sess ?: ($entreprise['exercice_courant'] ?? date('Y')));
    }

    /** Déduit le type_compte SYSCOHADA depuis la classe. */
    private function typeDepuisClasse(int $classe): string {
        switch ($classe) {
            case 1: return 'passif';
            case 2: case 3: case 5: return 'actif';
            case 4: return 'bilan';   // tiers (actif ou passif selon solde)
            case 6: return 'charge';
            case 7: return 'produit';
            default: return 'bilan';
        }
    }

    /** Récupère une valeur de ligne par liste d'alias d'en-têtes (insensible casse/accents). */
    private function col(array $row, array $alias): ?string {
        foreach ($alias as $a) {
            $key = $this->norm($a);
            if (array_key_exists($key, $row)) return $row[$key];
        }
        return null;
    }

    private function norm(string $s): string {
        $s = mb_strtolower(trim($s));
        $s = strtr($s, ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a','î'=>'i','ï'=>'i','ô'=>'o','û'=>'u','ç'=>'c']);
        return $s;
    }

    /** Nettoie un montant : "1 500 000,50 FCFA" -> 1500000.50 */
    private function montant(string $v): float {
        $v = preg_replace('/[^0-9,.\-]/', '', $v);
        // Si virgule décimale (format FR), retirer points de milliers puis virgule -> point
        if (strpos($v, ',') !== false) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return (float)$v;
    }

    /**
     * Lit le fichier uploadé (CSV ou XLSX) et retourne un tableau de lignes
     * associatives indexées par en-tête normalisé. Retourne null en cas d'erreur.
     */
    private function lireFichier(string $champ, string $redirectErr): ?array {
        if (empty($_FILES[$champ]['tmp_name']) || $_FILES[$champ]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = "Aucun fichier reçu ou upload échoué.";
            return null;
        }
        if ($_FILES[$champ]['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = "Fichier trop volumineux (max 10 Mo).";
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$champ]['name'], PATHINFO_EXTENSION));
        $allowed = ['csv','txt','xlsx','xls'];
        if (!in_array($ext, $allowed, true)) {
            $_SESSION['flash_error'] = "Format non supporté ($ext). Utilisez CSV ou Excel (.xlsx).";
            return null;
        }

        try {
            $matrice = in_array($ext, ['xlsx','xls'], true)
                ? $this->lireExcel($_FILES[$champ]['tmp_name'])
                : $this->lireCSV($_FILES[$champ]['tmp_name']);
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lecture du fichier impossible : " . $e->getMessage();
            return null;
        }

        if (count($matrice) < 2) {
            $_SESSION['flash_error'] = "Le fichier ne contient pas de données (en-tête + au moins 1 ligne attendus).";
            return null;
        }

        // Première ligne = en-têtes
        $entetes = array_map(fn($h) => $this->norm((string)$h), array_shift($matrice));
        $lignes = [];
        foreach ($matrice as $cols) {
            if (count(array_filter($cols, fn($c) => trim((string)$c) !== '')) === 0) continue; // ligne vide
            $row = [];
            foreach ($entetes as $i => $h) {
                if ($h === '') continue;
                $row[$h] = isset($cols[$i]) ? (string)$cols[$i] : '';
            }
            $lignes[] = $row;
        }
        return $lignes;
    }

    private function lireCSV(string $path): array {
        $content = file_get_contents($path);
        if ($content === false) throw new \RuntimeException("fichier illisible");
        // Retirer BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        // Détection séparateur : ; ou ,
        $premiereLigne = strtok($content, "\n");
        $sep = (substr_count($premiereLigne, ';') >= substr_count($premiereLigne, ',')) ? ';' : ',';

        $matrice = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) as $ligne) {
            if (trim($ligne) === '') continue;
            $matrice[] = str_getcsv($ligne, $sep);
        }
        return $matrice;
    }

    private function lireExcel(string $path): array {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $ss = $reader->load($path);
        return $ss->getActiveSheet()->toArray(null, true, false, false);
    }

    private function logImport(int $entrepriseId, string $action, string $details): void {
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log((int)(auth()['id']), $action, $entrepriseId, 'entreprises', $entrepriseId, $details);
    }
}
