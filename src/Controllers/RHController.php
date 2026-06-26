<?php
require_once APP_ROOT . '/src/Services/PaieService.php';

class RHController {

    private function getEntrepriseAccess(int $id): array {
        requireAuth();
        $entreprise = getEntreprise($id);
        if (empty($entreprise)) { http_response_code(404); echo "Dossier introuvable"; exit; }
        if (!userHasAccess($id)) { redirect('/dashboard'); }
        return $entreprise;
    }

    public function index(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $filtre_statut = $_GET['statut'] ?? '';
        $sql = "SELECT * FROM employes WHERE entreprise_id = ?";
        $params = [$id];
        if ($filtre_statut) { $sql .= " AND statut = ?"; $params[] = $filtre_statut; }
        $sql .= " ORDER BY nom, prenom";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Ressources Humaines';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/index.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function creerEmploye(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $employe = null;
        $pageTitle = 'Nouvel employé';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeEmploye(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO employes
            (entreprise_id, matricule, nom, prenom, date_naissance, lieu_naissance, sexe, nationalite,
             num_cni, situation_familiale, nombre_enfants, telephone, email, adresse, lieu_travail,
             poste, departement, categorie, statut_cadre, date_embauche, date_fin_contrat, periode_essai_mois, type_contrat, statut,
             regime_fiscal, nombre_parts,
             salaire_base, sursalaire, autres_indemnites, indemnite_logement, indemnite_transport, indemnite_representation,
             num_ipres, num_css, num_ipm, mode_paiement, banque, iban)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $id,
            $_POST['matricule'] ?? '',
            $_POST['nom'] ?? '',
            $_POST['prenom'] ?? '',
            $_POST['date_naissance'] ?: null,
            $_POST['lieu_naissance'] ?? '',
            $_POST['sexe'] ?? 'M',
            $_POST['nationalite'] ?? 'Sénégalaise',
            $_POST['num_cni'] ?? '',
            $_POST['situation_familiale'] ?? 'celibataire',
            (int)($_POST['nombre_enfants'] ?? 0),
            $_POST['telephone'] ?? '',
            $_POST['email'] ?? '',
            $_POST['adresse'] ?? '',
            $_POST['lieu_travail'] ?? '',
            $_POST['poste'] ?? '',
            $_POST['departement'] ?? '',
            $_POST['categorie'] ?? '',
            ($_POST['statut_cadre'] ?? 'non_cadre') === 'cadre' ? 'cadre' : 'non_cadre',
            $_POST['date_embauche'] ?: null,
            $_POST['date_fin_contrat'] ?: null,
            (int)($_POST['periode_essai_mois'] ?? 0),
            $_POST['type_contrat'] ?? 'CDI',
            $_POST['statut'] ?? 'actif',
            $_POST['regime_fiscal'] ?? 'imposable',
            (float)($_POST['nombre_parts'] ?? 1.0),
            (float)($_POST['salaire_base'] ?? 0),
            (float)($_POST['sursalaire'] ?? 0),
            (float)($_POST['autres_indemnites'] ?? 0),
            (float)($_POST['indemnite_logement'] ?? 0),
            (float)($_POST['indemnite_transport'] ?? 0),
            (float)($_POST['indemnite_representation'] ?? 0),
            $_POST['num_ipres'] ?? '',
            $_POST['num_css'] ?? '',
            $_POST['num_ipm'] ?? '',
            $_POST['mode_paiement'] ?? 'virement',
            $_POST['banque'] ?? '',
            $_POST['iban'] ?? '',
        ]);

        redirect("/dossier/rh?id=$id&created=1");
    }

    public function editEmploye(): void {
        $id = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM employes WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([$employe_id, $id]);
        $employe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$employe) { http_response_code(404); echo "Employé introuvable"; exit; }

        $pageTitle = 'Modifier employé';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function updateEmploye(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $employe_id = (int)($_POST['employe_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $db = getDB();
        $stmt = $db->prepare("UPDATE employes SET
            matricule=?, nom=?, prenom=?, date_naissance=?, lieu_naissance=?, sexe=?, nationalite=?,
            num_cni=?, situation_familiale=?, nombre_enfants=?, telephone=?, email=?, adresse=?, lieu_travail=?,
            poste=?, departement=?, categorie=?, statut_cadre=?, date_embauche=?, date_fin_contrat=?, periode_essai_mois=?, type_contrat=?, statut=?,
            regime_fiscal=?, nombre_parts=?,
            salaire_base=?, sursalaire=?, autres_indemnites=?, indemnite_logement=?, indemnite_transport=?, indemnite_representation=?,
            num_ipres=?, num_css=?, num_ipm=?, mode_paiement=?, banque=?, iban=?
            WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([
            $_POST['matricule'] ?? '',
            $_POST['nom'] ?? '',
            $_POST['prenom'] ?? '',
            $_POST['date_naissance'] ?: null,
            $_POST['lieu_naissance'] ?? '',
            $_POST['sexe'] ?? 'M',
            $_POST['nationalite'] ?? 'Sénégalaise',
            $_POST['num_cni'] ?? '',
            $_POST['situation_familiale'] ?? 'celibataire',
            (int)($_POST['nombre_enfants'] ?? 0),
            $_POST['telephone'] ?? '',
            $_POST['email'] ?? '',
            $_POST['adresse'] ?? '',
            $_POST['lieu_travail'] ?? '',
            $_POST['poste'] ?? '',
            $_POST['departement'] ?? '',
            $_POST['categorie'] ?? '',
            ($_POST['statut_cadre'] ?? 'non_cadre') === 'cadre' ? 'cadre' : 'non_cadre',
            $_POST['date_embauche'] ?: null,
            $_POST['date_fin_contrat'] ?: null,
            (int)($_POST['periode_essai_mois'] ?? 0),
            $_POST['type_contrat'] ?? 'CDI',
            $_POST['statut'] ?? 'actif',
            $_POST['regime_fiscal'] ?? 'imposable',
            (float)($_POST['nombre_parts'] ?? 1.0),
            (float)($_POST['salaire_base'] ?? 0),
            (float)($_POST['sursalaire'] ?? 0),
            (float)($_POST['autres_indemnites'] ?? 0),
            (float)($_POST['indemnite_logement'] ?? 0),
            (float)($_POST['indemnite_transport'] ?? 0),
            (float)($_POST['indemnite_representation'] ?? 0),
            $_POST['num_ipres'] ?? '',
            $_POST['num_css'] ?? '',
            $_POST['num_ipm'] ?? '',
            $_POST['mode_paiement'] ?? 'virement',
            $_POST['banque'] ?? '',
            $_POST['iban'] ?? '',
            $employe_id, $id
        ]);

        redirect("/dossier/rh?id=$id&updated=1");
    }

    public function bulletins(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $filtre_mois  = (int)($_GET['mois'] ?? 0);
        $filtre_annee = (int)($_GET['annee'] ?? 0);

        $sql = "SELECT b.*, e.nom, e.prenom, e.matricule, e.poste
                FROM bulletins_paie b
                JOIN employes e ON e.id = b.employe_id
                WHERE b.entreprise_id = ?";
        $params = [$id];
        if ($filtre_mois)  { $sql .= " AND b.periode_mois = ?";  $params[] = $filtre_mois; }
        if ($filtre_annee) { $sql .= " AND b.periode_annee = ?"; $params[] = $filtre_annee; }
        $sql .= " ORDER BY b.periode_annee DESC, b.periode_mois DESC, e.nom";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bulletins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Bulletins de paie';
        $activeTab = 'bulletins';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/bulletins.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function creerBulletin(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM employes WHERE entreprise_id = ? AND statut = 'actif' ORDER BY nom");
        $stmt->execute([$id]);
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Générer bulletin de paie';
        $activeTab = 'bulletins';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/bulletin-form.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeBulletin(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        $employe_id = (int)$_POST['employe_id'];
        $mois  = (int)$_POST['mois'];
        $annee = (int)$_POST['annee'];

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM employes WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([$employe_id, $id]);
        $employe = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$employe) { redirect("/dossier/rh/bulletins?id=$id"); }

        // Fix L — Bloquer l'écrasement d'un bulletin déjà validé
        $stmtCheck = $db->prepare("SELECT statut FROM bulletins_paie WHERE employe_id=? AND entreprise_id=? AND periode_mois=? AND periode_annee=?");
        $stmtCheck->execute([$employe_id, $id, $mois, $annee]);
        $existing = $stmtCheck->fetch();
        if ($existing && $existing['statut'] === 'valide') {
            redirect("/dossier/rh/bulletins?id=$id&error=bulletin_valide");
            return;
        }

        // Détecter les congés approuvés sur la période
        $date_debut_mois = sprintf('%04d-%02d-01', $annee, $mois);
        $date_fin_mois   = date('Y-m-t', strtotime($date_debut_mois));

        $stmtConges = $db->prepare("
            SELECT type_conge, SUM(nb_jours) as total_jours
            FROM conges
            WHERE employe_id = ? AND entreprise_id = ?
              AND statut = 'approuve'
              AND date_debut <= ? AND date_fin >= ?
            GROUP BY type_conge
        ");
        $stmtConges->execute([$employe_id, $id, $date_fin_mois, $date_debut_mois]);
        $conges_mois = $stmtConges->fetchAll(PDO::FETCH_ASSOC);

        // Calcul jours ouvrés du mois (lun-sam)
        $jours_ouvres_mois = 0;
        $cur = new DateTime($date_debut_mois);
        $fin = new DateTime($date_fin_mois);
        while ($cur <= $fin) {
            if ($cur->format('N') != 7) $jours_ouvres_mois++;
            $cur->modify('+1 day');
        }

        // Déduction sans solde uniquement
        $jours_sans_solde = 0;
        foreach ($conges_mois as $c) {
            if ($c['type_conge'] === 'sans_solde') {
                $jours_sans_solde += (int)$c['total_jours'];
            }
        }
        $deduction_absence = 0;
        if ($jours_sans_solde > 0 && $jours_ouvres_mois > 0) {
            $deduction_absence = round(($employe['salaire_base'] / $jours_ouvres_mois) * $jours_sans_solde);
        }

        // Load entreprise regime for CFCE exemption (CGU, MICRO, RNS = 0% CFCE)
        $entreprise = getEntreprise($id);
        $params = [
            'regime_fiscal' => $entreprise['regime_fiscal'] ?? 'CGI',
        ];

        // Load paie_parametres if available
        try {
            $pStmt = $db->prepare("SELECT * FROM paie_parametres WHERE entreprise_id = ? LIMIT 1");
            $pStmt->execute([$id]);
            $paieParams = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($paieParams) {
                $params = array_merge($params, $paieParams);
            }
        } catch (Exception $e) {
            // paie_parametres table may not exist — ignore
        }

        // Convertir les heures supplémentaires en montant FCFA (Fix P: $params chargé avant)
        $nb_heures_supp = (float)($_POST['heures_supp'] ?? 0);
        $heures_supp_montant = 0;
        if ($nb_heures_supp > 0 && $employe['salaire_base'] > 0) {
            $taux_horaire = $employe['salaire_base'] / 173; // 173h légales/mois
            $seuil = (int)($params['heures_supp_seuil'] ?? 8);
            $taux1 = (float)($params['heures_supp_taux1'] ?? 1.15);
            $taux2 = (float)($params['heures_supp_taux2'] ?? 1.40);
            $h1 = min($nb_heures_supp, $seuil);
            $h2 = max(0, $nb_heures_supp - $seuil);
            $heures_supp_montant = round($h1 * $taux_horaire * $taux1 + $h2 * $taux_horaire * $taux2);
        }

        $elements = [
            'heures_supp'       => $heures_supp_montant,
            'prime_saisie'      => (float)($_POST['prime_saisie'] ?? 0),
            'deduction_absence' => $deduction_absence,
            'prime_anciennete'  => !empty($_POST['prime_anciennete_override']) ? (float)($_POST['prime_anciennete'] ?? 0) : null,
        ];

        $bulletin = PaieService::calculerBulletin($employe, $elements, $params);

        // Save
        $stmt = $db->prepare("INSERT INTO bulletins_paie
            (entreprise_id, employe_id, user_id, periode_mois, periode_annee,
             salaire_base, sursalaire, indemnite_transport, indemnite_logement, indemnite_representation,
             prime_anciennete, heures_supp, primes, salaire_brut,
             ipres_salarie, trimf, ir_salarie, ipm_salarie,
             total_retenues, net_a_payer,
             ipres_patronal, css_accident, css_prestation, css_total, cfce, ipm_patronal,
             total_charges_patronales, cout_total_employeur, statut)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'brouillon')
            ON DUPLICATE KEY UPDATE
             salaire_base=VALUES(salaire_base), sursalaire=VALUES(sursalaire),
             indemnite_transport=VALUES(indemnite_transport), indemnite_logement=VALUES(indemnite_logement),
             indemnite_representation=VALUES(indemnite_representation),
             prime_anciennete=VALUES(prime_anciennete),
             heures_supp=VALUES(heures_supp), primes=VALUES(primes),
             salaire_brut=VALUES(salaire_brut),
             ipres_salarie=VALUES(ipres_salarie), trimf=VALUES(trimf),
             ir_salarie=VALUES(ir_salarie), ipm_salarie=VALUES(ipm_salarie),
             total_retenues=VALUES(total_retenues), net_a_payer=VALUES(net_a_payer),
             ipres_patronal=VALUES(ipres_patronal), css_accident=VALUES(css_accident),
             css_prestation=VALUES(css_prestation), css_total=VALUES(css_total),
             cfce=VALUES(cfce), ipm_patronal=VALUES(ipm_patronal),
             total_charges_patronales=VALUES(total_charges_patronales),
             cout_total_employeur=VALUES(cout_total_employeur),
             statut='brouillon'");
        $stmt->execute([
            $id, $employe_id, auth()['id'], $mois, $annee,
            $bulletin['salaire_base'],
            $bulletin['sursalaire'],
            $bulletin['indemnite_transport'],
            $bulletin['indemnite_logement'],
            $bulletin['indemnite_representation'],
            $bulletin['prime_anciennete'],
            $bulletin['heures_supp'],
            $bulletin['primes'],
            $bulletin['salaire_brut'],
            $bulletin['ipres_salarie'],
            $bulletin['trimf'],
            $bulletin['ir_salarie'],
            $bulletin['ipm_salarie'],
            $bulletin['total_retenues'],
            $bulletin['net_a_payer'],
            $bulletin['ipres_patronal'],
            $bulletin['css_accident'],
            $bulletin['css_prestation'],
            $bulletin['css_total'],
            $bulletin['cfce'],
            $bulletin['ipm_patronal'],
            $bulletin['total_charges_patronales'],
            $bulletin['cout_total_employeur'],
        ]);
        $bulletin_id = (int)$db->lastInsertId();
        if ($bulletin_id === 0) {
            $stmtId = $db->prepare("SELECT id FROM bulletins_paie WHERE employe_id=? AND entreprise_id=? AND periode_mois=? AND periode_annee=?");
            $stmtId->execute([$employe_id, $id, $mois, $annee]);
            $bulletin_id = (int)$stmtId->fetchColumn();
        }

        // Générer les écritures comptables automatiquement
        $this->genererEcrituresPaie($db, $id, $bulletin, $employe, $mois, $annee, $bulletin_id);

        redirect("/dossier/rh/bulletin?id=$id&bulletin_id=$bulletin_id");
    }

    private function genererEcrituresPaie($db, $entreprise_id, $bulletin, $employe, $mois, $annee, $bulletin_id): void {
        try {
            // Journal de Paie
            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='PAI' LIMIT 1");
            $jStmt->execute([$entreprise_id]);
            $journal = $jStmt->fetch(PDO::FETCH_ASSOC);
            if (!$journal) return;
            $journal_id = $journal['id'];

            $user_id = auth()['id'];
            $date_ecriture = sprintf('%04d-%02d-28', $annee, $mois);
            $mois_labels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
            $libelle_base = "Paie {$mois_labels[$mois]} $annee — {$employe['prenom']} {$employe['nom']}";
            $num_piece = "PAI-" . sprintf('%02d', $mois) . "-$annee-" . $employe['matricule'];

            // Supprimer les écritures existantes pour ce bulletin (recalcul)
            $db->prepare("DELETE e FROM ecritures e
                JOIN lignes_ecritures l ON l.ecriture_id = e.id
                WHERE e.entreprise_id=? AND e.numero_piece=?"
            )->execute([$entreprise_id, $num_piece]);

            // Helper: récupérer compte_id
            $getCompte = function($numero) use ($db, $entreprise_id) {
                $s = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $s->execute([$entreprise_id, $numero]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                return $r ? (int)$r['id'] : null;
            };

            $brut       = (float)$bulletin['salaire_brut'];
            $net        = (float)$bulletin['net_a_payer'];
            $ipres_sal  = (float)$bulletin['ipres_salarie'];
            $ir         = (float)$bulletin['ir_salarie'];
            $autres_ret = (float)$bulletin['total_retenues'] - $ipres_sal - $ir;
            $ipres_pat  = (float)$bulletin['ipres_patronal'];
            $css_pat    = (float)($bulletin['css_accident'] + $bulletin['css_prestation']);

            // Écriture 1 : Charge de personnel (salaire brut)
            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'brouillon')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $num_piece, $date_ecriture, $libelle_base, $annee, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");

            // 6611 Salaires bruts — débit
            if ($c = $getCompte('6611')) {
                $lStmt->execute([$ecriture_id, $c, "Salaire brut — {$employe['prenom']} {$employe['nom']}", $brut, 0]);
            }
            // 4311 IPRES salarié — crédit
            if ($ipres_sal > 0 && $c = $getCompte('4311')) {
                $lStmt->execute([$ecriture_id, $c, "IPRES salarié — {$employe['prenom']} {$employe['nom']}", 0, $ipres_sal]);
            }
            // 4471 IR/IRPP retenu — crédit
            if ($ir > 0 && $c = $getCompte('4471')) {
                $lStmt->execute([$ecriture_id, $c, "IR retenu — {$employe['prenom']} {$employe['nom']}", 0, $ir]);
            }
            // Autres retenues → 422
            if ($autres_ret > 0.01 && $c = $getCompte('422')) {
                $lStmt->execute([$ecriture_id, $c, "Autres retenues — {$employe['prenom']} {$employe['nom']}", 0, $autres_ret]);
            }
            // 422 Net à payer — crédit
            if ($c = $getCompte('422')) {
                $lStmt->execute([$ecriture_id, $c, "Net à payer — {$employe['prenom']} {$employe['nom']}", 0, $net]);
            }

            // Écriture 2 : Charges patronales
            if ($ipres_pat > 0 || $css_pat > 0) {
                $eStmt->execute([$entreprise_id, $journal_id, $user_id, $num_piece.'-PAT', $date_ecriture, "Charges patronales $libelle_base", $annee, $mois]);
                $ecriture_pat_id = (int)$db->lastInsertId();

                if ($ipres_pat > 0 && $c = $getCompte('6641')) {
                    $lStmt->execute([$ecriture_pat_id, $c, "IPRES patronal — {$employe['prenom']} {$employe['nom']}", $ipres_pat, 0]);
                }
                if ($css_pat > 0 && $c = $getCompte('6642')) {
                    $lStmt->execute([$ecriture_pat_id, $c, "CSS patronale — {$employe['prenom']} {$employe['nom']}", $css_pat, 0]);
                }
                $total_pat = $ipres_pat + $css_pat;
                if ($total_pat > 0 && $c = $getCompte('4312')) {
                    $lStmt->execute([$ecriture_pat_id, $c, "Cotisations patronales à payer", 0, $total_pat]);
                }
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la création du bulletin si les écritures échouent
        }
    }

    public function supprimerBulletin(): void {
        requireAuth();
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Réservé aux administrateurs']);
            exit;
        }
        $id         = (int)($_POST['entreprise_id'] ?? 0);
        $bulletin_id= (int)($_POST['bulletin_id'] ?? 0);
        $this->getEntrepriseAccess($id);
        $db = getDB();
        $bStmt = $db->prepare("SELECT id FROM bulletins_paie WHERE id=? AND entreprise_id=?");
        $bStmt->execute([$bulletin_id, $id]);
        if (!$bStmt->fetch()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Bulletin introuvable']);
            exit;
        }
        $db->prepare("DELETE FROM bulletins_paie WHERE id=? AND entreprise_id=?")->execute([$bulletin_id,$id]);
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true]);
    }

    public function changerStatutBulletin(): void {
        requireAuth();
        if (!isAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Réservé aux administrateurs']);
            exit;
        }
        $id          = (int)($_POST['entreprise_id'] ?? 0);
        $bulletin_id = (int)($_POST['bulletin_id'] ?? 0);
        $statut      = $_POST['statut'] ?? '';
        $this->getEntrepriseAccess($id);

        $allowed = ['valide', 'paye'];
        if (!in_array($statut, $allowed)) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Statut invalide']);
            exit;
        }

        $db = getDB();
        $bStmt = $db->prepare("SELECT statut FROM bulletins_paie WHERE id=? AND entreprise_id=?");
        $bStmt->execute([$bulletin_id, $id]);
        $bulletin = $bStmt->fetch(PDO::FETCH_ASSOC);

        if (!$bulletin) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Bulletin introuvable']);
            exit;
        }

        // Transitions autorisées : brouillon → valide → paye
        $transitions = ['brouillon' => 'valide', 'valide' => 'paye'];
        if (($transitions[$bulletin['statut']] ?? '') !== $statut) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false, 'error'=>'Transition de statut non autorisée']);
            exit;
        }

        $db->prepare("UPDATE bulletins_paie SET statut=? WHERE id=? AND entreprise_id=?")
           ->execute([$statut, $bulletin_id, $id]);

        // Quand on marque "payé" → écriture DÉBIT 422 / CRÉDIT 521
        if ($statut === 'paye') {
            $bFull = $db->prepare("SELECT bp.*, CONCAT(e.prenom,' ',e.nom) as employe_nom
                FROM bulletins_paie bp JOIN employes e ON e.id=bp.employe_id
                WHERE bp.id=? AND bp.entreprise_id=?");
            $bFull->execute([$bulletin_id, $id]);
            $bData = $bFull->fetch(PDO::FETCH_ASSOC);

            if ($bData) {
                $this->genererEcriturePaiement($db, $id, $bData);
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['ok'=>true]);
    }

    private function genererEcriturePaiement($db, int $entreprise_id, array $bulletin): void {
        try {
            $jStmt = $db->prepare("SELECT id FROM journaux WHERE entreprise_id=? AND code='BNQ' LIMIT 1");
            $jStmt->execute([$entreprise_id]);
            $journal = $jStmt->fetch(PDO::FETCH_ASSOC);
            if (!$journal) return;
            $journal_id = $journal['id'];

            $getCompte = function($numero) use ($db, $entreprise_id) {
                $s = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=? LIMIT 1");
                $s->execute([$entreprise_id, $numero]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                return $r ? (int)$r['id'] : null;
            };

            $mois   = (int)$bulletin['periode_mois'];
            $annee  = (int)$bulletin['periode_annee'];
            $net    = (float)$bulletin['net_a_payer'];
            $date   = sprintf('%04d-%02d-28', $annee, $mois);
            $piece  = 'PAI-REG-' . $bulletin['id'];
            $libelle = 'Paiement salaire ' . $bulletin['employe_nom'];
            $user_id = auth()['id'];

            // Éviter les doublons
            $check = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND numero_piece=?");
            $check->execute([$entreprise_id, $piece]);
            if ($check->fetchColumn() > 0) return;

            $eStmt = $db->prepare("INSERT INTO ecritures
                (entreprise_id, journal_id, user_id, numero_piece, date_ecriture, libelle, exercice, periode, statut)
                VALUES (?,?,?,?,?,?,?,?,'validee')");
            $eStmt->execute([$entreprise_id, $journal_id, $user_id, $piece, $date, $libelle, $annee, $mois]);
            $ecriture_id = (int)$db->lastInsertId();

            $lStmt = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id, compte_id, libelle, debit, credit) VALUES (?,?,?,?,?)");

            // DÉBIT 422 — solde dette salariale
            if ($c = $getCompte('422')) {
                $lStmt->execute([$ecriture_id, $c, $libelle, $net, 0]);
            }
            // CRÉDIT 5211 — sortie banque classique (fallback 521)
            $compteBank = $getCompte('5211') ? '5211' : '521';
            if ($c = $getCompte($compteBank)) {
                $lStmt->execute([$ecriture_id, $c, $libelle, 0, $net]);
            }
        } catch (\Exception $e) {
            // Ne pas bloquer le changement de statut
        }
    }

    public function voirBulletin(): void {
        $id = (int)($_GET['id'] ?? 0);
        $bulletin_id = (int)($_GET['bulletin_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);

        $db = getDB();
        $stmt = $db->prepare("SELECT b.*, e.nom, e.prenom, e.matricule, e.poste, e.departement,
                              e.num_ipres, e.num_css, e.num_ipm, e.type_contrat, e.date_embauche,
                              e.banque, e.iban,
                              p.num_ipres_entreprise, p.num_css_entreprise, p.num_ipm_entreprise
                              FROM bulletins_paie b
                              JOIN employes e ON e.id = b.employe_id
                              LEFT JOIN paie_parametres p ON p.entreprise_id = b.entreprise_id
                              WHERE b.id = ? AND b.entreprise_id = ?");
        $stmt->execute([$bulletin_id, $id]);
        $bulletin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$bulletin) { http_response_code(404); echo "Bulletin introuvable"; exit; }

        // Solde congés de l'année du bulletin
        $annee_bulletin = (int)$bulletin['periode_annee'];
        $stmtSolde = $db->prepare("SELECT * FROM soldes_conges WHERE employe_id=? AND annee=?");
        $stmtSolde->execute([$bulletin['employe_id'], $annee_bulletin]);
        $solde_conges = $stmtSolde->fetch(PDO::FETCH_ASSOC);

        // Congés pris dans l'année
        $stmtConges = $db->prepare("
            SELECT type_conge, SUM(nb_jours) as total
            FROM conges
            WHERE employe_id=? AND entreprise_id=? AND statut='approuve'
              AND YEAR(date_debut)=?
            GROUP BY type_conge
        ");
        $stmtConges->execute([$bulletin['employe_id'], $id, $annee_bulletin]);
        $conges_annee = $stmtConges->fetchAll(PDO::FETCH_ASSOC);

        // Cumuls annuels pour l'affichage bulletin (Code Travail Art. L.137)
        $stmtCumuls = $db->prepare("
            SELECT
                SUM(salaire_brut) as cumul_brut,
                SUM(ir_salarie)   as cumul_ir,
                SUM(ipres_salarie) as cumul_ipres
            FROM bulletins_paie
            WHERE employe_id = ? AND entreprise_id = ? AND periode_annee = ? AND periode_mois <= ?
        ");
        $stmtCumuls->execute([
            $bulletin['employe_id'],
            $id,
            $bulletin['periode_annee'],
            $bulletin['periode_mois'],
        ]);
        $cumulsRow   = $stmtCumuls->fetch(PDO::FETCH_ASSOC);
        $cumul_brut  = (float)($cumulsRow['cumul_brut']  ?? 0);
        $cumul_ir    = (float)($cumulsRow['cumul_ir']    ?? 0);
        $cumul_ipres = (float)($cumulsRow['cumul_ipres'] ?? 0);

        require APP_ROOT . '/views/dossier/rh/bulletin.php';
        exit;
    }

    public static function getParametres(int $entrepriseId): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM paie_parametres WHERE entreprise_id = ?");
        $stmt->execute([$entrepriseId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($p) return $p;
        // Valeurs par défaut (taux officiels 2024/2025)
        return [
            'ipres_salarie_a'      => 0.0560,
            'ipres_patronal_a'     => 0.0840,
            'ipres_salarie_b'      => 0.0240,
            'ipres_patronal_b'     => 0.0360,
            'plafond_ipres_a'      => 768000,
            'css_accidents_travail'=> 0.0300,
            'css_prestations_fam'  => 0.0700,
            'css_plafond_pf'       => 63000,
            'cfce_taux'            => 0.0300,
            'ipm_salarie'          => 0.0050,
            'ipm_patronal'         => 0.0300,
            'num_ipres_entreprise' => '',
            'num_css_entreprise'   => '',
            'num_ipm_entreprise'   => '',
            'heures_supp_taux1'    => 1.15,
            'heures_supp_taux2'    => 1.40,
            'heures_supp_seuil'    => 8,
        ];
    }

    public function parametres(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $params = self::getParametres($id);
        $saved = isset($_GET['saved']);

        $pageTitle = 'Paramètres Paie';
        $activeTab = 'rh-params';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/parametres.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function storeParametres(): void {
        $id = (int)($_POST['entreprise_id'] ?? 0);
        $this->getEntrepriseAccess($id);

        // Fix M — Réservé aux admins
        if (!isAdmin()) { redirect("/dossier/rh/parametres?id=$id&error=forbidden"); return; }

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO paie_parametres
            (entreprise_id, ipres_salarie_a, ipres_patronal_a, ipres_salarie_b, ipres_patronal_b,
             plafond_ipres_a, css_accidents_travail, css_prestations_fam, css_plafond_pf,
             cfce_taux, ipm_salarie, ipm_patronal,
             num_ipres_entreprise, num_css_entreprise, num_ipm_entreprise,
             heures_supp_taux1, heures_supp_taux2, heures_supp_seuil)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
             ipres_salarie_a=VALUES(ipres_salarie_a), ipres_patronal_a=VALUES(ipres_patronal_a),
             ipres_salarie_b=VALUES(ipres_salarie_b), ipres_patronal_b=VALUES(ipres_patronal_b),
             plafond_ipres_a=VALUES(plafond_ipres_a),
             css_accidents_travail=VALUES(css_accidents_travail), css_prestations_fam=VALUES(css_prestations_fam),
             css_plafond_pf=VALUES(css_plafond_pf), cfce_taux=VALUES(cfce_taux),
             ipm_salarie=VALUES(ipm_salarie), ipm_patronal=VALUES(ipm_patronal),
             num_ipres_entreprise=VALUES(num_ipres_entreprise),
             num_css_entreprise=VALUES(num_css_entreprise),
             num_ipm_entreprise=VALUES(num_ipm_entreprise),
             heures_supp_taux1=VALUES(heures_supp_taux1),
             heures_supp_taux2=VALUES(heures_supp_taux2),
             heures_supp_seuil=VALUES(heures_supp_seuil)");
        $stmt->execute([
            $id,
            (float)$_POST['ipres_salarie_a'] / 100,
            (float)$_POST['ipres_patronal_a'] / 100,
            (float)$_POST['ipres_salarie_b'] / 100,
            (float)$_POST['ipres_patronal_b'] / 100,
            (int)$_POST['plafond_ipres_a'],
            (float)$_POST['css_accidents_travail'] / 100,
            (float)$_POST['css_prestations_fam'] / 100,
            (int)$_POST['css_plafond_pf'],
            (float)$_POST['cfce_taux'] / 100,
            (float)$_POST['ipm_salarie'] / 100,
            (float)$_POST['ipm_patronal'] / 100,
            trim($_POST['num_ipres_entreprise'] ?? ''),
            trim($_POST['num_css_entreprise'] ?? ''),
            trim($_POST['num_ipm_entreprise'] ?? ''),
            (float)$_POST['heures_supp_taux1'],
            (float)$_POST['heures_supp_taux2'],
            (int)$_POST['heures_supp_seuil'],
        ]);

        // Fix M — Audit de la modification des taux de paie
        require_once APP_ROOT . '/src/Services/NotificationService.php';
        NotificationService::log(auth()['id'], 'PAIE_PARAMS_MODIF', $id, 'paie_parametres', $id, "Taux IPRES/CSS/CFCE modifiés");

        redirect("/dossier/rh/parametres?id=$id&saved=1");
    }

    private function getEmploye(int $employe_id, int $entreprise_id): array {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM employes WHERE id = ? AND entreprise_id = ?");
        $stmt->execute([$employe_id, $entreprise_id]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$emp) { http_response_code(404); echo "Employé introuvable"; exit; }
        return $emp;
    }

    public function voirEmploye(): void {
        $id = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $employe = $this->getEmploye($employe_id, $id);

        $db = getDB();
        // Derniers bulletins
        $stmt = $db->prepare("SELECT * FROM bulletins_paie WHERE employe_id = ? AND entreprise_id = ? ORDER BY periode_annee DESC, periode_mois DESC LIMIT 6");
        $stmt->execute([$employe_id, $id]);
        $derniers_bulletins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Solde congés
        $stmt2 = $db->prepare("SELECT sc.*, YEAR(CURDATE()) as annee_courante FROM soldes_conges sc WHERE sc.employe_id = ? AND sc.annee = YEAR(CURDATE())");
        $stmt2->execute([$employe_id]);
        $solde_conges = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Ancienneté
        $anciennete_mois = 0;
        if (!empty($employe['date_embauche'])) {
            $embauche = new DateTime($employe['date_embauche']);
            $now = new DateTime();
            $diff = $embauche->diff($now);
            $anciennete_mois = $diff->y * 12 + $diff->m;
        }

        $pageTitle = $employe['prenom'].' '.$employe['nom'];
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/employe-fiche.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function historiqueEmploye(): void {
        $id = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $employe = $this->getEmploye($employe_id, $id);

        $db = getDB();
        $annee = (int)($_GET['annee'] ?? 2026);
        $stmt = $db->prepare("SELECT * FROM bulletins_paie WHERE employe_id = ? AND entreprise_id = ? AND periode_annee = ? ORDER BY periode_mois DESC");
        $stmt->execute([$employe_id, $id, $annee]);
        $bulletins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totaux annuels
        $stmt2 = $db->prepare("SELECT SUM(salaire_brut) as total_brut, SUM(net_a_payer) as total_net, SUM(ipres_salarie) as total_ipres, SUM(ir_salarie) as total_ir FROM bulletins_paie WHERE employe_id = ? AND entreprise_id = ? AND periode_annee = ?");
        $stmt2->execute([$employe_id, $id, $annee]);
        $totaux = $stmt2->fetch(PDO::FETCH_ASSOC);

        $pageTitle = 'Bulletins — '.$employe['prenom'].' '.$employe['nom'];
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/employe-bulletins.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function attestation(): void {
        $id = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $employe = $this->getEmploye($employe_id, $id);

        $anciennete_mois = 0;
        $anciennete_label = '';
        if (!empty($employe['date_embauche'])) {
            $embauche = new DateTime($employe['date_embauche']);
            $now = new DateTime();
            $diff = $embauche->diff($now);
            $anciennete_mois = $diff->y * 12 + $diff->m;
            $parts = [];
            if ($diff->y > 0) $parts[] = $diff->y.' an'.($diff->y>1?'s':'');
            if ($diff->m > 0) $parts[] = $diff->m.' mois';
            $anciennete_label = implode(' et ', $parts) ?: 'moins d\'un mois';
        }

        require APP_ROOT . '/views/dossier/rh/attestation-print.php';
        exit;
    }

    public function soldeToutCompte(): void {
        $id = (int)($_GET['id'] ?? 0);
        $employe_id = (int)($_GET['employe_id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $employe = $this->getEmploye($employe_id, $id);

        $db = getDB();
        // Dernier bulletin
        $stmt = $db->prepare("SELECT * FROM bulletins_paie WHERE employe_id = ? AND entreprise_id = ? ORDER BY periode_annee DESC, periode_mois DESC LIMIT 1");
        $stmt->execute([$employe_id, $id]);
        $dernier_bulletin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ancienneté et calculs
        $anciennete_mois = 0;
        $anciennete_label = '';
        $date_depart = $_GET['date_depart'] ?? date('Y-m-d');
        if (!empty($employe['date_embauche'])) {
            $embauche = new DateTime($employe['date_embauche']);
            $depart = new DateTime($date_depart);
            $diff = $embauche->diff($depart);
            $anciennete_mois = $diff->y * 12 + $diff->m;
            $parts = [];
            if ($diff->y > 0) $parts[] = $diff->y.' an'.($diff->y>1?'s':'');
            if ($diff->m > 0) $parts[] = $diff->m.' mois';
            $anciennete_label = implode(' et ', $parts) ?: 'moins d\'un mois';
        }

        // Indemnité de fin de contrat (1/3 mois par année pour CDI)
        $salaire_moyen = $dernier_bulletin ? (float)$dernier_bulletin['salaire_brut'] : (float)$employe['salaire_base'];
        $annees_completes = floor($anciennete_mois / 12);
        $indemnite_licenciement = round($salaire_moyen / 3 * $annees_completes);

        // Solde congés
        $stmt3 = $db->prepare("SELECT * FROM soldes_conges WHERE employe_id = ? AND annee = YEAR(?)");
        $stmt3->execute([$employe_id, $date_depart]);
        $solde_conges = $stmt3->fetch(PDO::FETCH_ASSOC);
        $jours_conges_restants = $solde_conges ? (float)$solde_conges['jours_restants'] : 0;
        $indemnite_conges = round(($salaire_moyen / 26) * $jours_conges_restants);

        require APP_ROOT . '/views/dossier/rh/solde-tout-compte-print.php';
        exit;
    }

    public function registre(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();

        $filtre_statut = $_GET['statut'] ?? '';
        $filtre_dept   = $_GET['departement'] ?? '';
        $sql = "SELECT * FROM employes WHERE entreprise_id = ?";
        $params = [$id];
        if ($filtre_statut) { $sql .= " AND statut = ?"; $params[] = $filtre_statut; }
        if ($filtre_dept)   { $sql .= " AND departement = ?"; $params[] = $filtre_dept; }
        $sql .= " ORDER BY departement, nom, prenom";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt2 = $db->prepare("SELECT DISTINCT departement FROM employes WHERE entreprise_id = ? AND departement IS NOT NULL AND departement != '' ORDER BY departement");
        $stmt2->execute([$id]);
        $departements = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        $pageTitle = 'Registre du personnel';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/registre.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function declarationsSociales(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();

        $annee = (int)($_GET['annee'] ?? 2026);

        $stmt = $db->prepare("SELECT periode_mois as mois,
            SUM(ipres_salarie)         as total_sal_ipres_general,
            0                          as total_sal_ipres_cadre,
            SUM(ir_salarie)            as total_ir,
            SUM(trimf)                 as total_trimf,
            SUM(salaire_brut)          as total_brut,
            SUM(net_a_payer)           as total_net,
            SUM(ipres_patronal)        as total_pat_ipres,
            SUM(css_total)             as total_css,
            SUM(cout_total_employeur)  as cout_employeur,
            COUNT(*)                   as nb_bulletins
            FROM bulletins_paie
            WHERE entreprise_id = ? AND periode_annee = ?
            GROUP BY periode_mois ORDER BY periode_mois");
        $stmt->execute([$id, $annee]);
        $declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totaux annuels
        $stmt2 = $db->prepare("SELECT
            SUM(ipres_salarie)         as total_sal_ipres_general,
            0                          as total_sal_ipres_cadre,
            SUM(ir_salarie)            as total_ir,
            SUM(trimf)                 as total_trimf,
            SUM(salaire_brut)          as total_brut,
            SUM(net_a_payer)           as total_net,
            SUM(ipres_patronal)        as total_pat_ipres,
            SUM(css_total)             as total_css,
            SUM(cout_total_employeur)  as cout_employeur,
            COUNT(*) as nb_bulletins
            FROM bulletins_paie WHERE entreprise_id = ? AND periode_annee = ?");
        $stmt2->execute([$id, $annee]);
        $totaux = $stmt2->fetch(PDO::FETCH_ASSOC);

        $pageTitle = 'Déclarations sociales';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/declarations-sociales.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }

    public function organigramme(): void {
        $id = (int)($_GET['id'] ?? 0);
        $entreprise = $this->getEntrepriseAccess($id);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM employes WHERE entreprise_id = ? AND statut = 'actif' ORDER BY departement, poste, nom");
        $stmt->execute([$id]);
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by departement
        $par_dept = [];
        foreach ($employes as $emp) {
            $dept = $emp['departement'] ?: 'Non défini';
            $par_dept[$dept][] = $emp;
        }

        $pageTitle = 'Organigramme';
        $activeTab = 'rh';
        ob_start();
        require APP_ROOT . '/views/dossier/rh/organigramme.php';
        $content = ob_get_clean();
        require APP_ROOT . '/views/layouts/dossier.php';
    }
}
