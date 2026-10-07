<?php
require_once __DIR__ . "/functions.php";
// VULNERABLE: Intentionally lacks X-Frame-Options and Content-Security-Policy headers
$user = current_user();
$baseUrl = get_app_url();
$pageTitle = $pageTitle ?? "SecureCollab — Team Collaboration";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $baseUrl; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-end border-secondary" id="sidebar-wrapper">
        <div class="sidebar-heading border-bottom border-secondary text-white fw-bold py-3 px-3 fs-5 d-flex align-items-center">
            <i class="fa-solid fa-shield-halved text-primary me-2"></i>
            <span>SecureCollab</span>
        </div>
        <div class="list-group list-group-flush sidebar-nav">
            <a href="<?php echo $baseUrl; ?>/dashboard/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-chart-line me-2 text-info"></i> Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>/projects/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-diagram-project me-2 text-warning"></i> Projects
            </a>
            <a href="<?php echo $baseUrl; ?>/discussions/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-comments me-2 text-success"></i> Discussions
            </a>
            <a href="<?php echo $baseUrl; ?>/files/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-folder-open me-2 text-primary"></i> Files & Uploads
            </a>
            <a href="<?php echo $baseUrl; ?>/profile/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-user-gear me-2 text-secondary"></i> User Profile
            </a>
            <?php if ($user && $user["role"] === "admin"): ?>
            <div class="text-uppercase text-secondary px-3 pt-3 pb-1 fs-7 fw-semibold">Administration</div>
            <a href="<?php echo $baseUrl; ?>/admin/index.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-sliders me-2 text-danger"></i> Admin Console
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/logs.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-list-check me-2 text-info"></i> Activity Logs
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/security.php" class="list-group-item list-group-item-action bg-dark text-light border-0 py-2 px-3">
                <i class="fa-solid fa-shield-virus me-2 text-success"></i> Security Matrix
            </a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Page Content Wrapper -->
    <div id="page-content-wrapper" class="w-100 bg-light d-flex flex-column min-vh-100">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 py-2">
            <div class="container-fluid p-0">
                <button class="btn btn-outline-secondary btn-sm me-3" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <form class="d-flex me-auto" action="<?php echo $baseUrl; ?>/projects/search.php" method="GET">
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <input type="text" name="q" class="form-control" placeholder="Search projects..." required>
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>
                <div class="d-flex align-items-center">
                    <?php if ($user): ?>
                    <span class="me-3 text-secondary small">
                        <i class="fa-solid fa-circle-user me-1"></i> <?php echo htmlspecialchars($user["name"]); ?> 
                        <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($user["role"]); ?></span>
                    </span>
                    <a href="<?php echo $baseUrl; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                    <?php else: ?>
                    <a href="<?php echo $baseUrl; ?>/auth/login.php" class="btn btn-primary btn-sm me-2">Sign In</a>
                    <a href="<?php echo $baseUrl; ?>/auth/register.php" class="btn btn-outline-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <div class="container-fluid px-4 py-4 flex-grow-1">
