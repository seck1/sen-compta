<?php
/**
 * RegimeFiscalService — Gestion des régimes fiscaux du Sénégal
 *
 * Régimes supportés :
 *   CGI   — Régime réel normal (grandes entreprises CA > 50M FCFA)
 *   CGU   — Contribution Globale Unique (CA 5M–50M FCFA)
 *   RNS   — Régime Non-Salarié / Professions libérales (BNC)
 *   MICRO — Micro-entreprise / Impôt Libératoire (CA < 5M FCFA)
 *   EXONERE — Zone franche / exonération IS
 */
class RegimeFiscalService {

    /**
     * Retourne les modules disponibles pour un régime donné.
     * true = applicable, false = non applicable, 'optionnel' = selon CA
     */
    public static function getModulesDisponibles(string $regime): array {
        return match($regime) {
            'CGI'    => ['tva' => true,  'is' => true,  'cfce' => true,  'patente' => true,  'cgu' => false, 'bnc' => false, 'liberatoire' => false, 'exonere' => false],
            'CGU'    => ['tva' => false, 'is' => false, 'cfce' => false, 'patente' => false, 'cgu' => true,  'bnc' => false, 'liberatoire' => false, 'exonere' => false],
            'RNS'    => ['tva' => 'optionnel', 'is' => false, 'cfce' => false, 'patente' => false, 'cgu' => false, 'bnc' => true,  'liberatoire' => false, 'exonere' => false],
            'MICRO'  => ['tva' => false, 'is' => false, 'cfce' => false, 'patente' => false, 'cgu' => false, 'bnc' => false, 'liberatoire' => true,  'exonere' => false],
            'EXONERE'=> ['tva' => true,  'is' => false, 'cfce' => true,  'patente' => false, 'cgu' => false, 'bnc' => false, 'liberatoire' => false, 'exonere' => true],
            default  => [],
        };
    }

    /**
     * Calcule la CGU annuelle (Contribution Globale Unique)
     * Remplace IS + TVA + Patente + CFCE + taxe sur véhicules
     * Taux : 5% du CA HT, avec minimum par secteur
     */
    public static function calculerCGU(float $caTtc, string $secteur = 'commerce'): array {
        // Les entreprises CGU ne sont pas assujetties à la TVA : le CA saisi est directement HT
        $caHt = $caTtc; // pas de retraitement TVA (CGI Art. 548 : base = CA HT déclaré)
        $cgu_base = $caHt * 0.05;

        $minimum = match($secteur) {
            'commerce'  => 200000,
            'services'  => 150000,
            'industrie' => 250000,
            'artisanat' => 100000,
            default     => 150000,
        };

        $cgu_due = max($minimum, $cgu_base);

        // Acomptes : 3 versements égaux (juin, septembre, décembre)
        // Solde : mars de l'année suivante
        $acompte = round($cgu_due / 4);

        return [
            'ca_ttc'               => $caTtc,
            'ca_ht'                => round($caHt, 2),
            'cgu_base'             => round($cgu_base, 2),
            'minimum_secteur'      => $minimum,
            'cgu_due'              => round($cgu_due, 2),
            'acomptes_trimestriels'=> $acompte,
            'acompte_t1'           => $acompte,  // 30 juin
            'acompte_t2'           => $acompte,  // 30 sept
            'acompte_t3'           => $acompte,  // 31 déc
            'solde'                => round($cgu_due - ($acompte * 3), 2), // 31 mars N+1
        ];
    }

    /**
     * Calcule l'IS (Impôt sur les Sociétés) — Régime CGI
     * Taux : 30% du bénéfice imposable
     * Minimum : 500 000 F ou 0.5% CA HT
     */
    public static function calculerIS(float $beneficeImposable, float $caHt): array {
        $taux = 0.30;
        $is_theorique = $beneficeImposable * $taux;
        $minimum_is   = max(500000, $caHt * 0.005);
        $is_du = $beneficeImposable > 0 ? max($minimum_is, $is_theorique) : $minimum_is;

        // Acomptes provisionnels CGI Art. 213 : 40% avril / 20% juillet / 20% novembre / 20% solde mars N+1
        $a1 = round($is_du * 0.40); // 15 avril
        $a2 = round($is_du * 0.20); // 15 juillet
        $a3 = round($is_du * 0.20); // 15 novembre
        $solde = $is_du - $a1 - $a2 - $a3; // 20% solde 31 mars N+1

        return [
            'benefice_imposable'     => $beneficeImposable,
            'taux'                   => $taux,
            'is_theorique'           => round($is_theorique, 2),
            'minimum_is'             => round($minimum_is, 2),
            'is_du'                  => round($is_du, 2),
            'acomptes_provisionnels' => $a1 + $a2 + $a3,
            'acompte_avril'          => $a1,
            'acompte_juillet'        => $a2,
            'acompte_novembre'       => $a3,
            'solde_mars'             => round($solde, 2),
        ];
    }

    /**
     * Calcule l'Impôt Libératoire (Micro-entreprise)
     * Montant fixe trimestriel selon secteur
     */
    public static function calculerImpotLiberatoire(string $secteur, int $trimestre, int $annee): array {
        $montant_trim = match($secteur) {
            'commerce'  => 12500,
            'services'  => 12500,
            'artisanat' => 6250,
            'transport' => 12500,
            default     => 12500,
        };

        // Échéances : fin de chaque trimestre
        $echeances = [
            1 => ['libelle' => '1er trimestre', 'date' => $annee . '-03-31'],
            2 => ['libelle' => '2ème trimestre', 'date' => $annee . '-06-30'],
            3 => ['libelle' => '3ème trimestre', 'date' => $annee . '-09-30'],
            4 => ['libelle' => '4ème trimestre', 'date' => $annee . '-12-31'],
        ];

        return [
            'secteur'              => $secteur,
            'trimestre'            => $trimestre,
            'annee'                => $annee,
            'montant_trimestriel'  => $montant_trim,
            'montant_annuel'       => $montant_trim * 4,
            'echeance'             => $echeances[$trimestre] ?? $echeances[1],
            'toutes_echeances'     => $echeances,
        ];
    }

    /**
     * Calcule l'IR BNC (Bénéfices Non Commerciaux) — Régime RNS
     * Barème progressif CGI Art. 163 appliqué sur bénéfice net annuel
     */
    public static function calculerIRBNC(float $beneficeNet): array {
        $ir = 0;
        if ($beneficeNet <= 630000)         $ir = 0;
        elseif ($beneficeNet <= 1500000)    $ir = ($beneficeNet - 630000) * 0.20;
        elseif ($beneficeNet <= 4000000)    $ir = 174000 + ($beneficeNet - 1500000) * 0.30;
        elseif ($beneficeNet <= 8000000)    $ir = 924000 + ($beneficeNet - 4000000) * 0.35;
        elseif ($beneficeNet <= 13500000)   $ir = 2324000 + ($beneficeNet - 8000000) * 0.37;
        else                                $ir = 4359000 + ($beneficeNet - 13500000) * 0.40;

        return [
            'benefice_net'  => $beneficeNet,
            'ir_du'         => round($ir),
            'taux_effectif' => $beneficeNet > 0 ? round(($ir / $beneficeNet) * 100, 2) : 0,
        ];
    }

    /**
     * La CFCE (Contribution Forfaitaire à la Charge de l'Employeur) s'applique-t-elle ?
     */
    public static function cfceApplicable(string $regime): bool {
        return in_array($regime, ['CGI', 'EXONERE']);
    }

    /**
     * La TVA est-elle obligatoire ?
     */
    public static function tvaObligatoire(string $regime): bool {
        return in_array($regime, ['CGI', 'EXONERE']);
    }

    /**
     * Libellé complet du régime fiscal
     */
    public static function getLabel(string $regime): string {
        return match($regime) {
            'CGI'    => 'Régime Réel Normal (CGI)',
            'CGU'    => 'Contribution Globale Unique (CGU)',
            'RNS'    => 'Régime Non-Salarié / BNC',
            'MICRO'  => 'Micro-entreprise / Impôt Libératoire',
            'EXONERE'=> 'Régime Exonéré / Zone Franche',
            default  => $regime,
        };
    }

    /**
     * Couleur badge du régime
     */
    public static function getBadgeColor(string $regime): string {
        return match($regime) {
            'CGI'    => '#1d4ed8',
            'CGU'    => '#7c3aed',
            'RNS'    => '#0891b2',
            'MICRO'  => '#15803d',
            'EXONERE'=> '#b45309',
            default  => '#6b7280',
        };
    }

    /**
     * Prochaines échéances fiscales selon régime
     * Retourne un tableau trié par date croissante
     */
    public static function getEcheances(string $regime, int $annee): array {
        $echeances = [];

        switch ($regime) {
            case 'CGI':
                $echeances = [
                    ['date' => $annee . '-01-15', 'libelle' => 'TVA décembre N-1',          'type' => 'TVA',    'urgence' => 'normal'],
                    ['date' => $annee . '-03-31', 'libelle' => 'Déclaration IS — solde',     'type' => 'IS',     'urgence' => 'high'],
                    ['date' => $annee . '-04-15', 'libelle' => 'IS — 1er acompte (40% IS N-1)', 'type' => 'IS',   'urgence' => 'normal'],
                    ['date' => $annee . '-07-15', 'libelle' => 'IS — 2ème acompte (20% IS N-1)','type' => 'IS', 'urgence' => 'normal'],
                    ['date' => $annee . '-11-15', 'libelle' => 'IS — 3ème acompte (20% IS N-1)','type' => 'IS', 'urgence' => 'normal'],
                    ['date' => $annee . '-12-31', 'libelle' => 'Patente — déclaration annuelle','type' => 'Patente','urgence' => 'normal'],
                ];
                break;

            case 'CGU':
                $echeances = [
                    ['date' => $annee . '-03-31', 'libelle' => 'CGU — solde annuel N-1',     'type' => 'CGU',    'urgence' => 'high'],
                    ['date' => $annee . '-06-30', 'libelle' => 'CGU — 1er acompte',          'type' => 'CGU',    'urgence' => 'normal'],
                    ['date' => $annee . '-09-30', 'libelle' => 'CGU — 2ème acompte',         'type' => 'CGU',    'urgence' => 'normal'],
                    ['date' => $annee . '-12-31', 'libelle' => 'CGU — 3ème acompte',         'type' => 'CGU',    'urgence' => 'normal'],
                ];
                break;

            case 'RNS':
                $echeances = [
                    ['date' => $annee . '-03-31', 'libelle' => 'IR BNC — déclaration annuelle','type' => 'IR',   'urgence' => 'high'],
                    ['date' => $annee . '-06-30', 'libelle' => 'TVA (si assujetti) — T1',    'type' => 'TVA',    'urgence' => 'normal'],
                    ['date' => $annee . '-09-30', 'libelle' => 'TVA (si assujetti) — T2',    'type' => 'TVA',    'urgence' => 'normal'],
                ];
                break;

            case 'MICRO':
                $echeances = [
                    ['date' => $annee . '-03-31', 'libelle' => 'Impôt libératoire — T1',     'type' => 'IL',     'urgence' => 'normal'],
                    ['date' => $annee . '-06-30', 'libelle' => 'Impôt libératoire — T2',     'type' => 'IL',     'urgence' => 'normal'],
                    ['date' => $annee . '-09-30', 'libelle' => 'Impôt libératoire — T3',     'type' => 'IL',     'urgence' => 'normal'],
                    ['date' => $annee . '-12-31', 'libelle' => 'Impôt libératoire — T4',     'type' => 'IL',     'urgence' => 'normal'],
                ];
                break;

            case 'EXONERE':
                $echeances = [
                    ['date' => $annee . '-01-15', 'libelle' => 'TVA décembre N-1',           'type' => 'TVA',    'urgence' => 'normal'],
                    ['date' => $annee . '-03-31', 'libelle' => 'Déclaration résultats',       'type' => 'IS',     'urgence' => 'normal'],
                ];
                break;
        }

        usort($echeances, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $echeances;
    }

    /**
     * Génère toutes les échéances fiscales annuelles selon le régime
     */
    public static function getEcheancesAnnuelles(string $regime, int $annee, int $entrepriseId = 0): array {
        $mois_fr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $e = [];

        if (in_array($regime, ['CGI', 'EXONERE'])) {
            // TVA mensuelle : 15 du mois suivant
            for ($m = 1; $m <= 12; $m++) {
                $mSuivant = $m == 12 ? 1 : $m + 1;
                $anSuivant = $m == 12 ? $annee + 1 : $annee;
                $e[] = ['type'=>'TVA', 'libelle'=>"TVA {$mois_fr[$m]} {$annee}", 'date'=>sprintf('%04d-%02d-15', $anSuivant, $mSuivant), 'montant_estime'=>null];
            }
            // IS acomptes + solde
            $e[] = ['type'=>'IS', 'libelle'=>"IS Solde ".($annee-1)." — dépôt liasse", 'date'=>"$annee-03-31", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"IS Acompte 1 ($annee) — 40%", 'date'=>"$annee-04-15", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"IS Acompte 2 ($annee) — 20%", 'date'=>"$annee-07-15", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"IS Acompte 3 ($annee) — 20%", 'date'=>"$annee-11-15", 'montant_estime'=>null];
            // CFCE + Patente annuels
            $e[] = ['type'=>'CFCE', 'libelle'=>"CFCE annuelle $annee", 'date'=>"$annee-03-31", 'montant_estime'=>null];
            $e[] = ['type'=>'Patente', 'libelle'=>"Patente $annee", 'date'=>"$annee-03-31", 'montant_estime'=>null];
        }

        if ($regime === 'CGU') {
            $e[] = ['type'=>'IS', 'libelle'=>"CGU Solde ".($annee-1), 'date'=>"$annee-03-31", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"CGU Acompte T1 $annee", 'date'=>"$annee-06-30", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"CGU Acompte T2 $annee", 'date'=>"$annee-09-30", 'montant_estime'=>null];
            $e[] = ['type'=>'IS', 'libelle'=>"CGU Acompte T3 $annee", 'date'=>"$annee-12-31", 'montant_estime'=>null];
        }

        if ($regime === 'MICRO') {
            $e[] = ['type'=>'Autre', 'libelle'=>"Impôt Libératoire T1 $annee", 'date'=>"$annee-03-31", 'montant_estime'=>12500];
            $e[] = ['type'=>'Autre', 'libelle'=>"Impôt Libératoire T2 $annee", 'date'=>"$annee-06-30", 'montant_estime'=>12500];
            $e[] = ['type'=>'Autre', 'libelle'=>"Impôt Libératoire T3 $annee", 'date'=>"$annee-09-30", 'montant_estime'=>12500];
            $e[] = ['type'=>'Autre', 'libelle'=>"Impôt Libératoire T4 $annee", 'date'=>"$annee-12-31", 'montant_estime'=>12500];
        }

        // IPRES/CSS mensuel pour tous
        for ($m = 1; $m <= 12; $m++) {
            $mSuivant = $m == 12 ? 1 : $m + 1;
            $anSuivant = $m == 12 ? $annee + 1 : $annee;
            $e[] = ['type'=>'IPRES', 'libelle'=>"IPRES/CSS {$mois_fr[$m]} {$annee}", 'date'=>sprintf('%04d-%02d-15', $anSuivant, $mSuivant), 'montant_estime'=>null];
        }

        // Taxe Foncière — avril
        $e[] = ['type'=>'TF', 'libelle'=>"Taxe Foncière $annee", 'date'=>"$annee-04-30", 'montant_estime'=>null];

        usort($e, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $e;
    }

    /**
     * Calcule le CA HT de l'exercice à partir des écritures (comptes 70x)
     */
    public static function getCaHtFromEcritures(int $entrepriseId, int $exercice): float {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(l.credit - l.debit), 0) AS ca
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ?
              AND e.exercice = ?
              AND c.numero LIKE '70%'
        ");
        $stmt->execute([$entrepriseId, $exercice]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return abs((float)($row['ca'] ?? 0));
    }
}
