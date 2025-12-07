<?php
$page_title = 'Edit Release Checklist';
$page_description = 'Admin editing for the release checklist';
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
if (!$isAdmin) {
    header('Location: admin/login.php');
    exit;
}

// Default checklist data
function default_checklist() {
    return [
        'Technical Readiness' => [
            'Website Functionality' => [
                'Verify all FAQ pages load correctly (no 404s or missing content)',
                'Confirm Glossary page and hover-tooltips work on desktop and mobile',
                'Validate that the intro/welcome page appears only on a visitor’s first visit',
                'Confirm admin login issues are fully resolved',
                'Test all internal links, anchors, and cross-references',
                'Confirm search index is complete and accurate',
                'Test adding images and videos in WYSIWYG editor and confirm they render correctly',
            ],
            'Performance & Security' => [
                'Ensure HTTPS/SSL is active and valid on dieselsubs.com',
                'Test responsiveness across devices (iPhone, iPad, Mac)',
                'Test in multiple browsers (Safari, Chrome, Firefox)',
                'Check for console errors in browser DevTools',
                'Validate mobile navigation, menus, and breadcrumbs',
            ],
            'Feedback System' => [
                'Test feedback submission: anonymous, partial info, full info',
                'Verify feedback inserts into database tables correctly',
                'Confirm email notifications (if enabled)',
                'Check spam defenses (honeypot, rate limiting)',
                'Confirm feedback appears properly in admin dashboard',
            ],
        ],
        'Content Readiness' => [
            'FAQ Content' => [
                'Perform final proofread for clarity, grammar, and consistency',
                'Ensure glossary terms are complete and link correctly to definitions',
                'Update glossary definitions to begin with uppercase letters',
                'Verify all images: quality, alignment, captions',
                'Confirm each FAQ has a clear question and accurate, authoritative answer',
                'Add cross-links to related FAQs where relevant',
                'Remove placeholders or outdated testing text',
            ],
            'Historical Accuracy' => [
                'Dwight performs a final review for technical accuracy',
                'Validate references against manuals, diagrams, and historical records',
                'Add missing credits for diagrams, schematics, or photos',
            ],
        ],
        'UX & Visual Polish' => [
            'General' => [
                'Check screen reader compatibility (basic VoiceOver test)',
                'Verify typography, spacing, and formatting consistency',
                'Confirm color contrast meets accessibility expectations',
                'Conduct a “first-time visitor” walkthrough for clarity and orientation',
                'Validate category structure is intuitive and final',
                'Ensure mobile experience is clean and readable',
            ]
        ],
        'Branding & Presentation' => [
            'General' => [
                'Finalize favicon, dolphin insignia, and icons',
                'Confirm QR codes resolve to the production site',
                'Finalize About/Intro page language',
                'Verify docent credits and acknowledgments',
                'Ensure mission and project story messaging feels polished and authentic',
            ]
        ],
        'Launch Communication Preparation' => [
            'Outreach & PR' => [
                'Draft announcement email for Pampanito docents',
                'Draft outreach messages for Bowfin, U-505, and other museum partners',
                'Prepare 100–150 word press blurb',
                'Prepare longer newsletter-friendly announcement',
                'Finalize podcast pitch for Close All Tabs',
            ],
            'Visitor Cards' => [
                'Finalize design of 2.5×2.5 visitor cards',
                'Confirm QR code scanning works reliably',
                'Print small initial batch for review',
                'Collect feedback from several docents',
            ],
            'Social Media / Public Sharing (Optional)' => [
                'Draft social media announcement paragraph',
                'Create a promotional graphic or homepage screenshot',
                'Verify OpenGraph metadata previews correctly',
            ],
        ],
        'Museum-Facing Preparation' => [
            'General' => [
                'Ensure museum leadership has preview access',
                'Verify alignment with Pampanito’s visitor experience and restoration narrative',
                'Highlight Pampanito-specific FAQs for museum outreach',
                'Identify 3–5 FAQs recommended for museum newsletters or docent materials',
            ]
        ],
        'Post-Launch Infrastructure' => [
            'General' => [
                'Define workflow for reviewing and responding to visitor feedback',
                'Document admin dashboard usage (login, review, edits)',
                'Maintain internal “Known Issues” list',
                'Verify database backups are functioning',
                'Optional: Set up uptime monitoring for the site',
            ]
        ],
        'Long-Term Continuity & Handover Plan' => [
            'General' => [
                'Create the “How the System Works” one-page overview',
                'Document GitHub repository structure and hosting notes',
                'Identify individuals for future historical and technical stewardship roles',
                'Prepare a transition plan for long-term project maintenance',
                'Create a contributor onboarding guide (optional)',
            ]
        ],
    ];
}

function checklist_to_html($checklist) {
    $html = '';
    foreach ($checklist as $section => $groups) {
        $html .= '<h3>' . htmlspecialchars($section) . '</h3>';
        foreach ($groups as $group => $items) {
            $html .= '<h5>' . htmlspecialchars($group) . '</h5><ul>';
            foreach ($items as $item) {
                $html .= '<li>' . htmlspecialchars($item) . '</li>';
            }
            $html .= '</ul>';
        }
    }
    return $html;
}

function parse_checklist_html($html) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $body = $dom->getElementsByTagName('body')->item(0);

    $data = [];
    $currentSection = 'General';
    $currentGroup = 'Items';
    $ensure = function($section, $group) use (&$data) {
        if (!isset($data[$section])) {
            $data[$section] = [];
        }
        if (!isset($data[$section][$group])) {
            $data[$section][$group] = [];
        }
    };
    $ensure($currentSection, $currentGroup);

    $walker = function($node) use (&$walker, &$data, &$currentSection, &$currentGroup, $ensure) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);
            if (in_array($tag, ['h2', 'h3'])) {
                $text = trim($node->textContent);
                if ($text !== '') {
                    $currentSection = $text;
                    $currentGroup = 'Items';
                    $ensure($currentSection, $currentGroup);
                }
            } elseif (in_array($tag, ['h4', 'h5'])) {
                $text = trim($node->textContent);
                if ($text !== '') {
                    $currentGroup = $text;
                    $ensure($currentSection, $currentGroup);
                }
            } elseif (in_array($tag, ['ul', 'ol'])) {
                foreach ($node->getElementsByTagName('li') as $li) {
                    $itemText = trim($li->textContent);
                    if ($itemText !== '') {
                        $ensure($currentSection, $currentGroup);
                        $data[$currentSection][$currentGroup][] = $itemText;
                    }
                }
            } elseif ($tag === 'p') {
                $itemText = trim($node->textContent);
                if ($itemText !== '') {
                    $ensure($currentSection, $currentGroup);
                    $data[$currentSection][$currentGroup][] = $itemText;
                }
            }
        }
        foreach ($node->childNodes as $child) {
            $walker($child);
        }
    };

    if ($body) {
        foreach ($body->childNodes as $child) {
            $walker($child);
        }
    }

    // Clean empty groups/sections
    foreach ($data as $section => $groups) {
        foreach ($groups as $group => $items) {
            $items = array_values(array_filter(array_map('trim', $items)));
            if (empty($items)) {
                unset($data[$section][$group]);
            } else {
                $data[$section][$group] = $items;
            }
        }
        if (empty($data[$section])) {
            unset($data[$section]);
        }
    }

    return $data ?: null;
}

$message = '';
$error = '';

// Ensure storage table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS release_checklist (
            id INT PRIMARY KEY,
            content LONGTEXT NOT NULL,
            notes LONGTEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    try {
        $pdo->exec("ALTER TABLE release_checklist ADD COLUMN notes LONGTEXT NULL");
    } catch (Exception $e) {
        // ignore if exists
    }
} catch (Exception $e) {
    $error = 'Unable to prepare checklist storage: ' . $e->getMessage();
}

// Load existing content
$checklist = default_checklist();
$notesHtml = '';
if (!$error) {
    try {
        $stmt = $pdo->query("SELECT content, notes FROM release_checklist WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch();
        if ($row && $row['content']) {
            $decoded = json_decode($row['content'], true);
            if (is_array($decoded)) {
                $checklist = $decoded;
            }
            $notesHtml = $row['notes'] ?? '';
        } else {
            $stmt = $pdo->prepare("REPLACE INTO release_checklist (id, content, notes) VALUES (1, ?, ?)");
            $stmt->execute([json_encode($checklist), $notesHtml]);
        }
    } catch (Exception $e) {
        $error = 'Unable to load checklist: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && !$error) {
    $checklistHtml = $_POST['checklist_html'] ?? '';
    // Notes are preserved (no editing on this page)

    $parsed = parse_checklist_html($checklistHtml);
    if (!$parsed || !is_array($parsed)) {
        $error = 'Could not parse checklist content. Please ensure you use headings for sections/groups and bullets for items.';
    } else {
        try {
            $stmt = $pdo->prepare("REPLACE INTO release_checklist (id, content, notes) VALUES (1, ?, ?)");
            $stmt->execute([json_encode($parsed), $notesHtml]);
            $message = 'Checklist updated.';
            $checklist = $parsed;
            if (isset($_POST['autosave'])) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'message' => 'Checklist saved']);
                exit;
            }
        } catch (Exception $e) {
            if (isset($_POST['autosave'])) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
                exit;
            }
            $error = 'Failed to save checklist: ' . $e->getMessage();
        }
    }
}

$checklistHtmlPrefill = checklist_to_html($checklist);

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<style>
.ql-editor {
    font-size: 1.05rem;
}
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Edit Release Checklist</h1>
        <a class="btn btn-outline-secondary btn-sm" href="release-checklist.php">
            <i class="fas fa-arrow-left"></i> Back to Checklist
        </a>
    </div>

    <form method="POST" id="checklistForm">
        <div class="mb-4">
            <label class="form-label fw-bold">Checklist</label>
            <div id="checklistEditor" style="min-height: 260px;"></div>
            <textarea id="checklist_html" name="checklist_html" style="display:none;"><?php echo htmlspecialchars($checklistHtmlPrefill); ?></textarea>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Quill === 'undefined') {
        console.error('Quill failed to load.');
        return;
    }

    const checklistField = document.getElementById('checklist_html');

    const checklistEditor = new Quill('#checklistEditor', {
        theme: 'snow',
        placeholder: 'Use heading 3 for sections, heading 5 for groups, and bullets for items...',
        modules: {
            toolbar: [
                [{ header: [3, 5, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });

    if (checklistField.value) {
        checklistEditor.clipboard.dangerouslyPasteHTML(checklistField.value);
    }

    const debounce = (fn, delay) => {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    };

    const autosave = debounce(() => {
        const html = checklistEditor.root.innerHTML;
        fetch('release-checklist-edit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                checklist_html: html,
                autosave: '1'
            })
        })
        .then(res => res.json())
        .catch(() => {});
    }, 800);

    checklistEditor.on('text-change', autosave);
});
</script>

<?php require_once 'includes/footer.php'; ?>
