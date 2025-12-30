<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Get filter parameters
$era_filter = $_GET['era'] ?? 'all';
$search = $_GET['search'] ?? '';
$view = $_GET['view'] ?? 'list'; // 'cards' or 'list'

// Build query
$sql = "SELECT * FROM lost_submarines WHERE 1=1";
$params = [];

if ($era_filter !== 'all') {
    $sql .= " AND era = ?";
    $params[] = $era_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR designation LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY display_order ASC, boat_number ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $boats = $stmt->fetchAll();
} catch (Exception $e) {
    $boats = [];
    $error = $e->getMessage();
}

// Get statistics
try {
    $stats = [];
    $stmt = $pdo->query("SELECT era, COUNT(*) as count FROM lost_submarines GROUP BY era ORDER BY FIELD(era, 'pre-wwi', 'wwi', 'interwar', 'wwii', 'post-wwii')");
    while ($row = $stmt->fetch()) {
        $stats[$row['era']] = $row['count'];
    }
    
    $total_stmt = $pdo->query("SELECT COUNT(*) as total, SUM(CAST(SUBSTRING_INDEX(fatalities, ' ', 1) AS UNSIGNED)) as total_fatalities FROM lost_submarines");
    $totals = $total_stmt->fetch();
} catch (Exception $e) {
    $stats = [];
    $totals = ['total' => 0, 'total_fatalities' => 0];
}
?>

<div class="container mt-4">
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
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="era" class="form-label">Filter by Era</label>
                    <select name="era" id="era" class="form-select">
                        <option value="all" <?php echo $era_filter === 'all' ? 'selected' : ''; ?>>All Eras</option>
                        <option value="pre-wwi" <?php echo $era_filter === 'pre-wwi' ? 'selected' : ''; ?>>Pre-WWI</option>
                        <option value="wwi" <?php echo $era_filter === 'wwi' ? 'selected' : ''; ?>>World War I</option>
                        <option value="interwar" <?php echo $era_filter === 'interwar' ? 'selected' : ''; ?>>Interwar Period</option>
                        <option value="wwii" <?php echo $era_filter === 'wwii' ? 'selected' : ''; ?>>World War II</option>
                        <option value="post-wwii" <?php echo $era_filter === 'post-wwii' ? 'selected' : ''; ?>>Post-WWII</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Search by Name or Designation</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="e.g., USS Shark or SS-174" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
            </form>
        </div>
    </div>

    <!-- View Selector -->
    <div class="mb-3 d-flex justify-content-start">
        <form method="GET" class="d-flex align-items-center gap-2">
            <label for="view" class="form-label mb-0">View:</label>
            <select name="view" id="view" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="list" <?php echo $view === 'list' ? 'selected' : ''; ?>>List</option>
                <option value="cards" <?php echo $view === 'cards' ? 'selected' : ''; ?>>Cards</option>
            </select>
            <input type="hidden" name="era" value="<?php echo htmlspecialchars($era_filter); ?>">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        </form>
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
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

<?php require_once 'includes/footer.php'; ?>
