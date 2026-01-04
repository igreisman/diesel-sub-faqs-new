<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Get all published submarine class articles
$stmt = $pdo->query("
    SELECT * FROM reference_articles 
    WHERE category = 'submarine-classes' AND status = 'published'
    ORDER BY order_position, title
");
$articles = $stmt->fetchAll();

// Map eras to tab groups
$eraMapping = [
    'Pre-WWI' => 'early',
    'WWI' => 'early',
    'Interwar' => 'interwar',
    'WWII' => 'ww2',
    'Post-WWII' => 'postwar',
    'Nuclear' => 'postwar',
    'Cold War' => 'postwar',
    'Experimental' => 'postwar',
    'Modern' => 'postwar'
];

// Map class to era
$classToEra = [
    'Holland' => 'Pre-WWI', 'Plunger (A)' => 'Pre-WWI', 'Adder (B)' => 'Pre-WWI', 'Octopus (C)' => 'Pre-WWI',
    'Shark (D)' => 'Pre-WWI', 'E' => 'Pre-WWI', 'F' => 'Pre-WWI', 'G' => 'Pre-WWI',
    'H' => 'WWI', 'K' => 'WWI', 'L' => 'WWI', 'M' => 'WWI', 'N' => 'WWI', 'O' => 'WWI', 'R' => 'WWI',
    'S' => 'Interwar', 'Barracuda (V-1)' => 'Interwar', 'Narwhal (V-5)' => 'Interwar', 'Dolphin (V-7)' => 'Interwar',
    'Cachalot' => 'Interwar', 'Porpoise' => 'Interwar', 'Salmon' => 'Interwar', 'Sargo' => 'Interwar',
    'Tambor' => 'WWII', 'Gato' => 'WWII', 'Balao' => 'WWII', 'Tench' => 'WWII',
    'GUPPY (conversion)' => 'Post-WWII', 'Fleet Snorkel (conversion)' => 'Post-WWII', 'Tang' => 'Post-WWII',
    'Nautilus' => 'Nuclear', 'Seawolf (SSN-575)' => 'Nuclear', 'Skate' => 'Nuclear', 'Skipjack' => 'Nuclear',
    'Permit (Thresher)' => 'Nuclear', 'Sturgeon' => 'Nuclear',
    'George Washington' => 'Cold War', 'Ethan Allen' => 'Cold War', 'Lafayette' => 'Cold War',
    'James Madison' => 'Cold War', 'Benjamin Franklin' => 'Cold War', 'Grayback' => 'Cold War',
    'Halibut' => 'Cold War', 'Los Angeles' => 'Cold War', 'Seawolf' => 'Cold War',
    'Albacore' => 'Experimental', 'NR-1' => 'Experimental',
    'Ohio' => 'Modern', 'Ohio (SSGN conversion)' => 'Modern', 'Virginia' => 'Modern',
    'Jimmy Carter (SSN-23)' => 'Modern', 'Columbia' => 'Modern'
];

// Group articles by tab
$articlesByEra = [
    'early' => [],
    'interwar' => [],
    'ww2' => [],
    'postwar' => []
];

foreach ($articles as $article) {
    // Determine which tab this article belongs to
    if (!empty($article['class']) && isset($classToEra[$article['class']])) {
        $era = $classToEra[$article['class']];
        $tab = $eraMapping[$era];
        $articlesByEra[$tab][] = $article;
    } else {
        // Fallback to order_position if class is not set
        $order = $article['order_position'];
        if ($order < 100) {
            $articlesByEra['early'][] = $article;
        } elseif ($order < 200) {
            $articlesByEra['interwar'][] = $article;
        } elseif ($order < 300) {
            $articlesByEra['ww2'][] = $article;
        } else {
            $articlesByEra['postwar'][] = $article;
        }
    }
}
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="hero-section text-center mb-4">
        <h1 class="display-4"><i class="fas fa-ship"></i> US Submarine Classes</h1>
        <p class="lead">Evolution of American submarine design from early experimental boats to nuclear vessels</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="reference.php">Reference</a></li>
                <li class="breadcrumb-item active">Submarine Classes</li>
            </ol>
        </nav>
    </div>

    <!-- Era Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="eraTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="early-tab" data-bs-toggle="tab" data-bs-target="#early" type="button" role="tab">
                <i class="fas fa-history"></i> Early Development (1900-1920)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="interwar-tab" data-bs-toggle="tab" data-bs-target="#interwar" type="button" role="tab">
                <i class="fas fa-anchor"></i> Interwar Period (1920-1941)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ww2-tab" data-bs-toggle="tab" data-bs-target="#ww2" type="button" role="tab">
                <i class="fas fa-ship"></i> WW2 Fleet Boats (1941-1945)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="postwar-tab" data-bs-toggle="tab" data-bs-target="#postwar" type="button" role="tab">
                <i class="fas fa-rocket"></i> Post-WW2 & Nuclear Age
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="eraTabContent">
        
        <!-- Early Development Tab -->
        <div class="tab-pane fade show active" id="early" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Early Submarine Development (1900-1920)</h4>
                </div>
                <div class="card-body">
                    <p class="lead">From the Holland purchase to World War I service, early US submarines were experimental vessels that proved the concept of underwater warfare.</p>
                    
                    <?php if (empty($articlesByEra['early'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Content for early submarine classes coming soon. Check back later or contact admin to add articles.
                        </div>
                    <?php else: ?>
                        <!-- Accordion for Detailed Information -->
                        <div class="accordion" id="earlyAccordion">
                            <?php foreach ($articlesByEra['early'] as $index => $article): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#early-<?= $article['id'] ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="early-<?= $article['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                         data-bs-parent="#earlyAccordion">
                                        <div class="accordion-body">
                                            <?= $article['content'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Interwar Period Tab -->
        <div class="tab-pane fade" id="interwar" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Interwar Submarine Development (1920-1941)</h4>
                </div>
                <div class="card-body">
                    <p class="lead">Between the World Wars, submarine design matured with longer range, better habitability, and improved performance.</p>
                    
                    <?php if (empty($articlesByEra['interwar'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Content for interwar submarine classes coming soon. Check back later or contact admin to add articles.
                        </div>
                    <?php else: ?>
                        <!-- Accordion for Interwar Classes -->
                        <div class="accordion" id="interwarAccordion">
                            <?php foreach ($articlesByEra['interwar'] as $index => $article): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#interwar-<?= $article['id'] ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="interwar-<?= $article['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                         data-bs-parent="#interwarAccordion">
                                        <div class="accordion-body">
                                            <?= $article['content'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- WW2 Fleet Boats Tab -->
        <div class="tab-pane fade" id="ww2" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">World War II Fleet Submarines (1941-1945)</h4>
                </div>
                <div class="card-body">
                    <p class="lead">The workhorses of the Pacific War: Gato, Balao, and Tench class submarines that dominated Japanese shipping lanes.</p>
                    
                    <?php if (empty($articlesByEra['ww2'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Content for WW2 submarine classes coming soon. Check back later or contact admin to add articles.
                        </div>
                    <?php else: ?>
                        <!-- Accordion for WW2 Classes -->
                        <div class="accordion" id="ww2Accordion">
                            <?php foreach ($articlesByEra['ww2'] as $index => $article): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#ww2-<?= $article['id'] ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="ww2-<?= $article['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                         data-bs-parent="#ww2Accordion">
                                        <div class="accordion-body">
                                            <?= $article['content'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Post-WW2 Tab -->
        <div class="tab-pane fade" id="postwar" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Post-War & Nuclear Age (1945-Present)</h4>
                </div>
                <div class="card-body">
                    <p class="lead">From GUPPY conversions to nuclear power, the post-war era transformed submarine warfare forever.</p>
                    
                    <?php if (empty($articlesByEra['postwar'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Content for post-war submarine classes coming soon. Check back later or contact admin to add articles.
                        </div>
                    <?php else: ?>
                        <!-- Accordion for Post-War -->
                        <div class="accordion" id="postwarAccordion">
                            <?php foreach ($articlesByEra['postwar'] as $index => $article): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#postwar-<?= $article['id'] ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="postwar-<?= $article['id'] ?>" 
                                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                         data-bs-parent="#postwarAccordion">
                                        <div class="accordion-body">
                                            <?= $article['content'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Navigation -->
    <div class="mt-4 mb-5">
        <a href="reference.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Reference</a>
    </div>
</div>

<style>
.hero-section {
    padding: 2rem 0;
}

.nav-tabs .nav-link {
    color: #495057;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    font-weight: 600;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #004085;
}

.table-responsive {
    margin-top: 1.5rem;
}

.card-header h4 {
    font-weight: 600;
}

.accordion-item {
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
    overflow: hidden;
}

.breadcrumb {
    background: none;
    padding: 0;
}
</style>

<?php require_once 'includes/footer.php'; ?>
