<?php
$pageTitle = 'New Project';
require_once __DIR__ . '/../includes/header.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $cat   = trim($_POST['category'] ?? 'General');
    if (!$title || !$desc) {
        $error = 'Title and description are required.';
    } else {
        $db = get_db();
        $db->prepare("INSERT INTO projects (title, description, category, status, created_by) VALUES (?,?,?,'active',?)")
           ->execute([$title, $desc, $cat, $user['id']]);
        $pid = $db->lastInsertId();
        $db->prepare("INSERT IGNORE INTO project_members (project_id, user_id, role) VALUES (?,?,'Owner')")
           ->execute([$pid, $user['id']]);
        log_activity('Project Created', $title);
        header("Location: view.php?id=$pid");
        exit;
    }
}
?>
<div class="row justify-content-center">
<div class="col-md-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-folder-plus text-primary me-2"></i>Create New Project</h5>
    </div>
    <div class="card-body p-4">
        <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Project Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category" class="form-select">
                    <option>Infrastructure</option>
                    <option>Security</option>
                    <option>Software Dev</option>
                    <option>Mobile Dev</option>
                    <option>General</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Create Project</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>