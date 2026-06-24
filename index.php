<?php
require_once __DIR__ . '/config/app.php';

// ── Headers de sécurité HTTP ─────────────────────────────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
// CSP permissif pour permettre les polices Google, charts CDN existants
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src 'self' fonts.gstatic.com; img-src 'self' data: api.qrserver.com; connect-src 'self'; frame-ancestors 'none'");

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = preg_replace('#^cabinet-smc/?(public/)?#', '', $uri);
$uri = $uri ?: 'dashboard';

$routes = [
    ''                   => ['AuthController',      'loginPage'],
    'login'              => ['AuthController',      'loginPage'],
    'login/post'         => ['AuthController',      'login'],
    'login/2fa'          => ['AuthController',      'verify2fa'],
    'logout'                  => ['AuthController', 'logout'],
    'mot-de-passe-oublie'     => ['AuthController', 'forgotPage'],
    'mot-de-passe-oublie/post'=> ['AuthController', 'forgotPost'],

    // Profil utilisateur
    'profil'             => ['ProfilController',    'index'],
    'profil/update'      => ['ProfilController',    'update'],
    'profil/setup-2fa'   => ['ProfilController',    'setup2fa'],
    'profil/confirm-2fa' => ['ProfilController',    'confirm2fa'],
    'profil/disable-2fa' => ['ProfilController',    'disable2fa'],
    'dashboard'          => ['DashboardController', 'index'],
    'entreprises'        => ['EntrepriseController','index'],
    'entreprises/create' => ['EntrepriseController','create'],
    'entreprises/store'  => ['EntrepriseController','store'],
    'entreprises/edit'   => ['EntrepriseController','edit'],
    'entreprises/update' => ['EntrepriseController','update'],
    'entreprises/delete' => ['EntrepriseController','delete'],
    'users'              => ['UserController',      'index'],
    'users/create'       => ['UserController',      'create'],
    'users/store'        => ['UserController',      'store'],
    'users/edit'         => ['UserController',      'edit'],
    'users/update'       => ['UserController',      'update'],
    'users/delete'       => ['UserController',      'delete'],
    'users/assign'       => ['UserController',      'assign'],

    // Dossier comptable
    'dossier'              => ['DossierController', 'index'],
    'dossier/ecritures'    => ['DossierController', 'ecritures'],
    'dossier/nouvelle-ecriture' => ['DossierController', 'nouvelleEcriture'],
    'dossier/ecriture-scan'     => ['DossierController', 'ecritureScan'],
    'dossier/tiers'             => ['TiersController', 'index'],
    'dossier/tiers/voir'        => ['TiersController', 'voir'],
    'dossier/tiers/form'        => ['TiersController', 'form'],
    'dossier/tiers/store'       => ['TiersController', 'store'],
    'dossier/tiers/supprimer'   => ['TiersController', 'supprimer'],
    'dossier/tiers/json'        => ['TiersController', 'json'],
    'dossier/store-ecriture'    => ['DossierController', 'storeEcriture'],
    'dossier/modifier-ecriture' => ['DossierController', 'editEcriture'],
    'dossier/update-ecriture'   => ['DossierController', 'updateEcriture'],
    'dossier/valider-ecriture'  => ['DossierController', 'validerEcriture'],
    'dossier/supprimer-ecriture'=> ['DossierController', 'supprimerEcriture'],
    'dossier/regler-ecriture'   => ['ReglementController', 'regler'],
    'dossier/import-csv'        => ['DossierController', 'importCSV'],
    'dossier/exercice/switch'      => ['DossierController', 'switchExercice'],
    'dossier/exercice/creer'       => ['DossierController', 'creerExercice'],
    'dossier/grand-livre'          => ['DossierController', 'grandLivre'],
    'dossier/balance'              => ['DossierController', 'balance'],
    'dossier/livre-auxiliaire'     => ['DossierController', 'livreAuxiliaire'],
    'dossier/balance-auxiliaire'   => ['DossierController', 'balanceAuxiliaire'],
    'dossier/journaux'          => ['DossierController', 'journaux'],
    'dossier/plan-comptable'    => ['DossierController', 'planComptable'],
    'dossier/plan-comptable/store' => ['DossierController', 'storePlanComptable'],

    // Profil dossier
    'dossier/profil'                  => ['DossierController', 'profil'],
    'dossier/profil/store'            => ['DossierController', 'storeProfil'],
    'dossier/profil/activites'        => ['DossierController', 'storeActivitesR2'],
    'dossier/profil/conformite-dgid'  => ['DossierController', 'conformiteDGID'],

    // États financiers
    'dossier/bilan'           => ['EtatsFinanciersController', 'bilan'],
    'dossier/compte-resultat' => ['EtatsFinanciersController', 'compteResultat'],
    'dossier/tafire'          => ['EtatsFinanciersController', 'tafire'],
    'dossier/etat-financier-dgid'             => ['EtatsFinanciersController', 'etatDGID'],
    'dossier/etat-financier-dgid/telecharger' => ['EtatsFinanciersController', 'downloadDGID'],
    'dossier/tva'             => ['EtatsFinanciersController', 'tva'],
    'dossier/tva/store'       => ['EtatsFinanciersController', 'storeTVA'],
    'dossier/tva/payer'       => ['EtatsFinanciersController', 'payerTVA'],

    // Export PDF
    'dossier/export/bilan'           => ['EtatsFinanciersController', 'exportBilan'],
    'dossier/export/compte-resultat' => ['EtatsFinanciersController', 'exportCompteResultat'],

    // Immobilisations
    'dossier/immo'                => ['ImmoController', 'index'],
    'dossier/immo/creer'          => ['ImmoController', 'creer'],
    'dossier/immo/store'          => ['ImmoController', 'store'],
    'dossier/immo/amortissements' => ['ImmoController', 'calculerAmortissements'],

    // Rapprochement bancaire
    'dossier/rapprochement'         => ['RapprochementController', 'index'],
    'dossier/rapprochement/creer'   => ['RapprochementController', 'creer'],
    'dossier/rapprochement/store'   => ['RapprochementController', 'store'],
    'dossier/rapprochement/voir'    => ['RapprochementController', 'voir'],
    'dossier/rapprochement/marquer'      => ['RapprochementController', 'marquer'],
    'dossier/rapprochement/import-csv'   => ['RapprochementController', 'importCsv'],
    'dossier/rapprochement/lettrer-auto' => ['RapprochementController', 'lettrerAuto'],

    // RH & Paie
    'dossier/rh'              => ['RHController', 'index'],
    'dossier/rh/creer'        => ['RHController', 'creerEmploye'],
    'dossier/rh/store'        => ['RHController', 'storeEmploye'],
    'dossier/rh/edit'         => ['RHController', 'editEmploye'],
    'dossier/rh/update'       => ['RHController', 'updateEmploye'],
    'dossier/rh/bulletins'    => ['RHController', 'bulletins'],
    'dossier/rh/bulletin'     => ['RHController', 'voirBulletin'],
    'dossier/rh/bulletin/creer' => ['RHController', 'creerBulletin'],
    'dossier/rh/bulletin/store' => ['RHController', 'storeBulletin'],
    'dossier/rh/parametres'     => ['RHController', 'parametres'],
    'dossier/rh/parametres/store'  => ['RHController', 'storeParametres'],
    'dossier/rh/bulletin/supprimer'=> ['RHController', 'supprimerBulletin'],
    'dossier/rh/conges'         => ['CongeController', 'index'],
    'dossier/rh/conges/store'   => ['CongeController', 'store'],
    'dossier/rh/conges/traiter' => ['CongeController', 'traiter'],
    'dossier/rh/conges/solde'   => ['CongeController', 'solde'],
    'dossier/rh/conges/supprimer' => ['CongeController', 'supprimer'],
    'dossier/rh/conges/api'             => ['CongeController', 'api'],
    'dossier/rh/conges/parametres'      => ['CongeController', 'parametres'],
    'dossier/rh/conges/parametres/store'=> ['CongeController', 'storeParametres'],
    'dossier/rh/employe'               => ['RHController', 'voirEmploye'],
    'dossier/rh/employe/bulletins'     => ['RHController', 'historiqueEmploye'],
    'dossier/rh/employe/attestation'   => ['RHController', 'attestation'],
    'dossier/rh/employe/solde-tout-compte' => ['RHController', 'soldeToutCompte'],
    'dossier/rh/registre'              => ['RHController', 'registre'],
    'dossier/rh/declarations-sociales' => ['RHController', 'declarationsSociales'],
    'dossier/rh/organigramme'          => ['RHController', 'organigramme'],

    // Lettrage
    'dossier/lettrage'         => ['LettrerController', 'index'],
    'dossier/lettrage/lettrer' => ['LettrerController', 'lettrer'],
    'dossier/lettrage/delettrer' => ['LettrerController', 'delettrer'],

    // Fiscalité — régimes
    'dossier/fiscalite/regime'    => ['EtatsFinanciersController', 'regime'],
    'dossier/fiscalite/cgu'       => ['EtatsFinanciersController', 'cgu'],
    'dossier/fiscalite/cgu/store' => ['EtatsFinanciersController', 'storeCGU'],

    // IS
    'dossier/fiscalite/is'        => ['EtatsFinanciersController', 'is'],
    'dossier/fiscalite/is/store'  => ['EtatsFinanciersController', 'storeIS'],

    // Déclarations sociales
    'dossier/fiscalite/declaration-sociale'       => ['EtatsFinanciersController', 'declarationSociale'],
    'dossier/fiscalite/declaration-sociale/store' => ['EtatsFinanciersController', 'storeDeclarationSociale'],

    // Calendrier fiscal
    'dossier/fiscalite/calendrier'        => ['EtatsFinanciersController', 'calendrierFiscal'],
    'dossier/fiscalite/calendrier/marquer' => ['EtatsFinanciersController', 'marquerEcheance'],
    'dossier/fiscalite/calendrier/init'    => ['EtatsFinanciersController', 'initEcheances'],
    'dossier/fiscalite/calendrier/generer' => ['EtatsFinanciersController', 'genererEcheances'],

    // Balance âgée
    'dossier/balance-agee' => ['DossierController', 'balanceAgee'],

    // Relances clients
    'dossier/relances'            => ['RelanceController', 'index'],
    'dossier/relances/enregistrer'=> ['RelanceController', 'enregistrer'],
    'dossier/relances/reglee'     => ['RelanceController', 'marquerReglee'],
    'dossier/relances/email'      => ['RelanceController', 'envoyerEmail'],

    // Budget vs Réalisé
    'dossier/budget'       => ['BudgetController', 'index'],
    'dossier/budget/store' => ['BudgetController', 'store'],

    // Rapport de gestion mensuel
    'dossier/rapport-gestion'        => ['RapportGestionController', 'index'],
    'dossier/rapport-gestion/export' => ['RapportGestionController', 'export'],

    // Notes de frais
    'dossier/notes-frais'        => ['NoteFraisController', 'index'],
    'dossier/notes-frais/store'  => ['NoteFraisController', 'store'],
    'dossier/notes-frais/statut' => ['NoteFraisController', 'updateStatut'],

    // Import relevé bancaire
    'dossier/import-bancaire'         => ['ImportBancaireController', 'index'],
    'dossier/import-bancaire/csv'     => ['ImportBancaireController', 'importCSV'],
    'dossier/import-bancaire/rapprocher' => ['ImportBancaireController', 'rapprocher'],
    'dossier/import-bancaire/supprimer'  => ['ImportBancaireController', 'supprimerLigne'],
    'dossier/import-bancaire/auto'       => ['ImportBancaireController', 'rapprochementAuto'],

    // Reports à nouveaux
    'dossier/report-an'        => ['DossierController', 'reportANouveaux'],

    // Clôture
    'dossier/cloture'                  => ['CloturController', 'index'],
    'dossier/cloture/store'           => ['CloturController', 'cloturer'],
    'dossier/cloture/checklist'       => ['CloturController', 'checklist'],

    // Scan IA
    'scan-ia/analyser' => ['ScanIAController', 'analyser'],
    'scan-ia/valider'  => ['ScanIAController', 'valider'],

    // Planning missions
    'planning'        => ['PlanningController', 'index'],
    'planning/creer'  => ['PlanningController', 'creer'],
    'planning/store'  => ['PlanningController', 'store'],
    'planning/statut' => ['PlanningController', 'updateStatut'],

    // Notifications
    'notifications'              => ['NotificationController', 'page'],
    'notifications/marquer-lues' => ['NotificationController', 'marquerLues'],
    'notifications/api'          => ['NotificationController', 'liste'],
    'audit-log'                  => ['NotificationController', 'auditLog'],

    // Export Excel
    'export/ecritures'  => ['ExportController', 'ecritures'],
    'export/balance'    => ['ExportController', 'balance'],
    'export/grand-livre'=> ['ExportController', 'grandLivre'],

    // Rapport global cabinet
    'rapport-temps' => ['RapportTempsController', 'index'],

    // Modèles d'écritures
    'dossier/modeles'          => ['ModeleEcritureController', 'index'],
    'dossier/modeles/store'    => ['ModeleEcritureController', 'store'],
    'dossier/modeles/supprimer'=> ['ModeleEcritureController', 'supprimer'],
    'dossier/modeles/json'     => ['ModeleEcritureController', 'json'],
    'dossier/modeles/appliquer'=> ['ModeleEcritureController', 'appliquer'],

    // Suivi du temps
    'dossier/temps'            => ['TempsDossierController', 'index'],
    'dossier/temps/store'      => ['TempsDossierController', 'store'],
    'dossier/temps/supprimer'  => ['TempsDossierController', 'supprimer'],
    'dossier/temps/facturer'   => ['TempsDossierController', 'marquerFacture'],

    // Commentaires dossier
    'dossier/commentaires'          => ['CommentaireController', 'liste'],
    'dossier/commentaires/store'    => ['CommentaireController', 'store'],
    'dossier/commentaires/resoudre' => ['CommentaireController', 'resoudre'],
    'dossier/commentaires/supprimer'=> ['CommentaireController', 'supprimer'],

    // Notifications email
    'notifications-email'          => ['NotificationsEmailController', 'index'],
    'notifications-email/envoyer'  => ['NotificationsEmailController', 'envoyer'],

    // Honoraires (admin)
    'honoraires'               => ['HonorairesController', 'index'],
    'honoraires/creer'         => ['HonorairesController', 'creer'],
    'honoraires/store'         => ['HonorairesController', 'store'],
    'honoraires/voir'          => ['HonorairesController', 'voir'],
    'honoraires/pdf'           => ['HonorairesController', 'pdf'],
    'honoraires/missions'      => ['HonorairesController', 'missions'],
    'honoraires/mission/creer' => ['HonorairesController', 'creerMission'],
    'honoraires/mission/store'         => ['HonorairesController', 'storeMission'],
    'honoraires/mission/lettre-mission' => ['HonorairesController', 'lettreMission'],
    'honoraires/tableau'       => ['HonorairesController', 'tableau'],
    'honoraires/marquer-paye'  => ['HonorairesController', 'marquerPaye'],

    // Cron — alertes fiscales (auth par token, pas de session)
    'cron/alertes'             => ['CronController',  'alertes'],

    // Sauvegardes (admin)
    'admin/backups'            => ['BackupController', 'index'],
    'admin/backups/creer'      => ['BackupController', 'creer'],
    'admin/backups/telecharger'=> ['BackupController', 'telecharger'],
    'admin/backups/supprimer'  => ['BackupController', 'supprimer'],
    'admin/backups/auto'       => ['BackupController', 'auto'],

    // Commercial (Cabinet SMC)
    'commercial'                     => ['CommercialController', 'dashboard'],
    'commercial/dashboard'           => ['CommercialController', 'dashboard'],
    'commercial/prospects'           => ['CommercialController', 'prospects'],
    'commercial/prospects/nouveau'   => ['CommercialController', 'prospectForm'],
    'commercial/prospects/store'     => ['CommercialController', 'storeProspect'],
    'commercial/prospect'            => ['CommercialController', 'prospectVoir'],
    'commercial/prospect/edit'       => ['CommercialController', 'prospectForm'],
    'commercial/prospect/stage'      => ['CommercialController', 'updateStage'],
    'commercial/catalogue'           => ['CommercialController', 'catalogue'],
    'commercial/catalogue/store'     => ['CommercialController', 'storeCatalogue'],
    'commercial/catalogue/json'      => ['CommercialController', 'catalogueJson'],
    'commercial/devis'               => ['CommercialController', 'devis'],
    'commercial/devis/nouveau'       => ['CommercialController', 'devisForm'],
    'commercial/devis/store'         => ['CommercialController', 'storeDevis'],
    'commercial/devis/voir'          => ['CommercialController', 'devisVoir'],
    'commercial/devis/edit'          => ['CommercialController', 'devisForm'],
    'commercial/devis/convertir'     => ['CommercialController', 'convertirDevisEnFacture'],
    'commercial/devis/pdf'           => ['CommercialController', 'devisExport'],
    'commercial/factures'            => ['CommercialController', 'factures'],
    'commercial/factures/nouvelle'   => ['CommercialController', 'factureForm'],
    'commercial/factures/store'      => ['CommercialController', 'storeFacture'],
    'commercial/factures/voir'       => ['CommercialController', 'factureVoir'],
    'commercial/factures/edit'       => ['CommercialController', 'factureForm'],
    'commercial/factures/paiement'   => ['CommercialController', 'enregistrerPaiement'],
    'commercial/factures/pdf'        => ['CommercialController', 'factureExport'],
];

// Routes publiques exemptées de vérification CSRF
$csrf_exempt = [
    'login/post',
    'mot-de-passe-oublie/post',
    'admin/backups/auto',
    'cron/alertes',          // Auth par token — pas de session/CSRF
];

// Vérification CSRF globale sur toutes les requêtes POST (sauf routes exemptées)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($uri, $csrf_exempt)) {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    verifyCsrfToken($token);
}

if (isset($routes[$uri])) {
    [$controllerName, $method] = $routes[$uri];
    $controllerFile = APP_ROOT . '/src/Controllers/' . $controllerName . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$method();
    } else {
        http_response_code(404);
        echo "Contrôleur introuvable : $controllerName";
    }
} else {
    // Route inconnue → login
    require_once APP_ROOT . '/src/Controllers/AuthController.php';
    $controller = new AuthController();
    $controller->loginPage();
}
