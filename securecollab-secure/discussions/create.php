<?php
$pageTitle = 'New Discussion';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db       = get_db();
$projects = $db->query("SELECT id, title FROM projects WHERE status='active' ORDER BY title")->fetchAll();
$initPid  = intval($_GET['project_id'] ?? 0);
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid     = intval($_POST['project_id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if (!$pid || !$title || !$content) {
        $error = 'All fields are required.';
    } else {
        $db->prepare("INSERT INTO discussions (project_id, user_id, title, content) VALUES (?,?,?,?)")
           ->execute([$pid, $user['id'], $title, $content]);
        $did = $db->lastInsertId();
        log_activity('Discussion Created', $title);
        header("Location: view.php?id=$did");
        exit;
    }
}
?>
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-comment-medical text-success me-2"></i>Start a Discussion</h5>
    </div>
    <div class="card-body p-4">
        <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Project</label>
                <select name="project_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $p['id']==$initPid?'selected':''; ?>>
                        <?php echo htmlspecialchars($p['title']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Topic Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="content" class="form-control" rows="4" required></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success px-4">Post Discussion</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>