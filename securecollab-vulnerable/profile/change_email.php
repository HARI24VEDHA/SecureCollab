<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user(); // needed for $user['id']

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// VULNERABLE: No CSRF token check — attacker can forge this request from any page
$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?err=Invalid+email+address.');
    exit;
}

$db = get_db();
$db->prepare("UPDATE users SET email=? WHERE id=?")->execute([$email, $user['id']]);
$_SESSION['user_email'] = $email;
log_activity('Email Updated', $email);
header('Location: index.php?msg=Email+updated+successfully.');
exit;