<?php
$pageTitle = 'Project Members';
require_once __DIR__ . '/../includes/header.php';
require_login();

$pid = intval($_GET['project_id'] ?? 0);
$db  = get_db();
$stmt = $db->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$pid]);
$project = $stmt->fetch();
if (!$project) { echo '<div class="alert alert-danger">Invalid project.</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $role  = trim($_POST['role'] ?? 'Member');
    $uStmt = $db->prepare("SELECT id FROM users WHERE email=?");
    $uStmt->execute([$email]);
    $target = $uStmt->fetch();
    if ($target) {
        try {
            $db->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?,?,?)")->execute([$pid, $target['id'], $role]);
            log_activity('Member Added', $email);
            $msg = 'Member added successfully.';
        } catch (Exception $e) { $msg = 'User is already a member.'; }
    } else { $msg = 'User not found.'; }
}

$mStmt = $db->prepare("SELECT pm.*, u.name, u.email FROM project_members pm JOIN users u ON pm.user_id=u.id WHERE pm.project_id=?");
$mStmt->execute([$pid]);
$members = $mStmt->fetchAll();
?>

<a href="view.php?id=<?php echo $pid; ?>" class="btn btn-outline-secondary btn-sm mb-4">
    <i class="fa-solid fa-arrow-left me-1"></i> Back to Project
</a>

<h4 class="fw-bold mb-3">Team Members &mdash; <?php echo htmlspecialchars($project['title']); ?></h4>
<?php if ($msg): ?><div class="alert alert-info py-2 small"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<div class="row g-4">
<div class="col-md-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-user-plus text-primary me-2"></i>Add Member</h6></div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">User Email</label>
                    <input type="email" name="email" class="form-control" placeholder="user@company.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option>Developer</option><option>Security Auditor</option>
                        <option>DevOps Engineer</option><option>Designer</option><option>Member</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100">Add Member</button>
            </form>
        </div>
    </div>
</div>
<div class="col-md-8">
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-users text-success me-2"></i>Current Roster</h6></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($m['name']); ?></td>
                    <td class="text-muted small"><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m['role']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>