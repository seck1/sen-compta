<?php
// ── Gestion des erreurs selon l'environnement ────────────────────────────────
// En production : ne JAMAIS afficher les erreurs/warnings a l'ecran (cela casse
// les en-tetes HTTP et expose des infos). On les enregistre dans le log PHP.
$__host = $_SERVER['HTTP_HOST'] ?? '';
$__isProd = ($__host !== '' && $__host !== 'localhost' && !str_contains($__host, '127.0.0.1'));
if ($__isProd) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_WARNING);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ── Chargement des variables d'environnement depuis .env ─────────────────────
function loadEnv(string $envFile): void {
    if (!file_exists($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorer les commentaires et les lignes sans '='
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Ne pas écraser une variable déjà définie dans l'environnement
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

define('APP_ROOT', dirname(__DIR__));
loadEnv(APP_ROOT . '/.env');

define('APP_NAME', 'SenCompta');
define('APP_VERSION', '1.0.0');

// ── Identité du cabinet (mentions légales obligatoires sur factures) ──────────
define('CABINET_NOM',      getenv('CABINET_NOM')      ?: 'SenCompta');
define('CABINET_QUALITE',  getenv('CABINET_QUALITE')  ?: 'Expert-Comptable agréé — ONECCA-SN');
define('CABINET_NINEA',    getenv('CABINET_NINEA')    ?: '');
define('CABINET_RCCM',     getenv('CABINET_RCCM')     ?: '');
define('CABINET_ADRESSE',  getenv('CABINET_ADRESSE')  ?: '');
define('CABINET_TEL',      getenv('CABINET_TEL')      ?: '');
define('CABINET_EMAIL',    getenv('CABINET_EMAIL')    ?: '');

// Avertissement si NINEA non renseigné hors localhost (CGI Art. 358 bis + Loi 2004-06)
// error_log au lieu de trigger_error : ne JAMAIS produire d'output ici (casse les en-tetes HTTP).
if (!CABINET_NINEA && !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', ''])) {
    error_log('CABINET_NINEA non renseigné — factures non conformes DGID (CGI Art. 358 bis)');
}

// Détection automatique de l'URL selon l'environnement
if (getenv('APP_URL')) {
    define('APP_URL', getenv('APP_URL'));
} else {
    $_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if ($_host === 'localhost' || str_contains($_host, '127.0.0.1')) {
        define('APP_URL', 'http://localhost/sencompta/public');
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        define('APP_URL', $scheme . '://' . $_host);
    }
}

define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 7200));

// Token cron backup — Générer avec : openssl rand -hex 32
// Ne jamais utiliser de fallback prévisible en production.
if (!defined('BACKUP_TOKEN')) {
    $backupToken = getenv('BACKUP_TOKEN');
    if (!$backupToken) {
        error_log('BACKUP_TOKEN non défini dans .env — endpoint backup désactivé');
        $backupToken = '';
    }
    define('BACKUP_TOKEN', $backupToken);
}

// Démarrage session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_name('sencompta_sess');
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Autoload simple
spl_autoload_register(function (string $class) {
    $paths = [
        APP_ROOT . '/src/Controllers/' . $class . '.php',
        APP_ROOT . '/src/Models/'      . $class . '.php',
        APP_ROOT . '/src/Services/'    . $class . '.php',
        APP_ROOT . '/src/Middleware/'  . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/mail.php';

// Helpers globaux
function auth(): ?array {
    return $_SESSION['user'] ?? null;
}

function isAdmin(): bool {
    return (auth()['role'] ?? '') === 'admin';
}

function isSuperviseur(): bool {
    return in_array(auth()['role'] ?? '', ['admin', 'superviseur']);
}

function isCollaborateur(): bool {
    return (auth()['role'] ?? '') === 'collaborateur';
}

function canValiderEcriture(): bool {
    // Superviseur et admin peuvent valider
    return isSuperviseur();
}

function canInvaliderEcriture(): bool {
    // Seul l'admin peut repasser en brouillon une écriture validée
    return isAdmin();
}

function requireAuth(): void {
    if (!auth()) {
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }
}

function requireSuperviseur(): void {
    requireAuth();
    if (!isSuperviseur()) {
        header('Location: ' . APP_URL . '/dashboard');
        exit;
    }
}

function redirect(string $path): void {
    header('Location: ' . APP_URL . $path);
    exit;
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatMontant(float $montant): string {
    return number_format($montant, 0, ',', ' ') . ' FCFA';
}

function getEntreprise(int $id): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM entreprises WHERE id = ?");
    $stmt->execute([$id]);
    $ent = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if (empty($ent)) return $ent;

    // Exercice actif : session en priorité, sinon exercice_courant de la DB
    if (!empty($_SESSION['exercice'][$id])) {
        $ent['exercice_courant'] = (int)$_SESSION['exercice'][$id];
    }

    // Liste des exercices disponibles (écritures + table exercices)
    $stmtEx = $db->prepare("SELECT DISTINCT exercice as annee FROM ecritures WHERE entreprise_id=? UNION SELECT annee FROM exercices WHERE entreprise_id=? ORDER BY annee DESC");
    $stmtEx->execute([$id, $id]);
    $ent['_exercices'] = array_column($stmtEx->fetchAll(PDO::FETCH_ASSOC), 'annee');
    if (empty($ent['_exercices'])) $ent['_exercices'] = [$ent['exercice_courant']];
    if (!in_array($ent['exercice_courant'], $ent['_exercices'])) {
        array_unshift($ent['_exercices'], $ent['exercice_courant']);
    }

    return $ent;
}

function userHasAccess(int $entrepriseId): bool {
    $user = auth();
    if (!$user) return false;

    $cabinetId = $user['cabinet_id'] ?? null;
    $isSuperAdmin = ($user['role_saas'] ?? '') === 'super_admin';
    $db = getDB();

    // Super admin plateforme — accès total (pas de cabinet_id)
    if ($isSuperAdmin && !$cabinetId) return true;

    // Fail-closed : utilisateur sans cabinet assigné et non super admin
    if (!$cabinetId) return false;

    // Vérifier que l'entreprise appartient au même cabinet (protection IDOR)
    $stmt = $db->prepare("SELECT COUNT(*) FROM entreprises WHERE id = ? AND cabinet_id = ?");
    $stmt->execute([$entrepriseId, $cabinetId]);
    if (!$stmt->fetchColumn()) return false;

    // Admin et superviseur du cabinet voient tous les dossiers
    if (isSuperviseur()) return true;

    // Collaborateur : seulement les dossiers assignés
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_entreprises WHERE user_id = ? AND entreprise_id = ? AND actif = 1");
    $stmt->execute([$user['id'], $entrepriseId]);
    return $stmt->fetchColumn() > 0;
}

function getRoleLabel(string $role): string {
    return match($role) {
        'admin'         => 'Administrateur',
        'superviseur'   => 'Superviseur',
        'collaborateur' => 'Collaborateur',
        default         => $role,
    };
}

function getRoleBadgeColor(string $role): string {
    return match($role) {
        'admin'         => '#1e3a5f',
        'superviseur'   => '#7c3aed',
        'collaborateur' => '#0891b2',
        default         => '#6b7280',
    };
}

// ── Protection CSRF ──────────────────────────────────────────────────────────

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): void {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo "Token CSRF invalide. Veuillez recharger la page et réessayer.";
        exit;
    }
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken(), ENT_QUOTES) . '">';
}
