<?php
session_start();
require_once 'config/database.php';

// Detect AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    require_once 'includes/header.php';
}

// Get filter parameters
$era_filter = $_GET['era'] ?? 'all';
$search = $_GET['search'] ?? '';
$view = $_GET['view'] ?? 'list'; // 'cards' or 'list'

// Build query with calculated era based on date_lost
$sql = "SELECT *, 
    CASE 
        WHEN STR_TO_DATE(date_lost, '%Y-%m-%d') < '1939-09-01' OR STR_TO_DATE(date_lost, '%d %M %Y') < '1939-09-01' THEN 'pre-ww2'
        WHEN (STR_TO_DATE(date_lost, '%Y-%m-%d') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%Y-%m-%d') <= '1945-09-02') OR 
             (STR_TO_DATE(date_lost, '%d %M %Y') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%d %M %Y') <= '1945-09-02') THEN 'ww2'
        ELSE 'post-ww2'
    END as calculated_era
    FROM lost_submarines 
    WHERE 1=1
    AND (STR_TO_DATE(date_lost, '%Y-%m-%d') IS NOT NULL OR STR_TO_DATE(date_lost, '%d %M %Y') IS NOT NULL)";
$params = [];

if ($era_filter !== 'all') {
    if ($era_filter === 'pre-ww2') {
        $sql .= " AND (STR_TO_DATE(date_lost, '%Y-%m-%d') < '1939-09-01' OR STR_TO_DATE(date_lost, '%d %M %Y') < '1939-09-01')";
    } elseif ($era_filter === 'ww2') {
        $sql .= " AND ((STR_TO_DATE(date_lost, '%Y-%m-%d') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%Y-%m-%d') <= '1945-09-02') OR 
                      (STR_TO_DATE(date_lost, '%d %M %Y') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%d %M %Y') <= '1945-09-02'))";
    } elseif ($era_filter === 'post-ww2') {
        $sql .= " AND (STR_TO_DATE(date_lost, '%Y-%m-%d') > '1945-09-02' OR STR_TO_DATE(date_lost, '%d %M %Y') > '1945-09-02')";
    }
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR designation LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY display_order ASC, boat_number ASC";

try {
    // DEBUG: Output filter and SQL for troubleshooting
    if (isset($_GET['debug'])) {
        echo '<pre style="background:#fff;color:#000;z-index:99999;position:relative;">';
        echo 'era_filter: ' . htmlspecialchars($era_filter) . "\n";
        echo 'SQL: ' . htmlspecialchars($sql) . "\n";
        echo 'Params: ' . htmlspecialchars(json_encode($params)) . "\n";
        echo '</pre>';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $boats = $stmt->fetchAll();
} catch (Exception $e) {
    $boats = [];
    $error = $e->getMessage();
}

// Get statistics with calculated era
try {
    $stats = [];
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN STR_TO_DATE(date_lost, '%Y-%m-%d') < '1939-09-01' OR STR_TO_DATE(date_lost, '%d %M %Y') < '1939-09-01' THEN 'pre-ww2'
                WHEN (STR_TO_DATE(date_lost, '%Y-%m-%d') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%Y-%m-%d') <= '1945-09-02') OR 
                     (STR_TO_DATE(date_lost, '%d %M %Y') >= '1939-09-01' AND STR_TO_DATE(date_lost, '%d %M %Y') <= '1945-09-02') THEN 'ww2'
                ELSE 'post-ww2'
            END as calculated_era,
            COUNT(*) as count 
        FROM lost_submarines 
        GROUP BY calculated_era
        ORDER BY FIELD(calculated_era, 'pre-ww2', 'ww2', 'post-ww2')
    ");
    while ($row = $stmt->fetch()) {
        $stats[$row['calculated_era']] = $row['count'];
    }
    
    $total_stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CAST(SUBSTRING_INDEX(fatalities, ' ', 1) AS UNSIGNED)) as total_fatalities FROM lost_submarines");
    $totals = $total_stmt->fetch();
} catch (Exception $e) {
    $stats = [];
    $totals = ['total' => 0, 'total_fatalities' => 0];
}
?>

<?php if ($isAjax) {
    echo '<div id="resultsSection">';
    if ($view === 'list') {
        echo '<div class="card mb-4"><div class="card-body"><ul class="list-group list-group-flush">';
        foreach ($boats as $boat) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<a href="boat.php?id=' . $boat['id'] . '">' . htmlspecialchars($boat['name']) . ' (' . htmlspecialchars($boat['designation']) . ')</a>';
            echo '<span class="text-muted small locale-date" data-date="' . htmlspecialchars($boat['date_lost']) . '">' . htmlspecialchars($boat['date_lost']) . '</span>';
            echo '</li>';
        }
        echo '</ul></div></div>';
    } else {
        echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        foreach ($boats as $boat) {
            echo '<div class="col"><div class="card h-100"><div class="card-body">';
            echo '<h5 class="card-title mb-1">' . htmlspecialchars($boat['name']) . ' <span class="text-muted small">(' . htmlspecialchars($boat['designation']) . ')</span></h5>';
            echo '<p class="text-muted small"><i class="fas fa-calendar"></i> <span class="locale-date" data-date="' . htmlspecialchars($boat['date_lost']) . '">' . htmlspecialchars($boat['date_lost']) . '</span></p>';
            if ($boat['fatalities']) echo '<p class="small mb-2"><strong><i class="fas fa-users"></i> Fatalities:</strong> ' . htmlspecialchars($boat['fatalities']) . '</p>';
            if ($boat['location']) echo '<p class="small"><strong>Location:</strong> ' . htmlspecialchars($boat['location']) . '</p>';
            if ($boat['cause']) echo '<p class="small"><strong>Cause:</strong> ' . htmlspecialchars(substr($boat['cause'], 0, 150)) . '...</p>';
            echo '<a href="boat.php?id=' . $boat['id'] . '" class="btn btn-outline-primary btn-sm">View Full Details <i class="fas fa-arrow-right"></i></a>';
            echo '</div></div></div>';
        }
        echo '</div>';
    }
    echo '</div>';
    exit;
}
?>
<div class="container mt-4">
    <div class="mb-3">
        <a href="memorial.html" class="btn btn-secondary">&larr; Back to Memorial</a>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-4">⚓ Submarines on Eternal Patrol</h1>
            <p class="lead">Honoring the brave men and vessels lost in service to our nation</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="display-6"><?php echo $totals['total']; ?></h3>
                    <p class="text-muted">Total Submarines Lost</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="display-6"><?php echo number_format($totals['total_fatalities']); ?></h3>
                    <p class="text-muted">Lives Lost</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3 align-items-end" autocomplete="off">
                        <div class="col-md-4">
                            <label for="era" class="form-label">Filter by Era</label>
                            <select name="era" id="era" class="form-select">
                                <option value="all" <?php echo $era_filter === 'all' ? 'selected' : ''; ?>>All Eras</option>
                                <option value="pre-ww2" <?php echo $era_filter === 'pre-ww2' ? 'selected' : ''; ?>>Pre WW2</option>
                                <option value="ww2" <?php echo $era_filter === 'ww2' ? 'selected' : ''; ?>>WW2</option>
                                <option value="post-ww2" <?php echo $era_filter === 'post-ww2' ? 'selected' : ''; ?>>Post WW2</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="search" class="form-label">Search by Name or Designation</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="e.g., USS Shark or SS-174" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <span class="form-label mb-0">View:</span>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="view" id="viewList" value="list" <?php echo $view === 'list' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="viewList">List</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="view" id="viewCards" value="cards" <?php echo $view === 'cards' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="viewCards">Cards</label>
                </div>
                <span class="ms-3 text-muted small" id="boatCount">(<?php echo count($boats); ?> boats)</span>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">Error loading submarines: <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
    <div class="mb-3">
        <a href="admin-eternal-patrol.php" class="btn btn-primary">
            ⚙️ Manage Submarines
        </a>
    </div>
    <?php endif; ?>

    <!-- Boat List -->
    <?php if ($view === 'list'): ?>
    <!-- List View -->
    <div class="card">
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                <?php if (empty($boats)): ?>
                <li class="alert alert-info">No submarines found matching your criteria.</li>
                <?php else: ?>
                <?php foreach ($boats as $boat): ?>
                <li class="mb-2">
                    <a href="boat.php?id=<?php echo $boat['id']; ?>" class="text-decoration-none">
                        <i class="fas fa-ship"></i> <?php echo htmlspecialchars($boat['designation'] ?: $boat['name']); ?>
                    </a>
                    <span class="text-muted small">- <?php echo htmlspecialchars($boat['date_lost']); ?></span>
                </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php else: ?>
    <!-- Card View -->
    <div class="row">
        <?php if (empty($boats)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <p class="mb-0">No submarines found matching your criteria.</p>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($boats as $boat): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo htmlspecialchars($boat['designation'] ?: $boat['name']); ?>
                    </h5>
                    <p class="text-muted small">
                        <i class="fas fa-calendar"></i> <span class="locale-date" data-date="<?php echo htmlspecialchars($boat['date_lost']); ?>"><?php echo htmlspecialchars($boat['date_lost']); ?></span>
                    </p>
                    <?php if ($boat['fatalities']): ?>
                    <p class="small mb-2">
                        <strong><i class="fas fa-users"></i> Fatalities:</strong> <?php echo htmlspecialchars($boat['fatalities']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($boat['location']): ?>
                    <p class="small"><strong>Location:</strong> <?php echo htmlspecialchars($boat['location']); ?></p>
                    <?php endif; ?>
                    <?php if ($boat['cause']): ?>
                    <p class="small"><strong>Cause:</strong> <?php echo htmlspecialchars(substr($boat['cause'], 0, 150)); ?>...</p>
                    <?php endif; ?>
                    <a href="boat.php?id=<?php echo $boat['id']; ?>" class="btn btn-outline-primary btn-sm">
                        View Full Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Format dates according to user's locale
function formatLocaleDates() {
    const dateElements = document.querySelectorAll('.locale-date');
    dateElements.forEach(el => {
        const dateStr = el.getAttribute('data-date');
        if (dateStr) {
            try {
                const date = new Date(dateStr);
                if (!isNaN(date.getTime())) {
                    el.textContent = date.toLocaleDateString(undefined, { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                }
            } catch (e) {
                // Keep original if parsing fails
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    formatLocaleDates();

    // AJAX filtering
    const filterForm = document.getElementById('filterForm');
    const eraSelect = document.getElementById('era');
    const searchInput = document.getElementById('search');
    const viewForm = document.getElementById('viewForm');
    const resultsSection = document.getElementById('resultsSection');
    const boatCount = document.getElementById('boatCount');

    function getView() {
        const radios = viewForm.querySelectorAll('input[name="view"]');
        for (const r of radios) {
            if (r.checked) return r.value;
        }
        return 'list';
    }

    function updateResults() {
        // Always get the current view value from the radio buttons
        const viewRadios = document.querySelectorAll('input[name="view"]');
        let viewValue = 'list';
        viewRadios.forEach(r => { if (r.checked) viewValue = r.value; });
        const params = new URLSearchParams({
            era: eraSelect.value,
            search: searchInput.value,
            view: viewValue
        });
        fetch(window.location.pathname + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            // Extract just the #resultsSection and boat count
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const newResults = temp.querySelector('#resultsSection');
            const newBoatCount = temp.querySelector('#boatCount');
            if (newResults && resultsSection) {
                resultsSection.innerHTML = newResults.innerHTML;
            }
            if (newBoatCount && boatCount) {
                boatCount.innerHTML = newBoatCount.innerHTML;
            }
            formatLocaleDates();
        });
    }

    eraSelect.addEventListener('change', function(e) {
        e.preventDefault();
        updateResults();
    });
    searchInput.addEventListener('input', function(e) {
        e.preventDefault();
        updateResults();
    });
    viewForm.addEventListener('change', function(e) {
        if (e.target.name === 'view') {
            e.preventDefault();
            updateResults();
        }
    });
    // Prevent form submits
    filterForm.addEventListener('submit', e => e.preventDefault());
    viewForm.addEventListener('submit', e => e.preventDefault());
});
</script>

<?php require_once 'includes/footer.php'; ?>
