<?php
require_once __DIR__ . '/../includes/functions.php';

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$name || !$email || !$pass) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,'user')")
               ->execute([$name, $email, $hash]);
            $success = 'Account created! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SecureCollab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo get_app_url(); ?>/assets/css/style.css" rel="stylesheet">
    <style>body { background: #0f172a; } .reg-card { max-width: 440px; width: 100%; }</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="reg-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-success rounded-circle mb-3" style="width:56px;height:56px;">
                <i class="fa-solid fa-user-plus text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-bold mb-1">Create Account</h3>
            <p class="text-secondary small">Join your team workspace</p>
        </div>
        <div class="card border-0 shadow-lg">
            <div class="card-body p-4">
                <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success py-2 small"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Jane Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="jane@company.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold py-2">Register</button>
                </form>
                <hr class="my-3">
                <div class="text-center text-muted small">
                    Already have an account? <a href="login.php" class="text-primary fw-semibold">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>