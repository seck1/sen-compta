<?php
require_once APP_ROOT . '/config/app.php';

class ImmoController {

    private function getAccess(int $id): array {
        requireAuth();
        $entreprise = getEntreprise($id);
        if (empty($entreprise)) { http_response_code(404); echo "Dossier introuvable"; exit; }
        if (!userHasAccess($id)) redirect('/dashboard');
        return $entreprise;
    }

    public function index(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getAccess($id);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM immobilisations WHERE entreprise_id=? ORDER BY type, date_acquisition");
        $stmt->execute([$id]);
        $immobilisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = ['corporelle'=>[], 'incorporelle'=>[], 'financiere'=>[]];
        foreach ($immobilisations as $immo) {
            $grouped[$immo['type']][] = $immo;
        }

        $total_brut  = array_sum(array_column($immobilisations, 'valeur_brute'));
        $total_amort = array_sum(array_column($immobilisations, 'amort_cumule'));
        $total_net   = array_sum(array_column($immobilisations, 'valeur_nette'));
        $amort_calcule = isset($_GET['amort_calcule']);

        $pageTitle = 'Immobilisations';
        $activeTab = 'immo';
        ob_start();
        require APP_ROOT . '/views/dossier/immo/index.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function creer(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getAccess($id);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM comptes WHERE entreprise_id=? AND numero LIKE '2%' ORDER BY numero");
        $stmt->execute([$id]);
        $comptes_immo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Nouvelle immobilisation';
        $activeTab = 'immo';
        ob_start();
        require APP_ROOT . '/views/dossier/immo/form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getAccess($id);

        $valeur_brute      = (float)($_POST['valeur_brute'] ?? 0);
        $duree_amort       = (int)($_POST['duree_amort'] ?? 5);
        $methode           = $_POST['methode_amort'] ?? 'lineaire';
        $taux              = $methode === 'lineaire' ? round(1 / max(1,$duree_amort), 4) : round(2 / max(1,$duree_amort), 4);
        $valeur_residuelle = (float)($_POST['valeur_residuelle'] ?? 0);

        $db = getDB();
        $compte_numero = $_POST['compte_numero'];
        $date_acquisition = $_POST['date_acquisition'];
        $designation = trim($_POST['designation']);

        $stmt = $db->prepare("INSERT INTO immobilisations
            (entreprise_id, designation, type, categorie, compte_numero, date_acquisition, date_mise_service,
             valeur_brute, methode_amort, duree_amort, taux_amort, valeur_residuelle, valeur_nette, reference, fournisseur)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $id,
            $designation,
            $_POST['type'] ?? 'corporelle',
            trim($_POST['categorie'] ?? ''),
            $compte_numero,
            $date_acquisition,
            ($_POST['date_mise_service'] ?: null),
            $valeur_brute,
            $methode,
            $duree_amort,
            $taux,
            $valeur_residuelle,
            $valeur_brute - $valeur_residuelle,
            trim($_POST['reference'] ?? ''),
            trim($_POST['fournisseur'] ?? ''),
        ]);
        $immo_id = $db->lastInsertId();

        $this->genererEcritureAcquisition($db, $id, $immo_id, $designation, $compte_numero, $date_acquisition, $valeur_brute);

        redirect("/dossier/immo?id=$id");
    }

    private function genererEcritureAmortissement($db, int $entreprise_id, array $immo, float $dotation, int $exercice): void {
        try {
            $mois = 12;
            $date = "$exercice-12-31";
            $piece = 'AMO-' . $immo['id'] . '-' . $exercice;

            // Éviter les doublons
            $stmt = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND numero_piece=?");
            $stmt->execute([$entreprise_id, $piece]);
            if ($stmt->fetchColumn() > 0) return;

            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='OD' LIMIT 1");
            $jStmt->execute([$entreprise_id]);
            $journal = $jStmt->fetch(PDO::FETCH_ASSOC);
            if (!$journal) return;
            $journal_id = $journal['id'];

            $getCompte = function($numero_compte) use ($db, $entreprise_id) {
                $s = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $s->execute([$entreprise_id, $numero_compte]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                return $r ? (int)$r['id'] : null;
            };

            $compte_charge = $immo['type'] === 'incorporelle' ? '6812' : '6811';
            // Compte d'amortissement SYSCOHADA : 28 + racine du compte d'immo (classe 2x -> 28x).
            // Ex. 2441 (materiel) -> 2844 ; 2181 -> 2818 ; 213 -> 2813.
            // On prend "28" + tout le numero d'immo SAUF le premier chiffre (le "2" de la classe).
            $racine = preg_replace('/^2/', '', $immo['compte_numero']); // "2441" -> "441"
            // racine SYSCOHADA = 2 chiffres significatifs apres le 2 (compte d'amort a 4 chiffres : 28xx)
            $compte_amort  = '28' . substr($racine, 0, 2);              // "441" -> "2844"
            $libelle = "Dotation amort. $exercice : " . $immo['designation'];

            // Resoudre les comptes AVANT d'inserer : si l'un manque, on n'ecrit rien (anti-desequilibre)
            $cid_charge = $getCompte($compte_charge);
            $cid_amort  = $getCompte($compte_amort);
            if (!$cid_charge || !$cid_amort) {
                error_log("Immo: compte amortissement manquant (charge=$compte_charge, amort=$compte_amort) — ecriture de dotation non generee pour immo " . $immo['id']);
                return;
            }

            $user_id = auth()['id'];
            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'brouillon')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $piece, $date, $libelle, $exercice, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");
            $lStmt->execute([$ecriture_id, $cid_charge, $libelle, $dotation, 0]);   // debit 6811/6812
            $lStmt->execute([$ecriture_id, $cid_amort,  $libelle, 0, $dotation]);   // credit 28xx
        } catch (\Exception $e) {
            // Ne pas bloquer le calcul des amortissements
        }
    }

    private function genererEcritureAcquisition($db, int $entreprise_id, int $immo_id, string $designation, string $compte_immo, string $date, float $montant): void {
        try {
            $stmt = $db->prepare("SELECT exercice_courant FROM entreprises WHERE id=?");
            $stmt->execute([$entreprise_id]);
            $exercice = (int)($stmt->fetchColumn() ?: date('Y'));
            $mois = (int)date('n', strtotime($date));

            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='OD' LIMIT 1");
            $jStmt->execute([$entreprise_id]);
            $journal = $jStmt->fetch(PDO::FETCH_ASSOC);
            if (!$journal) return;
            $journal_id = $journal['id'];

            $getCompte = function($numero_compte) use ($db, $entreprise_id) {
                $s = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $s->execute([$entreprise_id, $numero_compte]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                return $r ? (int)$r['id'] : null;
            };

            $user_id = auth()['id'];
            $libelle = "Acquisition : $designation";
            $piece   = 'IMM-' . $immo_id;

            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'brouillon')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $piece, $date, $libelle, $exercice, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");

            // DÉBIT compte immobilisation (2xx)
            if ($c = $getCompte($compte_immo)) {
                $lStmt->execute([$ecriture_id, $c, $libelle, $montant, 0]);
            }
            // CRÉDIT 404 Fournisseurs d'immobilisations
            if ($c = $getCompte('404')) {
                $lStmt->execute([$ecriture_id, $c, $libelle, 0, $montant]);
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la création de l'immobilisation
        }
    }

    public function calculerAmortissements(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getAccess($id);
        $exercice = (int)($_POST['exercice'] ?? date('Y'));

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM immobilisations WHERE entreprise_id=? AND statut='actif'");
        $stmt->execute([$id]);
        $immos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($immos as $immo) {
            $dateAcq   = new DateTime($immo['date_acquisition']);
            $dateDebut = new DateTime("$exercice-01-01");
            $dateFin   = new DateTime("$exercice-12-31");

            if ($dateAcq > $dateFin) continue;

            $dotation = 0;
            if ($immo['methode_amort'] === 'lineaire') {
                $dotation = ($immo['valeur_brute'] - $immo['valeur_residuelle']) * (float)$immo['taux_amort'];
                if ($dateAcq > $dateDebut) {
                    $joursUtil = $dateFin->diff($dateAcq)->days + 1;
                    $dotation  = $dotation * $joursUtil / 365;
                }
            } else {
                $vnc = $immo['valeur_brute'] - $immo['amort_cumule'];
                $dotation = $vnc * (float)$immo['taux_amort'];
            }

            $new_amort = min($immo['valeur_brute'] - $immo['valeur_residuelle'], $immo['amort_cumule'] + $dotation);
            $new_net   = max(0, $immo['valeur_brute'] - $new_amort);

            $db->prepare("UPDATE immobilisations SET amort_cumule=?, valeur_nette=? WHERE id=? AND entreprise_id=?")
               ->execute([$new_amort, $new_net, $immo['id'], $id]);

            // Écriture de dotation aux amortissements
            if ($dotation > 0.01) {
                $this->genererEcritureAmortissement($db, $id, $immo, $dotation, $exercice);
            }
        }

        redirect("/dossier/immo?id=$id&amort_calcule=1");
    }
}
