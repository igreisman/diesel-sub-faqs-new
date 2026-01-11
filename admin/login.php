<?php
if (PHP_SESSION_NONE === session_status()) {
    session_start();
}

require_once '../config/database.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && true === $_SESSION['admin_logged_in']) {
    header('Location: dashboard.php');

    exit;
}

$error = '';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $password = $_POST['password'] ?? '';

    // Simple password-only authentication
    if ('1945' === $password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');

        exit;
    }
    $error = 'Invalid password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Submarine FAQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-ship fa-3x text-primary mb-3"></i>
                            <h3>Admin Login</h3>
                            <p class="text-muted">Submarine FAQ Dashboard</p>
                        </div>

                        <?php if ($error) { ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="password" class="form-label">Admin Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>

                        <div class="text-center">
                            <small class="text-muted">
                                <a href="../index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left"></i> Back to Website
                                </a>
                            </small>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Autofocus password field on load for quicker login
    window.addEventListener('DOMContentLoaded', function() {
        const pwd = document.getElementById('password');
        if (pwd) {
            pwd.focus();
        }
    });
</script>
</body>
</html>
