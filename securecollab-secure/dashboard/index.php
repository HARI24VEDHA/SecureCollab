<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_login();

$db = get_db();
$stats = [
    'projects'    => $db->query("SELECT COUNT(*) FROM projects WHERE status='active'")->fetchColumn(),
    'discussions' => $db->query("SELECT COUNT(*) FROM discussions")->fetchColumn(),
    'files'       => $db->query("SELECT COUNT(*) FROM files")->fetchColumn(),
    'members'     => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
];
$projects = $db->query("SELECT p.*, u.name creator FROM projects p JOIN users u ON p.created_by=u.id WHERE p.status='active' ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
$discs    = $db->query("SELECT d.*, p.title proj_title, u.name author FROM discussions d JOIN projects p ON d.project_id=p.id JOIN users u ON d.user_id=u.id ORDER BY d.created_at DESC LIMIT 4")->fetchAll();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Workspace Overview</h3>
        <p class="text-muted small mb-0">Welcome back, <?php echo htmlspecialchars($user['name']); ?> — here's what's happening today.</p>
    </div>
    <a href="<?php echo $baseUrl; ?>/projects/create.php" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> New Project
    </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $statConfig = [
        ['label'=>'Active Projects', 'key'=>'projects', 'icon'=>'fa-diagram-project', 'color'=>'primary'],
        ['label'=>'Discussions',     'key'=>'discussions', 'icon'=>'fa-comments',       'color'=>'success'],
        ['label'=>'Shared Files',    'key'=>'files',        'icon'=>'fa-folder-open',    'color'=>'info'],
        ['label'=>'Team Members',    'key'=>'members',      'icon'=>'fa-users',          'color'=>'warning'],
    ];
    foreach ($statConfig as $s): ?>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted small mb-1 fw-semibold text-uppercase" style="font-size:.72rem"><?php echo $s['label']; ?></p>
                    <h2 class="fw-bold mb-0"><?php echo $stats[$s['key']]; ?></h2>
                </div>
                <div class="text-<?php echo $s['color']; ?> fs-1 opacity-25">
                    <i class="fa-solid <?php echo $s['icon']; ?>"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Projects -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-diagram-project text-primary me-2"></i>Active Projects</h6>
                <a href="<?php echo $baseUrl; ?>/projects/index.php" class="btn btn-outline-secondary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Title</th><th>Category</th><th>By</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td class="fw-semibold">
                                <a href="<?php echo $baseUrl; ?>/projects/view.php?id=<?php echo $p['id']; ?>" class="text-dark text-decoration-none">
                                    <?php echo htmlspecialchars($p['title']); ?>
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category']); ?></span></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($p['creator']); ?></td>
                            <td>
                                <a href="<?php echo $baseUrl; ?>/projects/view.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Discussions -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-comments text-success me-2"></i>Recent Discussions</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($discs as $d): ?>
                <a href="<?php echo $baseUrl; ?>/discussions/view.php?id=<?php echo $d['id']; ?>" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold text-dark small"><?php echo htmlspecialchars($d['title']); ?></span>
                        <span class="text-muted" style="font-size:.72rem"><?php echo date('M d', strtotime($d['created_at'])); ?></span>
                    </div>
                    <p class="text-muted small mb-1 text-truncate"><?php echo htmlspecialchars($d['content']); ?></p>
                    <span class="text-primary" style="font-size:.75rem"><i class="fa-solid fa-folder me-1"></i><?php echo htmlspecialchars($d['proj_title']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>