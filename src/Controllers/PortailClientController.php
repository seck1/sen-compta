<?php
require_once APP_ROOT . '/config/app.php';

/**
 * Espace CLIENT du portail. Session separee de celle du cabinet.
 * Le client (lecture seule) consulte etats, factures, depose des pieces, suit son dossier.
 */
class PortailClientController {

    /* ---------- Session client ---------- */
    private function clientAuth(): ?array {
        return $_SESSION['portail_client'] ?? null;
    }

    private function requireClient(): array {
        $c = $this->clientAuth();
        if (!$c) redirect('/portail/login');
        // recharger depuis la base (permissions a jour + verif actif)
        $stmt = getDB()->prepare("SELECT * FROM portail_clients WHERE id = ? AND actif = 1");
        $stmt->execute([$c['id']]);
        $fresh = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fresh) { unset($_SESSION['portail_client']); redirect('/portail/login'); }
        return $fresh;
    }

    private function renderClient(string $view, array $vars = []): void {
        extract($vars);
        ob_start();
        require APP_ROOT . "/views/portail/$view.php";
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/portail.php';
    }

    /* ---------- Login ---------- */
    public function login(): void {
        if ($this->clientAuth()) redirect('/portail');
        $error = $_SESSION['portail_login_error'] ?? null;
        unset($_SESSION['portail_login_error']);
        require APP_ROOT . '/views/portail/login.php';
    }

    public function authentifier(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/portail/login');
        $email = trim($_POST['email'] ?? '');
        $pwd   = $_POST['password'] ?? '';

        $stmt = getDB()->prepare("SELECT * FROM portail_clients WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c || !password_verify($pwd, $c['password'])) {
            $_SESSION['portail_login_error'] = 'Email ou mot de passe incorrect.';
            redirect('/portail/login');
        }
        getDB()->prepare("UPDATE portail_clients SET derniere_connexion = NOW() WHERE id = ?")->execute([$c['id']]);
        $_SESSION['portail_client'] = ['id' => (int)$c['id'], 'nom' => $c['nom'], 'entreprise_id' => (int)$c['entreprise_id']];
        redirect('/portail');
    }

    public function logout(): void {
        unset($_SESSION['portail_client']);
        redirect('/portail/login');
    }

    /* ---------- Dashboard ---------- */
    public function dashboard(): void {
        $c = $this->requireClient();
        $db = getDB();
        $entId = (int)$c['entreprise_id'];

        $ent = $db->prepare("SELECT * FROM entreprises WHERE id = ?");
        $ent->execute([$entId]);
        $entreprise = $ent->fetch(PDO::FETCH_ASSOC);

        // Factures d'honoraires (si autorise)
        $factures = [];
        $totalImpaye = 0;
        if ($c['voir_honoraires']) {
            $f = $db->prepare("SELECT numero_facture, date_facture, date_echeance, libelle, montant_ttc, statut, date_paiement
                               FROM honoraires WHERE entreprise_id = ? ORDER BY date_facture DESC LIMIT 50");
            $f->execute([$entId]);
            $factures = $f->fetchAll(PDO::FETCH_ASSOC);
            foreach ($factures as $fac) if ($fac['statut'] !== 'payee') $totalImpaye += (float)$fac['montant_ttc'];
        }

        // Suivi du dossier : derniere cloture
        $clo = $db->prepare("SELECT exercice, statut, date_cloture FROM clotures WHERE entreprise_id = ? ORDER BY exercice DESC LIMIT 1");
        $clo->execute([$entId]);
        $cloture = $clo->fetch(PDO::FETCH_ASSOC) ?: null;

        // Pieces deposees par CE client
        $dep = $db->prepare("SELECT * FROM portail_depots WHERE client_id = ? ORDER BY created_at DESC LIMIT 20");
        $dep->execute([$c['id']]);
        $depots = $dep->fetchAll(PDO::FETCH_ASSOC);

        $msg = $_GET['message'] ?? null;
        $this->renderClient('dashboard', compact('c','entreprise','factures','totalImpaye','cloture','depots','msg'));
    }

    /* ---------- Consultation d'un etat financier ---------- */
    public function etat(): void {
        $c = $this->requireClient();
        if (!$c['voir_etats']) redirect('/portail');
        $db = getDB();
        $entId = (int)$c['entreprise_id'];

        $ent = $db->prepare("SELECT * FROM entreprises WHERE id = ?");
        $ent->execute([$entId]);
        $entreprise = $ent->fetch(PDO::FETCH_ASSOC);

        $type = ($_GET['type'] ?? 'bilan') === 'resultat' ? 'resultat' : 'bilan';

        // Soldes par compte sur l'exercice courant (ecritures validees uniquement)
        $exercice = (int)date('Y');
        $stmt = $db->prepare("SELECT c.classe, c.numero, c.intitule,
                                     SUM(l.debit) AS debit, SUM(l.credit) AS credit
                              FROM lignes_ecritures l
                              JOIN ecritures ec ON ec.id = l.ecriture_id
                              JOIN comptes c ON c.id = l.compte_id
                              WHERE ec.entreprise_id = ? AND ec.statut IN ('validee','cloturee') AND YEAR(ec.date_ecriture) = ?
                              GROUP BY c.id ORDER BY c.numero");
        $stmt->execute([$entId, $exercice]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderClient('etat', compact('c','entreprise','type','lignes','exercice'));
    }

    /* ---------- Depot de piece ---------- */
    public function deposer(): void {
        $c = $this->requireClient();
        if (!$c['permet_depot']) redirect('/portail');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/portail');
        verifyCsrfToken($_POST['csrf_token'] ?? '');

        if (empty($_FILES['fichier']['name']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            redirect('/portail?message=erreur');
        }
        $allowed = ['pdf','jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) redirect('/portail?message=type');
        if ($_FILES['fichier']['size'] > 8 * 1024 * 1024) redirect('/portail?message=taille');

        $dir = APP_ROOT . '/public/uploads/portail';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $safe = 'dep_' . $c['entreprise_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($_FILES['fichier']['tmp_name'], "$dir/$safe")) {
            redirect('/portail?message=erreur');
        }

        getDB()->prepare("INSERT INTO portail_depots (entreprise_id, client_id, fichier, nom_original, libelle, taille)
                          VALUES (?,?,?,?,?,?)")
            ->execute([
                $c['entreprise_id'], $c['id'], $safe,
                substr($_FILES['fichier']['name'], 0, 255),
                trim($_POST['libelle'] ?? '') ?: null,
                (int)$_FILES['fichier']['size'],
            ]);
        redirect('/portail?message=depose');
    }
}
