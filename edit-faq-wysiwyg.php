<?php
require_once 'config/database.php';

require_once 'includes/header.php';

require_once 'includes/markdown-helper.php';

$faq_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$preset_category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
$return_url = isset($_GET['return']) ? trim($_GET['return']) : '';
if ('' === $return_url && isset($_SESSION['admin_logged_in']) && true === $_SESSION['admin_logged_in']) {
    $return_url = '/admin/manage-faqs.php';
}
$faq = null;
$display_title = '';

if ($faq_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM faqs WHERE id = ?');
    $stmt->execute([$faq_id]);
    $faq = $stmt->fetch();

    if (!$faq) {
        header('Location: index.php');

        exit;
    }

    $display_title = htmlspecialchars($faq['title'] ?? strip_tags($faq['question']));
}

$initial_answer = '';
if ($faq && isset($faq['answer'])) {
    // Show existing content as rendered Markdown/HTML so tables and formatting appear correctly
    $initial_answer = render_markdown($faq['answer']);
}

$initial_title = $faq && isset($faq['title']) ? htmlspecialchars($faq['title']) : '';
$initial_question = '';
if ($faq && isset($faq['question'])) {
    // Render stored question content so Quill shows formatted text instead of raw Markdown/HTML
    $initial_question = render_content($faq['question']);
}

$display_order_value = $faq && isset($faq['display_order']) ? (int) $faq['display_order'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?> - WYSIWYG Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Quill.js WYSIWYG Editor - No API Key Required -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script></script>
    
    <style>
        .form-floating textarea.wysiwyg {
            height: 120px;
        }
        
        .editor-container {
            min-height: 500px;
        }
        
        .word-count {
            font-size: 0.875rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .editor-help {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section-label {
            font-size: 1rem;
            font-weight: 600;
            color: #6c757d;
        }
        /* Match editor text size to form fields (slightly reduced) */
        #question-editor .ql-editor,
        #quill-editor .ql-editor {
            font-size: 0.9375rem;
            line-height: 1.4;
        }
        .section-label {
            font-size: 1rem;
            font-weight: 600;
            color: #6c757d;
        }
        .editor-toggle .form-check-input {
            width: 0.375em;
            height: 0.375em;
            margin-top: 0.15rem;
            margin-right: 0.15rem;
            margin-left: 0;
        }
        .editor-toggle .form-check-label {
            font-size: 0.8rem;
            line-height: 1.1;
            margin-left: 0;
        }
        .editor-toggle .form-check-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.1rem;
            margin-right: 0.2rem !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <?php
                    $editorQuery = $faq ? '?id='.$faq['id'] : ($preset_category_id ? '?category_id='.$preset_category_id : '');
?>
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-edit text-primary"></i> <?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?></h1>
                    <div class="editor-toggle d-flex align-items-center">
                        <div class="form-check form-check-inline me-3">
                            <input class="form-check-input" type="radio" name="editorToggle" id="editorWysiwyg" value="wysiwyg" checked>
                            <label class="form-check-label" for="editorWysiwyg">WYSIWYG</label>
                        </div>
                        <div class="form-check form-check-inline me-2">
                            <input class="form-check-input" type="radio" name="editorToggle" id="editorMarkdown" value="markdown">
                            <label class="form-check-label" for="editorMarkdown">Markdown</label>
                        </div>
                        <a href="<?php echo $return_url ? htmlspecialchars($return_url) : ($faq ? 'faq.php?id='.$faq['id'] : 'index.php'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="faqForm" method="POST" action="save-faq-wysiwyg.php">
            <?php if ($faq) { ?>
                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
            <?php } ?>
            <input type="hidden" name="display_order" value="<?php echo $display_order_value; ?>">
            <input type="hidden" id="title-hidden" name="title" value="<?php echo $initial_title; ?>">
            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url, ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
            $cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
foreach ($cats as $cat) {
    $selected = '';
    if ($faq && $faq['category_id'] == $cat['id']) {
        $selected = 'selected';
    } elseif (!$faq && $preset_category_id == $cat['id']) {
        $selected = 'selected';
    }
    ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <label for="category_id"><i class="fas fa-folder"></i> Category</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="author" name="author"
                               value="<?php echo $faq ? htmlspecialchars($faq['author'] ?? '') : ''; ?>">
                        <label for="author"><i class="fas fa-user"></i> Author (optional)</label>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="date_submitted" name="date_submitted"
                               value="<?php echo $faq && !empty($faq['date_submitted']) ? date('Y-m-d', strtotime($faq['date_submitted'])) : ''; ?>">
                        <label for="date_submitted"><i class="fas fa-calendar-alt"></i> Date (optional)</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label section-label" for="question-editor">
                        <i class="fas fa-question-circle"></i> Question
                    </label>
                    <div id="question-editor" style="height: 60px;"></div>
                    <textarea id="question" name="question" style="display: none;"><?php echo $initial_question; ?></textarea>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label for="main_answer" class="form-label section-label">
                        <i class="fas fa-file-alt"></i> Answer
                    </label>
                    <div class="editor-container">
                        <!-- Quill Editor Container -->
                        <div id="quill-editor" style="height: 400px;"></div>
                        <!-- Hidden textarea for form submission -->
                        <textarea id="main_answer" name="main_answer" style="display: none;"><?php echo $initial_answer; ?></textarea>
                    </div>
                    <div class="word-count mt-2">
                        Content Length: <span id="mainAnswerCount">0</span> words
                    </div>
                </div>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <a href="<?php echo $return_url ? htmlspecialchars($return_url) : ($faq ? 'faq.php?id='.$faq['id'] : 'index.php'); ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> <?php echo $faq ? 'Update' : 'Create FAQ'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Status Indicator -->
    <div id="statusIndicator" class="status-indicator"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize Quill.js WYSIWYG Editor
        const questionToolbarOptions = [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link']
        ];

        const questionQuill = new Quill('#question-editor', {
            modules: { toolbar: questionToolbarOptions },
            placeholder: 'Enter your question...',
            theme: 'snow'
        });
        // Keep question editor compact (single-line feel)
        questionQuill.root.style.minHeight = '32px';
        questionQuill.root.style.height = '32px';
        questionQuill.root.style.paddingTop = '6px';
        questionQuill.root.style.paddingBottom = '6px';

        const toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
            ['blockquote', 'code-block'],
            ['link', 'image', 'video', 'formula'],

            [{ 'header': 1 }, { 'header': 2 }],               // custom button values
            [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
            [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
            [{ 'direction': 'rtl' }],                         // text direction

            [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
            [{ 'font': [] }],
            [{ 'align': [] }],

            ['clean']                                         // remove formatting button
        ];

        const quill = new Quill('#quill-editor', {
            modules: {
                toolbar: toolbarOptions,
                table: true,
                history: {
                    delay: 2000,
                    maxStack: 500,
                    userOnly: true
                }
            },
            formats: [
                'header', 'bold', 'italic', 'underline', 'strike',
                'list', 'bullet', 'link', 'image', 'video', 'formula',
                'color', 'background', 'font', 'align', 'size',
                'script', 'indent', 'direction', 'table'
            ],
            placeholder: 'Enter your detailed FAQ answer here. Use the toolbar above to format your text...',
            theme: 'snow'
        });

        // Load existing question content if editing
        const existingQuestion = document.getElementById('question').value;
        if (existingQuestion) {
            questionQuill.root.innerHTML = existingQuestion;
        }
        // Keep hidden question field in sync
        questionQuill.on('text-change', function() {
            document.getElementById('question').value = questionQuill.root.innerHTML;
            saveLocalDraft();
        });

        // Load existing content if editing
        const existingContent = document.getElementById('main_answer').value || <?php echo json_encode($initial_answer); ?> || '';
        if (existingContent) {
            quill.clipboard.dangerouslyPasteHTML(normalizeTables(existingContent));
        }

        // Update hidden textarea when content changes
        quill.on('text-change', function() {
            document.getElementById('main_answer').value = quill.root.innerHTML;
            updateWordCount();
            saveLocalDraft();
        });

        // Update word counts
        function updateWordCount() {
            const mainContent = quill.getText();
            
            document.getElementById('mainAnswerCount').textContent = mainContent.trim() ? mainContent.trim().split(/\s+/).length : 0;
        }
        
        const faqId = <?php echo $faq ? (int) $faq['id'] : 'null'; ?>;
        const draftKey = `faq_draft_${faqId || 'new'}`;
        
        // Show status messages
        function showStatus(message, type) {
            const indicator = document.getElementById('statusIndicator');
            indicator.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    const alert = indicator.querySelector('.alert');
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 3000);
            }
        }
        
        // Form submission handler
        document.getElementById('faqForm').addEventListener('submit', function(e) {
            // Update the textarea with Quill content before submission
            document.getElementById('question').value = questionQuill.root.innerHTML;
            document.getElementById('main_answer').value = quill.root.innerHTML;
            const questionText = questionQuill.getText().trim();
            if (questionText) {
                document.getElementById('title-hidden').value = questionText;
            }
            clearLocalDraft();
        });
        
        // Event listeners
        // No short answer; just track main answer length
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 's':
                        e.preventDefault();
                        break;
                    case 'p':
                        e.preventDefault();
                        break;
                }
            }
        });
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            updateWordCount();
            loadLocalDraft();

            ['question', 'category_id', 'author', 'date_submitted'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', saveLocalDraft);
                    el.addEventListener('change', saveLocalDraft);
                }
            });

            // Restore from draft if available
            const raw = localStorage.getItem(draftKey);
            const switchPayloadRaw = sessionStorage.getItem('faq_switch_payload');
            if (switchPayloadRaw) {
                sessionStorage.removeItem('faq_switch_payload');
            }
            const combined = switchPayloadRaw ? JSON.parse(switchPayloadRaw) : (raw ? JSON.parse(raw) : null);
            if (combined) {
                try {
                    if (combined.question && document.getElementById('question')) {
                        document.getElementById('question').value = combined.question;
                        questionQuill.root.innerHTML = combined.question;
                    }
                    if (combined.category_id && document.getElementById('category_id')) {
                        document.getElementById('category_id').value = combined.category_id;
                    }
                    if (combined.author !== undefined && document.getElementById('author')) {
                        document.getElementById('author').value = combined.author;
                    }
                    if (combined.date_submitted && document.getElementById('date_submitted')) {
                        document.getElementById('date_submitted').value = combined.date_submitted;
                    }
                    if (combined.answer && document.getElementById('main_answer')) {
                        document.getElementById('main_answer').value = combined.answer;
                        // If the answer looks like Markdown (no HTML tags), render it to HTML before pasting
                        if (combined.answer.indexOf('<') === -1) {
                            renderMarkdownToHtml(combined.answer).then(html => {
                                quill.clipboard.dangerouslyPasteHTML(normalizeTables(html));
                                updateWordCount();
                            }).catch(() => {
                                quill.clipboard.dangerouslyPasteHTML(normalizeTables(combined.answer));
                                updateWordCount();
                            });
                        } else {
                            quill.clipboard.dangerouslyPasteHTML(normalizeTables(combined.answer));
                            updateWordCount();
                        }
                    }
                } catch (e) {
                    console.warn('Failed to load combined draft/switch payload for FAQ', e);
                }
            }

            // Editor toggle
            document.querySelectorAll('input[name="editorToggle"]').forEach(r => {
                r.addEventListener('change', function() {
                    if (!this.checked) return;
                    if (this.value === 'markdown') {
                        saveLocalDraft();
                        const targetUrl = 'edit-faq.php<?php echo $editorQuery; ?>';
                        // allow storage write
                        setTimeout(() => window.location.href = targetUrl, 50);
                    }
                });
            });
        });

        // Draft helpers (kept for editor switching)
        function saveLocalDraft() {
            const draft = {
                question: document.getElementById('question')?.value || '',
                category_id: document.getElementById('category_id')?.value || '',
                author: document.getElementById('author')?.value || '',
                date_submitted: document.getElementById('date_submitted')?.value || '',
                answer: document.getElementById('main_answer')?.value || '',
                saved_at: new Date().toISOString()
            };
            localStorage.setItem(draftKey, JSON.stringify(draft));
        }

        function loadLocalDraft() {
            const raw = localStorage.getItem(draftKey);
            if (!raw) return;
            try {
                const draft = JSON.parse(raw);
                if (draft.question && document.getElementById('question')) {
                    document.getElementById('question').value = draft.question;
                    questionQuill.root.innerHTML = draft.question;
                }
                if (draft.category_id && document.getElementById('category_id')) {
                    document.getElementById('category_id').value = draft.category_id;
                }
                if (draft.author !== undefined && document.getElementById('author')) {
                    document.getElementById('author').value = draft.author;
                }
                if (draft.date_submitted && document.getElementById('date_submitted')) {
                    document.getElementById('date_submitted').value = draft.date_submitted;
                }
                if (draft.answer && document.getElementById('main_answer')) {
                    document.getElementById('main_answer').value = draft.answer;
                    quill.clipboard.dangerouslyPasteHTML(normalizeTables(draft.answer));
                    updateWordCount();
                }
            } catch (e) {
                console.warn('Failed to load local draft', e);
            }
        }

        function clearLocalDraft() {
            localStorage.removeItem(draftKey);
        }

        async function renderMarkdownToHtml(markdown) {
            const response = await fetch('render-markdown.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'content=' + encodeURIComponent(markdown) + '&force_markdown=1'
            });
            return response.text();
        }

        // Normalize tables so Quill displays headers correctly
        function normalizeTables(html) {
            try {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                doc.querySelectorAll('table').forEach(table => {
                    const thead = table.querySelector('thead');
                    const tbody = table.querySelector('tbody') || table.appendChild(doc.createElement('tbody'));
                    if (thead) {
                        const rows = Array.from(thead.querySelectorAll('tr'));
                        rows.forEach((tr, idx) => {
                            const newTr = doc.createElement('tr');
                            tr.querySelectorAll('th,td').forEach(cell => {
                                const td = doc.createElement('td');
                                td.innerHTML = cell.innerHTML;
                                newTr.appendChild(td);
                            });
                            tbody.insertBefore(newTr, tbody.firstChild);
                        });
                        thead.remove();
                    }
                });
                return doc.body.innerHTML;
            } catch (e) {
                return html;
            }
        }
    </script>
</body>
</html>
