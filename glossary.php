<?php
$page_title = 'Glossary';
$page_description = 'Submarine glossary';

require_once 'config/database.php';

$error = '';
$glossaryItems = [];

/**
 * Ensure glossary table exists (id + term + definition). If the table exists without id, add it.
 *
 * @param mixed $pdo
 */
function ensure_glossary_table($pdo)
{
    // Create if missing
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS glossary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            term TINYTEXT NOT NULL,
            definition TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');

    // Add id column if legacy table lacks it
    try {
        $pdo->exec('ALTER TABLE glossary ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST');
    } catch (Exception $e) {
        // ignore if column already exists
    }
}

try {
    ensure_glossary_table($pdo);
    $stmt = $pdo->query('SELECT term, definition FROM glossary ORDER BY term ASC');
    $glossaryItems = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Unable to load glossary items.';
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Glossary</h1>
        <small class="text-muted">Visitor view</small>
    </div>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php } elseif (empty($glossaryItems)) { ?>
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> No glossary entries yet.</div>
    <?php } else { ?>
        <div class="list-group">
            <?php foreach ($glossaryItems as $item) { ?>
                <div class="list-group-item">
                    <h5 class="mb-1"><?php echo htmlspecialchars($item['term']); ?></h5>
                    <div class="mb-0"><?php echo $item['definition']; ?></div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php require_once 'includes/footer.php'; ?>
