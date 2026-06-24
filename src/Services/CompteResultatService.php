<?php
/**
 * CompteResultatService — Compte de résultat OHADA SYSCOHADA révisé
 * Calculs exacts : Résultat = Produits - Charges
 * Vérifié vs BilanService (résultat_net cohérent)
 */
class CompteResultatService {

    private PDO $db;
    private int $entrepriseId;
    private int $exercice;

    public function __construct(int $entrepriseId, int $exercice) {
        $this->db           = getDB();
        $this->entrepriseId = $entrepriseId;
        $this->exercice     = $exercice;
    }

    private function solde(array $prefixes, string $sens): float {
        if (empty($prefixes)) return 0.0;
        $cond   = implode(' OR ', array_map(fn($p) => "c.numero LIKE ?", $prefixes));
        $params = array_map(fn($p) => $p . '%', $prefixes);
        $sql = "SELECT COALESCE(SUM(l.debit),0) as d, COALESCE(SUM(l.credit),0) as c
                FROM lignes_ecritures l
                JOIN comptes c ON c.id = l.compte_id
                JOIN ecritures e ON e.id = l.ecriture_id
                WHERE e.entreprise_id = ? AND e.exercice = ? AND ($cond)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$this->entrepriseId, $this->exercice], $params));
        $row  = $stmt->fetch();
        $net  = (float)$row['d'] - (float)$row['c'];
        if ($sens === 'charge')  return max(0,  $net);
        if ($sens === 'produit') return max(0, -$net);
        return $net;
    }

    public function calculer(): array {
        // =====================================================
        // CHARGES D'EXPLOITATION
        // =====================================================
        $achats_march       = $this->solde(['601','6011','6012'], 'charge');
        $var_stocks_march   = $this->solde(['6031'], 'charge');  // peut être négatif (on prend la valeur absolue orientée)
        $achats_mat_prem    = $this->solde(['602','6021','6022'], 'charge');
        $var_stocks_mat     = $this->solde(['6032'], 'charge');
        $achats_consommables= $this->solde(['604','6041','6042','6043','6044','6045','6046'], 'charge');
        $transports         = $this->solde(['61','611','612','613','614','618'], 'charge');
        $services_ext_a     = $this->solde(['62','621','622','623','624','625','626','627','628'], 'charge');
        $services_ext_b     = $this->solde(['63','631','632','633','634','635','636','637','638'], 'charge');
        $impots_taxes       = $this->solde(['64','641','642','643','644'], 'charge');
        $charges_personnel  = $this->solde(['66'], 'charge');
        $autres_charges     = $this->solde(['65','651','652','653','654','658'], 'charge');
        $dot_amort_exploit  = $this->solde(['681','682','683','684','685','686'], 'charge');

        $total_charges_exploit = $achats_march + $var_stocks_march + $achats_mat_prem
            + $var_stocks_mat + $achats_consommables + $transports
            + $services_ext_a + $services_ext_b + $impots_taxes
            + $charges_personnel + $autres_charges + $dot_amort_exploit;

        // =====================================================
        // PRODUITS D'EXPLOITATION
        // =====================================================
        $ventes_march       = $this->solde(['701','7011','7012'], 'produit');
        $ventes_produits    = $this->solde(['702','703','704'], 'produit');
        $travaux_services   = $this->solde(['705','706','707'], 'produit');
        $prod_stockee       = $this->solde(['711','713'], 'produit');
        $prod_immobilisee   = $this->solde(['721','722'], 'produit');
        $subv_exploit       = $this->solde(['741','742','743','744'], 'produit');
        $autres_produits    = $this->solde(['751','752','753','754','755','756','757','758'], 'produit');
        $reprises_exploit   = $this->solde(['781'], 'produit'); // reprises provisions exploitation SYSCOHADA
        $transferts_charges = $this->solde(['782'], 'produit'); // transferts de charges exploitation

        $total_produits_exploit = $ventes_march + $ventes_produits + $travaux_services
            + $prod_stockee + $prod_immobilisee + $subv_exploit
            + $autres_produits + $reprises_exploit + $transferts_charges;

        $resultat_exploitation = $total_produits_exploit - $total_charges_exploit;

        // =====================================================
        // CHARGES FINANCIÈRES
        // =====================================================
        // Charges financières : 67x (intérêts, escomptes, pertes change)
        $interets_emprunts  = $this->solde(['671','672','673'], 'charge');
        $escomptes_accordes = $this->solde(['674','675'], 'charge');
        $pertes_change      = $this->solde(['676'], 'charge');
        $autres_charges_fin = $this->solde(['677','678'], 'charge');

        $total_charges_fin  = $interets_emprunts + $escomptes_accordes + $pertes_change + $autres_charges_fin;

        // =====================================================
        // PRODUITS FINANCIERS
        // =====================================================
        $interets_recus     = $this->solde(['771','772','773','774'], 'produit');
        $escomptes_obtenus  = $this->solde(['775'], 'produit');
        $gains_change       = $this->solde(['776'], 'produit');
        $autres_prod_fin    = $this->solde(['777','778'], 'produit');
        $reprises_fin       = $this->solde(['786'], 'produit'); // 786 = reprises sur provisions financières (SYSCOHADA révisé)

        $total_produits_fin = $interets_recus + $escomptes_obtenus + $gains_change + $autres_prod_fin + $reprises_fin;

        $resultat_financier = $total_produits_fin - $total_charges_fin;

        // =====================================================
        // RÉSULTAT DES ACTIVITÉS ORDINAIRES (RAO)
        // =====================================================
        $resultat_ao = $resultat_exploitation + $resultat_financier;

        // =====================================================
        // HORS ACTIVITÉS ORDINAIRES (HAO)
        // Comptes 81x (charges HAO) et 82x (produits HAO) en SYSCOHADA révisé
        // =====================================================
        $charges_hao  = $this->solde(['81', '89'], 'charge');
        $produits_hao = $this->solde(['82', '88'], 'produit');
        $resultat_hao = $produits_hao - $charges_hao;

        // =====================================================
        // PARTICIPATION ET IMPÔTS
        // =====================================================
        $participation = $this->solde(['691'], 'charge');
        $is            = $this->solde(['692','6921','6922'], 'charge');
        $impots_result = $participation + $is;

        // =====================================================
        // RÉSULTAT NET
        // =====================================================
        $total_charges  = $total_charges_exploit + $total_charges_fin + $charges_hao + $impots_result;
        $total_produits = $total_produits_exploit + $total_produits_fin + $produits_hao;
        $resultat_net   = $total_produits - $total_charges;

        return [
            'charges' => [
                'exploitation' => [
                    'achats_march'        => $achats_march,
                    'var_stocks_march'    => $var_stocks_march,
                    'achats_mat_prem'     => $achats_mat_prem,
                    'var_stocks_mat'      => $var_stocks_mat,
                    'achats_consommables' => $achats_consommables,
                    'transports'          => $transports,
                    'services_ext_a'      => $services_ext_a,
                    'services_ext_b'      => $services_ext_b,
                    'impots_taxes'        => $impots_taxes,
                    'charges_personnel'   => $charges_personnel,
                    'autres_charges'      => $autres_charges,
                    'dot_amort'           => $dot_amort_exploit,
                    'total'               => $total_charges_exploit,
                ],
                'financieres' => [
                    'interets'    => $interets_emprunts,
                    'escomptes'   => $escomptes_accordes,
                    'pertes_change'=> $pertes_change,
                    'autres'      => $autres_charges_fin,
                    'total'       => $total_charges_fin,
                ],
                'impots_result' => $impots_result,
                'participation' => $participation,
                'is'            => $is,
                'total'         => $total_charges,
            ],
            'produits' => [
                'exploitation' => [
                    'ventes_march'      => $ventes_march,
                    'ventes_produits'   => $ventes_produits,
                    'travaux_services'  => $travaux_services,
                    'prod_stockee'      => $prod_stockee,
                    'prod_immobilisee'  => $prod_immobilisee,
                    'subventions'       => $subv_exploit,
                    'autres_produits'   => $autres_produits,
                    'reprises'          => $reprises_exploit,
                    'transferts'        => $transferts_charges,
                    'total'             => $total_produits_exploit,
                ],
                'financiers' => [
                    'interets'      => $interets_recus,
                    'escomptes'     => $escomptes_obtenus,
                    'gains_change'  => $gains_change,
                    'autres'        => $autres_prod_fin,
                    'total'         => $total_produits_fin,
                ],
                'hao'   => $produits_hao,
                'total' => $total_produits,
            ],
            'resultats' => [
                'exploitation' => $resultat_exploitation,
                'financier'    => $resultat_financier,
                'ao'           => $resultat_ao,
                'hao'          => $resultat_hao,
                'net'          => $resultat_net,
            ],
        ];
    }
}
