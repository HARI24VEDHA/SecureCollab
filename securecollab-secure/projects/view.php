<?php
$pageTitle = "Project Details — SecureCollab";
require_once __DIR__ . "/../includes/header.php";
require_login();

$id = intval($_GET["id"] ?? 0);
$db = get_db();

$stmt = $db->prepare("SELECT p.*, u.name as creator_name FROM projects p JOIN users u ON p.created_by = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    echo "<div class='alert alert-danger'>Project not found.</div>";
    require_once __DIR__ . "/../includes/footer.php";
    exit;
}

// Members
$membersStmt = $db->prepare("SELECT pm.*, u.name, u.email FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.project_id = ?");
$membersStmt->execute([$id]);
$members = $membersStmt->fetchAll();

// Discussions
$discStmt = $db->prepare("SELECT d.*, u.name as author_name FROM discussions d JOIN users u ON d.user_id = u.id WHERE d.project_id = ? ORDER BY d.created_at DESC");
$discStmt->execute([$id]);
$discussions = $discStmt->fetchAll();

// Files
$fileStmt = $db->prepare("SELECT f.*, u.name as uploader_name FROM files f JOIN users u ON f.user_id = u.id WHERE f.project_id = ? ORDER BY f.uploaded_at DESC");
$fileStmt->execute([$id]);
$files = $fileStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($project["title"]); ?></h3>
        <p class="text-muted small mb-0">
            Category: <span class="badge bg-secondary"><?php echo htmlspecialchars($project["category"]); ?></span> | 
            Status: <span class="badge <?php echo $project["status"] === "active" ? "bg-success" : "bg-secondary"; ?>"><?php echo ucfirst($project["status"]); ?></span> | 
            Created by <?php echo htmlspecialchars($project["creator_name"]); ?> on <?php echo date("M d, Y", strtotime($project["created_at"])); ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="members.php?project_id=<?php echo $project["id"]; ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Manage Members
        </a>
        <a href="archive.php?id=<?php echo $project["id"]; ?>" class="btn btn-outline-danger btn-sm">
            <i class="fa-solid fa-box-archive me-1"></i> Archive Project
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <!-- Project Description -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>Project Overview</h5>
            </div>
            <div class="card-body">
                <p class="card-text text-secondary"><?php echo nl2br(htmlspecialchars($project["description"])); ?></p>
            </div>
        </div>

        <!-- Dynamic Live Note / Preview Section (DOM XSS Target Area) -->
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-info">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-note-sticky text-info me-2"></i>Live Dynamic Project Preview</h5>
                <small class="text-muted">Interactive Client-Side Preview</small>
            </div>
            <div class="card-body bg-light">
                <p class="small text-muted mb-2">Notice or tag parameter loaded dynamically from client URL (e.g. <code>?preview_note=...</code>):</p>
                <div id="previewContent" class="p-3 bg-white border rounded text-dark font-monospace">
                    (No preview note specified in URL parameters)
                </div>
            </div>
        </div>

        <!-- Discussions Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-comments text-success me-2"></i>Project Discussions</h5>
                <a href="<?php echo $baseUrl; ?>/discussions/create.php?project_id=<?php echo $project["id"]; ?>" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-plus me-1"></i> New Topic
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($discussions)): ?>
                        <div class="p-3 text-muted">No discussion topics created yet.</div>
                    <?php endif; ?>
                    <?php foreach ($discussions as $d): ?>
                    <a href="<?php echo $baseUrl; ?>/discussions/view.php?id=<?php echo $d["id"]; ?>" class="list-group-item list-group-item-action py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($d["title"]); ?></h6>
                            <small class="text-muted"><?php echo date("M d, Y", strtotime($d["created_at"])); ?></small>
                        </div>
                        <p class="mb-1 text-muted small text-truncate"><?php echo htmlspecialchars($d["content"]); ?></p>
                        <small class="text-secondary">Started by <?php echo htmlspecialchars($d["author_name"]); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Team Members Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-users me-2 text-warning"></i>Team Members</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($members as $m): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div>
                            <div class="fw-semibold text-dark small"><?php echo htmlspecialchars($m["name"]); ?></div>
                            <div class="text-muted fs-7"><?php echo htmlspecialchars($m["email"]); ?></div>
                        </div>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m["role"]); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Files & Documents Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-file-arrow-up me-2 text-primary"></i>Shared Files</h6>
                <a href="<?php echo $baseUrl; ?>/files/index.php?project_id=<?php echo $project["id"]; ?>" class="btn btn-outline-primary btn-sm fs-7">Upload</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($files)): ?>
                        <li class="list-group-item text-muted small py-3">No files attached to this project.</li>
                    <?php endif; ?>
                    <?php foreach ($files as $f): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div class="text-truncate" style="max-width: 180px;">
                            <i class="fa-solid fa-file me-1 text-secondary"></i>
                            <span class="small fw-semibold text-dark"><?php echo htmlspecialchars($f["original_name"]); ?></span>
                        </div>
                        <span class="text-muted fs-7"><?php echo round($f["filesize"] / 1024, 1); ?> KB</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Preview JS Script -->
<script src="<?php echo $baseUrl; ?>/assets/js/preview.js"></script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
