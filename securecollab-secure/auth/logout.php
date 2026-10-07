<?php
require_once __DIR__ . '/../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
log_activity('Logout', 'Signed out');
session_unset();
session_destroy();
header('Location: ' . get_app_url() . '/auth/login.php');
exit;