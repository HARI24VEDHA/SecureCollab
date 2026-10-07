<?php
$pageTitle = 'Discussions';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db = get_db();
$discs = $db->query("SELECT d.*, p.title proj, u.name author, (SELECT COUNT(*) FROM comments c WHERE c.discussion_id=d.id) cnt FROM discussions d JOIN projects p ON d.project_id=p.id JOIN users u ON d.user_id=u.id ORDER BY d.created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Discussions</h3>
        <p class="text-muted small mb-0">Collaborative threads across all projects</p>
    </div>
    <a href="create.php" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> New Topic</a>
</div>

<div class="card border-0 shadow-sm">
<div class="list-group list-group-flush">
<?php foreach ($discs as $d): ?>
<a href="view.php?id=<?php echo $d['id']; ?>" class="list-group-item list-group-item-action p-4">
    <div class="d-flex justify-content-between align-items-start mb-1">
        <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($d['title']); ?></h5>
        <span class="badge bg-primary-subtle text-primary border">
            <i class="fa-solid fa-comment me-1"></i><?php echo $d['cnt']; ?>
        </span>
    </div>
    <p class="text-muted small mb-1 text-truncate"><?php echo htmlspecialchars($d['content']); ?></p>
    <div class="d-flex justify-content-between text-muted small">
        <span><i class="fa-solid fa-folder me-1 text-warning"></i><?php echo htmlspecialchars($d['proj']); ?></span>
        <span>by <?php echo htmlspecialchars($d['author']); ?> · <?php echo date('M d, Y', strtotime($d['created_at'])); ?></span>
    </div>
</a>
<?php endforeach; ?>
<?php if (empty($discs)): ?>
<div class="list-group-item text-muted p-4">No discussions yet.</div>
<?php endif; ?>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>