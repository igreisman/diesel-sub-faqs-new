<?php
$page_title = 'Release Checklist';
$page_description = 'Pre-launch checklist';

require_once 'config/database.php';

if (PHP_SESSION_NONE === session_status()) {
    session_start();
}
$isAdmin = isset($_SESSION['admin_logged_in']) && true === $_SESSION['admin_logged_in'];

// Default checklist data
function default_checklist()
{
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
            ],
        ],
        'Branding & Presentation' => [
            'General' => [
                'Finalize favicon, dolphin insignia, and icons',
                'Confirm QR codes resolve to the production site',
                'Finalize About/Intro page language',
                'Verify docent credits and acknowledgments',
                'Ensure mission and project story messaging feels polished and authentic',
            ],
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
            ],
        ],
        'Post-Launch Infrastructure' => [
            'General' => [
                'Define workflow for reviewing and responding to visitor feedback',
                'Document admin dashboard usage (login, review, edits)',
                'Maintain internal “Known Issues” list',
                'Verify database backups are functioning',
                'Optional: Set up uptime monitoring for the site',
            ],
        ],
        'Long-Term Continuity & Handover Plan' => [
            'General' => [
                'Create the “How the System Works” one-page overview',
                'Document GitHub repository structure and hosting notes',
                'Identify individuals for future historical and technical stewardship roles',
                'Prepare a transition plan for long-term project maintenance',
                'Create a contributor onboarding guide (optional)',
            ],
        ],
    ];
}

$message = '';
$error = '';

// Ensure storage table exists
try {
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS release_checklist (
            id INT PRIMARY KEY,
            content LONGTEXT NOT NULL,
            notes LONGTEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ');

    // Add notes column if missing
    try {
        $pdo->exec('ALTER TABLE release_checklist ADD COLUMN notes LONGTEXT NULL');
    } catch (Exception $e) {
        // ignore if exists
    }
} catch (Exception $e) {
    $error = 'Unable to prepare checklist storage: '.$e->getMessage();
}

// Load checklist
$checklist = default_checklist();
$notesHtml = '';
if (!$error) {
    try {
        $stmt = $pdo->query('SELECT content, notes FROM release_checklist WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        if ($row && $row['content']) {
            $decoded = json_decode($row['content'], true);
            if (is_array($decoded)) {
                $checklist = $decoded;
            }
            $notesHtml = $row['notes'] ?? '';
        } else {
            // Seed defaults
            $stmt = $pdo->prepare('REPLACE INTO release_checklist (id, content, notes) VALUES (1, ?, ?)');
            $stmt->execute([json_encode($checklist), $notesHtml]);
        }
    } catch (Exception $e) {
        $error = 'Unable to load checklist: '.$e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Release Checklist</h1>
        <?php if ($isAdmin) { ?>
            <a class="btn btn-sm btn-outline-primary" href="release-checklist-edit.php">
                <i class="fas fa-pen"></i> Edit Items
            </a>
        <?php } ?>
    </div>

    <?php if ($message) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>
    <?php if ($error) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <article class="card shadow-sm">
        <div class="card-body">
            <h2 class="h4">Diesel-Electric Submarine FAQs — Pre-Launch Checklist</h2>
            <p class="text-muted mb-1">Shared release checklist for Irving &amp; Dwight</p>
            <p class="text-muted"><em>Last updated: 2025-12-06</em></p>

            <?php if (!empty($notesHtml)) { ?>
                <div class="alert alert-info">
                    <?php echo $notesHtml; ?>
                </div>
            <?php } ?>

            <?php $sectionIndex = 1; ?>
            <?php foreach ($checklist as $section => $groups) { ?>
                <h3 class="mt-4"><?php echo $sectionIndex.'. '.htmlspecialchars($section); ?></h3>
                <?php foreach ($groups as $group => $items) { ?>
                    <h5 class="mt-3"><?php echo htmlspecialchars($group); ?></h5>
                    <ul class="list-unstyled ms-1 checklist-group">
                        <?php foreach ($items as $idx => $item) {
                            $id = 'chk_'.md5($section.'|'.$group.'|'.$item);
                            ?>
                            <li class="form-check">
                                <input class="form-check-input checklist-item" type="checkbox" id="<?php echo $id; ?>" data-key="<?php echo $id; ?>">
                                <label class="form-check-label" for="<?php echo $id; ?>"><?php echo htmlspecialchars($item); ?></label>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php ++$sectionIndex; ?>
            <?php } ?>
        </div>
    </article>

</div>

<script>
// Simple localStorage persistence per browser (non-blocking even if storage is corrupted)
(function() {
    const storageKey = 'release_checklist';
    let saved = {};
    try {
        const raw = localStorage.getItem(storageKey);
        saved = raw ? JSON.parse(raw) : {};
    } catch (e) {
        saved = {};
    }
    const boxes = document.querySelectorAll('.checklist-item');

    boxes.forEach(box => {
        const key = box.dataset.key;
        if (saved[key]) {
            box.checked = true;
        }
        box.addEventListener('change', () => {
            saved[key] = box.checked;
            localStorage.setItem(storageKey, JSON.stringify(saved));
        });
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
