<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM reference_articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: admin-reference.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $class = $_POST['class'] ?? null;
    $title = $_POST['title'];
    $slug = $_POST['slug'];
    $content = $_POST['content'];
    $order_position = (int)$_POST['order_position'];
    $status = $_POST['status'];
    
    if ($id > 0) {
        // Get old slug before updating
        $oldSlugStmt = $pdo->prepare("SELECT slug FROM reference_articles WHERE id = ?");
        $oldSlugStmt->execute([$id]);
        $oldSlug = $oldSlugStmt->fetchColumn();
        
        // Update existing
        $stmt = $pdo->prepare("
            UPDATE reference_articles 
            SET category = ?, class = ?, title = ?, slug = ?, content = ?, 
                order_position = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$category, $class, $title, $slug, $content, $order_position, $status, $id]);
        
        // If slug changed, create redirect from old to new
        if ($oldSlug && $oldSlug !== $slug) {
            $redirectStmt = $pdo->prepare("
                INSERT INTO slug_redirects (old_slug, new_slug, article_id)
                VALUES (?, ?, ?)
            ");
            $redirectStmt->execute([$oldSlug, $slug, $id]);
        }
    } else {
        // Insert new
        $stmt = $pdo->prepare("
            INSERT INTO reference_articles 
            (category, class, title, slug, content, order_position, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$category, $class, $title, $slug, $content, $order_position, $status]);
    }
    
    header('Location: admin-reference.php');
    exit;
}

// Define submarine classes by era
$submarineClasses = [
    'Pre-WWI' => ['Holland', 'Plunger (A)', 'Adder (B)', 'Octopus (C)', 'Shark (D)', 'E', 'F', 'G'],
    'WWI' => ['H', 'K', 'L', 'M', 'N', 'O', 'R'],
    'Interwar' => ['S', 'Barracuda (V-1)', 'Narwhal (V-5)', 'Dolphin (V-7)', 'Cachalot', 'Porpoise', 'Salmon', 'Sargo'],
    'WWII' => ['Tambor', 'Gato', 'Balao', 'Tench'],
    'Post-WWII' => ['GUPPY (conversion)', 'Fleet Snorkel (conversion)', 'Tang'],
    'Nuclear' => ['Nautilus', 'Seawolf (SSN-575)', 'Skate', 'Skipjack', 'Permit (Thresher)', 'Sturgeon'],
    'Cold War' => ['George Washington', 'Ethan Allen', 'Lafayette', 'James Madison', 'Benjamin Franklin', 'Grayback', 'Halibut', 'Los Angeles', 'Seawolf'],
    'Experimental' => ['Albacore', 'NR-1'],
    'Modern' => ['Ohio', 'Ohio (SSGN conversion)', 'Virginia', 'Jimmy Carter (SSN-23)', 'Columbia']
];
?>
<?php require_once 'includes/header.php'; ?>

<div class="container mt-4">
    <h1><i class="fas fa-edit"></i> <?= $id > 0 ? 'Edit' : 'Add' ?> Reference Article</h1>
    
    <div class="card mt-4">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>Category</strong></label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">Select Category...</option>
                            <option value="submarine-classes" <?= ($article['category'] ?? '') === 'submarine-classes' ? 'selected' : '' ?>>
                                Submarine Classes
                            </option>
                            <option value="operations" <?= ($article['category'] ?? '') === 'operations' ? 'selected' : '' ?>>
                                Operations & Tactics
                            </option>
                            <option value="technical" <?= ($article['category'] ?? '') === 'technical' ? 'selected' : '' ?>>
                                Technical Details
                            </option>
                            <option value="research" <?= ($article['category'] ?? '') === 'research' ? 'selected' : '' ?>>
                                Research Notes
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3" id="classField" style="display: none;">
                        <label class="form-label"><strong>Submarine Class</strong></label>
                        <select name="class" class="form-select">
                            <option value="">Select Class...</option>
                            <?php foreach ($submarineClasses as $era => $classes): ?>
                                <optgroup label="<?= htmlspecialchars($era) ?>">
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= htmlspecialchars($class) ?>" 
                                                <?= ($article['class'] ?? '') === $class ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Hidden fields -->
                <input type="hidden" name="status" value="<?= ($article['status'] ?? 'published') ?>">
                
                <div class="mb-3">
                    <label class="form-label"><strong>Title</strong></label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($article['title'] ?? '') ?>" 
                           required>
                </div>
                
                <!-- Hidden fields -->
                <input type="hidden" name="slug" id="slug" value="<?= htmlspecialchars($article['slug'] ?? '') ?>">
                <input type="hidden" name="order_position" value="<?= htmlspecialchars($article['order_position'] ?? 0) ?>">
                
                <div class="mb-3">
                    <label class="form-label"><strong>Content</strong> <em>(You can use HTML tags: &lt;p&gt;, &lt;h3&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;table&gt;, &lt;tr&gt;, &lt;td&gt;, etc.)</em></label>
                    <textarea name="content" id="content" class="form-control" rows="12" style="font-family: monospace;"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="admin-reference.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate slug from title
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/[\s_-]+/g, '-')  // Replace spaces and underscores with hyphens
        .replace(/^-+|-+$/g, '');  // Remove leading/trailing hyphens
}

document.addEventListener('DOMContentLoaded', function() {
    // Toggle submarine class field based on category selection
    function toggleClassField() {
        const category = document.getElementById('category').value;
        const classField = document.getElementById('classField');
        if (category === 'submarine-classes') {
            classField.style.display = 'block';
        } else {
            classField.style.display = 'none';
        }
    }
    
    // Check on page load
    toggleClassField();
    
    // Check when category changes
    document.getElementById('category').addEventListener('change', toggleClassField);
    
    // Auto-generate slug from title
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        const slug = generateSlug(this.value);
        slugInput.value = slug;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
