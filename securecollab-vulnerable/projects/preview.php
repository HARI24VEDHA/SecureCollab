<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$projectId = intval($_GET['id'] ?? 0);
$project   = null;
if ($projectId > 0) {
    try {
        $db   = get_db();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
    } catch (Exception $e) { $project = null; }
}

if (!$project && $projectId > 0) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Project Preview';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-file-invoice text-primary me-2"></i>
            <?php echo $project ? htmlspecialchars($project['title']) : 'Dynamic Preview'; ?>
        </h3>
        <p class="text-muted mb-0 small">Live documentation previewer and markdown renderer.</p>
    </div>
    <a href="<?php echo $project ? 'view.php?id='.$project['id'] : 'index.php'; ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Project
    </a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3 bg-white border-bottom">
                <span class="fw-bold"><i class="fa-solid fa-desktop me-2 text-primary"></i>Live Render Output</span>
            </div>
            <div class="card-body p-4">
                <?php if ($project): ?>
                <p class="text-secondary small mb-3"><?php echo htmlspecialchars($project['description'] ?? ''); ?></p>
                <hr class="my-3">
                <?php endif; ?>
                
                <h6 class="fw-semibold mb-3">Rendered Note:</h6>
                <!-- VULNERABLE: JS writes to innerHTML here -->
                <div id="preview-output" class="p-4 border rounded bg-light" style="min-height:150px; font-size: 0.95rem;">
                    <span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Initializing preview engine...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-3 bg-white border-bottom fw-bold">
                <i class="fa-solid fa-pen-to-square me-2 text-secondary"></i>Input Source
            </div>
            <div class="card-body p-4">
                <form method="GET">
                    <?php if ($project): ?>
                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Draft Note Content</label>
                        <textarea name="preview_note" class="form-control" rows="6"
                                  placeholder="Start typing your note here..."><?php
                            echo htmlspecialchars($_GET['preview_note'] ?? '');
                        ?></textarea>
                        <div class="form-text mt-2"><i class="fa-solid fa-circle-info me-1"></i>Supports rich text rendering via the preview engine.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="fa-solid fa-rotate-right me-1"></i> Generate Preview
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// VULNERABLE DOM XSS — reads from URL and injects as HTML
document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const note   = params.get("preview_note");
    const container = document.getElementById("preview-output");
    
    if (note !== null && container) {
        container.innerHTML = note; // ⚠ DANGEROUS SINK
    } else if (container && note === null) {
        container.innerHTML = '<span class="text-muted">Waiting for input... Use the form to generate a preview.</span>';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
