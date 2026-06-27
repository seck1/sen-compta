<?php
/**
 * PaieService — Calcul bulletin de paie Sénégal
 *
 * Taux officiels 2024/2025 :
 * IPRES régime général salarié : 5.6% plafonné 768 000 F/mois
 * IPRES régime général patronal: 8.4% plafonné 768 000 F/mois
 * IPRES cadre salarié          : 2.4% sur tranche B (768 001 → 1 536 000)
 * IPRES cadre patronal         : 3.6% sur tranche B
 * CSS accidents du travail     : 1% à 5% selon secteur (défaut 3%)
 * CSS prestations familiales   : 7% plafonné 63 000 F/mois (salaire brut)
 * TRIMF (ex-IRPP retenue)      : barème progressif
 * IR (Impôt sur le Revenu)     : barème CGI Sénégal
 * CFCE                         : 3% masse salariale brute (charge employeur)
 * IPM                          : variable (défaut 0.5% salarié / 3% patronal)
 */
class PaieService {

    // Plafonds IPRES 2024
    const PLAFOND_IPRES_A = 768000;    // Tranche A mensuelle (plafond assiette IPRES RG — réforme 2024)
    const PLAFOND_IPRES_B = 1536000;   // Tranche B mensuelle (plafond total assiette IPRES cadres — réforme 2024)

    // Taux IPRES régime général
    const IPRES_SALARIE_A  = 0.056;
    const IPRES_PATRONAL_A = 0.084;

    // Taux IPRES cadres (tranche B)
    const IPRES_SALARIE_B  = 0.024;
    const IPRES_PATRONAL_B = 0.036;

    // CSS
    const CSS_PRESTATIONS_FAMILIALES  = 0.07;
    const CSS_PLAFOND_ASSIETTE_PF     = 63000;  // plafond d'assiette mensuel (cotisation max = 63 000 × 7% = 4 410 F)
    const CSS_PLAFOND_PF              = 63000;  // cotisation maximale (conservé pour compatibilité)
    const CSS_ACCIDENTS_TRAVAIL       = 0.03;   // taux moyen (3%)

    // CFCE
    const CFCE_TAUX = 0.03;

    // IPM (défaut)
    const IPM_SALARIE  = 0.005;
    const IPM_PATRONAL = 0.030;

    /**
     * Calcule le TRIMF (Taxe Représentative de l'Impôt du Minimum Fiscal)
     * Barème progressif mensuel Sénégal
     */
    /** Décode un barème stocké en base (JSON string ou array) ; retourne null si vide/invalide. */
    public static function decoderBareme($valeur): ?array {
        if (is_array($valeur)) return !empty($valeur) ? $valeur : null;
        if (is_string($valeur) && $valeur !== '') {
            $d = json_decode($valeur, true);
            return (is_array($d) && !empty($d)) ? $d : null;
        }
        return null;
    }

    public static function calculerTRIMF(float $salaireBrut, ?array $bareme = null): float {
        if ($salaireBrut <= 0) return 0;
        // Barème personnalisé (depuis paie_parametres.bareme_trimf) : tranches sur le brut
        // ANNUEL ; le montant stocké est la retenue annuelle, mensualisée ici (/12).
        if (is_array($bareme) && !empty($bareme)) {
            $brutAnnuel = $salaireBrut * 12;
            foreach ($bareme as $t) {
                $min = (float)($t['min'] ?? 0);
                $max = (float)($t['max'] ?? PHP_INT_MAX);
                if ($brutAnnuel >= $min && $brutAnnuel <= $max) {
                    return round(((float)($t['montant'] ?? 0)) / 12);
                }
            }
            return 0;
        }
        // Barème par défaut (mensuel) — DGID Sénégal
        if ($salaireBrut <= 25000)    return 900;
        if ($salaireBrut <= 50000)    return 1800;
        if ($salaireBrut <= 75000)    return 2700;
        if ($salaireBrut <= 100000)   return 3600;
        if ($salaireBrut <= 125000)   return 4500;
        if ($salaireBrut <= 150000)   return 5400;
        if ($salaireBrut <= 200000)   return 7200;
        if ($salaireBrut <= 250000)   return 9000;
        if ($salaireBrut <= 300000)   return 10800;
        if ($salaireBrut <= 400000)   return 14400;
        if ($salaireBrut <= 500000)   return 18000;
        return round($salaireBrut * 0.04); // >500k : 4%
    }

    /**
     * Calcule l'IR (Impôt sur le Revenu) mensuel
     * Barème CGI Sénégal — Article 163
     * Appliqué sur revenu net imposable annualisé puis divisé par 12
     */
    public static function calculerIR(float $salaireBrut, float $nbParts = 1.0, float $cotisationsDeductibles = 0.0, ?array $bareme = null): float {
        // Abattement forfaitaire 30% (minimum 900 000 / maximum 3 600 000 F/an)
        // Assiette nette = brut annuel - cotisations déductibles annualisées (IPRES salarié + IPM salarié)
        // CGI Sénégal Art. 163
        $brutAnnuel      = $salaireBrut * 12;
        $cotisAnnuelles  = $cotisationsDeductibles * 12;
        $baseAbattement  = max(0, $brutAnnuel - $cotisAnnuelles);
        $abattement      = min(3600000, max(900000, $baseAbattement * 0.30));
        $revenuImposable = max(0, $baseAbattement - $abattement);

        // Quotient familial CGI Sénégal Art. 165 :
        // Diviser le revenu imposable par le nombre de parts, appliquer le barème, multiplier par les parts
        if ($nbParts <= 0) $nbParts = 1.0;
        $revenuParPart = $revenuImposable / $nbParts;

        // Barème progressif annuel appliqué sur le revenu par part.
        if (is_array($bareme) && !empty($bareme)) {
            // Barème personnalisé (depuis paie_parametres.bareme_ir) : tranches marginales.
            $irParPart = 0;
            foreach ($bareme as $t) {
                $min  = (float)($t['min'] ?? 0);
                $max  = (float)($t['max'] ?? PHP_INT_MAX);
                $taux = (float)($t['taux'] ?? 0) / 100;
                if ($revenuParPart > $min) {
                    $assiette = min($revenuParPart, $max) - $min;
                    if ($assiette > 0) $irParPart += $assiette * $taux;
                }
            }
        } else {
            // Barème par défaut — CGI Art. 163 Sénégal.
            if ($revenuParPart <= 630000)         $irParPart = 0;
            elseif ($revenuParPart <= 1500000)    $irParPart = ($revenuParPart - 630000) * 0.20;
            elseif ($revenuParPart <= 4000000)    $irParPart = 174000 + ($revenuParPart - 1500000) * 0.30;
            elseif ($revenuParPart <= 8000000)    $irParPart = 924000 + ($revenuParPart - 4000000) * 0.35;
            elseif ($revenuParPart <= 13500000)   $irParPart = 2324000 + ($revenuParPart - 8000000) * 0.37;
            else                                  $irParPart = 4359000 + ($revenuParPart - 13500000) * 0.40;
        }

        $irNet = max(0, $irParPart * $nbParts);
        return round($irNet / 12); // Mensualisation
    }

    /**
     * Calcule le bulletin de paie complet
     *
     * @param array $employe   Données de l'employé (depuis DB)
     * @param array $elements  Éléments variables du mois (heures supp, primes, etc.)
     * @param array $params    Paramètres optionnels : taux overrides + regime_fiscal
     */
    /**
     * Calcule la prime d'ancienneté selon le Code du travail sénégalais.
     * Base : 3% du salaire de base par an d'ancienneté, plafonné à 15 ans (45%).
     * La prime s'applique à partir de 2 ans d'ancienneté.
     */
    public static function calculerPrimeAnciennete(float $salaireBase, ?string $dateEmbauche): float {
        if (!$dateEmbauche || !$salaireBase) return 0;
        $embauche = new \DateTime($dateEmbauche);
        $now = new \DateTime();
        $annees = (int)$embauche->diff($now)->y;
        if ($annees < 2) return 0;
        $taux = min($annees * 0.03, 0.45); // 3% par an, max 45% à 15 ans
        return round($salaireBase * $taux);
    }

    public static function calculerBulletin(array $employe, array $elements = [], array $params = []): array {
        // Resolve regime — CFCE = 0 si CGU (absorbé dans CGU)
        $regime_fiscal = $params['regime_fiscal'] ?? 'CGI';
        $cfce_taux = in_array($regime_fiscal, ['CGU', 'MICRO', 'RNS']) ? 0.0
                   : (float)($params['cfce_taux'] ?? self::CFCE_TAUX);
        // =============================================
        // 1. CALCUL DU SALAIRE BRUT
        // =============================================
        $salaire_base          = (float)($elements['salaire_base']           ?? $employe['salaire_base']);
        $sursalaire            = (float)($elements['sursalaire']             ?? $employe['sursalaire']);
        $indemnite_logement    = (float)($elements['indemnite_logement']     ?? $employe['indemnite_logement']);
        $indemnite_transport   = (float)($elements['indemnite_transport']    ?? $employe['indemnite_transport']);
        $indemnite_represent   = (float)($elements['indemnite_representation']?? $employe['indemnite_representation']);
        $autres_indemnites     = (float)($elements['autres_indemnites']      ?? $employe['autres_indemnites']);
        $heures_supp           = (float)($elements['heures_supp']            ?? 0);
        $primes                = (float)($elements['primes']                 ?? 0);
        $deduction_absence     = (float)($elements['deduction_absence']      ?? 0);

        // Prime d'ancienneté — calculée auto si non forcée manuellement
        $prime_anciennete = (isset($elements['prime_anciennete']) && $elements['prime_anciennete'] !== null)
            ? (float)$elements['prime_anciennete']
            : self::calculerPrimeAnciennete($salaire_base, $employe['date_embauche'] ?? null);

        // Salaire brut = somme de tous les éléments moins déduction absences sans solde
        $salaire_brut = $salaire_base + $sursalaire
            + $indemnite_logement + $indemnite_transport
            + $indemnite_represent + $autres_indemnites
            + $heures_supp + $primes + $prime_anciennete
            - $deduction_absence;

        // =============================================
        // 2. COTISATIONS SALARIALES
        // =============================================

        // Résoudre les taux depuis $params (overrides DB) ou constantes par défaut
        $taux_ipres_sal_a = (float)($params['ipres_salarie_a']       ?? self::IPRES_SALARIE_A);
        $taux_ipres_pat_a = (float)($params['ipres_patronal_a']      ?? self::IPRES_PATRONAL_A);
        $taux_ipm_sal     = (float)($params['ipm_salarie']            ?? self::IPM_SALARIE);
        $taux_ipm_pat     = (float)($params['ipm_patronal']           ?? self::IPM_PATRONAL);
        $taux_css_at      = (float)($params['css_accidents_travail']  ?? self::CSS_ACCIDENTS_TRAVAIL);
        $plafond_ipres_a  = (float)($params['plafond_ipres_a']        ?? self::PLAFOND_IPRES_A);

        // Statut cadre : la tranche B (régime complémentaire cadres) ne s'applique
        // qu'aux employés déclarés "cadre". Pour les non-cadres, seule la tranche A joue.
        $est_cadre = ($employe['statut_cadre'] ?? 'non_cadre') === 'cadre';

        // IPRES salarié — Tranche A (plafonné)
        $base_ipres_a  = min($salaire_brut, $plafond_ipres_a);
        $ipres_salarie = round($base_ipres_a * $taux_ipres_sal_a);

        // IPRES cadre salarié — Tranche B (cadre uniquement, si salaire > plafond A)
        $ipres_cadre_salarie = 0;
        if ($est_cadre && $salaire_brut > $plafond_ipres_a) {
            $base_ipres_b        = min($salaire_brut - $plafond_ipres_a, self::PLAFOND_IPRES_B - $plafond_ipres_a);
            $ipres_cadre_salarie = round($base_ipres_b * self::IPRES_SALARIE_B);
        }
        $ipres_salarie_total = $ipres_salarie + $ipres_cadre_salarie;

        // TRIMF — barème personnalisé si défini dans les paramètres entreprise
        $bareme_trimf = self::decoderBareme($params['bareme_trimf'] ?? null);
        $trimf = self::calculerTRIMF($salaire_brut, $bareme_trimf);

        // IR — quotient familial CGI Sénégal Art. 165 :
        // célibataire/divorcé/veuf = 1 part ; marié = 1,5 part ; +0,5 par enfant à charge ; plafond 5 parts.
        $nb_parts = ($employe['situation_familiale'] === 'marie' ? 1.5 : 1.0)
            + ($employe['nombre_enfants'] ?? 0) * 0.5;
        $nb_parts = max(1.0, min((float)$nb_parts, 5.0));
        // IPM salarié — calculé avant IR (cotisation déductible du revenu imposable)
        $ipm_salarie = round($salaire_brut * $taux_ipm_sal);
        $bareme_ir = self::decoderBareme($params['bareme_ir'] ?? null);
        $ir       = self::calculerIR($salaire_brut, $nb_parts, $ipres_salarie_total + $ipm_salarie, $bareme_ir);

        // Total retenues salariales
        $total_retenues = $ipres_salarie_total + $trimf + $ir + $ipm_salarie;

        // Net à payer
        $net_a_payer = max(0, $salaire_brut - $total_retenues);

        // =============================================
        // 3. CHARGES PATRONALES
        // =============================================

        // IPRES patronal — Tranche A
        $ipres_patronal_a = round($base_ipres_a * $taux_ipres_pat_a);

        // IPRES cadre patronal — Tranche B (cadre uniquement)
        $ipres_cadre_patronal = 0;
        if ($est_cadre && $salaire_brut > $plafond_ipres_a) {
            $base_ipres_b_pat     = min($salaire_brut - $plafond_ipres_a, self::PLAFOND_IPRES_B - $plafond_ipres_a);
            $ipres_cadre_patronal = round($base_ipres_b_pat * self::IPRES_PATRONAL_B);
        }
        $ipres_patronal_total = $ipres_patronal_a + $ipres_cadre_patronal;

        // CSS — Prestations familiales (7% sur assiette plafonnée à 900 000 F, max cotisation = 63 000 F)
        $base_css_pf    = min($salaire_brut, self::CSS_PLAFOND_ASSIETTE_PF);
        $css_prestations = round($base_css_pf * self::CSS_PRESTATIONS_FAMILIALES);

        // CSS — Accidents du travail (taux paramétrable, défaut 3%)
        $css_accidents  = round($salaire_brut * $taux_css_at);

        $css_total      = $css_prestations + $css_accidents;

        // CFCE (3% salaire brut) — 0% si régime CGU, MICRO ou RNS (absorbé ou non applicable)
        $cfce           = round($salaire_brut * $cfce_taux);

        // IPM patronal
        $ipm_patronal   = round($salaire_brut * $taux_ipm_pat);

        $total_charges_patronales = $ipres_patronal_total + $css_total + $cfce + $ipm_patronal;
        $cout_total_employeur     = $salaire_brut + $total_charges_patronales;

        return [
            // Éléments de rémunération
            'salaire_base'           => $salaire_base,
            'sursalaire'             => $sursalaire,
            'indemnite_logement'     => $indemnite_logement,
            'indemnite_transport'    => $indemnite_transport,
            'indemnite_representation'=> $indemnite_represent,
            'autres_indemnites'      => $autres_indemnites,
            'heures_supp'            => $heures_supp,
            'primes'                 => $primes,
            'prime_anciennete'       => $prime_anciennete,
            'deduction_absence'      => $deduction_absence,
            'salaire_brut'           => $salaire_brut,

            // Retenues salariales
            'ipres_salarie'          => $ipres_salarie_total,
            'trimf'                  => $trimf,
            'ir_salarie'             => $ir,
            'ipm_salarie'            => $ipm_salarie,
            'total_retenues'         => $total_retenues,
            'net_a_payer'            => $net_a_payer,

            // Charges patronales
            'ipres_patronal'         => $ipres_patronal_total,
            'css_prestation'         => $css_prestations,
            'css_accident'           => $css_accidents,
            'css_total'              => $css_total,
            'cfce'                   => $cfce,
            'ipm_patronal'           => $ipm_patronal,
            'total_charges_patronales'=> $total_charges_patronales,
            'cout_total_employeur'   => $cout_total_employeur,

            // Détail IPRES pour affichage
            '_ipres_salarie_a'       => $ipres_salarie,
            '_ipres_salarie_b'       => $ipres_cadre_salarie,
            '_ipres_patronal_a'      => $ipres_patronal_a,
            '_ipres_patronal_b'      => $ipres_cadre_patronal,
            '_base_ipres_a'          => $base_ipres_a,
            '_nb_parts'              => $nb_parts,
        ];
    }

    /**
     * Calcule la TVA à partir des écritures — spécificités sénégalaises OHADA
     * Taux normal 18%, retenue à la source 30% marchés publics (4445)
     * Régimes : mensuel ou trimestriel (selon entreprises.regime_tva)
     */
    public static function calculerTVA(int $entrepriseId, int $mois, int $annee, float $creditAnterieur = 0, int $moisFin = 0): array {
        $db = getDB();

        $stmt = $db->prepare("SELECT regime_tva FROM entreprises WHERE id=?");
        $stmt->execute([$entrepriseId]);
        $ent = $stmt->fetch();
        $regime_tva = $ent['regime_tva'] ?? 'mensuel';

        // Période selon régime ou plage manuelle
        if ($moisFin > 0 && $moisFin !== $mois) {
            // Plage manuelle sélectionnée par l'utilisateur
            $dateDebut = sprintf('%04d-%02d-01', $annee, $mois);
            $dateFin   = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $annee, $moisFin)));
        } elseif ($regime_tva === 'trimestriel') {
            $trimestre = (int)ceil($mois / 3);
            $moisDebut = ($trimestre - 1) * 3 + 1;
            $moisFinTrim = $trimestre * 3;
            $dateDebut = sprintf('%04d-%02d-01', $annee, $moisDebut);
            $dateFin   = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $annee, $moisFinTrim)));
        } else {
            $dateDebut = sprintf('%04d-%02d-01', $annee, $mois);
            $dateFin   = date('Y-m-t', strtotime($dateDebut));
        }

        $fetch = function(string $likePattern, string $col) use ($db, $entrepriseId, $dateDebut, $dateFin): float {
            $stmt = $db->prepare("SELECT COALESCE(SUM(l.$col),0)
                FROM lignes_ecritures l
                JOIN comptes c ON c.id=l.compte_id
                JOIN ecritures e ON e.id=l.ecriture_id
                WHERE e.entreprise_id=? AND e.date_ecriture BETWEEN ? AND ?
                AND c.numero LIKE ?");
            $stmt->execute([$entrepriseId, $dateDebut, $dateFin, $likePattern]);
            return (float)$stmt->fetchColumn();
        };

        // TVA collectée (4431 ventes + 4432 services)
        $tva_ventes    = $fetch('4431%', 'credit');
        $tva_services  = $fetch('4432%', 'credit');
        $tva_collectee = $tva_ventes + $tva_services;

        // Retenue à la source TVA (4445) — 30% marchés publics, déduite de la collectée
        $retenue_source = $fetch('4445%', 'debit');

        // TVA déductible
        $tva_ded_biens   = $fetch('4441%', 'debit');
        $tva_ded_immo    = $fetch('4442%', 'debit');
        $tva_importation = $fetch('4443%', 'debit');

        $tva_deductible = $tva_ded_biens + $tva_ded_immo + $tva_importation;

        // Calcul net
        $tva_collectee_nette = $tva_collectee - $retenue_source;
        $tva_nette           = $tva_collectee_nette - $tva_deductible - $creditAnterieur;
        $tva_a_payer         = max(0, $tva_nette);
        $credit_report       = max(0, -$tva_nette);

        // CA HT pour vérification (comptes 7x)
        $stmtCA = $db->prepare("SELECT COALESCE(SUM(l.credit)-SUM(l.debit),0)
            FROM lignes_ecritures l JOIN comptes c ON c.id=l.compte_id JOIN ecritures e ON e.id=l.ecriture_id
            WHERE e.entreprise_id=? AND e.date_ecriture BETWEEN ? AND ? AND c.numero LIKE '7%'");
        $stmtCA->execute([$entrepriseId, $dateDebut, $dateFin]);
        $ca_ht = (float)$stmtCA->fetchColumn();

        return [
            'periode_debut'       => $dateDebut,
            'periode_fin'         => $dateFin,
            'regime_tva'          => $regime_tva,
            'tva_ventes'          => $tva_ventes,
            'tva_services'        => $tva_services,
            'tva_collectee'       => $tva_collectee,
            'retenue_source'      => $retenue_source,
            'tva_collectee_nette' => $tva_collectee_nette,
            'tva_ded_biens'       => $tva_ded_biens,
            'tva_ded_immo'        => $tva_ded_immo,
            'tva_importation'     => $tva_importation,
            'tva_deductible'      => $tva_deductible,
            'tva_nette'           => $tva_nette,
            'credit_anterieur'    => $creditAnterieur,
            'tva_a_payer'         => $tva_a_payer,
            'credit_reportable'   => $credit_report,
            'ca_ht'               => $ca_ht,
            'taux'                => 18,
        ];
    }
}
