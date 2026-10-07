<?php
$pageTitle = 'Activity Logs';
require_once __DIR__ . '/../includes/header.php';
require_admin();

$db   = get_db();
$logs = $db->query("SELECT l.*, u.name uname FROM activity_logs l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT 100")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Activity Logs</h3>
        <p class="text-muted small mb-0">Audit trail of user actions and system events</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
            <tr>
                <td class="text-muted small" style="white-space:nowrap"><?php echo date('Y-m-d H:i', strtotime($l['created_at'])); ?></td>
                <td class="fw-semibold"><?php echo htmlspecialchars($l['uname'] ?? 'System'); ?></td>
                <td><span class="badge bg-primary-subtle text-primary border"><?php echo htmlspecialchars($l['action']); ?></span></td>
                <td class="text-muted small"><?php echo htmlspecialchars($l['details']); ?></td>
                <td class="font-monospace text-muted small"><?php echo htmlspecialchars($l['ip_address']); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>