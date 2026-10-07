<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $dbName = 'securecollab';
echo '<!DOCTYPE html><html><head><title>Database Setup</title>';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head>';
echo '<body class="bg-light"><div class="container py-5"><div class="card shadow border-0 mx-auto" style="max-width:600px"><div class="card-body p-4">';
echo '<h4 class="fw-bold mb-3">SecureCollab — Database Setup</h4>';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sqlPath = __DIR__ . '/securecollab.sql';
    if (!file_exists($sqlPath)) throw new Exception("SQL file not found.");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    $statements = array_filter(array_map('trim', explode(';', file_get_contents($sqlPath))));
    foreach ($statements as $stmt) { if (!empty($stmt)) $pdo->exec($stmt); }
    echo '<div class="alert alert-success">Database initialized successfully.</div>';
    echo '<p><a href="../auth/login.php" class="btn btn-primary">Go to Login</a></p>';
} catch (Exception $e) {
    echo '<div class="alert alert-danger"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<p>Ensure MySQL is running in XAMPP Control Panel.</p>';
}
echo '</div></div></div></body></html>';