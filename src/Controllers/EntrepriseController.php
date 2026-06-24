<?php
require_once APP_ROOT . '/config/app.php';

class EntrepriseController {

    /**
     * Valide un upload de logo et le déplace dans public/logos/ avec un nom sûr.
     * Sécurité : allowlist extension + MIME réel, limite de taille, nom aléatoire.
     * Retourne le nom de fichier final, ou null si rejet/échec.
     */
    private function handleLogoUpload(array $file): ?string {
        if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$ext])) {
            return null; // extension non autorisée
        }
        if (($file['size'] ?? 0) > 2_000_000) {
            return null; // > 2 Mo
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if ($mime !== $allowed[$ext]) {
            return null; // le contenu réel ne correspond pas à l'extension
        }
        $fname = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest  = APP_ROOT . '/public/logos/' . $fname;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $fname;
        }
        return null;
    }

    public function index(): void {
        requireAuth();

        $db   = getDB();
        $user = auth();
        $cabinetId = $user['cabinet_id'] ?? null;

        // API count pour sidebar
        if (isset($_GET['api']) && $_GET['api'] === 'count') {
            header('Content-Type: application/json');
            if ($cabinetId) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM entreprises WHERE statut='actif' AND cabinet_id = ?");
                $stmt->execute([$cabinetId]);
                $count = (int)$stmt->fetchColumn();
            } else {
                $count = (int)$db->query("SELECT COUNT(*) FROM entreprises WHERE statut='actif'")->fetchColumn();
            }
            echo json_encode(['count' => $count]);
            exit;
        }

        if (isAdmin() && !$cabinetId) {
            // Super admin — voit tout
            $entreprises = $db->query("SELECT * FROM entreprises ORDER BY raison_sociale")->fetchAll();
        } elseif (isAdmin() && $cabinetId) {
            // Admin cabinet — voit uniquement son cabinet
            $stmt = $db->prepare("SELECT * FROM entreprises WHERE cabinet_id = ? ORDER BY raison_sociale");
            $stmt->execute([$cabinetId]);
            $entreprises = $stmt->fetchAll();
        } else {
            // Collaborateur — voit ses dossiers assignés dans son cabinet
            $stmt = $db->prepare("SELECT e.* FROM entreprises e JOIN user_entreprises ue ON ue.entreprise_id = e.id WHERE ue.user_id = ? AND ue.actif = 1 AND e.cabinet_id = ? ORDER BY e.raison_sociale");
            $stmt->execute([$user['id'], $cabinetId]);
            $entreprises = $stmt->fetchAll();
        }

        ob_start();
        require_once APP_ROOT . '/views/companies/index.php';
        $content = ob_get_clean();

        $pageTitle  = 'Dossiers Entreprises';
        $activePage = 'entreprises';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function create(): void {
        requireAdmin();
        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);
        ob_start();
        require_once APP_ROOT . '/views/companies/form.php';
        $content = ob_get_clean();
        $pageTitle  = 'Nouveau dossier';
        $activePage = 'entreprises';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function store(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/entreprises');

        $data = [
            'code_dossier'              => strtoupper(trim($_POST['code_dossier'] ?? '')),
            'raison_sociale'            => trim($_POST['raison_sociale'] ?? ''),
            'forme_juridique'           => $_POST['forme_juridique'] ?? 'SARL',
            'ninea'                     => trim($_POST['ninea'] ?? ''),
            'rccm'                      => trim($_POST['rccm'] ?? ''),
            'secteur_activite'          => trim($_POST['secteur_activite'] ?? ''),
            'adresse'                   => trim($_POST['adresse'] ?? ''),
            'telephone'                 => trim($_POST['telephone'] ?? ''),
            'email'                     => trim($_POST['email'] ?? ''),
            'exercice_courant'          => (int)($_POST['exercice_courant'] ?? date('Y')),
            'regime_fiscal'             => $_POST['regime_fiscal'] ?? 'CGI',
            'couleur'                   => $_POST['couleur'] ?? '#1e3a5f',
            'ca_annuel_estime'          => (float)($_POST['ca_annuel_estime'] ?? 0),
            'secteur_activite_detail'   => trim($_POST['secteur_activite_detail'] ?? ''),
            'numero_contribuable'       => trim($_POST['numero_contribuable'] ?? ''),
            'numero_registre_commerce'  => trim($_POST['numero_registre_commerce'] ?? ''),
            'regime_tva'                => $_POST['regime_tva'] ?? 'mensuel',
            'date_debut_exoneration'    => $_POST['date_debut_exoneration'] ?: null,
            'date_fin_exoneration'      => $_POST['date_fin_exoneration'] ?: null,
        ];

        if (!$data['code_dossier'] || !$data['raison_sociale']) {
            $_SESSION['form_error'] = 'Le code dossier et la raison sociale sont obligatoires.';
            redirect('/entreprises/create');
        }

        // Upload logo (validé : allowlist ext+MIME, taille, nom aléatoire)
        $logo = $this->handleLogoUpload($_FILES['logo'] ?? []);

        try {
            $db        = getDB();
            $user      = auth();
            $cabinetId = $user['cabinet_id'] ?? null;
            $stmt = $db->prepare("INSERT INTO entreprises (cabinet_id, code_dossier, raison_sociale, forme_juridique, ninea, rccm, secteur_activite, adresse, telephone, email, exercice_courant, regime_fiscal, couleur, ca_annuel_estime, secteur_activite_detail, numero_contribuable, numero_registre_commerce, regime_tva, date_debut_exoneration, date_fin_exoneration, logo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$cabinetId, ...array_values($data), $logo]);
            $new_id = (int)$db->lastInsertId();
            $this->initialiserDossier($db, $new_id);
            redirect('/entreprises');
        } catch (Exception $e) {
            $_SESSION['form_error'] = 'Ce code dossier existe déjà.';
            redirect('/entreprises/create');
        }
    }

    private function initialiserDossier($db, int $entreprise_id): void {
        // Journaux standard
        $journaux = [
            ['ACH', 'Journal des Achats',                  'achat'],
            ['VTE', 'Journal des Ventes',                  'vente'],
            ['BNQ', 'Journal de Banque',                   'banque'],
            ['CAI', 'Journal de Caisse',                   'caisse'],
            ['MOB', 'Mobile Money (Wave / Orange Money)',  'mobile_money'],
            ['OD',  'Opérations Diverses',                 'operations_diverses'],
            ['PAI', 'Journal de Paie',                     'paie'],
        ];
        $jStmt = $db->prepare("INSERT IGNORE INTO journaux (entreprise_id, code, libelle, type) VALUES (?,?,?,?)");
        foreach ($journaux as [$code, $libelle, $type]) {
            $jStmt->execute([$entreprise_id, $code, $libelle, $type]);
        }

        // Plan comptable SYSCOHADA essentiel
        $comptes = [
            // Classe 1 — Capitaux
            ['101', 'Capital social',                          'passif', 1],
            ['111', 'Report à nouveau créditeur',              'passif', 1],
            ['12',  'Résultat de l\'exercice',                 'passif', 1],
            // Classe 2 — Immobilisations
            ['211', 'Terrains',                                'actif',  2],
            ['221', 'Bâtiments',                               'actif',  2],
            ['231', 'Matériel et outillage industriel',        'actif',  2],
            ['241', 'Matériel de transport',                   'actif',  2],
            ['244', 'Matériel et mobilier de bureau',          'actif',  2],
            ['281', 'Amort. terrains',                         'passif', 2],
            ['282', 'Amort. bâtiments',                        'passif', 2],
            ['283', 'Amort. matériel et outillage',            'passif', 2],
            ['284', 'Amort. matériel de transport',            'passif', 2],
            ['285', 'Amort. matériel de bureau',               'passif', 2],
            // Classe 3 — Stocks
            ['31',  'Marchandises',                            'actif',  3],
            ['32',  'Matières premières',                      'actif',  3],
            // Classe 4 — Tiers
            ['401', 'Fournisseurs',                            'passif', 4],
            ['404', 'Fournisseurs d\'immobilisations',         'passif', 4],
            ['411', 'Clients',                                 'actif',  4],
            ['421', 'Personnel — avances et acomptes',         'actif',  4],
            ['422', 'Personnel — rémunérations dues',          'passif', 4],
            ['431', 'Sécurité sociale',                        'passif', 4],
            ['4311','IPRES — part salariale',                  'passif', 4],
            ['4312','IPRES — part patronale',                  'passif', 4],
            ['441', 'État — impôts sur bénéfices',             'passif', 4],
            ['4431','TVA facturée sur ventes',                 'passif', 4],
            ['4441','TVA récupérable sur achats',              'actif',  4],
            ['4471','Impôts sur salaires (IR/IRPP)',           'passif', 4],
            ['467', 'Autres comptes créditeurs',               'passif', 4],
            ['471', 'Débiteurs divers',                        'actif',  4],
            ['481', 'Charges constatées d\'avance',            'actif',  4],
            // Classe 5 — Trésorerie
            ['521',  'Banques',                'actif', 5],
            ['5211', 'Banque classique',        'actif', 5],
            ['5212', 'Orange Money',            'actif', 5],
            ['5213', 'Wave',                    'actif', 5],
            ['5214', 'Free Money',              'actif', 5],
            ['531',  'Caisse siège social',     'actif', 5],
            // Classe 6 — Charges
            ['601', 'Achats de marchandises',                  'charge', 6],
            ['602', 'Achats de matières premières',            'charge', 6],
            ['604', 'Achats non stockés',                      'charge', 6],
            ['6064','Fournitures de bureau',                   'charge', 6],
            ['611', 'Transports sur achats',                   'charge', 6],
            ['6251','Frais de transport',                      'charge', 6],
            ['6252','Frais de repas',                          'charge', 6],
            ['6253','Frais d\'hébergement',                    'charge', 6],
            ['6258','Autres frais de déplacements',            'charge', 6],
            ['6262','Frais de communication',                  'charge', 6],
            ['631', 'Frais bancaires',                         'charge', 6],
            ['641', 'Impôts et taxes',                         'charge', 6],
            ['6411','Patente',                                  'charge', 6],
            ['661', 'Rémunérations du personnel',              'charge', 6],
            ['6611','Salaires bruts',                          'charge', 6],
            ['6641','Cotisations patronales IPRES',            'charge', 6],
            ['6642','Cotisations patronales CSS',              'charge', 6],
            ['671', 'Intérêts sur emprunts',                   'charge', 6],
            ['6811','Dotations amort. immobilisations corp.',  'charge', 6],
            ['6812','Dotations amort. immobilisations incorp.','charge', 6],
            // Classe 7 — Produits
            ['701', 'Ventes de marchandises',                  'produit',7],
            ['702', 'Ventes de produits finis',                'produit',7],
            ['706', 'Prestations de services',                 'produit',7],
            ['708', 'Produits des activités annexes',          'produit',7],
            ['771', 'Intérêts reçus',                          'produit',7],
        ];

        $cStmt = $db->prepare("INSERT IGNORE INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,?,?)");
        foreach ($comptes as [$num, $intitule, $type, $classe]) {
            $cStmt->execute([$entreprise_id, $num, $intitule, $type, $classe]);
        }
    }

    public function edit(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM entreprises WHERE id = ?");
        $stmt->execute([$id]);
        $entreprise = $stmt->fetch();
        if (!$entreprise) redirect('/entreprises');

        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);
        $editMode = true;

        ob_start();
        require_once APP_ROOT . '/views/companies/form.php';
        $content = ob_get_clean();
        $pageTitle  = 'Modifier — ' . $entreprise['raison_sociale'];
        $activePage = 'entreprises';
        require_once APP_ROOT . '/views/layouts/main.php';
    }

    public function update(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/entreprises');
        $id = (int)($_POST['id'] ?? 0);

        $db = getDB();

        // Gestion logo
        $logoSql = '';
        $logoParams = [];

        if (!empty($_POST['supprimer_logo'])) {
            // Supprimer l'ancien fichier
            $stmtL = $db->prepare("SELECT logo FROM entreprises WHERE id=?");
            $stmtL->execute([$id]);
            $ancien = $stmtL->fetchColumn();
            if ($ancien && file_exists(APP_ROOT . '/public/logos/' . $ancien)) {
                unlink(APP_ROOT . '/public/logos/' . $ancien);
            }
            $logoSql = ', logo=NULL';
        } elseif (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            // Supprimer l'ancien
            $stmtL = $db->prepare("SELECT logo FROM entreprises WHERE id=?");
            $stmtL->execute([$id]);
            $ancien = $stmtL->fetchColumn();
            if ($ancien && file_exists(APP_ROOT . '/public/logos/' . $ancien)) {
                unlink(APP_ROOT . '/public/logos/' . $ancien);
            }
            $fname = $this->handleLogoUpload($_FILES['logo']);
            if ($fname !== null) {
                $logoSql = ', logo=?';
                $logoParams = [$fname];
            }
        }

        $stmt = $db->prepare("UPDATE entreprises SET raison_sociale=?, forme_juridique=?, ninea=?, rccm=?, secteur_activite=?, adresse=?, telephone=?, email=?, exercice_courant=?, regime_fiscal=?, couleur=?, statut=?, ca_annuel_estime=?, secteur_activite_detail=?, numero_contribuable=?, numero_registre_commerce=?, regime_tva=?, date_debut_exoneration=?, date_fin_exoneration=?$logoSql WHERE id=?");
        $stmt->execute([
            trim($_POST['raison_sociale'] ?? ''),
            $_POST['forme_juridique'] ?? 'SARL',
            trim($_POST['ninea'] ?? ''),
            trim($_POST['rccm'] ?? ''),
            trim($_POST['secteur_activite'] ?? ''),
            trim($_POST['adresse'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            trim($_POST['email'] ?? ''),
            (int)($_POST['exercice_courant'] ?? date('Y')),
            $_POST['regime_fiscal'] ?? 'CGI',
            $_POST['couleur'] ?? '#1e3a5f',
            $_POST['statut'] ?? 'actif',
            (float)($_POST['ca_annuel_estime'] ?? 0),
            trim($_POST['secteur_activite_detail'] ?? ''),
            trim($_POST['numero_contribuable'] ?? ''),
            trim($_POST['numero_registre_commerce'] ?? ''),
            $_POST['regime_tva'] ?? 'mensuel',
            $_POST['date_debut_exoneration'] ?: null,
            $_POST['date_fin_exoneration'] ?: null,
            ...$logoParams,
            $id,
        ]);
        redirect('/entreprises');
    }

    public function delete(): void {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $db = getDB();
        $db->prepare("UPDATE entreprises SET statut = 'archive' WHERE id = ?")->execute([$id]);
        redirect('/entreprises');
    }
}
