<?php
$pageTitle = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
require_admin();

$db    = get_db();
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Administration</h3>
        <p class="text-muted small mb-0">Platform configuration and user account management</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-users text-primary me-2"></i>Registered Users</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td class="text-muted"><?php echo $u['id']; ?></td>
                <td class="fw-semibold"><?php echo htmlspecialchars($u['name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge <?php echo $u['role']==='admin'?'bg-danger':'bg-secondary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                <td class="text-muted small"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>