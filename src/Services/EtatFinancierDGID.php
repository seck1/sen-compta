<?php
/**
 * Générateur États Financiers DGID — ouvre le modèle et remplit les cellules
 */

require_once APP_ROOT . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class EtatFinancierDGID {

    private $db;
    private $entreprise;
    private $exercice;
    private $soldes   = [];
    private $soldesN1 = [];

    private static function modelePath(): string {
        return APP_ROOT . '/etat financier/modele_etats_financiers_DGID (Nouveau).xlsx';
    }

    public function __construct($db, $entreprise, $exercice) {
        $this->db         = $db;
        $this->entreprise = $entreprise;
        $this->exercice   = (int)$exercice;

        // Charger les activités R2
        $stmt = $db->prepare("SELECT * FROM entreprise_activites_r2 WHERE entreprise_id=? ORDER BY ordre ASC");
        $stmt->execute([$entreprise['id']]);
        $this->entreprise['_activites_r2'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->chargerSoldes($this->exercice, false);
        $this->chargerSoldes($this->exercice - 1, true);
    }

    // ── Chargement des soldes ─────────────────────────────────────────────────

    private function chargerSoldes(int $exercice, bool $n1): void {
        $stmt = $this->db->prepare("
            SELECT c.numero,
                   COALESCE(SUM(le.debit),0)                               AS d,
                   COALESCE(SUM(le.credit),0)                              AS c,
                   COALESCE(SUM(le.debit),0) - COALESCE(SUM(le.credit),0) AS s
            FROM comptes c
            LEFT JOIN lignes_ecritures le ON le.compte_id = c.id
            LEFT JOIN ecritures e ON e.id = le.ecriture_id
                AND e.exercice = ?
                AND e.entreprise_id = ?
                AND e.statut IN ('validee','cloturee','brouillon')
            WHERE c.entreprise_id = ?
            GROUP BY c.id, c.numero
        ");
        $stmt->execute([$exercice, $this->entreprise['id'], $this->entreprise['id']]);
        $target = $n1 ? 'soldesN1' : 'soldes';
        $this->$target = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $this->$target[$r['numero']] = ['d' => (float)$r['d'], 'c' => (float)$r['c'], 's' => (float)$r['s']];
        }
    }

    // ── Helpers calcul ─────────────────────────────────────────────────────────

    private function sum(array $pfx, bool $n1 = false): float {
        $src = $n1 ? $this->soldesN1 : $this->soldes;
        $t = 0.0;
        foreach ($src as $num => $v) {
            foreach ($pfx as $p) {
                if (strpos((string)$num, (string)$p) === 0) { $t += $v['s']; break; }
            }
        }
        return $t;
    }

    private function brut(array $pfx, bool $n1 = false): float {
        $src = $n1 ? $this->soldesN1 : $this->soldes;
        $t = 0.0;
        foreach ($src as $num => $v) {
            foreach ($pfx as $p) {
                if (strpos((string)$num, (string)$p) === 0) { $t += $v['d']; break; }
            }
        }
        return $t;
    }

    private function amort(array $pfx, bool $n1 = false): float {
        $amortPfx = [];
        foreach ($pfx as $p) {
            $amortPfx[] = '28' . substr((string)$p, 1);
            $amortPfx[] = '29' . substr((string)$p, 1);
        }
        $src = $n1 ? $this->soldesN1 : $this->soldes;
        $t = 0.0;
        foreach ($src as $num => $v) {
            foreach ($amortPfx as $ap) {
                if (strpos((string)$num, $ap) === 0) { $t += abs($v['c']); break; }
            }
        }
        return $t;
    }

    private function net(float $b, float $a): float { return max(0, $b - $a); }
    private function passif(array $pfx, bool $n1 = false): float { return max(0, -$this->sum($pfx, $n1)); }
    private function produit(array $pfx, bool $n1 = false): float { return abs(min(0, $this->sum($pfx, $n1))); }
    private function charge(array $pfx, bool $n1 = false): float  { return max(0, $this->sum($pfx, $n1)); }
    private function v(float $val): int { return (int)round($val); }

    // ── Calculs bilan actif ───────────────────────────────────────────────────

    private function calcActif(bool $n1): array {
        $AE_b = $this->brut(['201','202'],$n1); $AE_a = $this->amort(['201','202'],$n1);
        $AF_b = $this->brut(['203','204'],$n1); $AF_a = $this->amort(['203','204'],$n1);
        $AG_b = $this->brut(['205','206'],$n1); $AG_a = $this->amort(['205','206'],$n1);
        $AH_b = $this->brut(['207','208'],$n1); $AH_a = $this->amort(['207','208'],$n1);
        $AD_b = $AE_b+$AF_b+$AG_b+$AH_b;        $AD_a = $AE_a+$AF_a+$AG_a+$AH_a;

        $AJ_b = $this->brut(['211'],$n1);        $AJ_a = $this->amort(['211'],$n1);
        $AK_b = $this->brut(['213'],$n1);        $AK_a = $this->amort(['213'],$n1);
        $AL_b = $this->brut(['212','215'],$n1);  $AL_a = $this->amort(['212','215'],$n1);
        $AM_b = $this->brut(['216','218'],$n1);  $AM_a = $this->amort(['216','218'],$n1);
        $AN_b = $this->brut(['217'],$n1);        $AN_a = $this->amort(['217'],$n1);
        $AI_b = $AJ_b+$AK_b+$AL_b+$AM_b+$AN_b; $AI_a = $AJ_a+$AK_a+$AL_a+$AM_a+$AN_a;

        $AP_b = $this->brut(['22'],$n1); $AP_a = 0;

        $AR_b = $this->brut(['261','262'],$n1);                              $AR_a = $this->amort(['261','262'],$n1);
        $AS_b = $this->brut(['271','272','274','275','276','277'],$n1);      $AS_a = $this->amort(['27'],$n1);
        $AQ_b = $AR_b+$AS_b;                                                 $AQ_a = $AR_a+$AS_a;

        $AZ_b = $AD_b+$AI_b+$AP_b+$AQ_b; $AZ_a = $AD_a+$AI_a+$AP_a+$AQ_a;

        $BA_b = $this->brut(['481','482','485'],$n1); $BA_a = 0;
        $BB_b = $this->brut(['31','32','33','34','35','36','37','38'],$n1);
        $BB_a = $this->amort(['31','32','33','34','35','36','37','38'],$n1);

        $BH_b = $this->brut(['409'],$n1); $BH_a = 0;
        $BI_b = $this->brut(['411','412','416'],$n1); $BI_a = $this->amort(['491','496'],$n1);
        $BJ_b = $this->brut(['421','422','425','426','431','441','442','444','445','446','449','451','455','458','467','471','472','473','474'],$n1);
        $BJ_a = $this->amort(['499'],$n1);
        $BG_b = $BH_b+$BI_b+$BJ_b; $BG_a = $BH_a+$BI_a+$BJ_a;
        $BK_b = $BA_b+$BB_b+$BG_b; $BK_a = $BA_a+$BB_a+$BG_a;

        $BQ_b = $this->brut(['50','501','502','503','504','506'],$n1); $BQ_a = $this->amort(['59'],$n1);
        $BR_b = $this->brut(['511','512','514','515','516'],$n1);      $BR_a = 0;
        $BS_b = $this->brut(['521','522','531','532','541','551','571'],$n1); $BS_a = 0;
        $BT_b = $BQ_b+$BR_b+$BS_b; $BT_a = $BQ_a;

        $BU_b = $this->brut(['476'],$n1); $BU_a = 0;
        $BZ_b = $AZ_b+$BK_b+$BT_b+$BU_b; $BZ_a = $AZ_a+$BK_a+$BT_a+$BU_a;

        return compact(
            'AD_b','AD_a','AE_b','AE_a','AF_b','AF_a','AG_b','AG_a','AH_b','AH_a',
            'AI_b','AI_a','AJ_b','AJ_a','AK_b','AK_a','AL_b','AL_a','AM_b','AM_a','AN_b','AN_a',
            'AP_b','AP_a','AQ_b','AQ_a','AR_b','AR_a','AS_b','AS_a','AZ_b','AZ_a',
            'BA_b','BA_a','BB_b','BB_a','BH_b','BH_a','BI_b','BI_a','BJ_b','BJ_a',
            'BG_b','BG_a','BK_b','BK_a',
            'BQ_b','BQ_a','BR_b','BR_a','BS_b','BS_a','BT_b','BT_a',
            'BU_b','BU_a','BZ_b','BZ_a'
        );
    }

    // ── Calculs bilan passif ──────────────────────────────────────────────────

    private function calcPassif(bool $n1): array {
        $CA = $this->passif(['101','102','103'],$n1);
        $CB = $this->passif(['1011'],$n1);
        $CD = $this->passif(['104'],$n1);
        $CE = $this->passif(['105'],$n1);
        $CF = $this->passif(['1061','1062','1063'],$n1);
        $CG = $this->passif(['1068'],$n1);
        $CH = -$this->sum(['111','119'],$n1);
        $CJ = -$this->sum(['12'],$n1);
        $CL = $this->passif(['13'],$n1);
        $CM = $this->passif(['14'],$n1);
        $CP = $CA - $CB + $CD + $CE + $CF + $CG + $CH + $CJ + $CL + $CM;

        $DA = $this->passif(['161','162','163','165','166'],$n1);
        $DB = $this->passif(['17'],$n1);
        $DC = $this->passif(['15'],$n1);
        $DD = $DA + $DB + $DC;
        $DF = $CP + $DD;

        $DH = $this->passif(['481','482','485'],$n1);
        $DI = $this->passif(['419'],$n1);
        $DJ = $this->passif(['401','402','404','408'],$n1);
        $DK = $this->passif(['431','441','442','444','445','446'],$n1);
        $DM = $this->passif(['46','47'],$n1);
        $DN = $this->passif(['155','156','158'],$n1);
        $DP = $DH + $DI + $DJ + $DK + $DM + $DN;

        $DQ = $this->passif(['564'],$n1);
        $DR = $this->passif(['561','562'],$n1);
        $DT = $DQ + $DR;
        $DV = $this->passif(['477'],$n1);
        $DZ = $DF + $DP + $DT + $DV;

        return compact('CA','CB','CD','CE','CF','CG','CH','CJ','CL','CM','CP',
                       'DA','DB','DC','DD','DF','DH','DI','DJ','DK','DM','DN','DP',
                       'DQ','DR','DT','DV','DZ');
    }

    // ── Calculs compte de résultat ────────────────────────────────────────────

    private function calcCR(bool $n1): array {
        $TA = $this->produit(['701'],$n1);
        $RA = $this->charge(['601'],$n1);
        $RB = $this->sum(['6031'],$n1);
        $XA = $TA - $RA - $RB;

        $TB = $this->produit(['702'],$n1);
        $TC = $this->produit(['703','704'],$n1);
        $TD = $this->produit(['705','706'],$n1);
        $XB = $TA + $TB + $TC + $TD;

        $TE = -$this->sum(['73'],$n1);
        $TF = $this->produit(['72'],$n1);
        $TG = $this->produit(['71'],$n1);
        $TH = $this->produit(['75','77'],$n1);
        $TI = $this->produit(['781'],$n1);
        $RC = $this->charge(['602'],$n1);
        $RD = $this->sum(['6032'],$n1);
        $RE = $this->charge(['604','605','606'],$n1);
        $RF = $this->sum(['6033'],$n1);
        $RG = $this->charge(['61'],$n1);
        $RH = $this->charge(['62','63'],$n1);
        $RI = $this->charge(['64'],$n1);
        $RJ = $this->charge(['65','67'],$n1);
        $XC = $XB + $TE + $TF + $TG + $TH + $TI - $RC - $RD - $RE - $RF - $RG - $RH - $RI - $RJ;

        $RK = $this->charge(['66'],$n1);
        $XD = $XC - $RK;

        $TJ = $this->produit(['791','798'],$n1);
        $RL = $this->charge(['681','691'],$n1);
        $XE = $XD + $TJ - $RL;

        $TK = $this->produit(['776','777','778'],$n1);
        $TL = $this->produit(['797'],$n1);
        $TM = $this->produit(['787'],$n1);
        $RM = $this->charge(['671','672','673','674','676','677','678'],$n1);
        $RN = $this->charge(['697'],$n1);
        $XF = $TK + $TL + $TM - $RM - $RN;
        $XG = $XE + $XF;

        $TN = $this->produit(['82'],$n1);
        $TO = $this->produit(['84','88'],$n1);
        $RO = $this->charge(['81'],$n1);
        $RP = $this->charge(['83','85'],$n1);
        $XH = $TN + $TO - $RO - $RP;

        $RQ = $this->charge(['869'],$n1);
        $RS = $this->charge(['891'],$n1);
        $XI = $XG + $XH - $RQ - $RS;

        return compact('TA','RA','RB','XA','TB','TC','TD','XB',
                       'TE','TF','TG','TH','TI','RC','RD','RE','RF','RG','RH','RI','RJ','XC',
                       'RK','XD','TJ','RL','XE','TK','TL','TM','RM','RN','XF','XG',
                       'TN','TO','RO','RP','XH','RQ','RS','XI');
    }

    // ── REMPLISSAGE DU MODÈLE ─────────────────────────────────────────────────

    public function generer(string $outputPath): string {
        $ent  = $this->entreprise;
        $ex   = $this->exercice;

        // Charger le modèle DGID sans recalculer les formules
        $modele = self::modelePath();
        if (!is_file($modele)) {
            throw new \RuntimeException("Modèle États Financiers DGID introuvable : $modele");
        }
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(false);
        $ss = $reader->load($modele);

        $aN  = $this->calcActif(false);
        $aN1 = $this->calcActif(true);
        $pN  = $this->calcPassif(false);
        $pN1 = $this->calcPassif(true);
        $crN = $this->calcCR(false);
        $crN1= $this->calcCR(true);

        // ── PAGE DE GARDE ────────────────────────────────────────────────────
        $pg = $ss->getSheetByName('Page de garde');
        if ($pg) {
            $pg->setCellValue('M27', '31/12/' . $ex);
            $pg->setCellValue('L35', $ent['raison_sociale'] ?? '');
            $pg->setCellValue('H41', $ent['sigle'] ?? '');
            $adressePg = trim(implode(' ', array_filter([
                $ent['adresse'] ?? '',
                $ent['boite_postale'] ? 'BP ' . ltrim($ent['boite_postale'], 'BP ') : '',
                $ent['ville'] ?? '',
                $ent['pays'] ?? '',
            ])));
            $pg->setCellValue('J46', $adressePg);
            $pg->setCellValue('N51', $ent['ninea'] ?? '');
        }

        // ── FICHE R1 ─────────────────────────────────────────────────────────
        $r1 = $ss->getSheetByName('Fiche de renseignement R1');
        if ($r1) {
            $r1->setCellValue('J4',  $ent['raison_sociale'] ?? '');
            $r1->setCellValue('AD7', $ent['sigle'] ?? '');
            $adresseR1 = trim(implode(' ', array_filter([
                $ent['adresse'] ?? '',
                $ent['boite_postale'] ? 'BP ' . ltrim($ent['boite_postale'], 'BP ') : '',
                $ent['ville'] ?? '',
                $ent['pays'] ?? '',
            ])));
            $r1->setCellValue('J7', $adresseR1);
            $r1->setCellValue('J10', $ent['ninea'] ?? '');
            $r1->setCellValue('V10', '31/12/' . $ex);
            $r1->setCellValue('AH10', 12);
            $r1->setCellValue('Y13',  '01/01/' . $ex);
            $r1->setCellValue('AH13', '31/12/' . $ex);
            $r1->setCellValue('Q20',  '31/12/' . ($ex-1));
            $r1->setCellValue('AL20', 12);
            $r1->setCellValue('F24',  'SN');
            $r1->setCellValue('I24',  $ent['rccm'] ?? $ent['numero_registre_commerce'] ?? '');
            $r1->setCellValue('G28',  $ent['num_caisse_sociale'] ?? '');
            $r1->setCellValue('S29',  $ent['code_importateur'] ?? '');
            $r1->setCellValue('AE29', $ent['code_activite_naf'] ?? '');
            $r1->setCellValue('D32',  $ent['raison_sociale'] ?? '');
            $r1->setCellValue('AE32', $ent['sigle'] ?? '');
            $r1->setCellValueExplicit('G36', $ent['telephone'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $r1->setCellValue('Q36',  $ent['email'] ?? '');
            $r1->setCellValue('AA36', $ent['boite_postale'] ?? '');
            $r1->setCellValue('AE36', $ent['ville'] ?? '');
            $r1->setCellValue('D40',  trim(($ent['adresse'] ?? '') . ' ' . ($ent['ville'] ?? '') . ' ' . ($ent['pays'] ?? '')));
            $r1->setCellValue('D44',  $ent['description_activite'] ?? $ent['secteur_activite'] ?? '');
            // Personne à contacter
            if (!empty($ent['personne_contact'])) {
                $r1->setCellValue('D48', $ent['personne_contact']);
            }
            // Expert comptable
            if (!empty($ent['expert_comptable_nom'])) {
                $ec = trim(($ent['expert_comptable_nom'] ?? '') . ' - ' . ($ent['expert_comptable_cabinet'] ?? '') . ($ent['expert_comptable_adresse'] ? ' - ' . $ent['expert_comptable_adresse'] : '') . ($ent['expert_comptable_telephone'] ? ' - Tél: ' . $ent['expert_comptable_telephone'] : ''));
                $r1->setCellValue('D52', $ec);
            }
            // Commissaire aux comptes
            if (!empty($ent['commissaire_nom'])) {
                $cac = trim(($ent['commissaire_nom'] ?? '') . ($ent['commissaire_adresse'] ? ' - ' . $ent['commissaire_adresse'] : '') . ($ent['commissaire_infos'] ? ' - ' . $ent['commissaire_infos'] : ''));
                $r1->setCellValue('D56', $cac);
            }
            // Signataire
            if (!empty($ent['signataire_nom'])) {
                $r1->setCellValue('D69', $ent['signataire_nom']);
                $r1->setCellValue('D72', $ent['signataire_qualite'] ?? '');
            } elseif (!empty($ent['dirigeant_nom'])) {
                $r1->setCellValue('D69', trim(($ent['dirigeant_prenom'] ?? '') . ' ' . ($ent['dirigeant_nom'] ?? '')));
                $r1->setCellValue('D72', $ent['dirigeant_qualite'] ?? '');
            }
            // Domiciliation bancaire
            if (!empty($ent['banque_domiciliation'])) {
                $r1->setCellValue('T70', $ent['banque_domiciliation']);
                $r1->setCellValue('AD70', $ent['numero_compte_bancaire'] ?? '');
            }
        }

        // ── ACTIVITÉS R2 ─────────────────────────────────────────────────────
        $r2 = $ss->getSheetByName("Activités de l'entreprise R2");
        if ($r2 && !empty($ent['_activites_r2'])) {
            // Lignes de données : 33, 36, 39, 42, 45, 48, 51, 54 (max 8 activités)
            $rows = [33, 36, 39, 42, 45, 48, 51, 54];
            $codeCols = ['D','E','F','G','H','I']; // 6 cases pour le code nomenclature
            foreach ($ent['_activites_r2'] as $idx => $act) {
                if ($idx >= count($rows)) break;
                $row = $rows[$idx];
                $r2->setCellValue('B' . $row, $act['designation']);
                // Code nomenclature : 1 caractère par case
                $code = str_pad($act['code_nomenclature'] ?? '', 6);
                foreach ($codeCols as $ci => $col) {
                    $r2->setCellValue($col . $row, $code[$ci] !== ' ' ? $code[$ci] : '');
                }
                $r2->setCellValue('L' . $row, (float)$act['valeur_ajoutee']);
            }
        }

        // ── BILAN PAYSAGE ─────────────────────────────────────────────────────
        $bil = $ss->getSheetByName('BILAN PAYSAGE');
        if ($bil) {
            // Actif : D=Brut, E=Amort, F=Net N, G=Net N-1
            $bilanActif = [
                9  => ['AD', $aN['AD_b'], $aN['AD_a'], $aN1['AD_b']-$aN1['AD_a']],
                10 => ['AE', $aN['AE_b'], $aN['AE_a'], $aN1['AE_b']-$aN1['AE_a']],
                11 => ['AF', $aN['AF_b'], $aN['AF_a'], $aN1['AF_b']-$aN1['AF_a']],
                12 => ['AG', $aN['AG_b'], $aN['AG_a'], $aN1['AG_b']-$aN1['AG_a']],
                13 => ['AH', $aN['AH_b'], $aN['AH_a'], $aN1['AH_b']-$aN1['AH_a']],
                14 => ['AI', $aN['AI_b'], $aN['AI_a'], $aN1['AI_b']-$aN1['AI_a']],
                15 => ['AJ', $aN['AJ_b'], $aN['AJ_a'], $aN1['AJ_b']-$aN1['AJ_a']],
                16 => ['AK', $aN['AK_b'], $aN['AK_a'], $aN1['AK_b']-$aN1['AK_a']],
                17 => ['AL', $aN['AL_b'], $aN['AL_a'], $aN1['AL_b']-$aN1['AL_a']],
                18 => ['AM', $aN['AM_b'], $aN['AM_a'], $aN1['AM_b']-$aN1['AM_a']],
                19 => ['AN', $aN['AN_b'], $aN['AN_a'], $aN1['AN_b']-$aN1['AN_a']],
                20 => ['AP', $aN['AP_b'], $aN['AP_a'], $aN1['AP_b']-$aN1['AP_a']],
                21 => ['AQ', $aN['AQ_b'], $aN['AQ_a'], $aN1['AQ_b']-$aN1['AQ_a']],
                22 => ['AR', $aN['AR_b'], $aN['AR_a'], $aN1['AR_b']-$aN1['AR_a']],
                23 => ['AS', $aN['AS_b'], $aN['AS_a'], $aN1['AS_b']-$aN1['AS_a']],
                24 => ['AZ', $aN['AZ_b'], $aN['AZ_a'], $aN1['AZ_b']-$aN1['AZ_a']],
                25 => ['BA', $aN['BA_b'], $aN['BA_a'], $aN1['BA_b']-$aN1['BA_a']],
                26 => ['BB', $aN['BB_b'], $aN['BB_a'], $aN1['BB_b']-$aN1['BB_a']],
                27 => ['BG', $aN['BG_b'], $aN['BG_a'], $aN1['BG_b']-$aN1['BG_a']],
                28 => ['BH', $aN['BH_b'], $aN['BH_a'], $aN1['BH_b']-$aN1['BH_a']],
                29 => ['BI', $aN['BI_b'], $aN['BI_a'], $aN1['BI_b']-$aN1['BI_a']],
                30 => ['BJ', $aN['BJ_b'], $aN['BJ_a'], $aN1['BJ_b']-$aN1['BJ_a']],
                31 => ['BK', $aN['BK_b'], $aN['BK_a'], $aN1['BK_b']-$aN1['BK_a']],
                32 => ['BQ', $aN['BQ_b'], $aN['BQ_a'], $aN1['BQ_b']-$aN1['BQ_a']],
                33 => ['BR', $aN['BR_b'], $aN['BR_a'], $aN1['BR_b']-$aN1['BR_a']],
                34 => ['BS', $aN['BS_b'], $aN['BS_a'], $aN1['BS_b']-$aN1['BS_a']],
                35 => ['BT', $aN['BT_b'], $aN['BT_a'], $aN1['BT_b']-$aN1['BT_a']],
                36 => ['BU', $aN['BU_b'], $aN['BU_a'], $aN1['BU_b']-$aN1['BU_a']],
                37 => ['BZ', $aN['BZ_b'], $aN['BZ_a'], $aN1['BZ_b']-$aN1['BZ_a']],
            ];
            foreach ($bilanActif as $row => [$ref, $brut, $amrt, $netN1]) {
                $netN = $this->net($brut, $amrt);
                if ($brut)  $bil->setCellValue("D$row", $this->v($brut));
                if ($amrt)  $bil->setCellValue("E$row", $this->v($amrt));
                if ($netN)  $bil->setCellValue("F$row", $this->v($netN));
                if ($netN1) $bil->setCellValue("G$row", $this->v(max(0,$netN1)));
            }

            // Passif : K=Net N, L=Net N-1
            $bilanPassif = [
                9  => [$pN['CA'],  $pN1['CA']],
                10 => [-$pN['CB'], -$pN1['CB']],
                11 => [$pN['CD'],  $pN1['CD']],
                12 => [$pN['CE'],  $pN1['CE']],
                13 => [$pN['CF'],  $pN1['CF']],
                14 => [$pN['CG'],  $pN1['CG']],
                15 => [$pN['CH'],  $pN1['CH']],
                16 => [$pN['CJ'],  $pN1['CJ']],
                17 => [$pN['CL'],  $pN1['CL']],
                18 => [$pN['CM'],  $pN1['CM']],
                19 => [$pN['CP'],  $pN1['CP']],
                20 => [$pN['DA'],  $pN1['DA']],
                21 => [$pN['DB'],  $pN1['DB']],
                22 => [$pN['DC'],  $pN1['DC']],
                23 => [$pN['DD'],  $pN1['DD']],
                24 => [$pN['DF'],  $pN1['DF']],
                25 => [$pN['DH'],  $pN1['DH']],
                26 => [$pN['DI'],  $pN1['DI']],
                27 => [$pN['DJ'],  $pN1['DJ']],
                28 => [$pN['DK'],  $pN1['DK']],
                29 => [$pN['DM'],  $pN1['DM']],
                30 => [$pN['DN'],  $pN1['DN']],
                31 => [$pN['DP'],  $pN1['DP']],
                33 => [$pN['DQ'],  $pN1['DQ']],
                34 => [$pN['DR'],  $pN1['DR']],
                35 => [$pN['DT'],  $pN1['DT']],
                36 => [$pN['DV'],  $pN1['DV']],
                37 => [$pN['DZ'],  $pN1['DZ']],
            ];
            foreach ($bilanPassif as $row => [$netN, $netN1]) {
                if ($netN)  $bil->setCellValue("K$row", $this->v($netN));
                if ($netN1) $bil->setCellValue("L$row", $this->v($netN1));
            }
        }

        // ── COMPTE DE RÉSULTAT ────────────────────────────────────────────────
        $cr = $ss->getSheetByName('COMPTE DE RESULTAT');
        if ($cr) {
            // E=valeur N, F=valeur N-1  (lignes 8 à 49)
            $crMap = [
                8  => [$crN['TA'],  $crN1['TA']],
                9  => [$crN['RA'],  $crN1['RA']],
                10 => [$crN['RB'],  $crN1['RB']],
                11 => [$crN['XA'],  $crN1['XA']],
                12 => [$crN['TB'],  $crN1['TB']],
                13 => [$crN['TC'],  $crN1['TC']],
                14 => [$crN['TD'],  $crN1['TD']],
                15 => [$crN['XB'],  $crN1['XB']],
                16 => [$crN['TE'],  $crN1['TE']],
                17 => [$crN['TF'],  $crN1['TF']],
                18 => [$crN['TG'],  $crN1['TG']],
                19 => [$crN['TH'],  $crN1['TH']],
                20 => [$crN['TI'],  $crN1['TI']],
                21 => [$crN['RC'],  $crN1['RC']],
                22 => [$crN['RD'],  $crN1['RD']],
                23 => [$crN['RE'],  $crN1['RE']],
                24 => [$crN['RF'],  $crN1['RF']],
                25 => [$crN['RG'],  $crN1['RG']],
                26 => [$crN['RH'],  $crN1['RH']],
                27 => [$crN['RI'],  $crN1['RI']],
                28 => [$crN['RJ'],  $crN1['RJ']],
                29 => [$crN['XC'],  $crN1['XC']],
                30 => [$crN['RK'],  $crN1['RK']],
                31 => [$crN['XD'],  $crN1['XD']],
                32 => [$crN['TJ'],  $crN1['TJ']],
                33 => [$crN['RL'],  $crN1['RL']],
                34 => [$crN['XE'],  $crN1['XE']],
                35 => [$crN['TK'],  $crN1['TK']],
                36 => [$crN['TL'],  $crN1['TL']],
                37 => [$crN['TM'],  $crN1['TM']],
                38 => [$crN['RM'],  $crN1['RM']],
                39 => [$crN['RN'],  $crN1['RN']],
                40 => [$crN['XF'],  $crN1['XF']],
                41 => [$crN['XG'],  $crN1['XG']],
                42 => [$crN['TN'],  $crN1['TN']],
                43 => [$crN['TO'],  $crN1['TO']],
                44 => [$crN['RO'],  $crN1['RO']],
                45 => [$crN['RP'],  $crN1['RP']],
                46 => [$crN['XH'],  $crN1['XH']],
                47 => [$crN['RQ'],  $crN1['RQ']],
                48 => [$crN['RS'],  $crN1['RS']],
                49 => [$crN['XI'],  $crN1['XI']],
            ];
            foreach ($crMap as $row => [$vN, $vN1]) {
                if ($vN != 0)  $cr->setCellValue("E$row", $this->v($vN));
                if ($vN1 != 0) $cr->setCellValue("F$row", $this->v($vN1));
            }
        }

        // ── FLUX DE TRÉSORERIE ────────────────────────────────────────────────
        $ft = $ss->getSheetByName('FLUX DE TRESORERIE');
        if ($ft) {
            $aN1obj = $this->calcActif(true);
            $pN1obj = $this->calcPassif(true);

            // Calculs CAFG et flux
            $dap     = $this->charge(['681','682','683','691','697']);
            $rep     = $this->produit(['791','797','798']);
            $pluVal  = $this->produit(['82']) - $this->charge(['81']);
            $cafg    = $crN['XI'] + $dap - $rep - $pluVal;

            $varStk  = -($this->net($aN['BB_b'],$aN['BB_a']) - $this->net($aN1obj['BB_b'],$aN1obj['BB_a']));
            $varCli  = -($this->net($aN['BI_b'],$aN['BI_a']) - $this->net($aN1obj['BI_b'],$aN1obj['BI_a']));
            $varCre  = -($this->net($aN['BJ_b'],$aN['BJ_a']) - $this->net($aN1obj['BJ_b'],$aN1obj['BJ_a']));
            $varFou  = $pN['DJ'] - $pN1obj['DJ'];
            $varFis  = $pN['DK'] - $pN1obj['DK'];
            $varHao  = ($pN['DH'] - $pN1obj['DH']) - ($aN['BA_b'] - $aN1obj['BA_b']);
            $fluxOp  = $cafg + $varHao + $varStk + ($varCli+$varCre) + ($varFou+$varFis);

            $acqII   = -($aN['AD_b'] - $aN1obj['AD_b']);
            $acqIC   = -($aN['AI_b'] - $aN1obj['AI_b']);
            $acqIF   = -($aN['AQ_b'] - $aN1obj['AQ_b']);
            $cesII   = $this->produit(['82']);
            $cesIF   = 0;
            $fluxI   = $acqII + $acqIC + $acqIF + $cesII + $cesIF;

            $augCap  = $pN['CA'] - $pN1obj['CA'];
            $subv    = $pN['CL'] - $pN1obj['CL'];
            $prelCap = 0;
            $divid   = 0;
            $fluxCP  = $augCap + $subv - $prelCap - $divid;

            $emprN   = max(0, $pN['DA'] - $pN1obj['DA']);
            $autDet  = max(0, $pN['DB'] - $pN1obj['DB']);
            $rembE   = max(0, $pN1obj['DA'] - $pN['DA']);
            $fluxCE  = $emprN + $autDet - $rembE;
            $fluxFin = $fluxCP + $fluxCE;

            $tresoN  = $this->net($aN['BT_b'],$aN['BT_a'])      - $pN['DT'];
            $tresoN1 = $this->net($aN1obj['BT_b'],$aN1obj['BT_a']) - $pN1obj['DT'];
            $varTre  = $tresoN - $tresoN1;

            $fluxMap = [
                8  => $tresoN1,
                10 => $cafg,
                11 => $varHao,
                12 => $varStk,
                13 => $varCli + $varCre,
                14 => $varFou + $varFis,
                16 => $fluxOp,
                18 => $acqII,
                19 => $acqIC,
                20 => $acqIF,
                21 => $cesII,
                22 => $cesIF,
                23 => $fluxI,
                25 => $augCap,
                26 => $subv,
                27 => $prelCap,
                28 => $divid,
                29 => $fluxCP,
                31 => $emprN,
                32 => $autDet,
                33 => $rembE,
                34 => $fluxCE,
                35 => $fluxFin,
                36 => $varTre,
                37 => $tresoN,
            ];
            foreach ($fluxMap as $row => $val) {
                if ($val != 0) $ft->setCellValue("E$row", $this->v($val));
            }
        }

        // Sauvegarder
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($ss, 'Xlsx');
        $writer->save($outputPath);
        return $outputPath;
    }
}
