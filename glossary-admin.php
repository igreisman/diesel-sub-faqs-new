<?php
$page_title = 'Glossary Admin';
$page_description = 'Manage glossary terms';

require_once 'config/database.php';

// Simple admin gate
if (!isset($_SESSION['admin_logged_in']) || true !== $_SESSION['admin_logged_in']) {
    header('Location: login.php');

    exit;
}

$message = '';
$error = '';

// Ensure glossary table exists (and add id if missing)
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
        // ignore if column exists
    }
}

try {
    ensure_glossary_table($pdo);
} catch (Exception $e) {
    $error = 'Unable to prepare glossary table: '.$e->getMessage();
}

// Handle create/update/delete
if ('POST' === $_SERVER['REQUEST_METHOD'] && !$error) {
    $action = $_POST['action'] ?? 'save';
    $glossaryId = isset($_POST['glossary_id']) ? (int) $_POST['glossary_id'] : 0;
    $term = trim($_POST['term'] ?? '');
    $definition = trim($_POST['glossary_content'] ?? '');

    try {
        if ('delete' === $action) {
            if ($glossaryId > 0) {
                $stmt = $pdo->prepare('DELETE FROM glossary WHERE id = ?');
                $stmt->execute([$glossaryId]);
                $message = 'Glossary term deleted.';
            }
        } else {
            if ('' === $term || '' === $definition) {
                throw new Exception('Term and definition are required.');
            }
            if ($glossaryId > 0) {
                $stmt = $pdo->prepare('UPDATE glossary SET term = ?, definition = ? WHERE id = ?');
                $stmt->execute([$term, $definition, $glossaryId]);
                $message = 'Glossary term updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO glossary (term, definition) VALUES (?, ?)');
                $stmt->execute([$term, $definition]);
                $message = 'Glossary term added.';
                $glossaryId = (int) $pdo->lastInsertId();
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load glossary items
$glossaryItems = [];
if (!$error) {
    try {
        $stmt = $pdo->query('SELECT id, term, definition FROM glossary ORDER BY term ASC');
        $glossaryItems = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Unable to load glossary items: '.$e->getMessage();
    }
}

// Preselect item if provided
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editTerm = '';
$editDefinition = '';
if ($editId && $glossaryItems) {
    foreach ($glossaryItems as $item) {
        if ((int) $item['id'] === $editId) {
            $editTerm = $item['term'];
            $editDefinition = $item['definition'];

            break;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Glossary</h1>
    </div>

    <?php if ($message) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>
    <?php if ($error) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <!-- Existing terms -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-list"></i>
                <span>Glossary Terms (<?php echo count($glossaryItems); ?> item<?php echo 1 === count($glossaryItems) ? '' : 's'; ?>)</span>
            </h5>
            <div>
                <a href="glossary-add.php?return=glossary-admin.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($glossaryItems)) { ?>
                <p class="p-3 mb-0 text-muted">No glossary terms found.</p>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Term</th>
                                <th>Definition</th>
                                <th class="text-center" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($glossaryItems as $item) { ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($item['term']); ?></td>
                                    <td class="text-muted">
                                        <?php echo htmlspecialchars(mb_strimwidth(strip_tags($item['definition']), 0, 120, '...')); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadGlossaryItem(<?php echo (int) $item['id']; ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this glossary term?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="glossary_id" value="<?php echo (int) $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Editor -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="fas fa-tools text-primary me-2"></i>
                <strong>Edit Glossary</strong>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                    <input class="form-check-input" type="radio" name="glossaryMode" value="wysiwyg" checked>
                    WYSIWYG
                </label>
                <label class="d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                    <input class="form-check-input" type="radio" name="glossaryMode" value="markdown">
                    Markdown
                </label>
            </div>
        </div>
        <div class="card-body">
            <form id="glossaryForm" method="POST" onsubmit="handleGlossarySave(event)">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="glossary_id" id="glossary_id" value="<?php echo (int) $editId; ?>">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-tag"></i> Term</label>
                    <input type="text" class="form-control" id="term" name="term" value="<?php echo htmlspecialchars($editTerm); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-edit"></i> Definition</label>
                    <div id="glossary-editor" style="height: 320px;"></div>
                    <textarea id="glossary-markdown" class="form-control" rows="12" style="display:none; margin-top:10px;" placeholder="Write glossary content in Markdown..."></textarea>
                    <textarea id="glossary_content" name="glossary_content" style="display:none;"></textarea>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Glossary
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetGlossaryForm()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <small class="text-muted">Content is saved to the glossary table.</small>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Quill WYSIWYG -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<!-- Markdown helpers -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/turndown/dist/turndown.min.js"></script>
<script>
    const glossaryData = <?php echo json_encode($glossaryItems); ?>;
    const glossaryQuill = new Quill('#glossary-editor', {
        theme: 'snow',
        placeholder: 'Add glossary terms and definitions here...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'blockquote', 'code-block', 'clean']
            ]
        }
    });

    let glossaryMode = 'wysiwyg';
    const markdownArea = document.getElementById('glossary-markdown');
    const quillContainer = document.getElementById('glossary-editor');
    const turndownService = new TurndownService();
    const hiddenHtml = document.getElementById('glossary_content');
    const termInput = document.getElementById('term');
    const glossaryIdInput = document.getElementById('glossary_id');

    document.querySelectorAll('input[name="glossaryMode"]').forEach(r => {
        r.addEventListener('change', (e) => {
            glossaryMode = e.target.value;
            if (glossaryMode === 'markdown') {
                // Switch to Markdown: convert current HTML to markdown
                const html = glossaryQuill.root.innerHTML;
                markdownArea.value = turndownService.turndown(html);
                quillContainer.style.display = 'none';
                markdownArea.style.display = 'block';
                markdownArea.focus();
            } else {
                // Switch to WYSIWYG: render markdown into Quill
                const html = marked.parse(markdownArea.value || '');
                glossaryQuill.clipboard.dangerouslyPasteHTML(html);
                markdownArea.style.display = 'none';
                quillContainer.style.display = 'block';
            }
        });
    });

    function handleGlossarySave(e) {
        e.preventDefault();
        let html = '';
        if (glossaryMode === 'markdown') {
            html = marked.parse(markdownArea.value || '');
        } else {
            html = glossaryQuill.root.innerHTML;
        }
        hiddenHtml.value = html;
        e.target.submit();
    }

    function resetGlossaryForm() {
        termInput.value = '';
        glossaryIdInput.value = '';
        glossaryQuill.setContents([]);
        markdownArea.value = '';
        hiddenHtml.value = '';
    }

    function loadGlossaryItem(id) {
        const item = glossaryData.find(i => parseInt(i.id, 10) === parseInt(id, 10));
        if (!item) return;
        termInput.value = item.term;
        glossaryIdInput.value = item.id;
        // Default load into WYSIWYG
        glossaryMode = 'wysiwyg';
        document.querySelectorAll('input[name="glossaryMode"]').forEach(r => { r.checked = (r.value === 'wysiwyg'); });
        quillContainer.style.display = 'block';
        markdownArea.style.display = 'none';
        glossaryQuill.clipboard.dangerouslyPasteHTML(item.definition || '');
        markdownArea.value = turndownService.turndown(item.definition || '');
    }

    // If editing via GET id, prefill
    <?php if ($editId && $editDefinition) { ?>
        loadGlossaryItem(<?php echo (int) $editId; ?>);
    <?php } ?>
</script>

<?php require_once 'includes/footer.php'; ?>
