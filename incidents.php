<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Get filter parameters
$era_filter = $_GET['era'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM submarine_incidents WHERE 1=1";
$params = [];

if ($era_filter !== 'all') {
    $sql .= " AND era = ?";
    $params[] = $era_filter;
}

if (!empty($search)) {
    $sql .= " AND (submarine_name LIKE ? OR hull_number LIKE ? OR incident_type LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $incidents = $stmt->fetchAll();
} catch (PDOException $e) {
    $incidents = [];
}

// Get statistics
$totalIncidents = count($incidents);
$totalCasualties = array_sum(array_column($incidents, 'casualties'));
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="memorial.php" class="btn btn-secondary">&larr; Back to Memorial</a>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-4">⚠️ Other Submarine Incidents</h1>
            <p class="lead">Submarines that experienced significant incidents but were not lost</p>
        </div>
    </div>

    <!-- About Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning border-3 shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">About These Incidents</h5>
                </div>
                <div class="card-body">
                    <p>We had an average of one submarine incident (sinking or grounding) every 27 months from the time of the USS Holland purchase in 1900 until the outbreak of WW2 in 1941. However, a number of those boats sank without loss of life and were salvaged and recommissioned.</p>
                    
                    <p>This page includes submarines that experienced significant incidents but were not included in the main Eternal Patrol list. These are boats that:</p>
                    <ul>
                        <li>Were salvaged and returned to service</li>
                        <li>Experienced major damage but survived</li>
                        <li>Had significant casualties but the boat itself was not lost</li>
                        <li>Historical incidents from early submarine development</li>
                    </ul>
                    
                    <p class="mb-0">These incidents remind us that submarine service has always been dangerous, even in peacetime and even when the boat itself survives.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="display-6"><?= $totalIncidents ?></h3>
                    <p class="text-muted">Total Incidents</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="display-6"><?= $totalCasualties ?></h3>
                    <p class="text-muted">Total Casualties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="display-6"><?= count(array_filter($incidents, fn($i) => $i['casualties'] == 0)) ?></h3>
                    <p class="text-muted">No Loss of Life</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="d-flex gap-2">
                <select name="era" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $era_filter === 'all' ? 'selected' : '' ?>>All Eras</option>
                    <option value="Pre-WW2" <?= $era_filter === 'Pre-WW2' ? 'selected' : '' ?>>Pre-WW2</option>
                    <option value="WW2" <?= $era_filter === 'WW2' ? 'selected' : '' ?>>WW2</option>
                    <option value="Post-WW2" <?= $era_filter === 'Post-WW2' ? 'selected' : '' ?>>Post-WW2</option>
                </select>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>
        <div class="col-md-6">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search incidents..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
                <input type="hidden" name="era" value="<?= htmlspecialchars($era_filter) ?>">
            </form>
        </div>
    </div>

    <!-- Incidents List -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($incidents)): ?>
                <div class="alert alert-info">No incidents found matching your criteria.</div>
            <?php else: ?>
                <?php 
                $currentEra = null;
                foreach ($incidents as $incident): 
                    // Add era header when era changes
                    if ($currentEra !== $incident['era']):
                        $currentEra = $incident['era'];
                        $eraClass = $currentEra === 'Pre-WW2' ? 'secondary' : 
                                   ($currentEra === 'WW2' ? 'danger' : 'info');
                ?>
                <div class="alert alert-<?= $eraClass ?> fw-bold mt-3 mb-2">
                    Incidents during <?= htmlspecialchars($currentEra) ?>
                </div>
                    <?php endif; ?>
                    
                <div class="card mb-3 ms-3">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">
                                <?= htmlspecialchars($incident['submarine_name']) ?>
                                <?php if ($incident['hull_number']): ?>
                                    <span class="text-muted">(<?= htmlspecialchars($incident['hull_number']) ?>)</span>
                                <?php endif; ?>
                            </h5>
                            <div class="text-muted small">
                                <i class="fas fa-calendar"></i> <?= date('F j, Y', strtotime($incident['date'])) ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($incident['incident_type']) ?></span>
                            <?php if ($incident['casualties'] > 0): ?>
                                <span class="badge bg-danger"><?= $incident['casualties'] ?> casualties</span>
                            <?php else: ?>
                                <span class="badge bg-success">No casualties</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><?= nl2br(htmlspecialchars($incident['description'])) ?></p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>Status:</strong> <?= htmlspecialchars($incident['status']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Era:</strong> <?= htmlspecialchars($incident['era']) ?>
                            </div>
                        </div>
                        
                        <?php if ($incident['notes']): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <strong>Additional Notes:</strong><br>
                                <?= nl2br(htmlspecialchars($incident['notes'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
