<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDBConnection();

$stmt = $db->query("SELECT a.*, u.full_name, u.email FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
$logs = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">System Audit & Activity Logs</h3>
        <p class="text-muted mb-0">Immutable event log tracking user actions, IP addresses, and system operations.</p>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-header bg-light fw-bold">
        <i class="fa-solid fa-list-check me-2 text-success"></i> Activity Event Stream
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>#<?php echo $log['id']; ?></td>
                            <td><small class="text-muted"><?php echo formatDate($log['created_at']); ?></small></td>
                            <td class="fw-semibold text-dark"><?php echo e($log['full_name'] ?? 'System'); ?></td>
                            <td><span class="badge bg-success-subtle text-success border"><?php echo e($log['action']); ?></span></td>
                            <td class="small"><?php echo e($log['details']); ?></td>
                            <td><code><?php echo e($log['ip_address']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
