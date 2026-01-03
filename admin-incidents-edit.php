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
$incident = null;
$isEdit = isset($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['date'];
        $submarine_name = $_POST['submarine_name'];
        $hull_number = !empty($_POST['hull_number']) ? $_POST['hull_number'] : null;
        $incident_type = $_POST['incident_type'];
        $description = $_POST['description'];
        $casualties = (int)$_POST['casualties'];
        $status = $_POST['status'];
        $era = $_POST['era'];
        $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;

        if ($isEdit) {
            $id = (int)$_GET['id'];
            $stmt = $pdo->prepare("UPDATE submarine_incidents SET 
                date = ?, submarine_name = ?, hull_number = ?, incident_type = ?, 
                description = ?, casualties = ?, status = ?, era = ?, notes = ?
                WHERE id = ?");
            $stmt->execute([$date, $submarine_name, $hull_number, $incident_type, 
                           $description, $casualties, $status, $era, $notes, $id]);
            $message = "Incident updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO submarine_incidents 
                (date, submarine_name, hull_number, incident_type, description, casualties, status, era, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$date, $submarine_name, $hull_number, $incident_type, 
                           $description, $casualties, $status, $era, $notes]);
            $message = "Incident added successfully!";
            header("Location: admin-incidents-edit.php?id=" . $pdo->lastInsertId());
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error saving incident: " . $e->getMessage();
    }
}

// Load incident if editing
if ($isEdit) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM submarine_incidents WHERE id = ?");
    $stmt->execute([$id]);
    $incident = $stmt->fetch();
    
    if (!$incident) {
        header('Location: admin-incidents.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Incident - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: #1a1a1a;
            color: #ffffff;
        }
        .card {
            background: #2d2d2d;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-control, .form-select {
            background: #1a1a1a;
            border-color: rgba(255,255,255,0.2);
            color: #ffffff;
        }
        .form-control:focus, .form-select:focus {
            background: #1a1a1a;
            border-color: #3498db;
            color: #ffffff;
        }
        .form-label {
            color: #aaaaaa;
        }
        textarea.form-control {
            min-height: 150px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Admin: <?= $isEdit ? 'Edit' : 'Add' ?> Incident</span>
            <div>
                <a href="admin-incidents.php" class="btn btn-sm btn-outline-light me-2">Back to List</a>
                <a href="admin-logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="submarine_name" class="form-label">Submarine Name *</label>
                            <input type="text" class="form-control" id="submarine_name" name="submarine_name" 
                                   value="<?= htmlspecialchars($incident['submarine_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hull_number" class="form-label">Hull Number</label>
                            <input type="text" class="form-control" id="hull_number" name="hull_number" 
                                   value="<?= htmlspecialchars($incident['hull_number'] ?? '') ?>" 
                                   placeholder="SS-164">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= htmlspecialchars($incident['date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="era" class="form-label">Era *</label>
                            <select class="form-select" id="era" name="era" required>
                                <option value="Pre-WW2" <?= ($incident['era'] ?? '') === 'Pre-WW2' ? 'selected' : '' ?>>Pre-WW2</option>
                                <option value="WW2" <?= ($incident['era'] ?? '') === 'WW2' ? 'selected' : '' ?>>WW2</option>
                                <option value="Post-WW2" <?= ($incident['era'] ?? '') === 'Post-WW2' ? 'selected' : '' ?>>Post-WW2</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="casualties" class="form-label">Casualties *</label>
                            <input type="number" class="form-control" id="casualties" name="casualties" 
                                   value="<?= htmlspecialchars($incident['casualties'] ?? '0') ?>" 
                                   min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="incident_type" class="form-label">Incident Type *</label>
                            <input type="text" class="form-control" id="incident_type" name="incident_type" 
                                   value="<?= htmlspecialchars($incident['incident_type'] ?? '') ?>" 
                                   placeholder="Fire, Grounding, Collision, etc." required>
                            <div class="form-text">Examples: Fire, Grounding, Collision, Foundered, Sinking, Combat loss</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <input type="text" class="form-control" id="status" name="status" 
                                   value="<?= htmlspecialchars($incident['status'] ?? '') ?>" 
                                   placeholder="Salvaged, Scrapped, etc." required>
                            <div class="form-text">Examples: Salvaged, Scrapped, Lost, Restored, Returned to service</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($incident['description'] ?? '') ?></textarea>
                        <div class="form-text">Full description of the incident</div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" style="min-height: 100px;"><?= htmlspecialchars($incident['notes'] ?? '') ?></textarea>
                        <div class="form-text">Additional context, also known as names, special details, etc.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="admin-incidents.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?= $isEdit ? 'Update' : 'Add' ?> Incident
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
