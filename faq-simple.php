<?php
require_once 'config/database.php';

$page_title = 'Simple FAQ Explorer';
$page_description = 'One-page view of every published FAQ with instant in-page search.';

try {
    $categories = $pdo->query("
        SELECT id, name, slug, description, sort_order
        FROM categories
        ORDER BY sort_order ASC, name ASC
    ")->fetchAll();

    $faqsStmt = $pdo->prepare("
        SELECT id, category_id, title, slug, question, answer, tags, updated_at, created_at, display_order
        FROM faqs
        WHERE status = 'published'
        ORDER BY display_order ASC, title ASC
    ");
    $faqsStmt->execute();
    $faqs = $faqsStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $faqs = [];
}

// Group FAQs by category
$grouped = [];
foreach ($categories as $cat) {
    $grouped[$cat['id']] = [
        'meta' => $cat,
        'faqs' => []
    ];
}

foreach ($faqs as $faq) {
    if (isset($grouped[$faq['category_id']])) {
        $grouped[$faq['category_id']]['faqs'][] = $faq;
    } else {
        // Bucket uncategorized in a synthetic group
        if (!isset($grouped[0])) {
            $grouped[0] = [
                'meta' => [
                    'id' => 0,
                    'name' => 'Uncategorized',
                    'slug' => 'uncategorized',
                    'description' => 'FAQs that do not have a category set',
                    'sort_order' => 9999
                ],
                'faqs' => []
            ];
        }
        $grouped[0]['faqs'][] = $faq;
    }
}

// Helper to make a safe anchor id
function faq_anchor_id($faq, $fallbackPrefix = 'faq') {
    $raw = !empty($faq['slug']) ? $faq['slug'] : $fallbackPrefix . '-' . $faq['id'];
    $slug = strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', $raw));
    $slug = trim($slug, '-');
    return $slug ?: ($fallbackPrefix . '-' . $faq['id']);
}

// Helper to render answers:
// - If it already contains HTML tags (common for pre-rendered markdown), trust and output as-is.
// - Otherwise, escape and add line breaks for plain text.
function render_answer($text) {
    $text = $text ?? '';
    $trimmed = trim($text);
    if ($trimmed !== '' && preg_match('/<\/?[a-z][\\s\\S]*>/i', $trimmed)) {
        return $text;
    }
    return nl2br(htmlspecialchars($text));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title . ' | ' . SITE_NAME); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; color: #1f2933; }
        .page-shell { max-width: 1100px; margin: 0 auto; padding: 24px 12px 60px; }
        .hero { background: linear-gradient(135deg, #0f2e4d, #18426c); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 18px; }
        .hero a { color: #d8e9ff; text-decoration: underline; }
        .search-card { background: #fff; border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.06); padding: 16px 18px; margin-bottom: 16px; }
        .category-nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
        .category-chip { background: #0f2e4d; color: #fff; padding: 8px 12px; border-radius: 999px; font-size: 0.9rem; text-decoration: none; }
        .category-chip small { opacity: 0.75; }
        .faq-section { margin-top: 22px; }
        .faq-section h2 { font-size: 1.4rem; margin-bottom: 8px; }
        .faq-section p.section-desc { margin-bottom: 8px; color: #4b5563; }
        .faq-list { display: grid; gap: 8px; }
        details.faq-item { background: #fff; border-radius: 10px; padding: 12px 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
        details summary { cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center; list-style: none; }
        details summary::-webkit-details-marker { display: none; }
        details summary .title { margin-right: 12px; }
        .faq-body { margin-top: 10px; color: #111827; }
        .faq-meta { font-size: 0.85rem; color: #6b7280; display: flex; gap: 12px; align-items: center; margin-top: 12px; }
        .copy-btn { background: transparent; border: 1px solid #cbd5e1; color: #0f2e4d; padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; }
        .copy-btn:hover { background: #0f2e4d; color: #fff; }
        .empty { text-align: center; padding: 32px; color: #6b7280; }
        /* Table styling for FAQ content */
        .faq-body table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .faq-body th, .faq-body td { border: 1px solid #bcd6f2; padding: 8px 10px; }
        .faq-body th,
        .faq-body thead th,
        .faq-body tr:first-child th,
        .faq-body tr:first-child td { background: #e6f2ff; font-weight: 600; }
        @media (max-width: 768px) {
            .page-shell { padding: 16px 10px 48px; }
            details summary { flex-direction: column; align-items: flex-start; gap: 6px; }
            .category-nav { gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="hero">
            <h1 class="h3 mb-2"><?php echo htmlspecialchars(SITE_NAME); ?> — Simple View</h1>
            <p class="mb-2"><?php echo htmlspecialchars($page_description); ?></p>
            <p class="mb-0">
                Live data pulls from the same database the admin uses. Add, edit, or delete FAQs in the CMS and they appear here automatically.
                Need the raw feed? See <code>/api/faqs-simple.php</code>.
            </p>
        </div>

        <div class="search-card">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                    <label for="faq-search" class="form-label mb-1">Filter instantly</label>
                    <div class="input-group">
                        <input type="search" id="faq-search" class="form-control" placeholder="Search titles, questions, answers, tags...">
                        <button id="clear-search" class="btn btn-outline-secondary" type="button">Clear</button>
                    </div>
                </div>
                <div id="result-count" class="text-muted small ms-auto"></div>
            </div>
        </div>

        <?php if (!empty($grouped)): ?>
            <div class="category-nav">
                <?php foreach ($grouped as $group):
                    $meta = $group['meta'];
                    $count = count($group['faqs']);
                    $slug = !empty($meta['slug']) ? $meta['slug'] : ('category-' . $meta['id']);
                    $anchor = 'cat-' . htmlspecialchars($slug);
                ?>
                    <a class="category-chip" href="#<?php echo $anchor; ?>">
                        <?php echo htmlspecialchars($meta['name']); ?>
                        <small>(<?php echo $count; ?>)</small>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php foreach ($grouped as $group):
                $meta = $group['meta'];
                $faqsInGroup = $group['faqs'];
                $slug = !empty($meta['slug']) ? $meta['slug'] : ('category-' . $meta['id']);
                $sectionId = 'cat-' . htmlspecialchars($slug);
            ?>
                <section class="faq-section" id="<?php echo $sectionId; ?>" data-category-section>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h2 class="mb-0"><?php echo htmlspecialchars($meta['name']); ?></h2>
                        <span class="badge bg-secondary"><?php echo count($faqsInGroup); ?> FAQs</span>
                    </div>
                    <?php if (!empty($meta['description'])): ?>
                        <p class="section-desc"><?php echo htmlspecialchars($meta['description']); ?></p>
                    <?php endif; ?>
                    <?php if (empty($faqsInGroup)): ?>
                        <div class="empty">No FAQs in this category yet.</div>
                    <?php else: ?>
                        <div class="faq-list">
                            <?php foreach ($faqsInGroup as $faq):
                                $faqAnchor = faq_anchor_id($faq);
                                $searchText = strtolower(
                                    strip_tags(($faq['title'] ?? '') . ' ' .
                                    ($faq['question'] ?? '') . ' ' .
                                    ($faq['answer'] ?? '') . ' ' .
                                    ($faq['tags'] ?? ''))
                                );
                            ?>
                                <details class="faq-item" id="<?php echo $faqAnchor; ?>" data-faq-item data-search="<?php echo htmlspecialchars($searchText); ?>">
                                    <summary>
                                        <span class="title"><?php echo htmlspecialchars($faq['title'] ?? $faq['question']); ?></span>
                                        <span class="text-muted small">#<?php echo (int)$faq['id']; ?></span>
                                    </summary>
                                    <div class="faq-body">
                                        <p class="mb-2"><?php echo render_answer($faq['answer']); ?></p>
                                        <div class="faq-meta">
                                            <span>Last updated: <?php echo htmlspecialchars(format_date($faq['updated_at'] ?? $faq['created_at'] ?? date('Y-m-d'))); ?></span>
                                            <?php if (!empty($faq['tags'])): ?>
                                                <span>Tags: <?php echo htmlspecialchars($faq['tags']); ?></span>
                                            <?php endif; ?>
                                            <button class="copy-btn" type="button" data-copy-anchor="<?php echo $faqAnchor; ?>">Copy link</button>
                                        </div>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">
                <p class="mb-2">No FAQs available yet.</p>
                <p class="mb-0 text-muted">Add content in the admin and refresh this page.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    (() => {
        const searchInput = document.getElementById('faq-search');
        const clearBtn = document.getElementById('clear-search');
        const faqItems = Array.from(document.querySelectorAll('[data-faq-item]'));
        const sections = Array.from(document.querySelectorAll('[data-category-section]'));
        const resultCount = document.getElementById('result-count');

        function applyFilter() {
            const query = searchInput.value.trim().toLowerCase();
            let visible = 0;

            faqItems.forEach(item => {
                const haystack = item.dataset.search || '';
                const match = !query || haystack.includes(query);
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            sections.forEach(section => {
                const anyVisible = section.querySelector('[data-faq-item]:not([style*="display: none"])');
                section.style.display = anyVisible ? '' : 'none';
            });

            if (resultCount) {
                const label = visible === 1 ? 'FAQ shown' : 'FAQs shown';
                resultCount.textContent = `${visible} ${label}`;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                applyFilter();
                searchInput.focus();
            });
        }
        applyFilter();

        document.querySelectorAll('[data-copy-anchor]').forEach(button => {
            button.addEventListener('click', () => {
                const anchor = button.getAttribute('data-copy-anchor');
                const url = `${window.location.origin}${window.location.pathname}#${anchor}`;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).catch(() => {});
                } else {
                    // Fallback: update location hash
                    window.location.hash = anchor;
                }
                const original = button.textContent;
                button.textContent = 'Link copied';
                setTimeout(() => { button.textContent = original; }, 1400);
            });
        });
    })();
    </script>
</body>
</html>
