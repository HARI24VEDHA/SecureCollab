<?php
// ── ALL PHP LOGIC BEFORE ANY HTML OUTPUT ─────────────────────────────────────
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user(); // must be set BEFORE header.php so POST handler can use $user['id']

$id = intval($_GET['id'] ?? 0);
$db = get_db();

$stmt = $db->prepare("SELECT d.*, p.title proj, u.name author
    FROM discussions d
    JOIN projects p ON d.project_id = p.id
    JOIN users    u ON d.user_id    = u.id
    WHERE d.id = ?");
$stmt->execute([$id]);
$disc = $stmt->fetch();

// Handle POST comment submission BEFORE including header (which outputs HTML)
if ($disc && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
    $text = trim($_POST['comment']);
    $db->prepare("INSERT INTO comments (discussion_id, user_id, comment) VALUES (?,?,?)")
       ->execute([$id, $user['id'], $text]);
    log_activity('Comment Posted', 'Discussion #' . $id);
    header("Location: view.php?id=$id");
    exit;
}

// Fetch comments for display
$commentsStmt = $db->prepare("SELECT c.*, u.name author, u.role urole
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.discussion_id = ?
    ORDER BY c.created_at ASC");
$commentsStmt->execute([$id]);
$comments = $commentsStmt->fetchAll();

// ── HTML OUTPUT STARTS HERE ───────────────────────────────────────────────────
$pageTitle = 'Discussion';
require_once __DIR__ . '/../includes/header.php';

if (!$disc) {
    echo '<div class="alert alert-danger">Discussion not found.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<a href="index.php" class="btn btn-outline-secondary btn-sm mb-4">
    <i class="fa-solid fa-arrow-left me-1"></i> Back
</a>

<h3 class="fw-bold mb-1"><?php echo htmlspecialchars($disc['title']); ?></h3>
<p class="text-muted small mb-3">
    <span class="badge bg-secondary"><?php echo htmlspecialchars($disc['proj']); ?></span>
    &bull; by <?php echo htmlspecialchars($disc['author']); ?>
    &bull; <?php echo date('M d, Y H:i', strtotime($disc['created_at'])); ?>
</p>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-secondary"><?php echo nl2br(htmlspecialchars($disc['content'])); ?></div>
</div>

<h5 class="fw-bold mb-3">
    <i class="fa-solid fa-comments text-primary me-2"></i>Comments (<?php echo count($comments); ?>)
</h5>

<?php foreach ($comments as $c): ?>
<div class="card border-0 shadow-sm mb-3 comment-card">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between mb-2">
            <span>
                <strong><?php echo htmlspecialchars($c['author']); ?></strong>
                <span class="badge bg-light text-dark border ms-2"><?php echo htmlspecialchars($c['urole']); ?></span>
            </span>
            <small class="text-muted"><?php echo date('M d, H:i', strtotime($c['created_at'])); ?></small>
        </div>
        <!-- VULNERABLE: comment rendered without output encoding (Stored XSS) -->
        <div class="text-dark">
            <?php echo $c['comment']; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-pen me-2 text-success"></i>Add Comment</h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <textarea name="comment" class="form-control" rows="3"
                    placeholder="Write your reply..." required></textarea>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-paper-plane me-1"></i> Post
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>