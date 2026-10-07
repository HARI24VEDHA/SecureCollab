<?php
$pageTitle = 'Files';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db  = get_db();
$pid = intval($_GET['project_id'] ?? 0);
$msg = $_GET['msg'] ?? '';

$projects = $db->query("SELECT id, title FROM projects WHERE status='active' ORDER BY title")->fetchAll();

if ($pid) {
    $stmt = $db->prepare("SELECT f.*, p.title proj, u.name uploader FROM files f JOIN projects p ON f.project_id=p.id JOIN users u ON f.user_id=u.id WHERE f.project_id=? ORDER BY f.uploaded_at DESC");
    $stmt->execute([$pid]);
} else {
    $stmt = $db->query("SELECT f.*, p.title proj, u.name uploader FROM files f JOIN projects p ON f.project_id=p.id JOIN users u ON f.user_id=u.id ORDER BY f.uploaded_at DESC");
}
$files = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">File Repository</h3>
        <p class="text-muted small mb-0">Upload and share project documents</p>
    </div>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2 small mb-3"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<div class="row g-4">
<div class="col-md-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Upload File</h6></div>
        <div class="card-body">
            <form method="POST" action="upload.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Project</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($projects as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $p['id']==$pid?'selected':''; ?>>
                            <?php echo htmlspecialchars($p['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">File</label>
                    <input type="file" name="attachment" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Upload</button>
            </form>
        </div>
    </div>
</div>
<div class="col-md-8">
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3"><h6 class="fw-bold mb-0"><i class="fa-solid fa-folder-open text-warning me-2"></i>Uploaded Documents</h6></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>File</th><th>Project</th><th>Uploaded By</th><th>Size</th></tr></thead>
                <tbody>
                <?php if (empty($files)): ?>
                <tr><td colspan="4" class="text-muted py-3">No files uploaded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($files as $f): ?>
                <tr>
                    <td><i class="fa-solid fa-file text-muted me-2"></i><?php echo htmlspecialchars($f['original_name']); ?></td>
                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($f['proj']); ?></span></td>
                    <td class="text-muted small"><?php echo htmlspecialchars($f['uploader']); ?></td>
                    <td class="text-muted small"><?php echo round($f['filesize']/1024,1); ?> KB</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>