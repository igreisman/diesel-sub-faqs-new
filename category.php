<?php
require_once 'config/database.php';
require_once 'includes/markdown-helper.php';

$category_name = $_GET['cat'] ?? '';
if (empty($category_name)) {
    header('Location: index.php');
    exit;
}

// Get category information
$stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ?");
$stmt->execute([$category_name]);
$category = $stmt->fetch();

if (!$category) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Category Not Found';
    require_once 'includes/header.php';
    echo '<div class="container"><h1>Category Not Found</h1><p>The requested category does not exist.</p></div>';
    require_once 'includes/footer.php';
    exit;
}

// Get FAQs for this category
$stmt = $pdo->prepare("
    SELECT * FROM faqs 
    WHERE category_id = ? AND status = 'published' 
    ORDER BY display_order ASC, featured DESC, title ASC
");
$stmt->execute([$category['id']]);
$faqs = $stmt->fetchAll();

// Preload contributions grouped by FAQ
$contribMap = [];
$contribStmt = $pdo->prepare("
    SELECT faq_id, contributor_name, contributed_at, notes
    FROM faq_contributions
    WHERE faq_id IN (SELECT id FROM faqs WHERE category_id = ?)
    ORDER BY contributed_at DESC, id DESC
");
$contribStmt->execute([$category['id']]);
while ($row = $contribStmt->fetch()) {
    $contribMap[$row['faq_id']][] = $row;
}

// Load glossary terms for tooltips
$glossaryTerms = [];
try {
    $gStmt = $pdo->query("SELECT term, definition FROM glossary");
    $glossaryTerms = $gStmt->fetchAll();
} catch (Exception $e) {
    $glossaryTerms = [];
}

$page_title = $category['name'];
$page_description = '';
// Clear description to avoid showing legacy "Questions about ..." copy
$category['description'] = '';
require_once 'includes/header.php';

function category_icon_fallback($name, $icon) {
    $icon = trim((string)$icon);
    if (!empty($icon) && stripos($icon, 'question-circle') === false) {
        return $icon;
    }
    $map = [
        'us ww2 subs in general' => 'fas fa-ship',
        'hull and compartments' => 'fas fa-cogs',
        'operating us subs in ww2' => 'fas fa-compass',
        'life aboard ww2 us subs' => 'fas fa-users',
        'who were the crews aboard ww2 us subs' => 'fas fa-user-friends',
        'crews aboard ww2 us subs' => 'fas fa-user-friends',
        'attacks and battles, small and large' => 'fas fa-crosshairs',
    ];
    $key = strtolower(trim($name));
    return $map[$key] ?? 'fas fa-ship';
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb and Search -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    </ol>
                </nav>
                <div class="category-search flex-grow-1" style="max-width: 640px;">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" placeholder="Enter text to search category..." id="category-search">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>

            <!-- Category name below breadcrumb -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h1 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h1>
                <a href="edit-faq-wysiwyg.php?category_id=<?php echo (int)$category['id']; ?>&return=<?php echo urlencode('category.php?cat=' . $category['name']); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add FAQ to this Category
                </a>
            </div>

            <?php if (empty($faqs)): ?>
                <div class="alert alert-info">
                    <h4>No FAQs Available</h4>
                    <p>There are currently no FAQs available in this category. Check back later for updates!</p>
                    <a href="index.php" class="btn btn-primary">Browse Other Categories</a>
                </div>
            <?php else: ?>
                <!-- Spacer after header/search -->
                <div class="mb-4"></div>

                <!-- Feedback Incentive Section -->
                <div class="feedback-incentive mb-4">
                    <div class="alert alert-info border-info">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-trophy fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1">Help Us Improve This Category!</h5>
                                <p class="mb-2">Your feedback really helps.</p>
                            </div>
                            <div class="ms-2">
                                <a href="feedback.php?category_id=<?php echo (int)$category['id']; ?>&category=<?php echo urlencode($category['name']); ?>" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-star"></i> Share Feedback
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQs List -->
                <ol class="faqs-list list-unstyled">
                    <?php foreach ($faqs as $idx => $faq): ?>
                        <?php $num = $idx + 1; ?>
                        <li class="faq-row" data-faq-id="<?php echo $faq['id']; ?>">
                            <div class="faq-row-header" data-bs-toggle="collapse" data-bs-target="#faq-collapse-<?php echo $faq['id']; ?>" aria-expanded="false">
                                <span class="faq-number"><?php echo $num; ?></span>
                                <span class="faq-question"><?php echo htmlspecialchars($faq['title']); ?></span>
                                <span class="faq-arrow"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <div id="faq-collapse-<?php echo $faq['id']; ?>" class="collapse">
                                    <div class="faq-body">
                                        <div class="faq-content">
                                            <?php echo render_content($faq['answer']); ?>
                                        </div>
                                    <?php if (!empty($faq['author']) || !empty($faq['date_submitted'])): ?>
                                        <div class="text-muted mt-3">
                                            <strong>Created by:</strong>
                                            <?php if (!empty($faq['author'])): ?>
                                                <span class="ms-1"><i class="fas fa-user"></i> <?php echo htmlspecialchars($faq['author']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($faq['date_submitted'])): ?>
                                                <span class="ms-3"><i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($faq['date_submitted'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($contribMap[$faq['id']])): ?>
                                        <?php $label = count($contribMap[$faq['id']]) > 1 ? 'Contributions by:' : 'Contribution by:'; ?>
                                        <div class="mt-2 text-muted">
                                            <?php foreach ($contribMap[$faq['id']] as $idx => $c): ?>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="contrib-label"><?php echo $idx === 0 ? $label : ''; ?></span>
                                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($c['contributor_name']); ?></span>
                                                    <?php if (!empty($c['contributed_at'])): ?>
                                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($c['contributed_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php 
                                    $current_faq = $faq;
                                    include 'includes/feedback-widget.php'; 
                                    ?>
                                    <div class="faq-actions mt-3">
                                        <a href="faq.php?id=<?php echo $faq['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-link"></i> Direct Link
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="copyLink(<?php echo $faq['id']; ?>)">
                                            <i class="fas fa-copy"></i> Copy Link
                                        </button>
                                    </div>
                                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                                        <div class="faq-actions mt-2">
                                            <a href="edit-faq-wysiwyg.php?id=<?php echo $faq['id']; ?>&return=<?php echo urlencode('category.php?cat=' . $category['name']); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" action="admin/manage-faqs.php" class="d-inline" onsubmit="return confirm('Delete this FAQ? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <!-- Back to Categories -->
                <div class="text-center mt-5">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to All Categories
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Category search functionality
document.getElementById('category-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const title = item.querySelector('.btn-link').textContent.toLowerCase();
        const content = item.querySelector('.faq-content').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Copy link functionality
function copyLink(faqId) {
    const url = window.location.origin + '/faq.php?id=' + faqId;
    navigator.clipboard.writeText(url).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
        }, 2000);
    });
}

// Track FAQ views when expanded
document.addEventListener('DOMContentLoaded', function() {
    const faqColllapses = document.querySelectorAll('[id^="faq-collapse-"]');
    faqColllapses.forEach(collapse => {
        collapse.addEventListener('shown.bs.collapse', function() {
            const faqId = this.id.replace('faq-collapse-', '');
            // Track view (you can send AJAX request to track views)
            fetch('api/track-view.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({faq_id: faqId})
            });
        });
    });

    // Ensure tag clicks navigate
    document.querySelectorAll('.faq-tags .tag-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            const href = this.getAttribute('href');
            if (href) {
                window.location.href = href;
            }
        });
        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href) {
                    window.location.href = href;
                }
            }
        });
    });
});
</script>

<script>
    // Glossary highlighting with tooltips
    function initGlossaryTooltips() {
        const glossary = <?php echo json_encode($glossaryTerms, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        if (!glossary || !glossary.length) return;

        const patterns = glossary.map(({term, definition}) => {
            const esc = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return {
                term,
                definition,
                regex: new RegExp(`\\b${esc}\\b`, 'gi')
            };
        });

        const containers = document.querySelectorAll('.faq-content');

        const walkerOptions = {
            acceptNode(node) {
                if (node.parentNode && node.parentNode.classList && node.parentNode.classList.contains('glossary-term')) {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            }
        };

        function highlightTextNode(textNode) {
            const text = textNode.nodeValue;
            let cursor = 0;
            const frag = document.createDocumentFragment();
            let changed = false;

            while (cursor < text.length) {
                let earliest = null;
                let earliestPattern = null;

                for (const p of patterns) {
                    p.regex.lastIndex = cursor;
                    const m = p.regex.exec(text);
                    if (m && (earliest === null || m.index < earliest.index)) {
                        earliest = m;
                        earliestPattern = p;
                    }
                }

                if (!earliest) break;

                if (earliest.index > cursor) {
                    frag.appendChild(document.createTextNode(text.slice(cursor, earliest.index)));
                }

                const span = document.createElement('span');
                span.className = 'glossary-term';
                span.textContent = earliest[0];
                span.setAttribute('data-bs-toggle', 'tooltip');
                span.setAttribute('title', earliestPattern.definition);
                frag.appendChild(span);

                cursor = earliest.index + earliest[0].length;
                changed = true;
            }

            if (changed) {
                if (cursor < text.length) {
                    frag.appendChild(document.createTextNode(text.slice(cursor)));
                }
                textNode.parentNode.replaceChild(frag, textNode);
            }
        }

        containers.forEach(container => {
            const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, walkerOptions);
            const nodes = [];
            let node;
            while ((node = walker.nextNode())) {
                nodes.push(node);
            }
            nodes.forEach(highlightTextNode);
        });

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(el => {
            if (window.bootstrap && window.bootstrap.Tooltip) {
                new bootstrap.Tooltip(el);
            }
        });
    }

    window.addEventListener('load', initGlossaryTooltips);
</script>

<style>
.faqs-list {
    counter-reset: faq-counter;
}
.faq-row {
    border-bottom: 1px solid #e5e5e5;
}
.faq-row-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    cursor: pointer;
    user-select: none;
}
.faq-number {
    font-weight: 700;
    color: #444;
    min-width: 22px;
}
.faq-question {
    font-weight: 600;
    flex: 1;
    color: #111;
}
.faq-arrow {
    color: #666;
    transition: transform 0.2s ease;
}
.faq-row-header[aria-expanded="true"] .faq-arrow i {
    transform: rotate(180deg);
}
.faq-body {
    padding: 0 0 10px 34px;
}
.faq-row:last-child {
    border-bottom: none;
}
.contrib-label {
    display: inline-block;
    width: 210px;
    font-weight: 600;
}
.glossary-term {
    border-bottom: 2px dotted #c00;
    cursor: help;
    color: inherit;
}
.glossary-term[data-bs-toggle="tooltip"]::after {
    content: '';
}
</style>

<?php require_once 'includes/footer.php'; ?>
