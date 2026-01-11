<?php
// Admin-only add page for glossary terms
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in']) || true !== $_SESSION['admin_logged_in']) {
    header('Location: login.php');

    exit;
}

$returnUrl = isset($_GET['return']) ? trim($_GET['return']) : 'glossary-admin.php';
if ($returnUrl && preg_match('/^https?:/i', $returnUrl)) {
    $returnUrl = 'glossary-admin.php';
}

// Ensure glossary table exists (with id)
function ensure_glossary_table($pdo)
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS glossary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            term TINYTEXT NOT NULL,
            definition TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');

    try {
        $pdo->exec('ALTER TABLE glossary ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST');
    } catch (Exception $e) {
        // ignore if column already exists
    }
}

$error = '';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $term = trim($_POST['term'] ?? '');
    $definition = trim($_POST['definition'] ?? '');

    try {
        ensure_glossary_table($pdo);
        if ('' === $term || '' === $definition) {
            throw new Exception('Term and definition are required.');
        }
        $stmt = $pdo->prepare('INSERT INTO glossary (term, definition) VALUES (?, ?)');
        $stmt->execute([$term, $definition]);
        header("Location: {$returnUrl}");

        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$page_title = 'Add Glossary Term';
$page_description = 'Add a new glossary term';

require_once 'includes/header.php';
?>

<style>
.label-inline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    line-height: 1.2;
}
.label-inline i {
    line-height: 1;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.8rem 1.2rem;
    height: 54px;
    line-height: 1.2;
}
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Add Glossary Term</h1>
        <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label label-inline">
                        <i class="fas fa-tag"></i>
                        <span>Term</span>
                    </label>
                    <input type="text" class="form-control" name="term" required>
                </div>
                <div class="mb-3">
                    <label class="form-label label-inline">
                        <i class="fas fa-align-left"></i>
                        <span>Definition</span>
                    </label>
                    <textarea name="definition" class="form-control" rows="8" required></textarea>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary action-btn">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                    <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-outline-secondary action-btn">
                        <i class="fas fa-ban me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
