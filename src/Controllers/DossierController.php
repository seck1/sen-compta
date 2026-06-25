<?php
require_once APP_ROOT . '/config/app.php';

class DossierController {

    private function genererNumeroPiece(int $entrepriseId, int $journalId, string $date): string {
        $db   = getDB();
        $stmt = $db->prepare("SELECT code FROM journaux WHERE id = ?");
        $stmt->execute([$journalId]);
        $j    = $stmt->fetch();
        $code = $j ? strtoupper($j['code']) : 'EC';
        $an   = date('Y', strtotime($date));
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND journal_id=? AND exercice=?");
        $stmt2->execute([$entrepriseId, $journalId, (int)$an]);
        $seq  = ((int)$stmt2->fetchColumn()) + 1;
        return $code . '-' . $an . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function getEntreprise(int $id): array {
        // Utilise la fonction globale qui applique la session exercice et construit _exercices
        $ent = getEntreprise($id);
        if (empty($ent)) redirect('/entreprises');

        // Vérifier accès si collaborateur
        if (!userHasAccess($id)) redirect('/dashboard');

        return $ent;
    }

    public function switchExercice(): void {
        requireAuth();
        $id    = (int)($_POST['entreprise_id'] ?? $_GET['id'] ?? 0);
        $annee = (int)($_POST['annee'] ?? $_GET['annee'] ?? 0);

        if ($id && $annee >= 2000 && $annee <= 2100) {
            $_SESSION['exercice'][$id] = $annee;
            $db = getDB();
            $db->prepare("UPDATE entreprises SET exercice_courant = ? WHERE id = ?")->execute([$annee, $id]);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        error_log("switchExercice: id=$id annee=$annee referer=$referer");

        if ($referer) {
            header('Location: ' . $referer);
        } else {
            header('Location: ' . APP_URL . "/dossier?id=$id");
        }
        exit;
    }

    public function creerExercice(): void {
        requireAuth();
        $id    = (int)($_POST['entreprise_id'] ?? $_GET['id'] ?? 0);
        $annee = (int)($_POST['annee'] ?? $_GET['annee'] ?? 0);
        $this->getEntreprise($id);

        if ($annee < 2000 || $annee > 2100) redirect("/dossier?id=$id");

        $db = getDB();
        $stmt = $db->prepare("INSERT IGNORE INTO exercices (entreprise_id, annee, date_debut, date_fin, statut) VALUES (?,?,?,?,'ouvert')");
        $stmt->execute([$id, $annee, "$annee-01-01", "$annee-12-31"]);

        // Switcher automatiquement vers le nouvel exercice
        $_SESSION['exercice'][$id] = $annee;
        $db->prepare("UPDATE entreprises SET exercice_courant = ? WHERE id = ?")->execute([$annee, $id]);
        session_write_close();

        $retour = $_SERVER['HTTP_REFERER'] ?? (APP_URL . "/dossier/exercices?id=$id");
        error_log("creerExercice: id=$id annee=$annee session=" . json_encode($_SESSION['exercice'] ?? []) . " retour=$retour");
        header('Location: ' . $retour);
        exit;
    }

    public function modifierExercice(): void {
        requireAuth();
        if (!isAdmin()) { redirect('/dashboard'); return; }
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $annee      = (int)($_POST['annee'] ?? 0);
        $date_debut = $_POST['date_debut'] ?? '';
        $date_fin   = $_POST['date_fin'] ?? '';
        $this->getEntreprise($id);
        if (!$annee || !$date_debut || !$date_fin) {
            redirect("/dossier/exercices?id=$id&error=invalid");
            return;
        }
        $db = getDB();
        $db->prepare("UPDATE exercices SET date_debut=?, date_fin=? WHERE entreprise_id=? AND annee=? AND statut='ouvert'")
           ->execute([$date_debut, $date_fin, $id, $annee]);
        redirect("/dossier/exercices?id=$id&saved=1");
    }

    public function exercices(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();
        $stmt = $db->prepare("SELECT e.*,
            (SELECT COUNT(*) FROM ecritures ec WHERE ec.entreprise_id=e.entreprise_id AND ec.exercice=e.annee) as nb_ecritures,
            (SELECT COUNT(*) FROM ecritures ec WHERE ec.entreprise_id=e.entreprise_id AND ec.exercice=e.annee AND ec.statut='validee') as nb_validees
            FROM exercices e WHERE e.entreprise_id=? ORDER BY e.annee DESC");
        $stmt->execute([$id]);
        $exercices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $saved = isset($_GET['saved']);
        $error = $_GET['error'] ?? '';
        $pageTitle = 'Gestion des exercices';
        $activeTab = 'exercices';
        ob_start();
        require APP_ROOT . '/views/dossier/exercices.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    private function initJournaux(int $entId): void {
        $db = getDB();
        $count = $db->prepare("SELECT COUNT(*) FROM journaux WHERE entreprise_id = ?");
        $count->execute([$entId]);
        if ($count->fetchColumn() > 0) return;

        $journaux = [
            ['ACH', 'Journal des Achats',   'achat'],
            ['VTE', 'Journal des Ventes',   'vente'],
            ['BNQ', 'Journal de Banque',    'banque'],
            ['CAI', 'Journal de Caisse',    'caisse'],
            ['OD',  'Opérations Diverses',  'operations_diverses'],
            ['PAI', 'Journal de Paie',      'paie'],
        ];
        $stmt = $db->prepare("INSERT IGNORE INTO journaux (entreprise_id, code, libelle, type) VALUES (?,?,?,?)");
        foreach ($journaux as $j) {
            $stmt->execute([$entId, $j[0], $j[1], $j[2]]);
        }
    }

    private function initPlanComptable(int $entId): void {
        $db = getDB();

        // Plan comptable OHADA SYSCOHADA révisé — complet
        $comptes = [
            // ============ CLASSE 1 — COMPTES DE RESSOURCES DURABLES ============
            ['10','Capital','passif',1],
            ['101','Capital social','passif',1],
            ['1011','Capital souscrit non appelé','passif',1],
            ['1012','Capital souscrit appelé non versé','passif',1],
            ['1013','Capital souscrit appelé versé','passif',1],
            ['104','Primes liées au capital','passif',1],
            ['1041','Primes d\'émission','passif',1],
            ['1042','Primes de fusion','passif',1],
            ['1043','Primes d\'apport','passif',1],
            ['105','Écarts de réévaluation','passif',1],
            ['106','Réserves','passif',1],
            ['1061','Réserve légale','passif',1],
            ['1062','Réserves statutaires','passif',1],
            ['1063','Réserves réglementées','passif',1],
            ['1068','Autres réserves','passif',1],
            ['11','Report à nouveau','passif',1],
            ['111','Report à nouveau créditeur','passif',1],
            ['119','Report à nouveau débiteur','actif',1],
            ['12','Résultat de l\'exercice','passif',1],
            ['121','Résultat en instance d\'affectation — bénéfice','passif',1],
            ['129','Résultat en instance d\'affectation — perte','actif',1],
            ['13','Subventions d\'investissement','passif',1],
            ['131','Subventions d\'équipement','passif',1],
            ['138','Autres subventions d\'investissement','passif',1],
            ['14','Provisions réglementées','passif',1],
            ['142','Provisions pour investissement','passif',1],
            ['143','Provisions pour hausse des prix','passif',1],
            ['15','Provisions pour risques et charges','passif',1],
            ['151','Provisions pour risques','passif',1],
            ['1511','Provisions pour litiges','passif',1],
            ['1515','Provisions pour amendes et pénalités','passif',1],
            ['1518','Autres provisions pour risques','passif',1],
            ['155','Provisions pour charges','passif',1],
            ['1551','Provisions pour garanties données aux clients','passif',1],
            ['1558','Autres provisions pour charges','passif',1],
            ['16','Emprunts et dettes assimilées','passif',1],
            ['161','Emprunts obligataires','passif',1],
            ['162','Emprunts et dettes auprès des établissements de crédit','passif',1],
            ['163','Emprunts et dettes financières diverses','passif',1],
            ['164','Dettes de location-acquisition','passif',1],
            ['165','Dépôts et cautionnements reçus','passif',1],
            ['166','Intérêts courus','passif',1],
            ['17','Dettes de location-financement','passif',1],
            ['18','Dettes liées à des participations','passif',1],
            ['181','Comptes de liaison des établissements','passif',1],
            ['182','Comptes de liaison des sociétés en participation','passif',1],

            // ============ CLASSE 2 — COMPTES D'ACTIF IMMOBILISÉ ============
            ['20','Charges immobilisées','actif',2],
            ['201','Frais d\'établissement','actif',2],
            ['202','Frais de recherche et de développement','actif',2],
            ['203','Brevets, licences, logiciels','actif',2],
            ['204','Marques','actif',2],
            ['205','Fonds commercial','actif',2],
            ['206','Droit au bail','actif',2],
            ['207','Autres immobilisations incorporelles','actif',2],
            ['208','Autres charges immobilisées','actif',2],
            ['21','Immobilisations corporelles','actif',2],
            ['211','Terrains','actif',2],
            ['2111','Terrains nus','actif',2],
            ['2112','Terrains aménagés','actif',2],
            ['2113','Terrains bâtis','actif',2],
            ['212','Agencements et aménagements de terrains','actif',2],
            ['213','Bâtiments et installations','actif',2],
            ['2131','Bâtiments industriels, agricoles','actif',2],
            ['2132','Bâtiments commerciaux','actif',2],
            ['2133','Bâtiments administratifs','actif',2],
            ['214','Ouvrages d\'infrastructure','actif',2],
            ['215','Installations et agencements','actif',2],
            ['216','Matériel','actif',2],
            ['2161','Matériel et outillage industriel','actif',2],
            ['2162','Matériel et outillage agricole','actif',2],
            ['217','Matériel de transport','actif',2],
            ['218','Mobilier, matériel de bureau et aménagements divers','actif',2],
            ['2181','Mobilier de bureau','actif',2],
            ['2182','Matériel de bureau','actif',2],
            ['2183','Matériel informatique','actif',2],
            ['2184','Matériel et mobilier de logement','actif',2],
            ['22','Avances et acomptes versés sur immobilisations','actif',2],
            ['23','Immobilisations en cours','actif',2],
            ['231','Immobilisations corporelles en cours','actif',2],
            ['232','Immobilisations incorporelles en cours','actif',2],
            ['24','Titres de participation','actif',2],
            ['241','Titres de participation','actif',2],
            ['245','Titres de créances rattachés à des participations','actif',2],
            ['25','Autres immobilisations financières','actif',2],
            ['251','Titres immobilisés de l\'activité de portefeuille','actif',2],
            ['252','Titres immobilisés','actif',2],
            ['255','Prêts et créances non commerciales','actif',2],
            ['256','Dépôts et cautionnements versés','actif',2],
            ['27','Amortissements','actif',2],
            ['271','Amortissements des frais d\'établissement','actif',2],
            ['272','Amortissements des frais de R&D','actif',2],
            ['273','Amortissements des brevets et licences','actif',2],
            ['275','Amortissements du fonds commercial','actif',2],
            ['281','Amortissements des terrains','actif',2],
            ['283','Amortissements des bâtiments','actif',2],
            ['284','Amortissements des ouvrages d\'infrastructure','actif',2],
            ['285','Amortissements des installations','actif',2],
            ['286','Amortissements du matériel','actif',2],
            ['287','Amortissements du matériel de transport','actif',2],
            ['288','Amortissements du mobilier et matériel de bureau','actif',2],
            ['29','Dépréciations des immobilisations','actif',2],
            ['291','Dépréciations des immobilisations incorporelles','actif',2],
            ['292','Dépréciations des immobilisations corporelles','actif',2],
            ['294','Dépréciations des titres de participation','actif',2],

            // ============ CLASSE 3 — COMPTES DE STOCKS ============
            ['31','Marchandises','actif',3],
            ['311','Marchandises A','actif',3],
            ['312','Marchandises B','actif',3],
            ['318','Autres marchandises','actif',3],
            ['32','Matières premières et fournitures liées','actif',3],
            ['321','Matières premières','actif',3],
            ['322','Fournitures liées à la production','actif',3],
            ['33','Autres approvisionnements','actif',3],
            ['331','Matières consommables','actif',3],
            ['332','Fournitures de bureau','actif',3],
            ['333','Fournitures d\'atelier','actif',3],
            ['334','Fournitures de magasin','actif',3],
            ['338','Emballages','actif',3],
            ['34','Produits en cours','actif',3],
            ['341','Produits en cours de fabrication','actif',3],
            ['345','Services en cours','actif',3],
            ['35','Produits finis','actif',3],
            ['351','Produits finis A','actif',3],
            ['358','Autres produits finis','actif',3],
            ['36','Produits intermédiaires et résiduels','actif',3],
            ['361','Produits intermédiaires','actif',3],
            ['365','Produits résiduels','actif',3],
            ['37','Stocks en cours de route','actif',3],
            ['38','Stocks en consignation ou en dépôt','actif',3],
            ['39','Dépréciations des stocks','actif',3],
            ['391','Dépréciations des marchandises','actif',3],
            ['392','Dépréciations des matières premières','actif',3],
            ['395','Dépréciations des produits finis','actif',3],

            // ============ CLASSE 4 — COMPTES DE TIERS ============
            ['40','Fournisseurs et comptes rattachés','passif',4],
            ['401','Fournisseurs','passif',4],
            ['4011','Fournisseurs — achats de biens et services','passif',4],
            ['4012','Fournisseurs — achats d\'immobilisations','passif',4],
            ['402','Fournisseurs — effets à payer','passif',4],
            ['408','Fournisseurs — factures non parvenues','passif',4],
            ['409','Fournisseurs débiteurs','actif',4],
            ['4091','Fournisseurs — avances et acomptes versés','actif',4],
            ['4098','Fournisseurs — RRR à obtenir','actif',4],
            ['41','Clients et comptes rattachés','actif',4],
            ['411','Clients','actif',4],
            ['4111','Clients — ventes de biens et services','actif',4],
            ['4112','Clients — cessions d\'immobilisations','actif',4],
            ['412','Clients — effets à recevoir','actif',4],
            ['413','Clients — effets escomptés non échus','actif',4],
            ['418','Clients — produits non encore facturés','actif',4],
            ['419','Clients créditeurs','passif',4],
            ['4191','Clients — avances et acomptes reçus','passif',4],
            ['4198','Clients — RRR à accorder','passif',4],
            ['42','Personnel','passif',4],
            ['421','Personnel — avances et acomptes','actif',4],
            ['422','Personnel — rémunérations dues','passif',4],
            ['423','Personnel — participations aux bénéfices','passif',4],
            ['424','Personnel — œuvres sociales','passif',4],
            ['425','Personnel — charges à payer','passif',4],
            ['426','Personnel — oppositions','passif',4],
            ['428','Personnel — charges à payer et produits à recevoir','passif',4],
            ['43','Organismes sociaux','passif',4],
            ['431','Sécurité sociale — CSS','passif',4],
            ['432','Retraite — IPRES','passif',4],
            ['433','Autres organismes sociaux','passif',4],
            ['434','IPM — Institution de Prévoyance Maladie','passif',4],
            ['438','Organismes sociaux — charges à payer','passif',4],
            ['44','État et collectivités publiques','passif',4],
            ['441','État — impôts et taxes recouvrables','actif',4],
            ['442','État — impôts et taxes','passif',4],
            ['4421','État — impôts sur les sociétés','passif',4],
            ['4422','État — contributions foncières','passif',4],
            ['4423','État — taxe patronale et d\'apprentissage','passif',4],
            ['4424','État — taxes sur la valeur ajoutée','passif',4],
            ['4425','État — autres impôts et taxes','passif',4],
            ['443','État — TVA facturée','passif',4],
            ['4431','TVA facturée sur ventes','passif',4],
            ['4432','TVA facturée sur prestations','passif',4],
            ['444','État — TVA récupérable','actif',4],
            ['4441','TVA récupérable sur achats','actif',4],
            ['4442','TVA récupérable sur immobilisations','actif',4],
            ['445','État — TVA à décaisser','passif',4],
            ['446','État — TVA à récupérer','actif',4],
            ['447','État — impôts retenus à la source','passif',4],
            ['4471','Retenues à la source sur salaires','passif',4],
            ['4472','Retenues à la source sur honoraires','passif',4],
            ['448','État — charges à payer et produits à recevoir','passif',4],
            ['449','État — créances et dettes diverses','actif',4],
            ['45','Organismes internationaux','passif',4],
            ['46','Associés et groupe','passif',4],
            ['461','Associés — opérations sur le capital','passif',4],
            ['462','Associés — dividendes à payer','passif',4],
            ['463','Associés — versements reçus sur augmentation de capital','passif',4],
            ['464','Associés — créances pour capital souscrit non appelé','actif',4],
            ['467','Associés — autres opérations','passif',4],
            ['47','Débiteurs et créditeurs divers','bilan',4],
            ['471','Débiteurs divers','actif',4],
            ['472','Créditeurs divers','passif',4],
            ['473','Débiteurs et créditeurs — charges et produits constatés d\'avance','bilan',4],
            ['476','Différences de conversion — actif','actif',4],
            ['477','Différences de conversion — passif','passif',4],
            ['48','Créances et dettes hors activités ordinaires','bilan',4],
            ['481','Fournisseurs d\'investissement','passif',4],
            ['485','Créances sur cessions d\'immobilisations','actif',4],
            ['486','Charges constatées d\'avance','actif',4],
            ['487','Produits constatés d\'avance','passif',4],
            ['49','Dépréciations et provisions pour risques à court terme','passif',4],
            ['491','Dépréciations des comptes clients','passif',4],
            ['495','Dépréciations des comptes du groupe','passif',4],
            ['499','Provisions pour risques à court terme','passif',4],

            // ============ CLASSE 5 — COMPTES DE TRÉSORERIE ============
            ['50','Titres de placement','actif',5],
            ['501','Actions','actif',5],
            ['502','Obligations','actif',5],
            ['503','Bons du trésor','actif',5],
            ['508','Autres titres de placement','actif',5],
            ['509','Versements restant à effectuer sur titres','passif',5],
            ['51','Valeurs à encaisser','actif',5],
            ['511','Effets à l\'encaissement','actif',5],
            ['512','Chèques à encaisser','actif',5],
            ['514','Chèques à l\'encaissement','actif',5],
            ['52','Banques','actif',5],
            ['521','Banques — comptes ordinaires','actif',5],
            ['522','Banques — comptes à terme','actif',5],
            ['524','Banques — crédits de campagne','actif',5],
            ['525','Banques — intérêts courus','actif',5],
            ['526','Intérêts sur opérations bancaires','actif',5],
            ['53','Établissements financiers et assimilés','actif',5],
            ['531','Chèques postaux','actif',5],
            ['532','Trésor public et comptes chèques postaux','actif',5],
            ['54','Instruments de monnaie électronique','actif',5],
            ['56','Banques — crédits de trésorerie','passif',5],
            ['561','Banques — découverts','passif',5],
            ['562','Banques — crédits de trésorerie','passif',5],
            ['57','Caisse','actif',5],
            ['571','Caisse siège social','actif',5],
            ['572','Caisse succursale 1','actif',5],
            ['58','Régies d\'avances et accréditifs','actif',5],
            ['581','Virements de fonds internes','actif',5],
            ['585','Virements de fonds','actif',5],
            ['59','Dépréciations des titres de placement','passif',5],

            // ============ CLASSE 6 — COMPTES DE CHARGES ============
            ['60','Achats','charge',6],
            ['601','Achats de marchandises','charge',6],
            ['6011','Achats de marchandises au Sénégal','charge',6],
            ['6012','Achats de marchandises à l\'étranger','charge',6],
            ['602','Achats de matières premières','charge',6],
            ['6021','Matières premières A','charge',6],
            ['6022','Matières premières B','charge',6],
            ['603','Variations des stocks de biens achetés','charge',6],
            ['6031','Variation des stocks de marchandises','charge',6],
            ['6032','Variation des stocks de matières premières','charge',6],
            ['604','Achats stockés de matières et fournitures consommables','charge',6],
            ['6041','Matières consommables','charge',6],
            ['6042','Fournitures de bureau','charge',6],
            ['6043','Fournitures d\'atelier','charge',6],
            ['6044','Fournitures de magasin','charge',6],
            ['6045','Carburant','charge',6],
            ['6046','Emballages perdus','charge',6],
            ['605','Achats de matériels, équipements et travaux','charge',6],
            ['608','Frais accessoires d\'achat','charge',6],
            ['61','Transports','charge',6],
            ['611','Transports sur achats','charge',6],
            ['612','Transports sur ventes','charge',6],
            ['613','Transports pour le compte de tiers','charge',6],
            ['614','Transports du personnel','charge',6],
            ['618','Autres frais de transport','charge',6],
            ['62','Services extérieurs A','charge',6],
            ['621','Sous-traitance générale','charge',6],
            ['622','Locations et charges locatives','charge',6],
            ['6221','Locations et charges locatives des bâtiments','charge',6],
            ['6222','Locations de matériel','charge',6],
            ['623','Redevances de crédit-bail','charge',6],
            ['624','Entretien, réparations et maintenance','charge',6],
            ['6241','Entretien des bâtiments','charge',6],
            ['6242','Entretien du matériel','charge',6],
            ['6243','Entretien du matériel de transport','charge',6],
            ['625','Primes d\'assurances','charge',6],
            ['6251','Assurances multirisques','charge',6],
            ['6252','Assurances transport','charge',6],
            ['6253','Assurances véhicules','charge',6],
            ['626','Études, recherches et documentation','charge',6],
            ['627','Publicité, publications, relations publiques','charge',6],
            ['6271','Annonces et insertions','charge',6],
            ['6272','Catalogues et imprimés','charge',6],
            ['6273','Foires et expositions','charge',6],
            ['628','Frais de télécommunications','charge',6],
            ['63','Services extérieurs B','charge',6],
            ['631','Frais bancaires','charge',6],
            ['6311','Frais sur effets','charge',6],
            ['6312','Commissions bancaires','charge',6],
            ['632','Rémunérations d\'intermédiaires et honoraires','charge',6],
            ['6321','Commissions','charge',6],
            ['6322','Courtages','charge',6],
            ['6323','Honoraires','charge',6],
            ['633','Frais de formation du personnel','charge',6],
            ['634','Redevances pour brevets et licences','charge',6],
            ['635','Cotisations','charge',6],
            ['636','Frais postaux','charge',6],
            ['637','Frais d\'actes et de contentieux','charge',6],
            ['638','Autres charges externes','charge',6],
            ['64','Impôts et taxes','charge',6],
            ['641','Impôts et taxes directes','charge',6],
            ['6411','Impôts fonciers','charge',6],
            ['6412','Patentes et licences','charge',6],
            ['6413','Taxes sur les véhicules','charge',6],
            ['6414','Droits d\'enregistrement et de timbre','charge',6],
            ['642','Taxes sur le chiffre d\'affaires','charge',6],
            ['6421','Taxe sur la valeur ajoutée non récupérable','charge',6],
            ['643','Contributions','charge',6],
            ['6431','Taxe patronale et d\'apprentissage','charge',6],
            ['644','Autres impôts et taxes','charge',6],
            ['65','Autres charges','charge',6],
            ['651','Pertes sur créances irrécouvrables','charge',6],
            ['652','Charges provisionnées sur risques à court terme','charge',6],
            ['653','Charges provisionnées financières','charge',6],
            ['654','Pertes sur créances liées à des participations','charge',6],
            ['658','Charges diverses','charge',6],
            ['66','Charges de personnel','charge',6],
            ['661','Rémunérations directes versées au personnel national','charge',6],
            ['6611','Appointements et salaires','charge',6],
            ['6612','Primes et gratifications','charge',6],
            ['6613','Congés payés','charge',6],
            ['6614','Indemnités de préavis et de licenciement','charge',6],
            ['6615','Heures supplémentaires','charge',6],
            ['662','Rémunérations du personnel extérieur','charge',6],
            ['663','Indemnités forfaitaires versées au personnel','charge',6],
            ['664','Charges sociales','charge',6],
            ['6641','Cotisations à la CSS','charge',6],
            ['6642','Cotisations à l\'IPRES','charge',6],
            ['6643','Cotisations à l\'IPM','charge',6],
            ['6644','Cotisations aux autres organismes sociaux','charge',6],
            ['665','Charges de retraite','charge',6],
            ['666','Charges sociales sur congés payés','charge',6],
            ['667','Rémunérations transférées','charge',6],
            ['668','Autres charges de personnel','charge',6],
            ['67','Frais financiers et charges assimilées','charge',6],
            ['671','Intérêts des emprunts','charge',6],
            ['6711','Intérêts des emprunts obligataires','charge',6],
            ['6712','Intérêts des emprunts bancaires','charge',6],
            ['672','Intérêts des comptes courants et dépôts créditeurs','charge',6],
            ['673','Intérêts bancaires et charges sur effets','charge',6],
            ['674','Escomptes accordés','charge',6],
            ['675','Escomptes des effets de commerce','charge',6],
            ['676','Pertes de change','charge',6],
            ['677','Pertes sur cessions de titres de placement','charge',6],
            ['678','Autres charges financières','charge',6],
            ['68','Dotations aux amortissements et aux provisions','charge',6],
            ['681','Dotations aux amortissements des immobilisations incorporelles','charge',6],
            ['682','Dotations aux amortissements des immobilisations corporelles','charge',6],
            ['6821','DAP — Bâtiments','charge',6],
            ['6822','DAP — Matériel et outillage','charge',6],
            ['6823','DAP — Matériel de transport','charge',6],
            ['6824','DAP — Mobilier et matériel de bureau','charge',6],
            ['6825','DAP — Matériel informatique','charge',6],
            ['683','Dotations aux provisions pour dépréciation des immobilisations','charge',6],
            ['684','Dotations aux provisions pour dépréciation des stocks','charge',6],
            ['685','Dotations aux provisions pour dépréciation des créances','charge',6],
            ['686','Dotations aux provisions pour risques et charges','charge',6],
            ['69','Impôts sur le résultat','charge',6],
            ['691','Participation des travailleurs','charge',6],
            ['692','Impôts sur les bénéfices','charge',6],
            ['6921','Impôt sur les sociétés (IS)','charge',6],
            ['6922','Contribution forfaitaire à la charge des employeurs','charge',6],
            ['694','Impôts différés — variations','charge',6],

            // ============ CLASSE 7 — COMPTES DE PRODUITS ============
            ['70','Ventes','produit',7],
            ['701','Ventes de marchandises','produit',7],
            ['7011','Ventes de marchandises au Sénégal','produit',7],
            ['7012','Ventes de marchandises à l\'étranger','produit',7],
            ['702','Ventes de produits finis','produit',7],
            ['703','Ventes de produits intermédiaires','produit',7],
            ['704','Ventes de produits résiduels','produit',7],
            ['705','Travaux facturés','produit',7],
            ['706','Services vendus','produit',7],
            ['707','Produits des activités annexes','produit',7],
            ['708','Produits des cessions d\'immobilisations','produit',7],
            ['709','Rabais, remises, ristournes accordés','produit',7],
            ['71','Production stockée','produit',7],
            ['711','Variation des stocks de produits finis','produit',7],
            ['713','Variation des stocks de produits en cours','produit',7],
            ['72','Production immobilisée','produit',7],
            ['721','Immobilisations incorporelles produites','produit',7],
            ['722','Immobilisations corporelles produites','produit',7],
            ['73','Variations des stocks de biens produits','produit',7],
            ['74','Subventions d\'exploitation','produit',7],
            ['741','Subventions d\'exploitation','produit',7],
            ['742','Subventions d\'équilibre','produit',7],
            ['743','Subventions de l\'État','produit',7],
            ['744','Subventions des autres collectivités','produit',7],
            ['75','Autres produits','produit',7],
            ['751','Redevances pour brevets, licences et produits assimilés','produit',7],
            ['752','Revenus des immeubles non affectés aux activités professionnelles','produit',7],
            ['753','Jetons de présence et rémunérations d\'administrateurs','produit',7],
            ['754','Ristournes et bonifications obtenues','produit',7],
            ['755','Quotes-parts de résultat sur opérations faites en commun','produit',7],
            ['756','Gains sur créances et dépôts','produit',7],
            ['757','Revenus de participations','produit',7],
            ['758','Produits divers','produit',7],
            ['76','Reprises de provisions et dépréciations','produit',7],
            ['761','Reprises d\'amortissements','produit',7],
            ['762','Reprises de dépréciations sur immobilisations','produit',7],
            ['764','Reprises de dépréciations sur stocks','produit',7],
            ['765','Reprises de dépréciations sur créances','produit',7],
            ['766','Reprises de provisions pour risques et charges','produit',7],
            ['77','Revenus financiers et produits assimilés','produit',7],
            ['771','Intérêts de prêts','produit',7],
            ['772','Revenus de créances','produit',7],
            ['773','Revenus des titres de placement','produit',7],
            ['774','Revenus des comptes courants et dépôts','produit',7],
            ['775','Escomptes obtenus','produit',7],
            ['776','Gains de change','produit',7],
            ['777','Gains sur cessions de titres de placement','produit',7],
            ['778','Autres revenus financiers','produit',7],
            ['78','Transferts de charges','produit',7],
            ['781','Transferts de charges d\'exploitation','produit',7],
            ['782','Transferts de charges financières','produit',7],
            ['791','Produits sur exercices antérieurs','produit',7],
        ];

        $stmt = $db->prepare("INSERT IGNORE INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,?,?)");
        foreach ($comptes as $c) {
            $stmt->execute([$entId, $c[0], $c[1], $c[2], $c[3]]);
        }
    }

    private function soldeComptes(int $entId, int $exercice, array $prefixes, string $sens): float {
        $db = getDB();
        $cond = implode(' OR ', array_map(fn($p) => "c.numero LIKE ?", $prefixes));
        $params = array_map(fn($p) => $p.'%', $prefixes);
        $stmt = $db->prepare("SELECT COALESCE(SUM(l.debit),0) as d, COALESCE(SUM(l.credit),0) as c FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND ($cond)");
        $stmt->execute(array_merge([$entId, $exercice], $params));
        $row = $stmt->fetch();
        $net = (float)$row['d'] - (float)$row['c'];
        return $sens === 'debit' ? max(0,$net) : max(0,-$net);
    }

    public function index(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $this->initJournaux($id);
        $this->initPlanComptable($id);

        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);
        // Persister le choix en session
        if (isset($_GET['exercice'])) {
            $_SESSION['exercice'][$id] = $exercice;
            $entreprise['exercice_courant'] = $exercice;
        }
        $moisCourant = date('Y-m');

        // Stats de base
        $nbEcritures = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id = ? AND exercice = ?");
        $nbEcritures->execute([$id, $exercice]);
        $nbEcritures = $nbEcritures->fetchColumn();

        $totalDebit = $db->prepare("SELECT COALESCE(SUM(l.debit),0) FROM lignes_ecritures l JOIN ecritures e ON e.id = l.ecriture_id WHERE e.entreprise_id = ? AND e.exercice = ?");
        $totalDebit->execute([$id, $exercice]);
        $totalDebit = (float)$totalDebit->fetchColumn();

        $totalCredit = $db->prepare("SELECT COALESCE(SUM(l.credit),0) FROM lignes_ecritures l JOIN ecritures e ON e.id = l.ecriture_id WHERE e.entreprise_id = ? AND e.exercice = ?");
        $totalCredit->execute([$id, $exercice]);
        $totalCredit = (float)$totalCredit->fetchColumn();

        // KPI financiers
        $soldeTresorerie = $this->soldeComptes($id, $exercice, ['50','51','52','53','54','55','56','57','58'], 'credit');

        // CA exercice : comptes 70x–75x (produits d'exploitation SYSCOHADA)
        $stmtCA = $db->prepare("
            SELECT COALESCE(SUM(l.credit) - SUM(l.debit), 0)
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
              AND c.numero REGEXP '^7[0-5]'
        ");
        $stmtCA->execute([$id, $exercice]);
        $caMois = max(0, (float)$stmtCA->fetchColumn());

        // Résultat exercice (produits 7xx - charges 6xx)
        $stmtProd = $db->prepare("SELECT COALESCE(SUM(l.credit),0)-COALESCE(SUM(l.debit),0) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '7%'");
        $stmtProd->execute([$id, $exercice]);
        $produits = (float)$stmtProd->fetchColumn();

        $stmtCh = $db->prepare("SELECT COALESCE(SUM(l.debit),0)-COALESCE(SUM(l.credit),0) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%'");
        $stmtCh->execute([$id, $exercice]);
        $charges = (float)$stmtCh->fetchColumn();
        $resultatExercice = $produits - $charges;

        // Créances clients : solde 41x (toutes écritures, débit - crédit)
        // On inclut lettrées et non-lettrées car le solde net = ce qui reste dû
        $stmtCreances = $db->prepare("
            SELECT COALESCE(SUM(l.debit),0) - COALESCE(SUM(l.credit),0)
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
              AND c.numero LIKE '41%'
        ");
        $stmtCreances->execute([$id, $exercice]);
        $creancesClients = max(0, (float)$stmtCreances->fetchColumn());

        // Dettes fournisseurs : solde 40x (crédit - débit = ce qu'on doit)
        $stmtDettes = $db->prepare("
            SELECT COALESCE(SUM(l.credit),0) - COALESCE(SUM(l.debit),0)
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
              AND c.numero LIKE '40%'
        ");
        $stmtDettes->execute([$id, $exercice]);
        $dettesFournisseurs = max(0, (float)$stmtDettes->fetchColumn());

        // Bulletins du mois
        $stmtBulletins = $db->prepare("SELECT COUNT(*) FROM bulletins_paie WHERE entreprise_id=? AND CONCAT(periode_annee,'-',LPAD(periode_mois,2,'0'))=?");
        $stmtBulletins->execute([$id, $moisCourant]);
        $bulletinsMois = (int)$stmtBulletins->fetchColumn();

        // Dernières 5 écritures
        $stmt = $db->prepare("SELECT e.*, j.libelle as journal_libelle, j.code as journal_code, u.prenom, u.nom,
            (SELECT SUM(l.debit) FROM lignes_ecritures l WHERE l.ecriture_id = e.id) as total_debit
            FROM ecritures e
            JOIN journaux j ON j.id = e.journal_id
            JOIN users u ON u.id = e.user_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            ORDER BY e.date_ecriture DESC, e.id DESC LIMIT 5");
        $stmt->execute([$id, $exercice]);
        $dernieres = $stmt->fetchAll();

        // Prochaines échéances fiscales (3 suivantes)
        $prochainesEcheances = [];
        try {
            $stmtEch = $db->prepare("SELECT * FROM echeances_fiscales WHERE entreprise_id=? AND date_echeance >= CURDATE() ORDER BY date_echeance ASC LIMIT 3");
            $stmtEch->execute([$id]);
            $prochainesEcheances = $stmtEch->fetchAll();
        } catch (\Exception $e) {}

        // Journaux
        $journaux = $db->prepare("SELECT * FROM journaux WHERE entreprise_id = ?");
        $journaux->execute([$id]);
        $journaux = $journaux->fetchAll();

        // Alertes: nb employés sans bulletin ce mois
        $stmtEmp = $db->prepare("SELECT COUNT(*) FROM employes WHERE entreprise_id=? AND statut='actif'");
        $stmtEmp->execute([$id]);
        $nbEmployes = (int)$stmtEmp->fetchColumn();
        $alerteBulletins = ($nbEmployes > 0 && $bulletinsMois < $nbEmployes);

        // Alerte TVA
        $alerteTVA = false;
        if (in_array($entreprise['regime_fiscal'] ?? '', ['RN','RS'])) {
            try {
                $stmtTva = $db->prepare("SELECT COUNT(*) FROM declarations_tva WHERE entreprise_id=? AND DATE_FORMAT(periode_debut,'%Y-%m')=?");
                $stmtTva->execute([$id, $moisCourant]);
                $alerteTVA = ($stmtTva->fetchColumn() == 0);
            } catch (\Exception $e) {}
        }

        // Graphiques : CA vs Charges sur 6 mois de l'exercice sélectionné
        $chartLabels = []; $chartCA = []; $chartCharges = [];
        // Si exercice courant : 6 derniers mois glissants. Sinon : mois 1 à 12 de l'exercice
        $isCurrentYear = ($exercice == (int)date('Y'));
        for ($i = 5; $i >= 0; $i--) {
            if ($isCurrentYear) {
                $d = new DateTime("first day of -$i month");
            } else {
                $mo_num = 12 - $i; // mois 7 à 12 de l'exercice passé
                $d = new DateTime("$exercice-$mo_num-01");
            }
            $chartLabels[] = $d->format('M Y');
            $yr = $d->format('Y'); $mo = $d->format('n');
            $ca = $db->prepare("SELECT COALESCE(SUM(l.credit)-SUM(l.debit),0) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND c.numero REGEXP '^7[0-5]' AND YEAR(e.date_ecriture)=? AND MONTH(e.date_ecriture)=?");
            $ca->execute([$id, $yr, $mo]); $chartCA[] = max(0, (float)$ca->fetchColumn());
            $ch = $db->prepare("SELECT COALESCE(SUM(l.debit)-SUM(l.credit),0) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND c.numero REGEXP '^6' AND YEAR(e.date_ecriture)=? AND MONTH(e.date_ecriture)=?");
            $ch->execute([$id, $yr, $mo]); $chartCharges[] = max(0, (float)$ch->fetchColumn());
        }

        // Top 5 comptes de charges
        $topCharges = $db->prepare("SELECT c.numero, c.intitule, COALESCE(SUM(l.debit)-SUM(l.credit),0) as total FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id WHERE e.entreprise_id=? AND e.exercice=? AND c.numero LIKE '6%' GROUP BY c.id ORDER BY total DESC LIMIT 5");
        $topCharges->execute([$id, $exercice]); $topCharges = $topCharges->fetchAll();

        // Écritures en brouillon à valider
        $nbBrouillons = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND exercice=? AND statut='brouillon'");
        $nbBrouillons->execute([$id, $exercice]); $nbBrouillons = (int)$nbBrouillons->fetchColumn();

        // Liste exercices disponibles
        $exercicesDispos = $db->prepare("SELECT DISTINCT exercice FROM ecritures WHERE entreprise_id=? ORDER BY exercice DESC");
        $exercicesDispos->execute([$id]); $exercicesDispos = $exercicesDispos->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($entreprise['exercice_courant'], $exercicesDispos)) array_unshift($exercicesDispos, $entreprise['exercice_courant']);
        if (!in_array($exercice, $exercicesDispos)) array_unshift($exercicesDispos, $exercice);
        $exercicesDispos = array_unique($exercicesDispos);
        rsort($exercicesDispos);

        ob_start();
        require_once APP_ROOT . '/views/dossier/dashboard.php';
        $content = ob_get_clean();

        $pageTitle  = 'Tableau de bord';
        $activePage = 'dossier';
        $activeTab  = 'dashboard';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function ecritures(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $exercice = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);
        $journal  = $_GET['journal'] ?? '';
        $statut_filtre = $_GET['statut'] ?? '';

        // Liste exercices disponibles
        $stmtEx = $db->prepare("SELECT DISTINCT exercice FROM ecritures WHERE entreprise_id=? ORDER BY exercice DESC");
        $stmtEx->execute([$id]);
        $exercicesDispos = array_column($stmtEx->fetchAll(), 'exercice');
        if (!in_array($entreprise['exercice_courant'], $exercicesDispos)) array_unshift($exercicesDispos, $entreprise['exercice_courant']);

        $sql = "SELECT e.*, j.libelle as journal_libelle, j.code as journal_code, u.prenom, u.nom,
            (SELECT SUM(l.debit)  FROM lignes_ecritures l WHERE l.ecriture_id = e.id) as total_debit,
            (SELECT SUM(l.credit) FROM lignes_ecritures l WHERE l.ecriture_id = e.id) as total_credit,
            (SELECT COUNT(*)      FROM lignes_ecritures l WHERE l.ecriture_id = e.id) as nb_lignes,
            (SELECT t.nom FROM lignes_ecritures l JOIN tiers t ON t.id = l.tiers_id WHERE l.ecriture_id = e.id AND l.tiers_id IS NOT NULL LIMIT 1) as nom_tiers,
            /* Montant tiers = débit 41x (client) ou crédit 40x (fournisseur) sur cette écriture */
            (SELECT COALESCE(
                (SELECT SUM(l.debit)  FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id WHERE l.ecriture_id=e.id AND c.numero LIKE '41%'),
                (SELECT SUM(l.credit) FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id WHERE l.ecriture_id=e.id AND c.numero LIKE '40%'),
                0
            )) as montant_tiers,
            /* Déjà réglé : somme des lignes 41x/40x dans les écritures BNQ/CAI liées à ce n° facture */
            (SELECT COALESCE(SUM(ABS(l2.debit - l2.credit)),0)
             FROM lignes_ecritures l2
             JOIN ecritures e2 ON e2.id=l2.ecriture_id
             JOIN journaux j2 ON j2.id=e2.journal_id
             JOIN comptes c2 ON c2.id=l2.compte_id
             WHERE e2.entreprise_id=e.entreprise_id
               AND e2.numero_facture=e.numero_facture
               AND e2.id != e.id
               AND j2.code IN ('BNQ','CAI')
               AND (c2.numero LIKE '40%' OR c2.numero LIKE '41%')
            ) as deja_regle
            FROM ecritures e
            JOIN journaux j ON j.id = e.journal_id
            JOIN users u ON u.id = e.user_id
            WHERE e.entreprise_id = ? AND e.exercice = ?";
        $params = [$id, $exercice];
        if ($journal) { $sql .= " AND j.code = ?"; $params[] = $journal; }
        if ($statut_filtre) { $sql .= " AND e.statut = ?"; $params[] = $statut_filtre; }
        $sql .= " ORDER BY e.date_ecriture DESC, e.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ecritures = $stmt->fetchAll();

        // Charger les lignes de toutes les écritures pour l'accordéon
        $ecritureIds = array_column($ecritures, 'id');
        $lignesParEcriture = [];
        if (!empty($ecritureIds)) {
            $placeholders = implode(',', array_fill(0, count($ecritureIds), '?'));
            $stmtLignes = $db->prepare("
                SELECT l.ecriture_id, l.debit, l.credit, l.libelle, l.code_lettrage,
                       c.numero as compte_numero, c.intitule as compte_intitule
                FROM lignes_ecritures l
                JOIN comptes c ON c.id = l.compte_id
                WHERE l.ecriture_id IN ($placeholders)
                ORDER BY l.ecriture_id, l.id
            ");
            $stmtLignes->execute($ecritureIds);
            foreach ($stmtLignes->fetchAll() as $l) {
                $lignesParEcriture[$l['ecriture_id']][] = $l;
            }
        }

        $nbBrouillons = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND exercice=? AND statut='brouillon'");
        $nbBrouillons->execute([$id, $exercice]);
        $nbBrouillons = (int)$nbBrouillons->fetchColumn();

        $journaux = $db->prepare("SELECT * FROM journaux WHERE entreprise_id = ?");
        $journaux->execute([$id]);
        $journaux = $journaux->fetchAll();

        ob_start();
        require_once APP_ROOT . '/views/dossier/ecritures.php';
        $content = ob_get_clean();

        $pageTitle = 'Écritures comptables';
        $activeTab = 'ecritures';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function ecritureScan(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $journaux = $db->prepare("SELECT * FROM journaux WHERE entreprise_id = ? ORDER BY code");
        $journaux->execute([$id]);
        $journaux = $journaux->fetchAll();

        $comptes = $db->prepare("SELECT id, numero, intitule FROM comptes WHERE entreprise_id = ? AND actif = 1 ORDER BY numero");
        $comptes->execute([$id]);
        $comptes = $comptes->fetchAll();

        ob_start();
        require_once APP_ROOT . '/views/dossier/ecriture-scan.php';
        $content = ob_get_clean();

        $pageTitle = 'Nouvelle écriture par scan';
        $activeTab = 'ecritures';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function nouvelleEcriture(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $journaux = $db->prepare("SELECT * FROM journaux WHERE entreprise_id = ? ORDER BY code");
        $journaux->execute([$id]);
        $journaux = $journaux->fetchAll();

        $comptes = $db->prepare("SELECT id, numero, intitule FROM comptes WHERE entreprise_id = ? AND actif = 1 ORDER BY numero");
        $comptes->execute([$id]);
        $comptes = $comptes->fetchAll();
        $comptesJson = json_encode(array_map(fn($c) => ['id'=>(int)$c['id'],'numero'=>$c['numero'],'intitule'=>$c['intitule']], $comptes), JSON_UNESCAPED_UNICODE);

        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        ob_start();
        require_once APP_ROOT . '/views/dossier/nouvelle-ecriture.php';
        $content = ob_get_clean();

        $pageTitle = 'Nouvelle écriture';
        $activeTab = 'ecritures';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeEcriture(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');

        $entId    = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($entId);
        $db       = getDB();

        $journalId = (int)($_POST['journal_id'] ?? 0);
        $date      = $_POST['date_ecriture'] ?? date('Y-m-d');
        $libelle   = trim($_POST['libelle'] ?? '');
        $exercice  = $entreprise['exercice_courant'];
        $periode   = (int)date('m', strtotime($date));

        $comptes   = $_POST['compte_id']      ?? [];
        $debits    = $_POST['debit']          ?? [];
        $credits   = $_POST['credit']         ?? [];
        $libelles  = $_POST['ligne_libelle']  ?? [];
        $tiers_ids = $_POST['tiers_id']       ?? [];

        if (!$libelle || !$journalId || empty($comptes)) {
            $_SESSION['form_error'] = 'Veuillez remplir tous les champs obligatoires.';
            redirect('/dossier/nouvelle-ecriture?id=' . $entId);
        }

        // Vérifier équilibre débit/crédit
        $totalD = array_sum(array_map('floatval', $debits));
        $totalC = array_sum(array_map('floatval', $credits));
        if (round($totalD, 2) !== round($totalC, 2)) {
            $_SESSION['form_error'] = "L'écriture n'est pas équilibrée (Débit: " . number_format($totalD,0,',',' ') . " ≠ Crédit: " . number_format($totalC,0,',',' ') . ")";
            redirect('/dossier/nouvelle-ecriture?id=' . $entId);
        }

        // Gestion pièce jointe
        $pieceJointe = null;
        if (!empty($_FILES['piece_jointe']['tmp_name']) && $_FILES['piece_jointe']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['piece_jointe']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['piece_jointe']['size'] <= 5 * 1024 * 1024) {
                $uploadDir = APP_ROOT . '/public/uploads/justificatifs/';
                $filename  = uniqid('pj_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['piece_jointe']['tmp_name'], $uploadDir . $filename)) {
                    $pieceJointe = $filename;
                }
            }
        }

        $db->beginTransaction();
        try {
            $numeroPiece   = trim($_POST['numero_piece']    ?? '');
            $numeroFacture = trim($_POST['numero_facture']  ?? '');
            $moyenPaiement = trim($_POST['moyen_paiement'] ?? '') ?: null;
            if (!$numeroPiece) $numeroPiece = $this->genererNumeroPiece($entId, $journalId, $date);
            $stmt = $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, user_id, date_ecriture, libelle, piece_jointe, exercice, periode, statut, numero_piece, numero_facture, moyen_paiement) VALUES (?,?,?,?,?,?,?,?,'validee',?,?,?)");
            $stmt->execute([$entId, $journalId, auth()['id'], $date, $libelle, $pieceJointe, $exercice, $periode, $numeroPiece, $numeroFacture ?: null, $moyenPaiement]);
            $ecritureId = $db->lastInsertId();

            $stmtL = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit, tiers_id) VALUES (?,?,?,?,?,?)");
            foreach ($comptes as $i => $compteId) {
                if (!$compteId) continue;
                $tiersId = !empty($tiers_ids[$i]) ? (int)$tiers_ids[$i] : null;
                $stmtL->execute([
                    $ecritureId,
                    (int)$compteId,
                    trim($libelles[$i] ?? $libelle),
                    floatval(str_replace(',','.',$debits[$i] ?? 0)),
                    floatval(str_replace(',','.',$credits[$i] ?? 0)),
                    $tiersId,
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['form_error'] = 'Erreur lors de l\'enregistrement.';
            redirect('/dossier/nouvelle-ecriture?id=' . $entId);
        }

        redirect('/dossier/ecritures?id=' . $entId);
    }

    public function editEcriture(): void {
        requireAuth();
        $ecritureId = (int)($_GET['id']  ?? 0);
        $entId      = (int)($_GET['ent'] ?? 0);
        $entreprise = $this->getEntreprise($entId);
        $db         = getDB();

        $stmt = $db->prepare("SELECT e.*, j.code as journal_code FROM ecritures e JOIN journaux j ON j.id = e.journal_id WHERE e.id = ? AND e.entreprise_id = ?");
        $stmt->execute([$ecritureId, $entId]);
        $ecriture = $stmt->fetch();
        if (!$ecriture) redirect('/dossier/ecritures?id=' . $entId);

        $stmtL = $db->prepare("SELECT l.*, c.numero, c.intitule FROM lignes_ecritures l JOIN comptes c ON c.id = l.compte_id WHERE l.ecriture_id = ? ORDER BY l.id");
        $stmtL->execute([$ecritureId]);
        $lignes = $stmtL->fetchAll();

        $journaux = $db->prepare("SELECT * FROM journaux WHERE entreprise_id = ? ORDER BY code");
        $journaux->execute([$entId]);
        $journaux = $journaux->fetchAll();

        $comptes = $db->prepare("SELECT id, numero, intitule FROM comptes WHERE entreprise_id = ? AND actif = 1 ORDER BY numero");
        $comptes->execute([$entId]);
        $comptes = $comptes->fetchAll();
        $comptesJson = json_encode(array_map(fn($c) => ['id'=>(int)$c['id'],'numero'=>$c['numero'],'intitule'=>$c['intitule']], $comptes), JSON_UNESCAPED_UNICODE);

        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        ob_start();
        require_once APP_ROOT . '/views/dossier/modifier-ecriture.php';
        $content = ob_get_clean();

        $pageTitle = 'Modifier écriture';
        $activeTab = 'ecritures';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function updateEcriture(): void {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');

        $ecritureId = (int)($_POST['ecriture_id'] ?? 0);
        $entId      = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($entId);
        $db         = getDB();

        // Vérifier que l'écriture appartient à cette entreprise
        $stmt = $db->prepare("SELECT id, statut, piece_jointe FROM ecritures WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([$ecritureId, $entId]);
        $ecriture = $stmt->fetch();
        if (!$ecriture) redirect('/dossier/ecritures?id=' . $entId);

        $journalId = (int)($_POST['journal_id'] ?? 0);
        $date      = $_POST['date_ecriture'] ?? date('Y-m-d');
        $libelle   = trim($_POST['libelle'] ?? '');
        $exercice  = $entreprise['exercice_courant'];
        $periode   = (int)date('m', strtotime($date));

        $comptes   = $_POST['compte_id']     ?? [];
        $debits    = $_POST['debit']         ?? [];
        $credits   = $_POST['credit']        ?? [];
        $libelles  = $_POST['ligne_libelle'] ?? [];
        $tiers_ids = $_POST['tiers_id']      ?? [];

        if (!$libelle || !$journalId || empty($comptes)) {
            $_SESSION['form_error'] = 'Veuillez remplir tous les champs obligatoires.';
            redirect('/dossier/modifier-ecriture?id=' . $ecritureId . '&ent=' . $entId);
        }

        $totalD = array_sum(array_map('floatval', $debits));
        $totalC = array_sum(array_map('floatval', $credits));
        if (round($totalD, 2) !== round($totalC, 2)) {
            $_SESSION['form_error'] = "L'écriture n'est pas équilibrée (Débit: " . number_format($totalD,0,',',' ') . " ≠ Crédit: " . number_format($totalC,0,',',' ') . ")";
            redirect('/dossier/modifier-ecriture?id=' . $ecritureId . '&ent=' . $entId);
        }

        // Gestion pièce jointe (nouveau fichier optionnel)
        $pieceJointe = $ecriture['piece_jointe']; // conserver l'ancien par défaut
        if (!empty($_FILES['piece_jointe']['tmp_name']) && $_FILES['piece_jointe']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['piece_jointe']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['piece_jointe']['size'] <= 5 * 1024 * 1024) {
                $uploadDir = APP_ROOT . '/public/uploads/justificatifs/';
                $filename  = uniqid('pj_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['piece_jointe']['tmp_name'], $uploadDir . $filename)) {
                    // Supprimer l'ancien fichier si existant
                    if ($pieceJointe && file_exists($uploadDir . $pieceJointe)) {
                        unlink($uploadDir . $pieceJointe);
                    }
                    $pieceJointe = $filename;
                }
            }
        }

        $db->beginTransaction();
        try {
            $numeroPiece   = trim($_POST['numero_piece']    ?? '');
            $numeroFacture = trim($_POST['numero_facture']  ?? '');
            $moyenPaiement = trim($_POST['moyen_paiement'] ?? '') ?: null;
            if (!$numeroPiece) $numeroPiece = $this->genererNumeroPiece($entId, $journalId, $date);
            $db->prepare("UPDATE ecritures SET journal_id=?, date_ecriture=?, libelle=?, piece_jointe=?, exercice=?, periode=?, numero_piece=?, numero_facture=?, moyen_paiement=? WHERE id=?")
               ->execute([$journalId, $date, $libelle, $pieceJointe, $exercice, $periode, $numeroPiece, $numeroFacture ?: null, $moyenPaiement, $ecritureId]);

            // Supprimer les anciennes lignes et recréer
            $db->prepare("DELETE FROM lignes_ecritures WHERE ecriture_id = ?")->execute([$ecritureId]);

            $stmtL = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit, tiers_id) VALUES (?,?,?,?,?,?)");
            foreach ($comptes as $i => $compteId) {
                if (!$compteId) continue;
                $tiersId = !empty($tiers_ids[$i]) ? (int)$tiers_ids[$i] : null;
                $stmtL->execute([
                    $ecritureId,
                    (int)$compteId,
                    trim($libelles[$i] ?? $libelle),
                    floatval(str_replace(',','.',$debits[$i] ?? 0)),
                    floatval(str_replace(',','.',$credits[$i] ?? 0)),
                    $tiersId,
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['form_error'] = 'Erreur lors de la mise à jour.';
            redirect('/dossier/modifier-ecriture?id=' . $ecritureId . '&ent=' . $entId);
        }

        redirect('/dossier/ecritures?id=' . $entId);
    }

    public function validerEcriture(): void {
        requireAuth();
        $ecritureId = (int)($_POST['ecriture_id'] ?? 0);
        $action     = $_POST['action'] ?? 'valider'; // valider | invalider | valider_tout | rejeter | en_brouillon
        $entId      = (int)($_POST['entreprise_id'] ?? 0);
        // Securite : l'utilisateur doit avoir acces a cette entreprise (anti-IDOR cross-cabinet)
        if (!userHasAccess($entId)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Accès refusé']);
            return;
        }
        $db = getDB();

        // Vérifications de droits
        if ($action === 'invalider' && !canInvaliderEcriture()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Seul un administrateur peut repasser une écriture en brouillon.']);
            return;
        }
        if (in_array($action, ['valider','valider_tout']) && !canValiderEcriture()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Vous n\'avez pas le droit de valider des écritures.']);
            return;
        }
        if ($action === 'rejeter' && !canValiderEcriture()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Seul un superviseur ou administrateur peut rejeter une écriture.']);
            return;
        }
        if ($action === 'en_brouillon' && !isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Seul un administrateur peut remettre une écriture en brouillon.']);
            return;
        }

        if ($action === 'valider_tout') {
            $entreprise = $db->prepare("SELECT exercice_courant FROM entreprises WHERE id=?");
            $entreprise->execute([$entId]);
            $entreprise = $entreprise->fetch();
            $exercice = (int)($_POST['exercice'] ?? $entreprise['exercice_courant']);
            $stmt = $db->prepare("UPDATE ecritures SET statut='validee' WHERE entreprise_id=? AND statut='brouillon' AND exercice=?");
            $stmt->execute([$entId, $exercice]);
        } elseif ($action === 'invalider') {
            $stmt = $db->prepare("UPDATE ecritures SET statut='brouillon' WHERE id=? AND entreprise_id=? AND statut='validee'");
            $stmt->execute([$ecritureId, $entId]);
        } elseif ($action === 'rejeter') {
            $motif = trim($_POST['motif'] ?? 'Rejeté');
            $stmt = $db->prepare("UPDATE ecritures SET statut='rejetee', motif_rejet=? WHERE id=? AND entreprise_id=? AND statut='brouillon'");
            $stmt->execute([$motif, $ecritureId, $entId]);
            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'ECRITURE_REJETEE', $entId, 'ecritures', $ecritureId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'statut' => 'rejetee']);
            return;
        } elseif ($action === 'en_brouillon') {
            $stmt = $db->prepare("UPDATE ecritures SET statut='brouillon', motif_rejet=NULL WHERE id=? AND entreprise_id=? AND statut='rejetee'");
            $stmt->execute([$ecritureId, $entId]);
            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'ECRITURE_EN_BROUILLON', $entId, 'ecritures', $ecritureId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'statut' => 'brouillon']);
            return;
        } else {
            $stmt = $db->prepare("UPDATE ecritures SET statut='validee' WHERE id=? AND entreprise_id=? AND statut='brouillon'");
            $stmt->execute([$ecritureId, $entId]);
        }

        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log(auth()['id'], 'ECRITURE_' . strtoupper($action), $entId, 'ecritures', $ecritureId ?: null);

        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function supprimerEcriture(): void {
        requireAuth();
        $ecritureId = (int)($_POST['ecriture_id'] ?? 0);
        $entId      = (int)($_POST['entreprise_id'] ?? 0);
        // Securite : acces a l'entreprise obligatoire (anti-IDOR cross-cabinet)
        if (!userHasAccess($entId)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Accès refusé']);
            return;
        }
        $db = getDB();

        $stmt = $db->prepare("SELECT e.statut, j.code as journal_code FROM ecritures e LEFT JOIN journaux j ON j.id=e.journal_id WHERE e.id=? AND e.entreprise_id=?");
        $stmt->execute([$ecritureId, $entId]);
        $ec = $stmt->fetch();
        if (!$ec) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Écriture introuvable']);
            return;
        }

        $isReglement = in_array($ec['journal_code'], ['BNQ','CAI']);

        // Brouillons supprimables toujours ; validés seulement si règlement
        if ($ec['statut'] !== 'brouillon' && !$isReglement) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Seuls les brouillons ou les règlements peuvent être supprimés']);
            return;
        }

        $db->beginTransaction();
        try {
            // Récupérer les codes lettrage des lignes de cette écriture
            $stmtCodes = $db->prepare("SELECT DISTINCT code_lettrage FROM lignes_ecritures WHERE ecriture_id=? AND code_lettrage IS NOT NULL AND code_lettrage != ''");
            $stmtCodes->execute([$ecritureId]);
            $codes = $stmtCodes->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($codes)) {
                // Dé-lettrer toutes les lignes ayant ces codes (dans toute l'entreprise)
                $placeholders = implode(',', array_fill(0, count($codes), '?'));
                $stmtDelL = $db->prepare("UPDATE lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id SET l.code_lettrage=NULL WHERE l.code_lettrage IN ($placeholders) AND e.entreprise_id=?");
                $stmtDelL->execute(array_merge($codes, [$entId]));

                // Supprimer les entrées lettrages
                $stmtDelLt = $db->prepare("DELETE FROM lettrages WHERE code_lettrage IN ($placeholders) AND entreprise_id=?");
                $stmtDelLt->execute(array_merge($codes, [$entId]));
            }

            $db->prepare("DELETE FROM lignes_ecritures WHERE ecriture_id=?")->execute([$ecritureId]);
            $db->prepare("DELETE FROM ecritures WHERE id=?")->execute([$ecritureId]);

            $db->commit();
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'delettre' => $codes]);
        } catch (Exception $e) {
            $db->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function importCSV(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
            $journal_code = strtoupper(trim($_POST['journal_code'] ?? 'BNQ'));
            $exercice     = (int)($_POST['exercice'] ?? $entreprise['exercice_courant']);
            $separateur   = $_POST['separateur'] ?? ';';
            $col_date     = (int)($_POST['col_date'] ?? 0);
            $col_libelle  = (int)($_POST['col_libelle'] ?? 1);
            $col_debit    = (int)($_POST['col_debit'] ?? 2);
            $col_credit   = (int)($_POST['col_credit'] ?? 3);
            $col_montant  = (int)($_POST['col_montant'] ?? -1);
            $sens_montant = $_POST['sens_montant'] ?? 'debit_positif';
            $skip_header  = !empty($_POST['skip_header']);
            $compte_contrepartie = trim($_POST['compte_contrepartie'] ?? '512100');
            $compte_tiers        = trim($_POST['compte_tiers'] ?? '401000');

            $file = $_FILES['fichier']['tmp_name'];
            if (!$file || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash_error'] = 'Erreur lors du téléchargement du fichier.';
                redirect('/dossier/import-csv?id=' . $id);
            }

            // Détection encodage et conversion UTF-8
            $content = file_get_contents($file);
            $enc = mb_detect_encoding($content, ['UTF-8','ISO-8859-1','Windows-1252'], true);
            if ($enc && $enc !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $enc);
            }
            // Supprimer BOM
            $content = ltrim($content, "\xEF\xBB\xBF");

            $lines = preg_split('/\r\n|\r|\n/', trim($content));
            if ($skip_header && count($lines) > 0) array_shift($lines);

            // Vérifier/créer journal
            $stmtJ = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code=?");
            $stmtJ->execute([$id, $journal_code]);
            if (!$stmtJ->fetch()) {
                $db->prepare("INSERT INTO journaux (entreprise_id, code, libelle, type) VALUES (?,?,?,?)")
                   ->execute([$id, $journal_code, 'Import CSV', 'banque']);
            }

            $importees = 0;
            $erreurs   = [];

            foreach ($lines as $i => $line) {
                $line = trim($line);
                if ($line === '') continue;

                $cols = str_getcsv($line, $separateur);

                $date_str = trim($cols[$col_date] ?? '');
                $libelle  = trim($cols[$col_libelle] ?? 'Import');
                $debit    = 0.0;
                $credit   = 0.0;

                if ($col_montant >= 0) {
                    // Colonne unique montant
                    $montant = (float)str_replace([' ',"\u{00A0}",','], ['','','.'], $cols[$col_montant] ?? '0');
                    if ($sens_montant === 'debit_positif') {
                        if ($montant >= 0) $debit = $montant; else $credit = abs($montant);
                    } else {
                        if ($montant >= 0) $credit = $montant; else $debit = abs($montant);
                    }
                } else {
                    $debit  = (float)str_replace([' ',"\u{00A0}",','], ['','','.'], $cols[$col_debit] ?? '0');
                    $credit = (float)str_replace([' ',"\u{00A0}",','], ['','','.'], $cols[$col_credit] ?? '0');
                }

                if ($debit == 0 && $credit == 0) continue;

                // Parse date (DD/MM/YYYY or YYYY-MM-DD or DD-MM-YYYY)
                $date = null;
                if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $date_str, $m)) {
                    $date = $m[3] . '-' . $m[2] . '-' . $m[1];
                } elseif (preg_match('#^(\d{2})-(\d{2})-(\d{4})$#', $date_str, $m)) {
                    $date = $m[3] . '-' . $m[2] . '-' . $m[1];
                } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $date_str)) {
                    $date = $date_str;
                } else {
                    $erreurs[] = "Ligne " . ($i+1) . " : date invalide ($date_str)";
                    continue;
                }

                // Créer écriture brouillon
                $db->beginTransaction();
                try {
                    $db->prepare("INSERT INTO ecritures (entreprise_id, journal_code, date_ecriture, libelle, exercice, statut, source, created_by) VALUES (?,?,?,?,?,'brouillon','import',?)")
                       ->execute([$id, $journal_code, $date, $libelle, $exercice, auth()['id']]);
                    $ecritureId = (int)$db->lastInsertId();

                    // Ligne 1 : compte de trésorerie (512/571)
                    $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_num, libelle, debit, credit) VALUES (?,?,?,?,?)")
                       ->execute([$ecritureId, $compte_contrepartie, $libelle, $debit, $credit]);

                    // Ligne 2 : contrepartie tiers
                    $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_num, libelle, debit, credit) VALUES (?,?,?,?,?)")
                       ->execute([$ecritureId, $compte_tiers, $libelle, $credit, $debit]);

                    $db->commit();
                    $importees++;
                } catch (\Exception $e) {
                    $db->rollBack();
                    $erreurs[] = "Ligne " . ($i+1) . " : " . $e->getMessage();
                }
            }

            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'import_csv', "Import CSV : $importees écritures créées pour " . $entreprise['nom']);

            $_SESSION['flash_success'] = "$importees écriture(s) importée(s) avec succès en brouillon.";
            if ($erreurs) $_SESSION['flash_warning'] = count($erreurs) . " ligne(s) ignorée(s).";
            redirect('/dossier/ecritures?id=' . $id);
        }

        // GET — afficher formulaire
        $journaux = $db->prepare("SELECT code, libelle FROM journaux WHERE entreprise_id=? ORDER BY code");
        $journaux->execute([$id]);
        $journaux_liste = $journaux->fetchAll();

        $exercicesDispos = [];
        $stmt = $db->prepare("SELECT DISTINCT exercice FROM ecritures WHERE entreprise_id=? ORDER BY exercice DESC");
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $r) $exercicesDispos[] = $r['exercice'];
        if (!in_array($entreprise['exercice_courant'], $exercicesDispos)) array_unshift($exercicesDispos, $entreprise['exercice_courant']);

        $content = '';
        ob_start();
        require APP_ROOT . '/views/dossier/import-csv.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/main.php';
    }

    public function grandLivre(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $compte_filtre = trim($_GET['compte'] ?? '');
        $classe_filtre = trim($_GET['classe'] ?? '');
        $journal_filtre = trim($_GET['journal'] ?? '');
        $date_debut    = $_GET['date_debut'] ?? '';
        $date_fin      = $_GET['date_fin'] ?? '';
        $exercice      = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);

        // Pagination
        $par_page = 10; // comptes par page
        $page = max(1, (int)($_GET['page'] ?? 1));

        $sql = "SELECT c.numero, c.intitule, c.classe,
            l.libelle as ligne_libelle, l.debit, l.credit, l.tiers, l.code_lettrage,
            e.date_ecriture, e.numero_piece, e.libelle as ecriture_libelle, j.code as journal_code
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            JOIN journaux j ON j.id = e.journal_id
            WHERE e.entreprise_id = ? AND e.exercice = ?";
        $params = [$id, $exercice];
        if ($compte_filtre) { $sql .= " AND c.numero LIKE ?"; $params[] = $compte_filtre . '%'; }
        if ($classe_filtre) { $sql .= " AND c.classe = ?"; $params[] = (int)$classe_filtre; }
        if ($journal_filtre) { $sql .= " AND j.code = ?"; $params[] = $journal_filtre; }
        if ($date_debut)   { $sql .= " AND e.date_ecriture >= ?"; $params[] = $date_debut; }
        if ($date_fin)     { $sql .= " AND e.date_ecriture <= ?"; $params[] = $date_fin; }
        $sql .= " ORDER BY c.numero, e.date_ecriture, e.id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lignes = $stmt->fetchAll();

        // Grouper par compte
        $comptes_gl_all = [];
        foreach ($lignes as $l) {
            $num = $l['numero'];
            if (!isset($comptes_gl_all[$num])) {
                $comptes_gl_all[$num] = ['numero' => $num, 'intitule' => $l['intitule'], 'classe' => $l['classe'], 'lignes' => [], 'total_debit' => 0, 'total_credit' => 0];
            }
            $comptes_gl_all[$num]['lignes'][]      = $l;
            $comptes_gl_all[$num]['total_debit']  += $l['debit'];
            $comptes_gl_all[$num]['total_credit'] += $l['credit'];
        }

        $total_comptes = count($comptes_gl_all);
        $total_pages   = max(1, (int)ceil($total_comptes / $par_page));
        $page          = min($page, $total_pages);
        $comptes_gl    = array_slice($comptes_gl_all, ($page - 1) * $par_page, $par_page, true);

        $journaux_liste = $db->prepare("SELECT code, libelle FROM journaux WHERE entreprise_id=? ORDER BY code");
        $journaux_liste->execute([$id]); $journaux_liste = $journaux_liste->fetchAll();

        // Exercices dispos
        $exercicesDispos = $db->prepare("SELECT DISTINCT exercice FROM ecritures WHERE entreprise_id=? ORDER BY exercice DESC");
        $exercicesDispos->execute([$id]); $exercicesDispos = $exercicesDispos->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($entreprise['exercice_courant'], $exercicesDispos)) array_unshift($exercicesDispos, $entreprise['exercice_courant']);
        rsort($exercicesDispos);

        ob_start();
        require_once APP_ROOT . '/views/dossier/grand-livre.php';
        $content = ob_get_clean();

        $pageTitle = 'Grand livre';
        $activeTab = 'grand-livre';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function balance(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $exercice       = (int)($_GET['exercice'] ?? $entreprise['exercice_courant']);
        $classe_filtre  = $_GET['classe'] ?? '';
        $compte_filtre  = trim($_GET['compte'] ?? '');
        $journal_filtre = $_GET['journal'] ?? '';

        // Exercices disponibles
        $stmtEx = $db->prepare("SELECT DISTINCT exercice FROM ecritures WHERE entreprise_id=? ORDER BY exercice DESC");
        $stmtEx->execute([$id]);
        $exercicesDispos = array_column($stmtEx->fetchAll(), 'exercice');
        if (!in_array($entreprise['exercice_courant'], $exercicesDispos)) {
            array_unshift($exercicesDispos, $entreprise['exercice_courant']);
        }

        // Journaux pour le filtre
        $stmtJ = $db->prepare("SELECT code, libelle FROM journaux WHERE entreprise_id=? ORDER BY code");
        $stmtJ->execute([$id]);
        $journaux_liste = $stmtJ->fetchAll();

        // Mouvements exercice N avec filtre journal optionnel
        $whereN  = "e.entreprise_id=? AND e.exercice=? AND e.statut IN ('brouillon','validee')";
        $paramsN = [$id, $exercice];
        if ($journal_filtre) {
            $whereN   .= " AND j.code=?";
            $paramsN[] = $journal_filtre;
        }

        $sql = "SELECT
            c.id, c.numero, c.intitule, c.classe, c.type_compte,
            COALESCE(an.debit_an,  0) AS an_debit,
            COALESCE(an.credit_an, 0) AS an_credit,
            COALESCE(mv.mvt_debit,  0) AS mouvement_debit,
            COALESCE(mv.mvt_credit, 0) AS mouvement_credit
        FROM comptes c
        LEFT JOIN (
            SELECT l.compte_id,
                COALESCE(SUM(l.debit),  0) AS debit_an,
                COALESCE(SUM(l.credit), 0) AS credit_an
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id=? AND e.exercice=?
            GROUP BY l.compte_id
        ) an ON an.compte_id = c.id
        LEFT JOIN (
            SELECT l.compte_id,
                COALESCE(SUM(l.debit),  0) AS mvt_debit,
                COALESCE(SUM(l.credit), 0) AS mvt_credit
            FROM lignes_ecritures l
            JOIN ecritures e ON e.id = l.ecriture_id
            LEFT JOIN journaux j ON j.id = e.journal_id
            WHERE $whereN
            GROUP BY l.compte_id
        ) mv ON mv.compte_id = c.id
        WHERE c.entreprise_id=?
            AND c.actif=1
            AND (an.debit_an IS NOT NULL OR mv.mvt_debit IS NOT NULL)
        ";

        $params = array_merge([$id, $exercice - 1], $paramsN, [$id]);

        if ($classe_filtre) { $sql .= " AND c.classe=?";        $params[] = $classe_filtre; }
        if ($compte_filtre) { $sql .= " AND c.numero LIKE ?";   $params[] = $compte_filtre . '%'; }
        $sql .= " ORDER BY c.numero";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $balance = $stmt->fetchAll();

        // Garder seulement les comptes ayant au moins un montant
        $balance = array_values(array_filter(
            $balance,
            fn($b) => ($b['an_debit'] + $b['an_credit'] + $b['mouvement_debit'] + $b['mouvement_credit']) > 0
        ));

        ob_start();
        require APP_ROOT . '/views/dossier/balance.php';
        $content = ob_get_clean();
        $pageTitle = 'Balance générale';
        $activeTab = 'balance';
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function journaux(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();
        $exercice   = $entreprise['exercice_courant'];

        $stmt = $db->prepare("SELECT j.*,
            COUNT(e.id) as nb_ecritures,
            COALESCE(SUM(l.debit),0) as total_debit
            FROM journaux j
            LEFT JOIN ecritures e ON e.journal_id = j.id AND e.exercice = ?
            LEFT JOIN lignes_ecritures l ON l.ecriture_id = e.id
            WHERE j.entreprise_id = ?
            GROUP BY j.id ORDER BY j.code");
        $stmt->execute([$exercice, $id]);
        $journaux = $stmt->fetchAll();

        ob_start();
        require_once APP_ROOT . '/views/dossier/journaux.php';
        $content = ob_get_clean();

        $pageTitle = 'Journaux';
        $activeTab = 'journaux';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function planComptable(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $this->initPlanComptable($id);

        $db     = getDB();
        $filtre = $_GET['classe'] ?? '';

        $sql = "SELECT c.*,
            COALESCE((SELECT SUM(l.debit)  FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id WHERE l.compte_id=c.id AND e.exercice=?),0) as mvt_debit,
            COALESCE((SELECT SUM(l.credit) FROM lignes_ecritures l JOIN ecritures e ON e.id=l.ecriture_id WHERE l.compte_id=c.id AND e.exercice=?),0) as mvt_credit
            FROM comptes c WHERE c.entreprise_id = ?";
        $params = [$entreprise['exercice_courant'], $entreprise['exercice_courant'], $id];
        if ($filtre) { $sql .= " AND c.classe = ?"; $params[] = (int)$filtre; }
        $sql .= " ORDER BY c.numero";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $comptes = $stmt->fetchAll();

        $error = $_SESSION['form_error'] ?? null;
        unset($_SESSION['form_error']);

        ob_start();
        require_once APP_ROOT . '/views/dossier/plan-comptable.php';
        $content = ob_get_clean();

        $pageTitle = 'Plan comptable';
        $activeTab = 'plan-comptable';
        require_once APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storePlanComptable(): void {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard');

        $entId   = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntreprise($entId);
        $db      = getDB();

        $numero  = strtoupper(trim($_POST['numero'] ?? ''));
        $intitule = trim($_POST['intitule'] ?? '');
        $type    = $_POST['type_compte'] ?? 'charge';
        $classe  = (int)substr($numero, 0, 1);

        if (!$numero || !$intitule || !$classe) {
            $_SESSION['form_error'] = 'Numéro et intitulé obligatoires.';
            redirect('/dossier/plan-comptable?id=' . $entId);
        }

        try {
            $stmt = $db->prepare("INSERT INTO comptes (entreprise_id, numero, intitule, type_compte, classe) VALUES (?,?,?,?,?)");
            $stmt->execute([$entId, $numero, $intitule, $type, $classe]);
        } catch (Exception $e) {
            $_SESSION['form_error'] = 'Ce numéro de compte existe déjà.';
        }

        redirect('/dossier/plan-comptable?id=' . $entId);
    }

    public function conformiteDGID(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        if (!userHasAccess($id)) redirect('/dashboard');

        $db = getDB();

        // Sections et leurs champs
        $sections = [
            'Identification' => [
                'fields' => ['raison_sociale','ninea','rccm','forme_juridique','sigle','numero_contribuable'],
                'icon' => '🏢', 'label' => 'Identification légale',
            ],
            'Contacts' => [
                'fields' => ['telephone','email','adresse','ville','pays','boite_postale'],
                'icon' => '📧', 'label' => 'Contacts & Adresse',
            ],
            'Comptable' => [
                'fields' => ['regime_fiscal','regime_tva','num_caisse_sociale','greffe','debut_exercice_social','fin_exercice_social'],
                'icon' => '📊', 'label' => 'Paramètres Comptables',
            ],
            'Activite' => [
                'fields' => ['secteur_activite','code_activite_naf','description_activite'],
                'icon' => '🏭', 'label' => 'Activité (R2)',
            ],
            'Dirigeant' => [
                'fields' => ['dirigeant_nom','dirigeant_prenom','dirigeant_qualite'],
                'icon' => '👔', 'label' => 'Dirigeant',
            ],
            'Professionnel' => [
                'fields' => ['expert_comptable_nom','expert_comptable_cabinet'],
                'icon' => '🏛️', 'label' => 'Expert-Comptable',
            ],
            'Signature' => [
                'fields' => ['signataire_nom','signataire_qualite','banque_domiciliation','personne_contact'],
                'icon' => '✍️', 'label' => 'Signature & Banque',
            ],
        ];

        // Calculer scores
        $totalFields = 0;
        $totalFilled = 0;
        foreach ($sections as &$sec) {
            $filled = 0;
            foreach ($sec['fields'] as $f) {
                if (!empty($entreprise[$f])) $filled++;
            }
            $sec['filled'] = $filled;
            $sec['total']  = count($sec['fields']);
            $sec['pct']    = $sec['total'] > 0 ? round($filled / $sec['total'] * 100) : 0;
            $totalFields  += $sec['total'];
            $totalFilled  += $filled;
        }
        unset($sec);

        // Activités R2
        $stmtAct = $db->prepare("SELECT COUNT(*) FROM entreprise_activites_r2 WHERE entreprise_id=?");
        $stmtAct->execute([$id]);
        $nbActivites = (int)$stmtAct->fetchColumn();

        // Écritures de l'exercice
        $stmtEcr = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND YEAR(date_ecriture)=? AND statut='validee'");
        $stmtEcr->execute([$id, $entreprise['exercice_courant']]);
        $nbEcritures = (int)$stmtEcr->fetchColumn();

        $scorePct = $totalFields > 0 ? round($totalFilled / $totalFields * 100) : 0;
        $grade = $scorePct >= 90 ? 'A' : ($scorePct >= 70 ? 'B' : ($scorePct >= 50 ? 'C' : 'D'));
        $exportPret = $scorePct >= 70 && $nbEcritures > 0;

        $pageTitle = 'Conformité DGID';
        $activeTab = 'profil';
        ob_start();
        require APP_ROOT . '/views/dossier/conformite-dgid.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function profil(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        if (!userHasAccess($id)) redirect('/dashboard');

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM entreprise_activites_r2 WHERE entreprise_id=? ORDER BY ordre ASC");
        $stmt->execute([$id]);
        $activitesR2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $saved = isset($_GET['saved']);
        $pageTitle = 'Profil & Paramètres DGID';
        $activeTab = 'profil';
        ob_start();
        require APP_ROOT . '/views/dossier/profil.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeActivitesR2(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        if (!userHasAccess($id)) redirect('/dashboard');

        $db = getDB();
        $db->prepare("DELETE FROM entreprise_activites_r2 WHERE entreprise_id=?")->execute([$id]);

        $designations = $_POST['designation'] ?? [];
        $codes        = $_POST['code_nomenclature'] ?? [];
        $vas          = $_POST['valeur_ajoutee'] ?? [];

        $stmt = $db->prepare("INSERT INTO entreprise_activites_r2 (entreprise_id, ordre, designation, code_nomenclature, valeur_ajoutee) VALUES (?,?,?,?,?)");
        foreach ($designations as $i => $desig) {
            if (trim($desig) === '') continue;
            $stmt->execute([$id, $i + 1, trim($desig), trim($codes[$i] ?? ''), (float)str_replace([' ',','], ['','.'], $vas[$i] ?? 0)]);
        }

        redirect('/dossier/profil?id=' . $id . '&saved=1');
    }

    public function storeProfil(): void {
        requireAuth();
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        if (!userHasAccess($id)) redirect('/dashboard');

        $db = getDB();

        // Gestion upload logo
        $logoPath = $entreprise['logo'] ?? null;
        if (!empty($_FILES['logo']['tmp_name'])) {
            $file = $_FILES['logo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','svg','webp']) && $file['size'] < 2097152) {
                $filename = 'logo_' . $id . '_' . time() . '.' . $ext;
                $dest = APP_ROOT . '/public/logos/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Supprimer ancien logo
                    if ($logoPath && file_exists(APP_ROOT . '/public/logos/' . basename($logoPath))) {
                        @unlink(APP_ROOT . '/public/logos/' . basename($logoPath));
                    }
                    $logoPath = $filename;
                }
            }
        }

        $stmt = $db->prepare("UPDATE entreprises SET
            logo=?,
            raison_sociale=?, forme_juridique=?, ninea=?, rccm=?,
            sigle=?, boite_postale=?, code_importateur=?,
            telephone=?, email=?, email_secondaire=?, site_web=?,
            adresse=?, ville=?, pays=?,
            regime_fiscal=?, regime_tva=?, ca_annuel_estime=?,
            secteur_activite=?, secteur_activite_detail=?, code_activite_naf=?, description_activite=?,
            numero_contribuable=?, numero_registre_commerce=?,
            num_caisse_sociale=?, greffe=?, annee_premiere_cloture=?,
            debut_exercice_social=?, fin_exercice_social=?, affectation_resultat=?,
            nombre_employes=?,
            dirigeant_nom=?, dirigeant_prenom=?, dirigeant_qualite=?,
            dirigeant_num_fiscal=?, dirigeant_adresse=?,
            expert_comptable_nom=?, expert_comptable_cabinet=?,
            expert_comptable_adresse=?, expert_comptable_telephone=?,
            commissaire_nom=?, commissaire_adresse=?, commissaire_infos=?,
            auditeur_externe=?, cabinet_audit_juridique=?,
            date_debut_exoneration=?, date_fin_exoneration=?,
            signataire_nom=?, signataire_qualite=?,
            personne_contact=?, banque_domiciliation=?, numero_compte_bancaire=?
            WHERE id=?");

        $nullIfEmpty = fn($v) => $v === '' ? null : $v;

        $stmt->execute([
            $logoPath,
            trim($_POST['raison_sociale']),
            $_POST['forme_juridique'] ?? '',
            trim($_POST['ninea'] ?? ''),
            trim($_POST['rccm'] ?? ''),
            trim($_POST['sigle'] ?? ''),
            trim($_POST['boite_postale'] ?? ''),
            trim($_POST['code_importateur'] ?? ''),
            trim($_POST['telephone'] ?? ''),
            trim($_POST['email'] ?? ''),
            trim($_POST['email_secondaire'] ?? ''),
            trim($_POST['site_web'] ?? ''),
            trim($_POST['adresse'] ?? ''),
            trim($_POST['ville'] ?? ''),
            trim($_POST['pays'] ?? 'Sénégal'),
            $_POST['regime_fiscal'] ?? 'CGI',
            $_POST['regime_tva'] ?? 'mensuel',
            (float)($_POST['ca_annuel_estime'] ?? 0),
            trim($_POST['secteur_activite'] ?? ''),
            trim($_POST['secteur_activite_detail'] ?? ''),
            trim($_POST['code_activite_naf'] ?? ''),
            trim($_POST['description_activite'] ?? ''),
            trim($_POST['numero_contribuable'] ?? ''),
            trim($_POST['numero_registre_commerce'] ?? ''),
            trim($_POST['num_caisse_sociale'] ?? ''),
            trim($_POST['greffe'] ?? ''),
            $nullIfEmpty($_POST['annee_premiere_cloture'] ?? ''),
            $nullIfEmpty($_POST['debut_exercice_social'] ?? ''),
            $nullIfEmpty($_POST['fin_exercice_social'] ?? ''),
            $nullIfEmpty($_POST['affectation_resultat'] ?? ''),
            (int)($_POST['nombre_employes'] ?? 0),
            trim($_POST['dirigeant_nom'] ?? ''),
            trim($_POST['dirigeant_prenom'] ?? ''),
            $nullIfEmpty($_POST['dirigeant_qualite'] ?? ''),
            trim($_POST['dirigeant_num_fiscal'] ?? ''),
            trim($_POST['dirigeant_adresse'] ?? ''),
            trim($_POST['expert_comptable_nom'] ?? ''),
            trim($_POST['expert_comptable_cabinet'] ?? ''),
            trim($_POST['expert_comptable_adresse'] ?? ''),
            trim($_POST['expert_comptable_telephone'] ?? ''),
            trim($_POST['commissaire_nom'] ?? ''),
            trim($_POST['commissaire_adresse'] ?? ''),
            trim($_POST['commissaire_infos'] ?? ''),
            trim($_POST['auditeur_externe'] ?? ''),
            trim($_POST['cabinet_audit_juridique'] ?? ''),
            $nullIfEmpty($_POST['date_debut_exoneration'] ?? ''),
            $nullIfEmpty($_POST['date_fin_exoneration'] ?? ''),
            trim($_POST['signataire_nom'] ?? ''),
            trim($_POST['signataire_qualite'] ?? ''),
            trim($_POST['personne_contact'] ?? ''),
            trim($_POST['banque_domiciliation'] ?? ''),
            trim($_POST['numero_compte_bancaire'] ?? ''),
            $id,
        ]);

        redirect('/dossier/profil?id=' . $id . '&saved=1');
    }

    // ----------------------------------------------------------------
    // Livre auxiliaire clients / fournisseurs
    // ----------------------------------------------------------------
    public function livreAuxiliaire(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $type       = $_GET['type'] ?? 'client';   // client | fournisseur
        $tiers_id   = (int)($_GET['tiers_id'] ?? 0);
        $ex         = $entreprise['exercice_courant'];
        $date_debut = $_GET['date_debut'] ?? "$ex-01-01";
        $date_fin   = $_GET['date_fin']   ?? "$ex-12-31";

        $compte_prefix = $type === 'client' ? '411' : '401';

        // Liste des tiers du type sélectionné
        $stmt = $db->prepare("SELECT t.id, t.nom FROM tiers t WHERE t.entreprise_id=? AND t.type IN (?,?) AND t.actif=1 ORDER BY t.nom");
        $stmt->execute([$id, $type, 'les_deux']);
        $liste_tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mouvements du tiers sélectionné
        $mouvements = [];
        $solde_ouverture = 0;
        $tiers_courant = null;

        if ($tiers_id) {
            $stmt = $db->prepare("SELECT id, nom FROM tiers WHERE id=? AND entreprise_id=?");
            $stmt->execute([$tiers_id, $id]);
            $tiers_courant = $stmt->fetch(PDO::FETCH_ASSOC);

            // Solde d'ouverture avant date_debut
            $stmt = $db->prepare("SELECT COALESCE(SUM(l.debit)-SUM(l.credit),0)
                FROM lignes_ecritures l
                JOIN ecritures e ON e.id=l.ecriture_id
                JOIN comptes c ON c.id=l.compte_id
                WHERE e.entreprise_id=? AND l.tiers_id=? AND c.numero LIKE ?
                AND e.date_ecriture < ?");
            $stmt->execute([$id, $tiers_id, $compte_prefix.'%', $date_debut]);
            $solde_ouverture = (float)$stmt->fetchColumn();

            // Mouvements sur la période
            $stmt = $db->prepare("SELECT e.date_ecriture, e.numero_piece, j.code as journal,
                e.libelle, l.code_lettrage, l.debit, l.credit
                FROM lignes_ecritures l
                JOIN ecritures e ON e.id=l.ecriture_id
                JOIN journaux j ON j.id=e.journal_id
                JOIN comptes c ON c.id=l.compte_id
                WHERE e.entreprise_id=? AND l.tiers_id=? AND c.numero LIKE ?
                AND e.date_ecriture BETWEEN ? AND ?
                ORDER BY e.date_ecriture, e.id");
            $stmt->execute([$id, $tiers_id, $compte_prefix.'%', $date_debut, $date_fin]);
            $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageTitle = 'Livre Auxiliaire';
        $activeTab = 'livre-auxiliaire';
        ob_start();
        require APP_ROOT . '/views/dossier/livre-auxiliaire.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ----------------------------------------------------------------
    // Balance auxiliaire clients / fournisseurs
    // ----------------------------------------------------------------
    public function balanceAuxiliaire(): void {
        requireAuth();
        $id         = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db         = getDB();

        $type       = $_GET['type'] ?? 'client';
        $ex         = $entreprise['exercice_courant'];
        $date_debut = $_GET['date_debut'] ?? "$ex-01-01";
        $date_fin   = $_GET['date_fin']   ?? "$ex-12-31";

        $compte_prefix = $type === 'client' ? '411' : '401';

        $stmt = $db->prepare("SELECT t.id, t.nom, t.telephone, t.email,
            COALESCE(SUM(CASE WHEN c.numero LIKE ? AND e.date_ecriture BETWEEN ? AND ? THEN l.debit ELSE 0 END),0) as total_debit,
            COALESCE(SUM(CASE WHEN c.numero LIKE ? AND e.date_ecriture BETWEEN ? AND ? THEN l.credit ELSE 0 END),0) as total_credit,
            COALESCE(SUM(CASE WHEN c.numero LIKE ? AND e.date_ecriture BETWEEN ? AND ? THEN l.debit-l.credit ELSE 0 END),0) as solde
            FROM tiers t
            LEFT JOIN lignes_ecritures l ON l.tiers_id=t.id
            LEFT JOIN ecritures e ON e.id=l.ecriture_id AND e.entreprise_id=?
            LEFT JOIN comptes c ON c.id=l.compte_id
            WHERE t.entreprise_id=? AND t.type IN (?,?) AND t.actif=1
            GROUP BY t.id, t.nom, t.telephone, t.email
            ORDER BY t.nom");
        $p = $compte_prefix.'%';
        $stmt->execute([$p, $date_debut, $date_fin, $p, $date_debut, $date_fin, $p, $date_debut, $date_fin, $id, $id, $type, 'les_deux']);
        $balance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Balance Auxiliaire';
        $activeTab = 'balance-auxiliaire';
        ob_start();
        require APP_ROOT . '/views/dossier/balance-auxiliaire.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    // ----------------------------------------------------------------
    // Balance âgée clients & fournisseurs
    // ----------------------------------------------------------------
    public function balanceAgee(): void {
        requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        if (!userHasAccess($id)) redirect('/dashboard');

        $type    = $_GET['type'] ?? 'clients';
        $dateRef = $_GET['date_ref'] ?? date('Y-m-d');

        $db = getDB();
        $prefixe = $type === 'clients' ? '41' : '40';

        $stmt = $db->prepare("
            SELECT
                c.numero, c.intitule,
                l.debit, l.credit,
                e.date_ecriture, e.libelle, e.numero_piece as reference,
                l.code_lettrage,
                DATEDIFF(?, e.date_ecriture) as age_jours
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ?
              AND c.numero LIKE ?
              AND (l.code_lettrage IS NULL OR l.code_lettrage = '')
            ORDER BY c.numero, e.date_ecriture
        ");
        $stmt->execute([$dateRef, $id, $prefixe . '%']);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comptes = [];
        foreach ($lignes as $l) {
            $num = $l['numero'];
            if (!isset($comptes[$num])) {
                $comptes[$num] = [
                    'numero'   => $num,
                    'intitule' => $l['intitule'],
                    'total'    => 0,
                    'courant'  => 0,
                    'j30_60'   => 0,
                    'j60_90'   => 0,
                    'j90_180'  => 0,
                    'plus180'  => 0,
                    'lignes'   => []
                ];
            }
            $solde = $type === 'clients'
                ? ($l['debit'] - $l['credit'])
                : ($l['credit'] - $l['debit']);
            $age = (int)$l['age_jours'];
            $comptes[$num]['total'] += $solde;
            if ($age <= 30)      $comptes[$num]['courant']  += $solde;
            elseif ($age <= 60)  $comptes[$num]['j30_60']   += $solde;
            elseif ($age <= 90)  $comptes[$num]['j60_90']   += $solde;
            elseif ($age <= 180) $comptes[$num]['j90_180']  += $solde;
            else                 $comptes[$num]['plus180']  += $solde;
            $comptes[$num]['lignes'][] = $l;
        }
        $comptes = array_filter($comptes, fn($c) => $c['total'] > 0.5);

        $pageTitle = $type === 'clients' ? 'Balance âgée clients' : 'Balance âgée fournisseurs';
        $activeTab = 'balance-agee';
        ob_start();
        require APP_ROOT . '/views/dossier/balance-agee.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function reportANouveaux(): void {
        requireAuth();
        $id       = (int)($_POST['entreprise_id'] ?? 0);
        $exercice = (int)($_POST['exercice'] ?? 0);
        $entreprise = $this->getEntreprise($id);
        $db = getDB();

        // Vérifier si déjà fait
        $check = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND exercice=? AND libelle LIKE 'Report à nouveau%'");
        $check->execute([$id, $exercice]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['flash_error'] = "Un report à nouveau existe déjà pour l'exercice $exercice.";
            redirect("/dossier/ecritures?id=$id&exercice=$exercice");
        }

        // Récupérer journal OD
        $stmtJ = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='OD' LIMIT 1");
        $stmtJ->execute([$id]);
        $journal = $stmtJ->fetch();
        if (!$journal) {
            $_SESSION['flash_error'] = "Journal OD introuvable.";
            redirect("/dossier/ecritures?id=$id");
        }

        // Soldes N-1 comptes bilan (classes 1-5)
        $stmt = $db->prepare("SELECT c.id as compte_id, c.numero, c.intitule,
            COALESCE(SUM(l.debit),0)  as total_debit,
            COALESCE(SUM(l.credit),0) as total_credit
            FROM comptes c
            LEFT JOIN lignes_ecritures l ON l.compte_id=c.id
            LEFT JOIN ecritures e ON e.id=l.ecriture_id AND e.exercice=?
            WHERE c.entreprise_id=? AND c.classe BETWEEN 1 AND 5
            GROUP BY c.id
            HAVING total_debit != 0 OR total_credit != 0");
        $stmt->execute([$exercice - 1, $id]);
        $soldes = $stmt->fetchAll();

        if (empty($soldes)) {
            $_SESSION['flash_error'] = "Aucun solde trouvé pour l'exercice " . ($exercice - 1) . ".";
            redirect("/dossier/ecritures?id=$id&exercice=$exercice");
        }

        // Créer l'écriture AN
        $db->beginTransaction();
        try {
            $dateOuv = $exercice . '-01-01';
            $db->prepare("INSERT INTO ecritures (entreprise_id, journal_id, user_id, date_ecriture, libelle, exercice, periode, statut, numero_piece)
                VALUES (?, ?, ?, ?, 'Report à nouveau exercice $exercice', ?, 1, 'brouillon', 'AN-$exercice')")
               ->execute([$id, $journal['id'], auth()['id'], $dateOuv, $exercice]);
            $ecritureId = (int)$db->lastInsertId();

            $nbLignes = 0;
            foreach ($soldes as $s) {
                $solde = $s['total_debit'] - $s['total_credit'];
                if (abs($solde) < 0.01) continue;
                $debit  = $solde > 0 ? $solde : 0;
                $credit = $solde < 0 ? abs($solde) : 0;
                $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)")
                   ->execute([$ecritureId, $s['compte_id'], 'AN ' . $s['numero'], $debit, $credit]);
                $nbLignes++;
            }

            // Mettre à jour exercice courant si besoin
            if ($exercice > $entreprise['exercice_courant']) {
                $db->prepare("UPDATE entreprises SET exercice_courant=? WHERE id=?")->execute([$exercice, $id]);
            }

            $db->commit();
            require_once APP_ROOT . '/src/Services/NotificationService.php';
            NotificationService::log(auth()['id'], 'report_an', "Report à nouveau exercice $exercice créé pour " . $entreprise['raison_sociale']);
            $_SESSION['flash_success'] = "Report à nouveau exercice $exercice créé avec $nbLignes lignes.";
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
        }

        redirect("/dossier/ecritures?id=$id&exercice=$exercice");
    }
}
