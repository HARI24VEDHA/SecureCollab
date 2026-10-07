<?php
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (is_logged_in()) {
    header('Location: ' . get_app_url() . '/dashboard/index.php');
} else {
    header('Location: ' . get_app_url() . '/auth/login.php');
}
exit;