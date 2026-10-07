<?php
$pageTitle = 'Archive Project';
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$db = get_db();
$stmt = $db->prepare("SELECT * FROM projects WHERE id=?");
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) { echo '<div class="alert alert-danger">Project not found.</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }

$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $db->prepare("UPDATE projects SET status='archived' WHERE id=?")->execute([$id]);
    log_activity('Project Archived', $project['title']);
    $done = true;
}
?>

<div class="row justify-content-center">
<div class="col-md-6">
<div class="card border-0 shadow-sm border-top border-4 border-danger">
    <div class="card-body p-4 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-3" style="width:60px;height:60px;">
            <i class="fa-solid fa-box-archive fs-2"></i>
        </div>
        <h4 class="fw-bold mb-2">Archive Project</h4>
        <?php if ($done): ?>
            <div class="alert alert-success py-2 mb-3">
                <i class="fa-solid fa-check-circle me-1"></i>
                <strong><?php echo htmlspecialchars($project['title']); ?></strong> has been archived.
            </div>
            <a href="index.php" class="btn btn-primary">Back to Projects</a>
        <?php else: ?>
            <p class="text-muted mb-4">
                You are about to archive <strong><?php echo htmlspecialchars($project['title']); ?></strong>.
                Archived projects are read-only and can be restored later.
            </p>
            <form method="POST">
                <input type="hidden" name="confirm" value="1">
                <div class="d-flex justify-content-center gap-3">
                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-light px-4">Cancel</a>
                    <button id="archiveButton" type="submit" class="btn btn-danger px-4 fw-semibold">
                        Confirm Archive
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>