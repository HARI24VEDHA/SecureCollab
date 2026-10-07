<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    if ($email && $pass) {
        $db   = get_db();
        
        // VULNERABLE: Direct string concatenation allows SQL Injection
        // Attackers can bypass login by entering ' OR '1'='1 in the password field.
        $query = "SELECT * FROM users WHERE email = '$email' AND password = '$pass'";
        $user = null;
        try {
            $stmt = $db->query($query);
            $user = $stmt->fetch();
        } catch (Exception $e) {}

        // Fallback for legitimate users (since legitimate passwords are hashed in DB)
        if (!$user) {
            try {
                $stmt = $db->query("SELECT * FROM users WHERE email = '$email'");
                $normal_user = $stmt->fetch();
                if ($normal_user && password_verify($pass, $normal_user['password'])) {
                    $user = $normal_user;
                }
            } catch (Exception $e) {}
        }

        if ($user) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            log_activity('Login', 'Signed in');
            header('Location: ' . get_app_url() . '/dashboard/index.php');
            exit;
        }
        $error = 'Invalid email or password.';
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — SecureCollab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo get_app_url(); ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body { background: #0f172a; }
        .login-card { max-width: 420px; width: 100%; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width:56px;height:56px;">
                <i class="fa-solid fa-shield-halved text-white fs-3"></i>
            </div>
            <h3 class="text-white fw-bold mb-1">SecureCollab</h3>
            <p class="text-secondary small">Sign in to your workspace</p>
        </div>
        <div class="card border-0 shadow-lg">
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                            <input type="text" name="email" class="form-control" placeholder="you@company.com"
                                   value="user@securecollab.local" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••"
                                   value="SecureDemo!2026" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mt-1">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                    </button>
                </form>
                <hr class="my-3">
                <div class="text-center text-muted small">
                    No account? <a href="register.php" class="text-primary fw-semibold">Register here</a>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3 border-secondary">
            <div class="card-body py-2 px-3 small text-muted">
                <strong>Demo accounts:</strong><br>
                Admin: <code>admin@securecollab.local</code> / <code>SecureDemo!2026</code><br>
                User: <code>user@securecollab.local</code> / <code>SecureDemo!2026</code>
            </div>
        </div>
    </div>
</body>
</html>