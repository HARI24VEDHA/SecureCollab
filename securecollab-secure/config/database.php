<?php
// ── App base URL — update this if you move the folder ────────────────────────
if (!defined('BASE_URL')) {
    define('BASE_URL', '/securecollab-secure');
}


$host = "localhost";
$db   = "securecollab";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // DB not yet created fallback
     $pdo = null;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", 1);
    ini_set("session.cookie_samesite", "Lax");
    session_start();
}
