<?php
require_once 'config/database.php';

require_once 'includes/markdown-helper.php';

require_once 'includes/header.php';

$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

if ('' === $tag) {
    header('Location: index.php');

    exit;
}

$faqs = [];

try {
    $like = '%'.strtolower($tag).'%';
    $stmt = $pdo->prepare("
        SELECT f.id, f.title, f.question, f.answer, f.updated_at, c.name AS category_name
        FROM faqs f
        JOIN categories c ON f.category_id = c.id
        WHERE f.status = 'published'
          AND LOWER(f.tags) LIKE ?
        ORDER BY f.updated_at DESC, f.id DESC
        LIMIT 200
    ");
    $stmt->execute([$like]);
    $faqs = $stmt->fetchAll();
} catch (Exception $e) {
    $faqs = [];
}
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-tags text-primary"></i>
                FAQs tagged with "<?php echo htmlspecialchars($tag); ?>"
            </h1>
            <p class="text-muted mb-0">Showing <?php echo count($faqs); ?> result(s)</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>

    <?php if (empty($faqs)) { ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No FAQs found with this tag yet.
        </div>
    <?php } else { ?>
        <div class="list-group">
            <?php foreach ($faqs as $faq) { ?>
                <a href="faq.php?id=<?php echo $faq['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($faq['title'] ?: $faq['question']); ?></h5>
                        <small class="text-muted">
                            <?php echo !empty($faq['updated_at']) ? date('M j, Y', strtotime($faq['updated_at'])) : ''; ?>
                        </small>
                    </div>
                    <p class="mb-1 text-muted"><?php echo htmlspecialchars(strip_tags($faq['question'])); ?></p>
                    <small class="text-muted"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($faq['category_name']); ?></small>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php require_once 'includes/footer.php'; ?>
