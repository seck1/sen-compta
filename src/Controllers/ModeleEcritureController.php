<?php
require_once APP_ROOT . '/config/app.php';

class ModeleEcritureController {

    private function getEntreprise(int $id): array {
        requireAuth();
        if (!userHasAccess($id)) redirect('/dashboard');
        $e = getEntreprise($id);
        if (empty($e)) redirect('/dashboard');
        return $e;
    }

    public function index(): void {
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $stmt = $db->prepare("SELECT m.*, u.prenom, u.nom as user_nom FROM modeles_ecritures m LEFT JOIN users u ON u.id=m.created_by WHERE m.entreprise_id=? ORDER BY m.nom");
        $stmt->execute([$id]);
        $modeles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Modèles d\'écritures';
        $activeTab = 'modeles';
        ob_start();
        require APP_ROOT . '/views/dossier/modeles-ecritures.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function store(): void {
        requireAuth();
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();

        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $journal_code= strtoupper(trim($_POST['journal_code'] ?? 'OD'));
        $lignes_raw  = $_POST['lignes'] ?? [];

        if (empty($nom) || empty($lignes_raw)) redirect("/dossier/modeles?id=$id&error=1");

        $lignes = [];
        foreach ($lignes_raw as $l) {
            $compte  = trim($l['compte'] ?? '');
            $libelle = trim($l['libelle'] ?? '');
            $debit   = (float)str_replace(',','.',$l['debit'] ?? '0');
            $credit  = (float)str_replace(',','.',$l['credit'] ?? '0');
            if (empty($compte)) continue;
            $lignes[] = ['compte'=>$compte,'libelle'=>$libelle,'debit'=>$debit,'credit'=>$credit];
        }
        if (empty($lignes)) redirect("/dossier/modeles?id=$id&error=1");

        $modele_id = (int)($_POST['modele_id'] ?? 0);
        if ($modele_id) {
            $stmt = $db->prepare("UPDATE modeles_ecritures SET nom=?,description=?,journal_code=?,lignes=? WHERE id=? AND entreprise_id=?");
            $stmt->execute([$nom,$description,$journal_code,json_encode($lignes),$modele_id,$id]);
        } else {
            $stmt = $db->prepare("INSERT INTO modeles_ecritures (entreprise_id,nom,description,journal_code,lignes,created_by) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$id,$nom,$description,$journal_code,json_encode($lignes),auth()['id']]);
        }
        redirect("/dossier/modeles?id=$id&ok=1");
    }

    public function supprimer(): void {
        requireAuth();
        $id        = (int)($_POST['entreprise_id'] ?? 0);
        $modele_id = (int)($_POST['modele_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $db->prepare("DELETE FROM modeles_ecritures WHERE id=? AND entreprise_id=?")->execute([$modele_id,$id]);
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true]);
    }

    // API : retourne les lignes d'un modèle pour pré-remplir le formulaire écriture
    public function json(): void {
        requireAuth();
        $id        = (int)($_GET['id'] ?? 0);
        $modele_id = (int)($_GET['modele_id'] ?? 0);
        $this->getEntreprise($id);
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM modeles_ecritures WHERE id=? AND entreprise_id=?");
        $stmt->execute([$modele_id,$id]);
        $modele = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$modele) { echo json_encode(['ok'=>false]); return; }
        $modele['lignes'] = json_decode($modele['lignes'], true);
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true,'modele'=>$modele]);
    }

    // Appliquer un modèle : crée directement une écriture brouillon
    public function appliquer(): void {
        requireAuth();
        $id        = (int)($_POST['entreprise_id'] ?? 0);
        $modele_id = (int)($_POST['modele_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM modeles_ecritures WHERE id=? AND entreprise_id=?");
        $stmt->execute([$modele_id,$id]);
        $modele = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$modele) { echo json_encode(['ok'=>false,'error'=>'Modèle introuvable']); return; }

        $lignes = json_decode($modele['lignes'], true);
        $date   = $_POST['date'] ?? date('Y-m-d');
        $libelle_ecriture = trim($_POST['libelle'] ?? $modele['nom']);
        $exercice = (int)$entreprise['exercice_courant'];

        // Trouver le journal
        $stmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code=? LIMIT 1");
        $stmt->execute([$id, $modele['journal_code']]);
        $journal_id = $stmt->fetchColumn();
        if (!$journal_id) {
            $stmt = $db->prepare("INSERT INTO journaux (entreprise_id,code,libelle) VALUES (?,?,?)");
            $stmt->execute([$id,$modele['journal_code'],'Opérations diverses']);
            $journal_id = $db->lastInsertId();
        }

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO ecritures (entreprise_id,journal_id,date_ecriture,libelle,exercice,periode,statut) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$id,$journal_id,$date,$libelle_ecriture,$exercice,(int)date('n'),'brouillon']);
            $ecriture_id = $db->lastInsertId();

            foreach ($lignes as $ligne) {
                // Résoudre le compte par numéro
                $stmt = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $stmt->execute([$id,$ligne['compte']]);
                $compte_id = $stmt->fetchColumn();
                if (!$compte_id) continue;

                $stmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id,compte_id,libelle,debit,credit) VALUES (?,?,?,?,?)");
                $stmt->execute([$ecriture_id,$compte_id,$ligne['libelle']??$libelle_ecriture,$ligne['debit'],$ligne['credit']]);
            }
            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['ok'=>true,'ecriture_id'=>$ecriture_id]);
        } catch (Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }
}
