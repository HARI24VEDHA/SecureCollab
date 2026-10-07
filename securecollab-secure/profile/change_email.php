<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// SECURE: CSRF token validated with timing-safe hash_equals()
if (!verify_csrf_token()) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403 Forbidden</title></head>
<body style="font-family:sans-serif;padding:60px;text-align:center;background:#0f172a;color:#fff">
<h2>&#x26D4; Request Blocked</h2>
<p style="color:#94a3b8">Invalid or missing CSRF security token.<br>This request has been rejected.</p>
<a href="../profile/index.php" style="color:#60a5fa">Return to Profile</a>
</body></html>');
}

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