<?php
require_once dirname(__DIR__) . '/config/app.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = preg_replace('#^sencompta/?(public/)?#', '',$uri);
$uri = $uri ?: 'dashboard';

$routes = [
    // Auth
    ''             => ['AuthController', 'loginPage'],
    'login'        => ['AuthController', 'loginPage'],
    'login/post'   => ['AuthController', 'login'],
    'login/verify' => ['AuthController', 'verify2fa'],
    'login/2fa'    => ['AuthController', 'verify2fa'],   // alias (lien vue)
    'logout'       => ['AuthController', 'logout'],

    // Mot de passe oublie
    'mot-de-passe-oublie'      => ['AuthController', 'forgotPage'],
    'mot-de-passe-oublie/post' => ['AuthController', 'forgotPost'],

    // SaaS — Inscription
    'inscription'      => ['SaasController', 'inscriptionPage'],
    'inscription/post' => ['SaasController', 'inscriptionPost'],

    // Pages legales (publiques — RGPD / CDP Senegal)
    'confidentialite'  => ['LegalController', 'confidentialite'],
    'mentions-legales' => ['LegalController', 'mentions'],
    'cgu'              => ['LegalController', 'cgu'],
    'cookies'          => ['LegalController', 'cookies'],

    // Portail client — espace dedie (session separee du cabinet)
    'portail'          => ['PortailClientController', 'dashboard'],
    'portail/login'    => ['PortailClientController', 'login'],
    'portail/auth'     => ['PortailClientController', 'authentifier'],
    'portail/logout'   => ['PortailClientController', 'logout'],
    'portail/deposer'  => ['PortailClientController', 'deposer'],
    'portail/etat'     => ['PortailClientController', 'etat'],

    // SaaS — Espace cabinet
    'mon-compte'            => ['SaasController', 'monCompte'],
    'mon-compte/upgrade'    => ['SaasController', 'demandeUpgrade'],

    // SaaS — Super Admin
    'superadmin'              => ['SaasController', 'adminDashboard'],
    'superadmin/cabinets'     => ['SaasController', 'adminCabinets'],
    'superadmin/cabinets/action' => ['SaasController', 'adminCabinetAction'],
    'superadmin/paiements'    => ['SaasController', 'adminPaiements'],
    'superadmin/paiements/valider' => ['SaasController', 'adminValiderPaiement'],
    'superadmin/demandes'     => ['SaasController', 'adminDemandes'],

    // Dashboard admin
    'dashboard' => ['DashboardController', 'index'],

    // Entreprises
    'entreprises'        => ['EntrepriseController', 'index'],
    'entreprises/create' => ['EntrepriseController', 'create'],
    'entreprises/store'  => ['EntrepriseController', 'store'],
    'entreprises/edit'   => ['EntrepriseController', 'edit'],
    'entreprises/update' => ['EntrepriseController', 'update'],
    'entreprises/delete' => ['EntrepriseController', 'delete'],

    // Utilisateurs
    'users'        => ['UserController', 'index'],
    'users/create' => ['UserController', 'create'],
    'users/store'  => ['UserController', 'store'],
    'users/edit'   => ['UserController', 'edit'],
    'users/update' => ['UserController', 'update'],
    'users/delete' => ['UserController', 'delete'],
    'users/assign' => ['UserController', 'assign'],

    // Profil utilisateur connecté
    'profil'          => ['ProfilController', 'index'],
    'profil/update'   => ['ProfilController', 'update'],
    'profil/2fa'      => ['ProfilController', 'setup2fa'],
    'profil/2fa/confirm'  => ['ProfilController', 'confirm2fa'],
    'profil/2fa/disable'  => ['ProfilController', 'disable2fa'],
    // Alias kebab-case utilises par les vues profil
    'profil/setup-2fa'    => ['ProfilController', 'setup2fa'],
    'profil/confirm-2fa'  => ['ProfilController', 'confirm2fa'],
    'profil/disable-2fa'  => ['ProfilController', 'disable2fa'],
    // RGPD — droits des personnes
    'profil/exporter-donnees'    => ['ProfilController', 'exporterDonnees'],
    'profil/demander-suppression'=> ['ProfilController', 'demanderSuppression'],

    // Tableau de bord dossier
    'dossier'           => ['DossierController', 'index'],
    'dossier/dashboard' => ['DossierController', 'index'],

    // Écritures
    'dossier/ecritures'           => ['DossierController', 'ecritures'],
    'dossier/ecritures/nouvelle'  => ['DossierController', 'nouvelleEcriture'],
    'dossier/ecritures/store'     => ['DossierController', 'storeEcriture'],
    'dossier/ecritures/edit'      => ['DossierController', 'editEcriture'],
    'dossier/ecritures/update'    => ['DossierController', 'updateEcriture'],
    'dossier/ecritures/valider'   => ['DossierController', 'validerEcriture'],
    'dossier/ecritures/supprimer' => ['DossierController', 'supprimerEcriture'],
    'dossier/ecritures/scan'      => ['DossierController', 'ecritureScan'],
    'dossier/import-csv'          => ['DossierController', 'importCSV'],
    // Alias kebab-case utilisés dans les vues
    'dossier/nouvelle-ecriture'   => ['DossierController', 'nouvelleEcriture'],
    'dossier/store-ecriture'      => ['DossierController', 'storeEcriture'],
    'dossier/modifier-ecriture'   => ['DossierController', 'editEcriture'],
    'dossier/update-ecriture'     => ['DossierController', 'updateEcriture'],
    'dossier/valider-ecriture'    => ['DossierController', 'validerEcriture'],
    'dossier/supprimer-ecriture'  => ['DossierController', 'supprimerEcriture'],
    'dossier/regler-ecriture'     => ['ReglementController', 'regler'],
    'dossier/ecriture-scan'       => ['DossierController', 'ecritureScan'],

    // Journaux
    'dossier/journaux' => ['DossierController', 'journaux'],

    // Grand livre
    'dossier/grand-livre'        => ['DossierController', 'grandLivre'],
    'dossier/livre-auxiliaire'   => ['DossierController', 'livreAuxiliaire'],
    'dossier/balance-auxiliaire' => ['DossierController', 'balanceAuxiliaire'],

    // Relances clients
    'dossier/relances'             => ['RelanceController', 'index'],
    'dossier/relances/enregistrer' => ['RelanceController', 'enregistrer'],
    'dossier/relances/email'       => ['RelanceController', 'envoyerEmail'],
    'dossier/relances/reglee'      => ['RelanceController', 'marquerReglee'],

    // Portail client (cote cabinet : gestion des acces)
    'dossier/portail'              => ['PortailAdminController', 'index'],
    'dossier/portail/creer'        => ['PortailAdminController', 'creer'],
    'dossier/portail/update'       => ['PortailAdminController', 'update'],
    'dossier/portail/depot'        => ['PortailAdminController', 'traiterDepot'],

    // Budget vs Realise
    'dossier/budget'               => ['BudgetController', 'index'],
    'dossier/budget/store'         => ['BudgetController', 'store'],

    // Plan comptable
    'dossier/plan-comptable'       => ['DossierController', 'planComptable'],
    'dossier/plan-comptable/store' => ['DossierController', 'storePlanComptable'],

    // Balance générale
    'dossier/balance' => ['DossierController', 'balance'],

    // Profil dossier (DGID)
    'dossier/profil'                 => ['DossierController', 'profil'],
    'dossier/profil/store'           => ['DossierController', 'storeProfil'],
    'dossier/profil/conformite-dgid' => ['DossierController', 'conformiteDGID'],

    // Temps dossier
    'dossier/temps'                  => ['TempsDossierController', 'index'],
    'dossier/temps/store'            => ['TempsDossierController', 'store'],
    'dossier/temps/supprimer'        => ['TempsDossierController', 'supprimer'],
    'dossier/temps/marquer-facture'  => ['TempsDossierController', 'marquerFacture'],
    'dossier/temps/total-non-facture'=> ['TempsDossierController', 'totalNonFacture'],

    // Modèles d'écritures
    'dossier/modeles'          => ['ModeleEcritureController', 'index'],
    'dossier/modeles/store'    => ['ModeleEcritureController', 'store'],
    'dossier/modeles/supprimer'=> ['ModeleEcritureController', 'supprimer'],
    'dossier/modeles/json'     => ['ModeleEcritureController', 'json'],
    'dossier/modeles/appliquer'=> ['ModeleEcritureController', 'appliquer'],

    // Clôture
    'dossier/cloture'           => ['CloturController', 'index'],
    'dossier/cloture/checklist' => ['CloturController', 'checklist'],
    'dossier/cloture/cloturer'  => ['CloturController', 'cloturer'],

    // Rapport de gestion dossier
    'dossier/rapport-gestion'        => ['RapportGestionController', 'index'],
    'dossier/rapport-gestion/export' => ['RapportGestionController', 'export'],

    // Balance âgée
    'dossier/balance-agee' => ['DossierController', 'balanceAgee'],

    // Report à nouveau
    'dossier/report-an' => ['DossierController', 'reportANouveaux'],

    // États financiers
    'dossier/bilan'           => ['EtatsFinanciersController', 'bilan'],
    'dossier/compte-resultat' => ['EtatsFinanciersController', 'compteResultat'],
    'dossier/tafire'          => ['EtatsFinanciersController', 'tafire'],
    'dossier/etat-financier-dgid'             => ['EtatsFinanciersController', 'etatDGID'],
    'dossier/etat-financier-dgid/telecharger' => ['EtatsFinanciersController', 'downloadDGID'],

    // TVA
    'dossier/tva'       => ['EtatsFinanciersController', 'tva'],
    'dossier/tva/store' => ['EtatsFinanciersController', 'storeTVA'],

    // Fiscalité
    'dossier/fiscalite/regime'                    => ['EtatsFinanciersController', 'regime'],
    'dossier/fiscalite/is'                        => ['EtatsFinanciersController', 'is'],
    'dossier/fiscalite/is/store'                  => ['EtatsFinanciersController', 'storeIS'],
    'dossier/fiscalite/cgu'                       => ['EtatsFinanciersController', 'cgu'],
    'dossier/fiscalite/cgu/store'                 => ['EtatsFinanciersController', 'storeCGU'],
    'dossier/fiscalite/declaration-sociale'       => ['EtatsFinanciersController', 'declarationSociale'],
    'dossier/fiscalite/declaration-sociale/store' => ['EtatsFinanciersController', 'storeDeclarationSociale'],
    'dossier/fiscalite/calendrier'                => ['EtatsFinanciersController', 'calendrierFiscal'],
    'dossier/fiscalite/calendrier/marquer'        => ['EtatsFinanciersController', 'marquerEcheance'],
    'dossier/fiscalite/calendrier/init'           => ['EtatsFinanciersController', 'initEcheances'],

    // Export imprimables
    'dossier/export/bilan'           => ['EtatsFinanciersController', 'exportBilan'],
    'dossier/export/compte-resultat' => ['EtatsFinanciersController', 'exportCompteResultat'],

    // Exports CSV/Excel
    'dossier/export/ecritures'   => ['ExportController', 'ecritures'],
    'dossier/export/balance'     => ['ExportController', 'balance'],
    'dossier/export/grand-livre' => ['ExportController', 'grandLivre'],

    // Lettrage
    'dossier/lettrage'          => ['LettrerController', 'index'],
    'dossier/lettrage/index'    => ['LettrerController', 'index'],
    'dossier/lettrage/lettrer'  => ['LettrerController', 'lettrer'],
    'dossier/lettrage/delettrer'=> ['LettrerController', 'delettrer'],

    // Tiers
    'dossier/tiers'           => ['TiersController', 'index'],
    'dossier/tiers/index'     => ['TiersController', 'index'],
    'dossier/tiers/form'      => ['TiersController', 'form'],
    'dossier/tiers/store'     => ['TiersController', 'store'],
    'dossier/tiers/voir'      => ['TiersController', 'voir'],
    'dossier/tiers/supprimer' => ['TiersController', 'supprimer'],
    'dossier/tiers/json'      => ['TiersController', 'json'],

    // Immobilisations
    'dossier/immo'                => ['ImmoController', 'index'],
    'dossier/immo/index'          => ['ImmoController', 'index'],
    'dossier/immo/creer'          => ['ImmoController', 'creer'],
    'dossier/immo/form'           => ['ImmoController', 'creer'],
    'dossier/immo/store'          => ['ImmoController', 'store'],
    'dossier/immo/amortissements' => ['ImmoController', 'calculerAmortissements'],

    // Rapprochement bancaire
    'dossier/rapprochement'         => ['RapprochementController', 'index'],
    'dossier/rapprochement/index'   => ['RapprochementController', 'index'],
    'dossier/rapprochement/creer'   => ['RapprochementController', 'creer'],
    'dossier/rapprochement/form'    => ['RapprochementController', 'creer'],
    'dossier/rapprochement/store'   => ['RapprochementController', 'store'],
    'dossier/rapprochement/voir'    => ['RapprochementController', 'voir'],
    'dossier/rapprochement/marquer' => ['RapprochementController', 'marquer'],

    // Gestion des exercices
    'dossier/exercices'              => ['DossierController', 'exercices'],
    'dossier/exercice/modifier'      => ['DossierController', 'modifierExercice'],
    'dossier/exercice/creer'         => ['DossierController', 'creerExercice'],
    'dossier/exercice/switch'        => ['DossierController', 'switchExercice'],

    // Clôture exercice
    'dossier/cloture'         => ['CloturController', 'index'],
    'dossier/cloture/cloturer'=> ['CloturController', 'cloturer'],
    'dossier/cloture/store'   => ['CloturController', 'cloturer'],

    // RH
    'dossier/rh'                       => ['RHController', 'index'],
    'dossier/rh/creer-employe'         => ['RHController', 'creerEmploye'],
    'dossier/rh/store-employe'         => ['RHController', 'storeEmploye'],
    'dossier/rh/edit-employe'          => ['RHController', 'editEmploye'],
    'dossier/rh/update-employe'        => ['RHController', 'updateEmploye'],
    'dossier/rh/bulletins'             => ['RHController', 'bulletins'],
    'dossier/rh/bulletins/creer'       => ['RHController', 'creerBulletin'],
    'dossier/rh/bulletins/store'       => ['RHController', 'storeBulletin'],
    'dossier/rh/bulletins/voir'        => ['RHController', 'voirBulletin'],
    'dossier/rh/parametres'            => ['RHController', 'parametres'],
    'dossier/rh/parametres/store'      => ['RHController', 'storeParametres'],
    // Alias RH kebab-case des vues
    'dossier/rh/creer'                 => ['RHController', 'creerEmploye'],
    'dossier/rh/store'                 => ['RHController', 'storeEmploye'],
    'dossier/rh/edit'                  => ['RHController', 'editEmploye'],
    'dossier/rh/update'                => ['RHController', 'updateEmploye'],
    'dossier/rh/bulletin'              => ['RHController', 'voirBulletin'],
    'dossier/rh/bulletin-form'         => ['RHController', 'creerBulletin'],
    'dossier/rh/bulletin/creer'        => ['RHController', 'creerBulletin'],
    'dossier/rh/bulletin/store'        => ['RHController', 'storeBulletin'],
    'dossier/rh/bulletin/supprimer'    => ['RHController', 'supprimerBulletin'],
    'dossier/rh/bulletins/supprimer'   => ['RHController', 'supprimerBulletin'],
    'dossier/rh/bulletin/statut'       => ['RHController', 'changerStatutBulletin'],
    'dossier/rh/form'                  => ['RHController', 'creerEmploye'],
    'dossier/rh/index'                 => ['RHController', 'index'],
    'dossier/rh/employe'               => ['RHController', 'voirEmploye'],
    'dossier/rh/historique'            => ['RHController', 'historiqueEmploye'],
    'dossier/rh/attestation'           => ['RHController', 'attestation'],
    'dossier/rh/solde-tout-compte'     => ['RHController', 'soldeToutCompte'],
    'dossier/rh/registre'              => ['RHController', 'registre'],
    'dossier/rh/declarations-sociales' => ['RHController', 'declarationsSociales'],
    'dossier/rh/organigramme'          => ['RHController', 'organigramme'],

    // Congés
    'dossier/rh/conges'             => ['CongeController', 'index'],
    'dossier/rh/conges/store'       => ['CongeController', 'store'],
    'dossier/rh/conges/traiter'     => ['CongeController', 'traiter'],
    'dossier/rh/conges/solde'       => ['CongeController', 'solde'],
    'dossier/rh/conges/api'         => ['CongeController', 'api'],
    'dossier/rh/conges/supprimer'   => ['CongeController', 'supprimer'],
    'dossier/rh/conges/parametres'  => ['CongeController', 'parametres'],
    'dossier/rh/conges/parametres/store' => ['CongeController', 'storeParametres'],

    // Notes de frais
    'dossier/notes-frais'        => ['NoteFraisController', 'index'],
    'dossier/notes-frais/store'  => ['NoteFraisController', 'store'],
    'dossier/notes-frais/statut' => ['NoteFraisController', 'updateStatut'],

    // Règlement rapide
    'dossier/regler' => ['ReglementController', 'regler'],

    // Scan IA
    'dossier/scan-ia'         => ['ScanIAController', 'analyser'],
    'dossier/scan-ia/valider' => ['ScanIAController', 'valider'],
    'scan-ia/analyser'        => ['ScanIAController', 'analyser'],
    'scan-ia/valider'         => ['ScanIAController', 'valider'],

    // Module Commercial
    'commercial'                          => ['CommercialController', 'dashboard'],
    'commercial/dashboard'                => ['CommercialController', 'dashboard'],
    'commercial/prospects'                => ['CommercialController', 'prospects'],
    'commercial/prospects/nouveau'        => ['CommercialController', 'prospectForm'],
    'commercial/prospects/store'          => ['CommercialController', 'storeProspect'],
    'commercial/prospect'                 => ['CommercialController', 'prospectVoir'],
    'commercial/prospect/edit'            => ['CommercialController', 'prospectForm'],
    'commercial/prospect/stage'           => ['CommercialController', 'updateStage'],
    'commercial/catalogue'                => ['CommercialController', 'catalogue'],
    'commercial/catalogue/store'          => ['CommercialController', 'storeCatalogue'],
    'commercial/catalogue/json'           => ['CommercialController', 'catalogueJson'],
    'commercial/devis'                    => ['CommercialController', 'devis'],
    'commercial/devis/nouveau'            => ['CommercialController', 'devisForm'],
    'commercial/devis/store'              => ['CommercialController', 'storeDevis'],
    'commercial/devis/voir'               => ['CommercialController', 'devisVoir'],
    'commercial/devis/edit'               => ['CommercialController', 'devisForm'],
    'commercial/devis/convertir'          => ['CommercialController', 'convertirDevisEnFacture'],
    'commercial/devis/pdf'                => ['CommercialController', 'devisExport'],
    'commercial/factures'                 => ['CommercialController', 'factures'],
    'commercial/factures/nouvelle'        => ['CommercialController', 'factureForm'],
    'commercial/factures/store'           => ['CommercialController', 'storeFacture'],
    'commercial/factures/voir'            => ['CommercialController', 'factureVoir'],
    'commercial/factures/edit'            => ['CommercialController', 'factureForm'],
    'commercial/factures/paiement'        => ['CommercialController', 'enregistrerPaiement'],
    'commercial/factures/pdf'             => ['CommercialController', 'factureExport'],

    // Honoraires
    'honoraires'               => ['HonorairesController', 'index'],
    'honoraires/creer'         => ['HonorairesController', 'creer'],
    'honoraires/store'         => ['HonorairesController', 'store'],
    'honoraires/voir'          => ['HonorairesController', 'voir'],
    'honoraires/pdf'           => ['HonorairesController', 'pdf'],
    'honoraires/missions'      => ['HonorairesController', 'missions'],
    'honoraires/missions/creer'=> ['HonorairesController', 'creerMission'],
    'honoraires/missions/store'=> ['HonorairesController', 'storeMission'],
    'honoraires/missions/payer'=> ['HonorairesController', 'marquerPaye'],
    'honoraires/tableau'       => ['HonorairesController', 'tableau'],

    // Planning
    'planning'               => ['PlanningController', 'index'],
    'planning/creer'         => ['PlanningController', 'creer'],
    'planning/store'         => ['PlanningController', 'store'],
    'planning/update-statut' => ['PlanningController', 'updateStatut'],

    // Notifications
    'notifications'                => ['NotificationController', 'page'],
    'notifications/liste'          => ['NotificationController', 'liste'],
    'notifications/marquer-lues'   => ['NotificationController', 'marquerLues'],
    'notifications/audit-log'      => ['NotificationController', 'auditLog'],
    'notifications-email'          => ['NotificationsEmailController', 'index'],
    'notifications-email/envoyer'  => ['NotificationsEmailController', 'envoyer'],
    'audit-log'                    => ['NotificationController', 'auditLog'],

    // Backups admin
    'admin/backups'            => ['BackupController', 'index'],
    'admin/backups/creer'      => ['BackupController', 'creer'],
    'admin/backups/telecharger'=> ['BackupController', 'telecharger'],
    'admin/backups/supprimer'  => ['BackupController', 'supprimer'],
    'admin/backups/auto'       => ['BackupController', 'auto'],

    // Rapports transversaux
    'rapport-temps'          => ['RapportTempsController', 'index'],
    'rapport-gestion'        => ['RapportGestionController', 'index'],
    'rapport-gestion/export' => ['RapportGestionController', 'export'],
];

if (isset($routes[$uri])) {
    [$controllerName, $method] = $routes[$uri];
    $controllerFile = APP_ROOT . '/src/Controllers/' . $controllerName . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$method();
    } else {
        http_response_code(404);
        echo "Page introuvable";
    }
} else {
    http_response_code(404);
    echo "Page introuvable";
}
