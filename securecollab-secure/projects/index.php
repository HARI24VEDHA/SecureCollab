<?php
$pageTitle = "Projects — SecureCollab";
require_once __DIR__ . "/../includes/header.php";
require_login();

$db = get_db();
$statusFilter = $_GET["status"] ?? "active";

$stmt = $db->prepare("SELECT p.*, u.name as creator_name, (SELECT COUNT(*) FROM project_members pm WHERE pm.project_id = p.id) as member_count FROM projects p JOIN users u ON p.created_by = u.id WHERE p.status = ? ORDER BY p.created_at DESC");
$stmt->execute([$statusFilter]);
$projects = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Collaboration Projects</h3>
        <p class="text-muted small mb-0">Browse active and archived workspace projects</p>
    </div>
    <a href="<?php echo $baseUrl; ?>/projects/create.php" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Create Project
    </a>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter === "active" ? "active fw-bold" : ""; ?>" href="index.php?status=active">
            Active Projects
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $statusFilter === "archived" ? "active fw-bold" : ""; ?>" href="index.php?status=archived">
            Archived Projects
        </a>
    </li>
</ul>

<div class="row g-4">
    <?php if (empty($projects)): ?>
        <div class="col-12">
            <div class="alert alert-info">No projects found in this category.</div>
        </div>
    <?php endif; ?>

    <?php foreach ($projects as $p): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo htmlspecialchars($p["category"]); ?></span>
                    <span class="badge <?php echo $p["status"] === "active" ? "bg-success" : "bg-secondary"; ?>"><?php echo ucfirst($p["status"]); ?></span>
                </div>
                <h5 class="card-title fw-bold text-dark mb-2">
                    <a href="view.php?id=<?php echo $p["id"]; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($p["title"]); ?></a>
                </h5>
                <p class="card-text text-muted small flex-grow-1">
                    <?php echo htmlspecialchars(substr($p["description"], 0, 120)) . (strlen($p["description"]) > 120 ? "..." : ""); ?>
                </p>
                <hr class="my-3">
                <div class="d-flex justify-content-between align-items-center text-muted small">
                    <span><i class="fa-solid fa-users me-1"></i> <?php echo $p["member_count"]; ?> members</span>
                    <div class="btn-group">
                        <a href="preview.php?id=<?php echo $p["id"]; ?>" class="btn btn-sm btn-outline-info">Preview</a>
                        <a href="view.php?id=<?php echo $p["id"]; ?>" class="btn btn-sm btn-outline-primary">View Project</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
