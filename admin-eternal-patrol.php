<?php
$page_title = 'Manage Eternal Patrol';
$page_description = 'Admin: Manage lost submarines';
require_once 'config/database.php';

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';
$error = '';

// Add display_order column if it doesn't exist
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM lost_submarines LIKE 'display_order'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE lost_submarines ADD COLUMN display_order INT DEFAULT 0");
    }
} catch (PDOException $e) {
    // Column might already exist, that's fine
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM lost_submarines WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Submarine deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Error deleting submarine: ' . $e->getMessage();
    }
}

// Handle reorder (drag and drop)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    header('Content-Type: application/json');
    
    try {
        $id = (int)$_POST['id'];
        $newPosition = (int)$_POST['position']; // 0-based index
        
        // Get all submarines in current order
        $stmt = $pdo->query("SELECT id, display_order FROM lost_submarines ORDER BY display_order ASC");
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Find the submarine being moved
        $oldIndex = null;
        foreach ($subs as $index => $sub) {
            if ($sub['id'] == $id) {
                $oldIndex = $index;
                break;
            }
        }
        
        if ($oldIndex === null) {
            echo json_encode(['success' => false, 'error' => 'Submarine not found']);
            exit;
        }
        
        // Remove submarine from old position
        $movedSub = array_splice($subs, $oldIndex, 1)[0];
        
        // Insert at new position
        array_splice($subs, $newPosition, 0, [$movedSub]);
        
        // Update all display_order values
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE lost_submarines SET display_order = ? WHERE id = ?");
        foreach ($subs as $index => $sub) {
            $stmt->execute([$index + 1, $sub['id']]);
        }
        $pdo->commit();
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Initialize display_order if needed
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM lost_submarines WHERE display_order = 0");
    $result = $stmt->fetch();
    if ($result['cnt'] > 0) {
        // Set initial display order based on date_lost
        $stmt = $pdo->query("
            SELECT id FROM lost_submarines 
            ORDER BY date_lost ASC
        ");
        $subs = $stmt->fetchAll();
        $order = 1;
        foreach ($subs as $sub) {
            $pdo->prepare("UPDATE lost_submarines SET display_order = ? WHERE id = ?")->execute([$order++, $sub['id']]);
        }
    }
} catch (PDOException $e) {
    // Ignore errors
}

// Get all submarines
try {
    $stmt = $pdo->query("
        SELECT id, boat_number, name, designation, date_lost, location, era, display_order 
        FROM lost_submarines 
        ORDER BY display_order ASC
    ");
    $submarines = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error loading submarines: ' . $e->getMessage();
    $submarines = [];
}

// Get stats
try {
    $stats = $pdo->query("SELECT COUNT(*) as total, era FROM lost_submarines GROUP BY era")->fetchAll();
} catch (PDOException $e) {
    $stats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Diesel-Electric Submarine FAQs</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #e0e0e0;
            padding-bottom: 50px;
        }
        .container {
            max-width: 1400px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .stats-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
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
        .alert {
            border-radius: 8px;
        }
        .action-cell {
            white-space: nowrap;
        }
        .drag-handle {
            cursor: move;
            cursor: grab;
            font-size: 1.2rem;
            user-select: none;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        tr[draggable="true"] {
            cursor: move;
        }
        tr.dragging {
            opacity: 0.5;
            background: #3498db !important;
        }
        tr.drag-over {
            border-top: 3px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">⚓ Manage Eternal Patrol</h1>
                    <p class="mb-0 text-muted">Admin: Add, edit, or delete lost submarines</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary me-2">← Back to Site</a>
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

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= count($submarines) ?></div>
                    <div class="text-muted">Total Boats</div>
                </div>
            </div>
            <?php foreach ($stats as $stat): ?>
            <div class="col-md-2">
                <div class="stats-card">
                    <div class="stats-number"><?= $stat['total'] ?></div>
                    <div class="text-muted"><?= strtoupper(str_replace('-', ' ', $stat['era'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Button -->
        <div class="mb-3">
            <a href="admin-eternal-patrol-edit.php" class="btn btn-success btn-lg me-2">
                + Add New Submarine
            </a>
            <a href="parse-submarine-text.php" class="btn btn-info btn-lg">
                📋 Parse Text
            </a>
        </div>

        <!-- Submarines Table -->
        <div class="card bg-dark text-light">
            <div class="card-body">
                <h3 class="card-title mb-4">All Submarines (<?= count($submarines) ?>)</h3>
                
                <?php if (empty($submarines)): ?>
                    <p class="text-muted">No submarines in database yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">⋮⋮</th>
                                    <th>ID</th>
                                    <th>Boat #</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Date Lost</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="submarines-tbody">
                                <?php foreach ($submarines as $index => $sub): ?>
                                    <tr draggable="true" data-id="<?= $sub['id'] ?>">
                                        <td class="drag-handle" title="Drag to reorder">⋮⋮</td>
                                        <td><?= $sub['id'] ?></td>
                                        <td><?= htmlspecialchars($sub['boat_number']) ?></td>
                                        <td><?= htmlspecialchars($sub['name']) ?></td>
                                        <td><?= htmlspecialchars($sub['designation']) ?></td>
                                        <td><?= htmlspecialchars($sub['date_lost']) ?></td>
                                        <td><?= htmlspecialchars(substr($sub['location'], 0, 30)) ?><?= strlen($sub['location']) > 30 ? '...' : '' ?></td>
                                        <td class="action-cell">
                                            <a href="boat.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-info" target="_blank" title="View">👁️</a>
                                            <a href="admin-eternal-patrol-edit.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-warning" title="Edit">✏️</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete <?= htmlspecialchars($sub['name']) ?>?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">🗑️</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Drag and drop functionality
    const tbody = document.getElementById('submarines-tbody');
    let draggedRow = null;
    
    if (tbody) {
        tbody.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'TR') {
                draggedRow = e.target;
                e.target.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', e.target.innerHTML);
            }
        });
        
        tbody.addEventListener('dragend', function(e) {
            if (e.target.tagName === 'TR') {
                e.target.classList.remove('dragging');
            }
        });
        
        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const afterElement = getDragAfterElement(tbody, e.clientY);
            const dragging = document.querySelector('.dragging');
            
            // Remove all drag-over classes
            tbody.querySelectorAll('tr').forEach(row => row.classList.remove('drag-over'));
            
            if (afterElement == null) {
                tbody.appendChild(dragging);
            } else {
                tbody.insertBefore(dragging, afterElement);
                afterElement.classList.add('drag-over');
            }
        });
        
        tbody.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove all drag-over classes
            tbody.querySelectorAll('tr').forEach(row => row.classList.remove('drag-over'));
            
            if (draggedRow) {
                const id = draggedRow.dataset.id;
                const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
                const newPosition = rows.indexOf(draggedRow);
                
                // Send update to server
                fetch('admin-eternal-patrol.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reorder&id=${id}&position=${newPosition}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optionally reload to ensure consistency
                        location.reload();
                    } else {
                        alert('Error reordering: ' + (data.error || 'Unknown error'));
                        location.reload(); // Reload to restore original order
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error reordering submarine');
                    location.reload();
                });
            }
        });
    }
    
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('tr[draggable]:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    </script>
</body>
</html>
