<?php
session_start();
require_once 'config/database.php';

$page_title = 'Submarine Incidents';
$page_description = 'Historical database of US submarine incidents including accidents, collisions, and groundings';

// Get filter parameters
$era_filter = $_GET['era'] ?? 'all';
$type_filter = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM submarine_incidents WHERE 1=1";
$params = [];

if ($era_filter !== 'all') {
    $sql .= " AND era = ?";
    $params[] = $era_filter;
}

if ($type_filter !== 'all') {
    $sql .= " AND incident_type = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $sql .= " AND (submarine_name LIKE ? OR hull_number LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $incidents = $stmt->fetchAll();
} catch (Exception $e) {
    $incidents = [];
    $error = "Error loading incidents: " . $e->getMessage();
}

// Get statistics
$stats = [
    'total' => count($incidents),
    'pre_ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'Pre-WW2')),
    'ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'WW2')),
    'post_ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'Post-WW2')),
    'total_casualties' => array_sum(array_column($incidents, 'casualties'))
];

// Get unique incident types for filter
$types = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT incident_type FROM submarine_incidents ORDER BY incident_type");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $types = [];
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h1 class="display-4 mb-3">
                <i class="fas fa-exclamation-triangle text-warning"></i> Submarine Incidents
            </h1>
            <p class="lead">
                A comprehensive record of US submarine incidents throughout history, including accidents, collisions, groundings, and other notable events.
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['total'] ?></h3>
                    <p class="mb-0">Total Incidents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['pre_ww2'] ?></h3>
                    <p class="mb-0">Pre-WW2</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['ww2'] ?></h3>
                    <p class="mb-0">WW2 Era</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $stats['post_ww2'] ?></h3>
                    <p class="mb-0">Post-WW2</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="era" class="form-label">Era</label>
                    <select name="era" id="era" class="form-select">
                        <option value="all" <?= $era_filter === 'all' ? 'selected' : '' ?>>All Eras</option>
                        <option value="Pre-WW2" <?= $era_filter === 'Pre-WW2' ? 'selected' : '' ?>>Pre-WW2</option>
                        <option value="WW2" <?= $era_filter === 'WW2' ? 'selected' : '' ?>>WW2</option>
                        <option value="Post-WW2" <?= $era_filter === 'Post-WW2' ? 'selected' : '' ?>>Post-WW2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Incident Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="all" <?= $type_filter === 'all' ? 'selected' : '' ?>>All Types</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by submarine name or description..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Incidents List -->
    <?php if (empty($incidents)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No incidents found matching your criteria.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($incidents as $incident): ?>
                <div class="col-12 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <i class="fas fa-ship"></i> 
                                        <?= htmlspecialchars($incident['submarine_name']) ?>
                                        <?php if ($incident['hull_number']): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($incident['hull_number']) ?></span>
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="badge bg-<?= $incident['era'] === 'Pre-WW2' ? 'info' : ($incident['era'] === 'WW2' ? 'danger' : 'secondary') ?>">
                                        <?= htmlspecialchars($incident['era']) ?>
                                    </span>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars($incident['incident_type']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong><i class="fas fa-calendar"></i> Date:</strong> 
                                        <?= date('F j, Y', strtotime($incident['date'])) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong><i class="fas fa-user-injured"></i> Casualties:</strong>
                                        <?php if ($incident['casualties'] > 0): ?>
                                            <span class="text-danger"><?= $incident['casualties'] ?></span>
                                        <?php else: ?>
                                            <span class="text-success">None</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <p class="mb-2">
                                <strong><i class="fas fa-info-circle"></i> Description:</strong>
                            </p>
                            <p class="mb-3"><?= nl2br(htmlspecialchars($incident['description'])) ?></p>
                            
                            <?php if ($incident['status']): ?>
                                <p class="mb-2">
                                    <strong><i class="fas fa-flag"></i> Status:</strong> 
                                    <span class="badge bg-<?= $incident['status'] === 'Lost' ? 'danger' : 'success' ?>">
                                        <?= htmlspecialchars($incident['status']) ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($incident['notes']): ?>
                                <div class="alert alert-light mt-3">
                                    <strong><i class="fas fa-sticky-note"></i> Notes:</strong><br>
                                    <?= nl2br(htmlspecialchars($incident['notes'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Total Casualties Summary -->
    <div class="alert alert-warning mt-4">
        <h5><i class="fas fa-heart-broken"></i> Total Casualties</h5>
        <p class="mb-0">
            Across all recorded incidents: <strong><?= $stats['total_casualties'] ?></strong> casualties
        </p>
    </div>

    <!-- Related Links -->
    <div class="card mt-4">
        <div class="card-body">
            <h5><i class="fas fa-link"></i> Related Pages</h5>
            <div class="row">
                <div class="col-md-6">
                    <a href="eternal-patrol.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-flag-usa"></i> Eternal Patrol (Lost Submarines)
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="memorial.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-monument"></i> Memorial Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
