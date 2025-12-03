<?php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/markdown-helper.php';

$faq_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preset_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$faq = null;
$editorQuery = '';

if ($faq_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$faq_id]);
    $faq = $stmt->fetch();
    
    if (!$faq) {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?> - Submarine FAQ Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <style>
        .form-floating textarea {
            height: 120px;
        }
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
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
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-edit text-primary"></i> <?php echo $faq ? 'Edit FAQ' : 'Create New FAQ'; ?></h1>
                    <div class="editor-toggle d-flex align-items-center">
                        <?php $editorQuery = $faq ? '?id=' . $faq['id'] : ($preset_category_id ? '?category_id=' . $preset_category_id : ''); ?>
                        <div class="form-check form-check-inline me-3">
                            <input class="form-check-input" type="radio" name="editorToggle" id="editorWysiwyg" value="wysiwyg">
                            <label class="form-check-label" for="editorWysiwyg">WYSIWYG</label>
                        </div>
                        <div class="form-check form-check-inline me-2">
                            <input class="form-check-input" type="radio" name="editorToggle" id="editorMarkdown" value="markdown" checked>
                            <label class="form-check-label" for="editorMarkdown">Markdown</label>
                        </div>
                        <a href="<?php echo $faq ? 'faq.php?id=' . $faq['id'] : 'index.php'; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="faqForm" method="POST" action="save-faq.php">
            <?php if ($faq): ?>
                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
            <?php endif; ?>
            <?php
                $plainQuestion = $faq ? htmlspecialchars(strip_tags($faq['question'])) : '';
                $initialTitle = $faq ? htmlspecialchars($faq['title'] ?? $plainQuestion) : '';
            ?>
            <input type="hidden" id="title-hidden" name="title" value="<?php echo $initialTitle; ?>">
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
                            foreach ($cats as $cat):
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
                            <?php endforeach; ?>
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
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="date_submitted" name="date_submitted"
                               value="<?php echo $faq && !empty($faq['date_submitted']) ? date('Y-m-d', strtotime($faq['date_submitted'])) : ''; ?>">
                        <label for="date_submitted"><i class="fas fa-calendar-alt"></i> Date Submitted (optional)</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="question"><i class="fas fa-question-circle"></i> Question</label>
                    <input type="text" class="form-control" id="question" name="question" 
                           value="<?php echo $faq ? htmlspecialchars(strip_tags($faq['question'])) : ''; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-edit"></i> Answer</label>
                <div class="mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('**Bold Text**')">Bold</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('*Italic Text*')">Italic</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('# Header 1')">H1</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('## Header 2')">H2</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('### Header 3')">H3</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('- List item')">List</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('1. Numbered item')">Numbers</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('> Quote')">Quote</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('`code`')">Code</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertMarkdown('[Link Text](URL)')">Link</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertTable()">Table</button>
                </div>
                <textarea class="form-control" id="main_answer" name="main_answer" rows="14" placeholder="Write your answer in Markdown..."><?php echo $faq ? htmlspecialchars($faq['answer']) : ''; ?></textarea>
            </div>

            <div class="row mt-4 mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-end">
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> <?php echo $faq ? 'Update FAQ' : 'Create FAQ'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Status Indicator -->
    <div id="statusIndicator" class="status-indicator"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const faqId = <?php echo $faq ? (int)$faq['id'] : 'null'; ?>;
        const draftKey = `faq_draft_${faqId || 'new'}`;

        // Save draft
        function saveDraft() {
            showStatus('Saving draft...', 'info');

            const formData = new FormData(document.getElementById('faqForm'));
            formData.append('save_draft', '1');
            syncTitleFromQuestion();
            
            fetch('save-faq.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('Draft saved successfully!', 'success');
                    clearLocalDraft();
                } else {
                    showStatus('Error saving draft: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                showStatus('Error saving draft: ' + error.message, 'danger');
            });
        }

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

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            syncTitleFromQuestion();
            const questionInput = document.getElementById('question');
            if (questionInput) {
                questionInput.addEventListener('input', syncTitleFromQuestion);
            }
            loadLocalDraft();
            attachDraftListeners();

            // Clear local draft on submit
            document.getElementById('faqForm').addEventListener('submit', clearLocalDraft);

            // If the answer field contains HTML, coerce it to Markdown for editing
            const ansEl = document.getElementById('main_answer');
            if (ansEl && ansEl.value && ansEl.value.indexOf('<') !== -1) {
                ansEl.value = htmlToMarkdown(ansEl.value);
            }

            // Editor toggle
            document.querySelectorAll('input[name="editorToggle"]').forEach(r => {
                r.addEventListener('change', function() {
                    if (!this.checked) return;
                    if (this.value === 'wysiwyg') {
                        saveLocalDraft();
                        // Also stash current form into sessionStorage for immediate transfer
                        sessionStorage.setItem('faq_switch_payload', JSON.stringify(collectCurrentPayload()));
                        const targetUrl = 'edit-faq-wysiwyg.php<?php echo $editorQuery; ?>';
                        setTimeout(() => window.location.href = targetUrl, 20); // allow storage write
                    }
                });
            });
        });

        // Quick Markdown insertion helpers for the textarea
        function insertMarkdown(snippet) {
            const textarea = document.getElementById('main_answer');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const value = textarea.value;

            textarea.value = value.substring(0, start) + snippet + value.substring(end);
            // Place cursor after inserted text
            const cursorPos = start + snippet.length;
            textarea.focus();
            textarea.setSelectionRange(cursorPos, cursorPos);
        }

        function insertTable() {
            const tableTemplate = `| Column 1 | Column 2 | Column 3 |
|----------|----------|----------|
| Row 1    | Data     | Data     |
| Row 2    | Data     | Data     |`;
            insertMarkdown(tableTemplate);
        }

        function syncTitleFromQuestion() {
            const q = document.getElementById('question');
            const t = document.getElementById('title-hidden');
            if (q && t) {
                t.value = q.value || '';
            }
        }

        function attachDraftListeners() {
            const fields = ['question', 'main_answer', 'category_id', 'author', 'date_submitted'];
            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', saveLocalDraft);
                    el.addEventListener('change', saveLocalDraft);
                }
            });
        }

        function saveLocalDraft() {
            const draft = {
                question: document.getElementById('question')?.value || '',
                answer: document.getElementById('main_answer')?.value || '',
                category_id: document.getElementById('category_id')?.value || '',
                author: document.getElementById('author')?.value || '',
                date_submitted: document.getElementById('date_submitted')?.value || '',
                saved_at: new Date().toISOString()
            };
            localStorage.setItem(draftKey, JSON.stringify(draft));
        }

        function collectCurrentPayload() {
            return {
                question: document.getElementById('question')?.value || '',
                answer: document.getElementById('main_answer')?.value || '',
                category_id: document.getElementById('category_id')?.value || '',
                author: document.getElementById('author')?.value || '',
                date_submitted: document.getElementById('date_submitted')?.value || '',
                faq_id: <?php echo $faq ? (int)$faq['id'] : 'null'; ?>
            };
        }

        function loadLocalDraft() {
            const raw = localStorage.getItem(draftKey);
            if (!raw) return;
            try {
                const draft = JSON.parse(raw);
                if (draft.question && document.getElementById('question')) {
                    const cleanQ = toPlainText(draft.question);
                    document.getElementById('question').value = cleanQ;
                    syncTitleFromQuestion();
                }
                if (draft.answer && document.getElementById('main_answer')) {
                    document.getElementById('main_answer').value = coerceHtmlToMarkdown(draft.answer);
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
            } catch (e) {
                console.warn('Failed to load draft from localStorage', e);
            }
        }

        function clearLocalDraft() {
            localStorage.removeItem(draftKey);
        }

        // Basic HTML-to-Markdown coercion for pasted/loaded HTML answers
        function coerceHtmlToMarkdown(value) {
            if (!value || value.indexOf('<') === -1) return value;
            try {
                return htmlToMarkdown(value);
            } catch (e) {
                return value.replace(/<[^>]+>/g, '');
            }
        }

        function toPlainText(html) {
            if (!html || html.indexOf('<') === -1) return html || '';
            const div = document.createElement('div');
            div.innerHTML = html;
            return (div.textContent || '').trim();
        }

        // Robust-ish HTML to Markdown conversion for editor seeding
        function htmlToMarkdown(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            function convertTable(table) {
                const rows = Array.from(table.querySelectorAll('tr'));
                if (!rows.length) return '';
                const headerCells = Array.from(rows[0].querySelectorAll('th'));
                const bodyRows = headerCells.length ? rows.slice(1) : rows;
                let md = '';
                if (headerCells.length) {
                    const header = headerCells.map(c => c.textContent.trim()).join(' | ');
                    const sep = headerCells.map(() => '---').join(' | ');
                    md += `| ${header} |\n| ${sep} |\n`;
                }
                bodyRows.forEach(tr => {
                    const cells = Array.from(tr.querySelectorAll('td,th')).map(c => c.textContent.trim());
                    if (cells.length) {
                        md += `| ${cells.join(' | ')} |\n`;
                    }
                });
                return md.trim();
            }

            function walk(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    return node.textContent;
                }
                if (node.nodeType !== Node.ELEMENT_NODE) return '';
                const tag = node.tagName.toLowerCase();
                const content = Array.from(node.childNodes).map(walk).join('');
                switch (tag) {
                    case 'h1': return `# ${content}\n\n`;
                    case 'h2': return `## ${content}\n\n`;
                    case 'h3': return `### ${content}\n\n`;
                    case 'h4': return `#### ${content}\n\n`;
                    case 'h5': return `##### ${content}\n\n`;
                    case 'h6': return `###### ${content}\n\n`;
                    case 'p': return `${content}\n\n`;
                    case 'br': return '\n';
                    case 'strong':
                    case 'b': return `**${content}**`;
                    case 'em':
                    case 'i': return `*${content}*`;
                    case 'code': return `\`${content}\``;
                    case 'pre': return `\`\`\`\n${content}\n\`\`\`\n\n`;
                    case 'a': {
                        const href = node.getAttribute('href') || '#';
                        return `[${content}](${href})`;
                    }
                    case 'ul': return Array.from(node.children).map(li => `- ${walk(li)}`).join('\n') + '\n\n';
                    case 'ol': {
                        let idx = 1;
                        return Array.from(node.children).map(li => `${idx++}. ${walk(li)}`).join('\n') + '\n\n';
                    }
                    case 'li': return content;
                    case 'table': return convertTable(node) + '\n\n';
                    default: return content;
                }
            }

            return walk(doc.body).replace(/\n{3,}/g, '\n\n').trim();
        }
    </script>
</body>
</html>
