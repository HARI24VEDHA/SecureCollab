<?php
// ALL PHP logic before any HTML output
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$msg = htmlspecialchars($_GET['msg'] ?? '');
$err = htmlspecialchars($_GET['err'] ?? '');

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-md-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-id-card text-primary me-2"></i>Account Settings</h5>
    </div>
    <div class="card-body p-4">
        <?php if ($msg): ?><div class="alert alert-success py-2 small"><?php echo $msg; ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-danger py-2 small"><?php echo $err; ?></div><?php endif; ?>

        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                 style="width:52px;height:52px;font-size:1.4rem;">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
                <p class="text-muted mb-0 small">
                    <?php echo htmlspecialchars($user['email']); ?>
                    <span class="badge bg-secondary ms-2"><?php echo ucfirst($user['role']); ?></span>
                </p>
            </div>
        </div>

        <h6 class="fw-bold mb-3"><i class="fa-solid fa-envelope-open text-warning me-2"></i>Update Email Address</h6>

        <!-- VULNERABLE: No CSRF token in this form — action relies only on session cookie -->
        <form method="POST" action="change_email.php" class="p-3 border rounded bg-light">
            <div class="mb-3">
                <label class="form-label small fw-semibold">New Email Address</label>
                <input type="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-warning fw-semibold">
                <i class="fa-solid fa-save me-1"></i> Update Email
            </button>
        </form>
    </div>
</div>
</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>