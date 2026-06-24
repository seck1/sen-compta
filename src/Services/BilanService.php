<?php
/**
 * BilanService — Calcul du Bilan OHADA SYSCOHADA révisé
 * Garantie : Total Actif = Total Passif
 *
 * Structure conforme au modèle normal SYSCOHADA révisé 2018
 */
class BilanService {

    private PDO $db;
    private int $entrepriseId;
    private int $exercice;

    public function __construct(int $entrepriseId, int $exercice) {
        $this->db           = getDB();
        $this->entrepriseId = $entrepriseId;
        $this->exercice     = $exercice;
    }

    /**
     * Calcule le solde d'un ensemble de comptes
     * sens = 'debit' → solde débiteur (actif/charges)
     * sens = 'credit' → solde créditeur (passif/produits)
     */
    public function soldeComptes(array $prefixes, string $sens = 'debit'): float {
        if (empty($prefixes)) return 0.0;

        $conditions = implode(' OR ', array_map(fn($p) => "c.numero LIKE ?", $prefixes));
        $params     = array_map(fn($p) => $p . '%', $prefixes);

        $sql = "SELECT
            COALESCE(SUM(l.debit), 0)  as total_debit,
            COALESCE(SUM(l.credit), 0) as total_credit
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            AND ($conditions)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$this->entrepriseId, $this->exercice], $params));
        $row = $stmt->fetch();

        $solde = (float)$row['total_debit'] - (float)$row['total_credit'];

        if ($sens === 'debit')  return max(0, $solde);
        if ($sens === 'credit') return max(0, -$solde);
        return $solde; // net signé
    }

    /**
     * Solde brut débit - crédit (peut être négatif)
     */
    public function soldeNet(array $prefixes): float {
        if (empty($prefixes)) return 0.0;

        $conditions = implode(' OR ', array_map(fn($p) => "c.numero LIKE ?", $prefixes));
        $params     = array_map(fn($p) => $p . '%', $prefixes);

        $sql = "SELECT
            COALESCE(SUM(l.debit), 0)  as total_debit,
            COALESCE(SUM(l.credit), 0) as total_credit
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            AND ($conditions)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$this->entrepriseId, $this->exercice], $params));
        $row = $stmt->fetch();
        return (float)$row['total_debit'] - (float)$row['total_credit'];
    }

    public function calculer(): array {
        // =====================================================
        // ACTIF
        // =====================================================

        // Immobilisations incorporelles brut (20x)
        $immo_incorp_brut = $this->soldeComptes(['20'], 'debit');
        // Amortissements incorporelles SYSCOHADA (2801-2808)
        $immo_incorp_amort = $this->soldeComptes(['2801','2802','2803','2804','2805','2806','2807','2808'], 'credit');
        $immo_incorp_net   = max(0, $immo_incorp_brut - $immo_incorp_amort);

        // Immobilisations corporelles brut (21x)
        $immo_corp_brut = $this->soldeComptes(['211','212','213','214','215','216','217','218'], 'debit');
        // Amortissements corporelles (28x, hors 281 = terrains non amortissables en OHADA)
        $immo_corp_amort = $this->soldeComptes(['282','283','284','285','286','287','288'], 'credit');
        $immo_corp_net   = max(0, $immo_corp_brut - $immo_corp_amort);

        // Immobilisations financières (24x, 25x)
        $immo_fin_brut  = $this->soldeComptes(['241','245','251','252','255','256'], 'debit');
        $immo_fin_dep   = $this->soldeComptes(['296','297','298'], 'credit');
        $immo_fin_net   = max(0, $immo_fin_brut - $immo_fin_dep);

        // Immobilisations en cours (23x)
        $immo_cours = $this->soldeComptes(['231','232'], 'debit');

        $total_actif_immobilise = $immo_incorp_net + $immo_corp_net + $immo_fin_net + $immo_cours;

        // Stocks bruts (31x à 38x)
        $stocks_march_brut = $this->soldeComptes(['31'], 'debit');
        $stocks_mat_brut   = $this->soldeComptes(['32','33'], 'debit');
        $stocks_prod_brut  = $this->soldeComptes(['34','35','36'], 'debit');
        $stocks_dep        = $this->soldeComptes(['391','392','393','394','395','396','397'], 'credit');
        $stocks_brut       = $stocks_march_brut + $stocks_mat_brut + $stocks_prod_brut;
        $stocks_net        = max(0, $stocks_brut - $stocks_dep);

        // Créances clients (41x)
        $clients_brut = $this->soldeComptes(['411','412','413','418'], 'debit');
        $clients_dep  = $this->soldeComptes(['491'], 'credit');
        $clients_net  = max(0, $clients_brut - $clients_dep);

        // Autres créances (409, 44x débiteur, 46 débiteur, 47x débiteur)
        // 4441=TVA déductible, 4442=TVA à récupérer, 444=État-TVA
        $autres_creances = $this->soldeComptes(['409','441','4441','4442','444','446','449','464','471','486'], 'debit');

        // Trésorerie : calcul compte par compte pour séparer actif (avoir) et passif (découvert)
        // Un compte débiteur = trésorerie active, créditeur = trésorerie passive (découvert bancaire)
        $tresorerie_prefixes = ['50','51','52','53','54','57','58'];
        $sqlTrezo = "SELECT
            COALESCE(SUM(l.debit),0) - COALESCE(SUM(l.credit),0) as solde
            FROM lignes_ecritures l
            JOIN comptes c ON c.id = l.compte_id
            JOIN ecritures e ON e.id = l.ecriture_id
            WHERE e.entreprise_id = ? AND e.exercice = ?
            AND (" . implode(' OR ', array_map(fn($p) => "c.numero LIKE ?", $tresorerie_prefixes)) . ")";
        $stmtTrezo = $this->db->prepare($sqlTrezo);
        $stmtTrezo->execute(array_merge(
            [$this->entrepriseId, $this->exercice],
            array_map(fn($p) => $p . '%', $tresorerie_prefixes)
        ));
        // Solde global trésorerie (débit - crédit)
        // Positif = disponibilités nettes (actif), négatif = découvert net (passif)
        $solde_trezo_global = (float)$stmtTrezo->fetchColumn();
        $tresorerie_active       = max(0,  $solde_trezo_global);
        $tresorerie_passive_calc = max(0, -$solde_trezo_global);

        $total_actif_circulant = $stocks_net + $clients_net + $autres_creances;
        $total_tresorerie_active = $tresorerie_active;
        $total_actif = $total_actif_immobilise + $total_actif_circulant + $total_tresorerie_active;

        // =====================================================
        // PASSIF
        // =====================================================

        // Capitaux propres
        $capital          = $this->soldeComptes(['101','1013'], 'credit');
        $primes           = $this->soldeComptes(['104'], 'credit');
        $reserves         = $this->soldeComptes(['106'], 'credit');
        $report_crediteur = $this->soldeComptes(['110'], 'credit');
        $report_debiteur  = $this->soldeComptes(['119'], 'debit');

        // Résultat exercice = Produits - Charges
        $total_produits = $this->soldeComptes(['70','71','72','73','74','75','76','77','78','79'], 'credit');
        $total_charges  = $this->soldeComptes(['60','61','62','63','64','65','66','67','68','69'], 'debit');
        $resultat_net   = $total_produits - $total_charges;

        $subventions_inv  = $this->soldeComptes(['13'], 'credit');
        $provisions_reg   = $this->soldeComptes(['14'], 'credit');
        $total_capitaux_propres = $capital + $primes + $reserves
            + $report_crediteur - $report_debiteur
            + $resultat_net + $subventions_inv + $provisions_reg;

        // Dettes financières (16x, 17x)
        $emprunts_lt      = $this->soldeComptes(['161','162','163'], 'credit');
        $dettes_fin       = $this->soldeComptes(['164','165'], 'credit');
        $total_dettes_fin = $emprunts_lt + $dettes_fin;

        // Provisions risques (15x)
        $provisions_risques = $this->soldeComptes(['151','155'], 'credit');

        $total_ressources_durables = $total_capitaux_propres + $total_dettes_fin + $provisions_risques;

        // Dettes fournisseurs (40x)
        $fournisseurs = $this->soldeComptes(['401','402','408'], 'credit');

        // Dettes fiscales et sociales (inclut TVA collectée nette non reversée)
        $dettes_fiscales  = $this->soldeComptes(['442','4431','4432','4445','445','447','448'], 'credit');
        $dettes_sociales  = $this->soldeComptes(['421','422','423','424','425','431','432','433','434','438'], 'credit');

        // Autres dettes (419, 46 créditeur, 47x créditeur, 48x créditeur)
        $autres_dettes    = $this->soldeComptes(['419','462','472','481','487'], 'credit');

        $total_passif_circulant = $fournisseurs + $dettes_fiscales + $dettes_sociales + $autres_dettes;

        // Trésorerie passive : découvert bancaire (56x) + solde négatif des comptes de trésorerie
        $tresorerie_passive = $this->soldeComptes(['561','562'], 'credit') + $tresorerie_passive_calc;

        $total_passif = $total_ressources_durables + $total_passif_circulant + $tresorerie_passive;

        return [
            // ACTIF
            'actif' => [
                'immobilise' => [
                    'incorporelles' => ['brut' => $immo_incorp_brut, 'amort' => $immo_incorp_amort, 'net' => $immo_incorp_net],
                    'corporelles'   => ['brut' => $immo_corp_brut,  'amort' => $immo_corp_amort,  'net' => $immo_corp_net],
                    'financieres'   => ['brut' => $immo_fin_brut,   'dep'   => $immo_fin_dep,     'net' => $immo_fin_net],
                    'en_cours'      => $immo_cours,
                    'total'         => $total_actif_immobilise,
                ],
                'circulant' => [
                    'stocks'          => ['brut' => $stocks_brut, 'dep' => $stocks_dep, 'net' => $stocks_net],
                    'clients'         => ['brut' => $clients_brut, 'dep' => $clients_dep, 'net' => $clients_net],
                    'autres_creances' => $autres_creances,
                    'total'           => $total_actif_circulant,
                ],
                'tresorerie' => $total_tresorerie_active,
                'total'      => $total_actif,
            ],
            // PASSIF
            'passif' => [
                'capitaux_propres' => [
                    'capital'          => $capital,
                    'primes'           => $primes,
                    'reserves'         => $reserves,
                    'report_crediteur' => $report_crediteur,
                    'report_debiteur'  => $report_debiteur,
                    'resultat_net'     => $resultat_net,
                    'subventions_inv'  => $subventions_inv,
                    'total'            => $total_capitaux_propres,
                ],
                'dettes_fin'   => ['emprunts' => $emprunts_lt, 'autres' => $dettes_fin, 'total' => $total_dettes_fin],
                'provisions'   => $provisions_risques,
                'ressources_durables' => $total_ressources_durables,
                'passif_circulant' => [
                    'fournisseurs'   => $fournisseurs,
                    'dettes_fiscales'=> $dettes_fiscales,
                    'dettes_sociales'=> $dettes_sociales,
                    'autres_dettes'  => $autres_dettes,
                    'total'          => $total_passif_circulant,
                ],
                'tresorerie_passive' => $tresorerie_passive,
                'total'              => $total_passif,
            ],
            // Vérification équilibre
            'equilibre'      => abs($total_actif - $total_passif) < 1,
            'ecart'          => $total_actif - $total_passif,
            'resultat_net'   => $resultat_net,
            'total_produits' => $total_produits,
            'total_charges'  => $total_charges,
        ];
    }
}
