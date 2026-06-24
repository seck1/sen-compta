<?php
// Copier ce fichier en database.php et remplir les valeurs
define('DB_HOST', 'sql304.infinityfree.com'); // à remplacer
define('DB_NAME', 'votre_base');              // à remplacer
define('DB_USER', 'votre_user');              // à remplacer
define('DB_PASS', 'votre_password');          // à remplacer
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
