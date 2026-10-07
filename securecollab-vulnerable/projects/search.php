<?php
// ALL PHP logic before any HTML output
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$query   = $_GET['q'] ?? '';
$results = [];
if ($query !== '') {
    $db   = get_db();
    $like = '%' . $query . '%';
    $stmt = $db->prepare("SELECT p.*, u.name creator FROM projects p JOIN users u ON p.created_by=u.id WHERE p.title LIKE ? OR p.description LIKE ? ORDER BY p.created_at DESC");
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
}

$pageTitle = 'Search Results';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass text-primary me-2"></i>Project Search</h4>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-10">
                <!-- VULNERABLE: $query echoed raw into the value attribute AND into the results text below -->
                <input type="text" name="q" class="form-control"
                       value="<?php echo $query; ?>"
                       placeholder="Search projects by name or description...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>

        <?php if ($query !== ''): ?>
        <p class="text-muted small mb-3">
            <!-- VULNERABLE: Reflected XSS — $query printed without htmlspecialchars() -->
            Search results for: <strong><?php echo $query; ?></strong>
            &nbsp;(<?php echo count($results); ?> found)
        </p>
        <?php endif; ?>

        <?php if (empty($results) && $query !== ''): ?>
            <div class="alert alert-light border">No projects match your search.</div>
        <?php endif; ?>

        <div class="list-group">
        <?php foreach ($results as $r): ?>
            <a href="view.php?id=<?php echo $r['id']; ?>" class="list-group-item list-group-item-action p-3">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1 fw-bold text-primary"><?php echo htmlspecialchars($r['title']); ?></h6>
                    <small class="text-muted"><?php echo htmlspecialchars($r['category'] ?? ''); ?></small>
                </div>
                <p class="mb-1 text-secondary small"><?php echo htmlspecialchars($r['description']); ?></p>
                <small class="text-muted">By <?php echo htmlspecialchars($r['creator']); ?></small>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>