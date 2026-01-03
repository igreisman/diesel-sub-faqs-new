<?php
session_start();
require_once 'config/database.php';

// Default about text
$defaultAboutText = '<p>Each Memorial Day, United States Submarine Veterans read the list of the boats lost in World War 2. It is called with the "Tolling of the Boats." The reading of the names of the lost boats typically includes the number of sailors lost with each one. Since the ceremony was created by WW2 sub vets, the focus was on their era. Recently, as more of the sub vets are from post WW2 service, the list has included the four submarines that we have lost since the war. Although they were not lost in active combat, we acknowledge that these sailors also gave their lives in the service of their country.</p>
                    
<p>However, this reading has not usually included the submarines we lost prior to WW2. We had an average of one submarine incident (sinking or grounding) every 27 months from the time of the USS Holland purchase in 1900 until the outbreak of WW2 in 1941. However, a number of those boats sank without loss of life and were salvaged and recommissioned. Therefore, we made an arbitrary decision about which boats to include in our list of those lost prior to WW2. We only included the eleven US Navy submarines lost prior to WW2 with loss of life or where the boat was not salvaged. The boats we did not include are listed in an appendix.</p>
                    
<p><strong>So how did this particular project come about?</strong><br>
This project came about for the USS Pampanito (SS-383), at the maritime museum in San Francisco. The Mare Island base of the USSVI sub vets normally holds their "Tolling of the Boats" at the Pampanito. This document is an effort to make the list more inclusive and to give it more texture, more depth and, hopefully, make it more interesting. Although this document is too lengthy to be read in full at the ceremony, we hope it might provide more information for anyone who might be interested.</p>
                    
<p>Some of the stories of the lost submarines are interesting or particularly tragic. Most of them had interesting histories prior to their last patrols. That is the sort of thing we wanted to convey. For example, one boat rescued gold bars and silver coins from banks in the Philippines, only to have one gold bar go "missing" on the way home. One submarine sank a Japanese carrier but wasn\'t immediately aware of it. The submarine was long gone before the carrier went down. The stories of the sister ships USS Squalus/Sailfish and the USS Sculpin are particularly ironic and sad. There was also the frequent tension between what captains thought they sank and what they got credit for in the postwar audit.</p>
                    
<p><strong>How is the document organized?</strong><br>
The prewar losses that met our criteria are listed in section 1. Section 2 details the WW2 losses and postwar losses are in section 3. The listings are generally in order of the dates the boats were lost. That isn\'t an exact sort since we still don\'t always know the exact dates of the losses.</p>
                    
<p>For each listing, we start with basic information about the boat such as the class and building shipyard. Next, we describe the last patrol and what we know about the submarine\'s loss. Then we go back and summarize its prior history.</p>
                    
<p>The officers\' photographs, unless otherwise noted, are those of the last commanding officers. Although the majority of the captains, and their crews, were lost when the boat went down, not all perished. In two cases, the captains are listed with two different boats. In four cases the boats went aground and the entire crews were rescued. In a few more cases, captains were on the bridge when the boat was sunk and they, along with a few other crew members were able to make it to safety.</p>
                    
<p>The pictures of the submarines, again unless otherwise noted, are those of the lost boats. Obviously, there may not be much difference between boats of the same classes, but there are huge differences between our first class of submarines, such as the A-7, and the nuclear-powered boats. Manitowoc boats were launched sideways and that process looks very different. Therefore, we included the photos of many of the boats.</p>
                    
<p>Like so many other professions, sailors - particularly on submarines - speak a very odd language. Hopefully, Appendix B translates most of that jargon into a reasonable version of English.</p>
                    
<p><strong>Thanks.</strong><br>
My thanks go to Diane Cooper for the idea which we then expanded. Her guidance and suggestions throughout were most helpful. Suggestions and reminders from others are also appreciated.</p>
                    
<p>The greatest thanks go to my wife, Sue, for putting up with my strange obsession. A benefit of this project may have been to get me out of her hair a couple days per week. However, I do realize that I still try her patience at times.</p>
                    
<p><strong>Dedication.</strong><br>
This is dedicated to all submariners, particularly those who gave their lives for their countries, in times of war and in keeping the peace.</p>';

// Get about text from database
$aboutText = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'eternal_patrol_about'");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        $aboutText = $result['setting_value'];
    } else {
        $aboutText = $defaultAboutText;
    }
} catch (PDOException $e) {
    // If table doesn't exist or query fails, use default text
    $aboutText = $defaultAboutText;
}

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
        WHEN date_lost_sort IS NOT NULL AND date_lost_sort < '1941-12-07' THEN 'pre-ww2'
        WHEN date_lost_sort IS NOT NULL AND date_lost_sort > '1945-09-02' THEN 'post-ww2'
        ELSE 'ww2'
    END as calculated_era
    FROM lost_submarines 
    WHERE 1=1
    AND date_lost_sort IS NOT NULL";
$params = [];

if ($era_filter !== 'all') {
    $sql .= " HAVING calculated_era = ?";
    $params[] = $era_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR designation LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY date_lost_sort IS NULL ASC, date_lost_sort ASC, boat_number ASC";

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
                WHEN date_lost_sort IS NOT NULL AND date_lost_sort < '1941-12-07' THEN 'pre-ww2'
                WHEN date_lost_sort IS NOT NULL AND date_lost_sort > '1945-09-02' THEN 'post-ww2'
                ELSE 'ww2'
            END as calculated_era,
            COUNT(*) as count 
        FROM lost_submarines 
        WHERE date_lost_sort IS NOT NULL
        GROUP BY calculated_era
        ORDER BY FIELD(calculated_era, 'pre-ww2', 'ww2', 'post-ww2')
    ");
    while ($row = $stmt->fetch()) {
        $stats[$row['calculated_era']] = $row['count'];
    }
    $total_stmt = $pdo->query("SELECT COUNT(*) as total, SUM(fatalities_num) as total_fatalities FROM lost_submarines");
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
        $currentEra = null;
        foreach ($boats as $boat) {
            // Add era header when era changes
            if ($currentEra !== $boat['calculated_era']) {
                $currentEra = $boat['calculated_era'];
                $eraLabel = ucwords(str_replace('-', ' ', $currentEra));
                if ($currentEra !== null) {
                    echo '<li class="list-group-item bg-light fw-bold">Submarines lost ' . htmlspecialchars($eraLabel) . '</li>';
                }
            }
            echo '<li class="list-group-item d-flex justify-content-between align-items-center ps-4">';
            echo '<a href="boat.php?id=' . $boat['id'] . '">' . htmlspecialchars($boat['designation'] ?: $boat['name']) . '</a>';
            echo '<span class="text-muted small locale-date" data-date="' . htmlspecialchars($boat['date_lost']) . '">' . htmlspecialchars($boat['date_lost']) . '</span>';
            echo '</li>';
        }
        echo '</ul></div></div>';
    } else {
        echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        $currentEra = null;
        foreach ($boats as $boat) {
            // Add era header when era changes
            if ($currentEra !== $boat['calculated_era']) {
                $currentEra = $boat['calculated_era'];
                $eraLabel = ucwords(str_replace('-', ' ', $currentEra));
                if ($currentEra !== null) {
                    echo '<div class="col-12"><h4 class="border-bottom pb-2 mb-3">Submarines lost ' . htmlspecialchars($eraLabel) . '</h4></div>';
                }
            }
            echo '<div class="col ms-3"><div class="card h-100"><div class="card-body">';
            echo '<h5 class="card-title mb-1">' . htmlspecialchars($boat['designation'] ?: $boat['name']) . '</h5>';
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
        </div>
    </div>

    <!-- About Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary border-3 shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">About the Tolling of the Boats</h5>
                    <div>
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                            <a href="admin-eternal-patrol-about.php" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        <?php endif; ?>
                        <small><i class="fas fa-arrow-down"></i> Scroll for more</small>
                    </div>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto; position: relative;">
                    <?php echo $aboutText; ?>
                </div>
            </div>
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
            <form id="viewForm" class="d-flex align-items-center gap-3 mb-0">
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
            </form>
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
    <div id="resultsSection">
    <?php if ($view === 'list'): ?>
    <!-- List View -->
    <div class="card">
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                <?php if (empty($boats)): ?>
                <li class="alert alert-info">No submarines found matching your criteria.</li>
                <?php else: ?>
                <?php 
                $currentEra = null;
                foreach ($boats as $boat): 
                    // Add era header when era changes
                    if ($currentEra !== $boat['calculated_era']):
                        $currentEra = $boat['calculated_era'];
                        $eraLabel = ucwords(str_replace('-', ' ', $currentEra));
                ?>
                <li class="fw-bold text-primary mt-3 mb-2" style="list-style: none;">Submarines lost <?php echo htmlspecialchars($eraLabel); ?></li>
                <?php endif; ?>
                <li class="mb-2 ps-4">
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
        <?php 
        $currentEra = null;
        foreach ($boats as $boat): 
            // Add era header when era changes
            if ($currentEra !== $boat['calculated_era']):
                $currentEra = $boat['calculated_era'];
                $eraLabel = ucwords(str_replace('-', ' ', $currentEra));
        ?>
        <div class="col-12">
            <h4 class="border-bottom pb-2 mb-3 mt-3">Submarines lost <?php echo htmlspecialchars($eraLabel); ?></h4>
        </div>
        <?php endif; ?>
        <div class="col-md-6 mb-4 ms-3">
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
