<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // Simple password check - you can enhance this later with proper user management
    if ($password === '1945') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

$page_title = 'Admin Login';
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <h4><i class="fas fa-submarine text-primary"></i> Admin Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <script>
                        window.addEventListener('DOMContentLoaded', function() {
                            var pw = document.getElementById('password');
                            if (pw) pw.focus();
                        });
                        </script>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
