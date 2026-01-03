<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM submarine_incidents WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Incident deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting incident: " . $e->getMessage();
    }
}

// Get all incidents
try {
    $stmt = $pdo->query("SELECT * FROM submarine_incidents ORDER BY date ASC");
    $incidents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading incidents: " . $e->getMessage();
    $incidents = [];
}

// Get statistics
$stats = [
    'total' => count($incidents),
    'pre_ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'Pre-WW2')),
    'ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'WW2')),
    'post_ww2' => count(array_filter($incidents, fn($i) => $i['era'] === 'Post-WW2')),
    'total_casualties' => array_sum(array_column($incidents, 'casualties'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submarine Incidents - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: #1a1a1a;
            color: #ffffff;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .stats-card {
            background: #2d2d2d;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }
        .table-dark {
            background: #2d2d2d;
        }
        .table-dark thead {
            background: #1a1a1a;
        }
        .btn-sm {
            margin: 0 2px;
        }
        .era-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">⚠️ Manage Submarine Incidents</h1>
                    <p class="mb-0 text-muted">Admin: Add, edit, or delete submarine incidents</p>
                </div>
                <div>
                    <a href="incidents.php" class="btn btn-info me-2" target="_blank">
                        <i class="bi bi-eye"></i> View Public Page
                    </a>
                    <a href="admin-eternal-patrol.php" class="btn btn-secondary me-2">← Lost Submarines</a>
                    <a href="admin-logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total'] ?></div>
                    <div class="text-muted">Total Incidents</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['pre_ww2'] ?></div>
                    <div class="text-muted">Pre-WW2</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['ww2'] ?></div>
                    <div class="text-muted">WW2</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['post_ww2'] ?></div>
                    <div class="text-muted">Post-WW2</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $stats['total_casualties'] ?></div>
                    <div class="text-muted">Total Casualties</div>
                </div>
            </div>
        </div>

        <div class="card bg-dark">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Incidents</h5>
                <a href="admin-incidents-edit.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add New Incident
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Submarine</th>
                                <th>Hull #</th>
                                <th>Type</th>
                                <th>Casualties</th>
                                <th>Status</th>
                                <th>Era</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($incident['date'])) ?></td>
                                <td><?= htmlspecialchars($incident['submarine_name']) ?></td>
                                <td><?= htmlspecialchars($incident['hull_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($incident['incident_type']) ?></td>
                                <td><?= $incident['casualties'] ?></td>
                                <td><?= htmlspecialchars($incident['status']) ?></td>
                                <td>
                                    <span class="badge era-badge <?php 
                                        echo $incident['era'] === 'Pre-WW2' ? 'bg-secondary' : 
                                             ($incident['era'] === 'WW2' ? 'bg-danger' : 'bg-info'); 
                                    ?>">
                                        <?= htmlspecialchars($incident['era']) ?>
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <a href="admin-incidents-edit.php?id=<?= $incident['id'] ?>" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?delete=<?= $incident['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this incident?')" 
                                       title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
