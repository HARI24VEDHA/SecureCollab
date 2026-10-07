<?php
require_once __DIR__ . "/../config/database.php";

function get_db() {
    global $pdo;
    if (!$pdo) {
        header("Location: " . get_app_url() . "/database/setup_db.php");
        exit;
    }
    return $pdo;
}

function get_app_url() {
    $scriptName = $_SERVER["SCRIPT_NAME"];
    $base = str_replace("\\", "/", dirname($scriptName));
    if (strpos($base, "/securecollab-secure") !== false) {
        return "/securecollab-secure";
    }
    return "/securecollab-secure";
}

function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        "id" => $_SESSION["user_id"],
        "name" => $_SESSION["user_name"],
        "email" => $_SESSION["user_email"],
        "role" => $_SESSION["user_role"]
    ];
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: " . get_app_url() . "/auth/login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if ($_SESSION["user_role"] !== "admin") {
        die("Access Denied: Administrative privilege required.");
    }
}

function log_activity($action, $details = "") {
    $db = get_db();
    $userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;
    $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ip]);
}

// SECURE CSRF IMPLEMENTATION
function generate_csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") . '">';
}

function verify_csrf_token() {
    if (!isset($_POST["csrf_token"]) || empty($_SESSION["csrf_token"])) {
        return false;
    }
    return hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"]);
}
