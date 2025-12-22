<?php
$page_title = 'Edit Submarine';
$page_description = 'Admin: Add or edit a lost submarine';
require_once 'config/database.php';

// Suppress htmlspecialchars deprecation warnings for null values
error_reporting(E_ALL & ~E_DEPRECATED);

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';
$error = '';
$isEdit = false;
$submarine = [
    'id' => 0,
    'boat_number' => '',
    'name' => '',
    'designation' => '',
    'class_info' => '',
    'last_captain' => '',
    'date_lost' => '',
    'location' => '',
    'fatalities' => '',
    'cause' => '',
    'loss_narrative' => '',
    'prior_history' => '',
    'era' => 'wwii',
    'year_lost' => date('Y'),
    'photo_url' => ''
];

// Load existing submarine if editing
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM lost_submarines WHERE id = ?");
        $stmt->execute([$id]);
        $loadedSub = $stmt->fetch();
        if ($loadedSub) {
            // Replace all null values with empty strings
            foreach ($loadedSub as $key => $value) {
                $submarine[$key] = $value ?? '';
            }
            $isEdit = true;
        } else {
            $error = 'Submarine not found.';
        }
    } catch (PDOException $e) {
        $error = 'Error loading submarine: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submarine['boat_number'] = trim($_POST['boat_number'] ?? '');
    $submarine['name'] = trim($_POST['name'] ?? '');
    $submarine['designation'] = trim($_POST['designation'] ?? '');
    $submarine['class_info'] = trim($_POST['class_info'] ?? '');
    $submarine['last_captain'] = trim($_POST['last_captain'] ?? '');
    $submarine['date_lost'] = trim($_POST['date_lost'] ?? '');
    $submarine['location'] = trim($_POST['location'] ?? '');
    $submarine['fatalities'] = trim($_POST['fatalities'] ?? '');
    $submarine['cause'] = trim($_POST['cause'] ?? '');
    $submarine['loss_narrative'] = trim($_POST['loss_narrative'] ?? '');
    $submarine['prior_history'] = trim($_POST['prior_history'] ?? '');
    $submarine['era'] = $_POST['era'] ?? 'wwii';
    $submarine['year_lost'] = (int)($_POST['year_lost'] ?? date('Y'));
    $submarine['photo_url'] = trim($_POST['photo_url'] ?? '');

    // Validation
    if (empty($submarine['name'])) {
        $error = 'Name is required.';
    } else {
        try {
            if ($isEdit) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE lost_submarines SET
                        boat_number = ?, name = ?, designation = ?, class_info = ?,
                        last_captain = ?, date_lost = ?, location = ?, fatalities = ?,
                        cause = ?, loss_narrative = ?, prior_history = ?,
                        era = ?, year_lost = ?, photo_url = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $submarine['boat_number'], $submarine['name'], $submarine['designation'],
                    $submarine['class_info'], $submarine['last_captain'], $submarine['date_lost'],
                    $submarine['location'], $submarine['fatalities'], $submarine['cause'],
                    $submarine['loss_narrative'], $submarine['prior_history'],
                    $submarine['era'], $submarine['year_lost'], $submarine['photo_url'],
                    $submarine['id']
                ]);
                $message = 'Submarine updated successfully!';
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO lost_submarines 
                    (boat_number, name, designation, class_info, last_captain, date_lost, 
                     location, fatalities, cause, loss_narrative, prior_history, 
                     era, year_lost, photo_url)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $submarine['boat_number'], $submarine['name'], $submarine['designation'],
                    $submarine['class_info'], $submarine['last_captain'], $submarine['date_lost'],
                    $submarine['location'], $submarine['fatalities'], $submarine['cause'],
                    $submarine['loss_narrative'], $submarine['prior_history'],
                    $submarine['era'], $submarine['year_lost'], $submarine['photo_url']
                ]);
                $submarine['id'] = $pdo->lastInsertId();
                $isEdit = true;
                $message = 'Submarine added successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Submarine - Admin</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #e0e0e0;
            padding-bottom: 50px;
        }
        .container {
            max-width: 900px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        .form-label {
            font-weight: 600;
            color: #8b9dc3;
        }
        .form-control, .form-select {
            background: #2d2d2d;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background: #363636;
            border-color: #8b9dc3;
            color: #e0e0e0;
            box-shadow: 0 0 0 0.2rem rgba(139, 157, 195, 0.25);
        }
        textarea.form-control {
            min-height: 150px;
        }
        .card {
            background: #2d2d2d;
            border: 1px solid #444;
        }
        .help-text {
            font-size: 0.875rem;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1>⚓ <?= $isEdit ? 'Edit' : 'Add' ?> Submarine</h1>
                <a href="admin-eternal-patrol.php" class="btn btn-secondary">← Back to List</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <?php if ($isEdit): ?>
                    <a href="boat.php?id=<?= $submarine['id'] ?>" target="_blank" class="alert-link">View Page</a>
                <?php endif; ?>
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
                            <label class="form-label">Boat Number *</label>
                            <input type="text" name="boat_number" class="form-control" 
                                   value="<?= htmlspecialchars($submarine['boat_number'] ?? '') ?>" 
                                   placeholder="SS-195">
                            <div class="help-text">e.g., SS-195, SS-131</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($submarine['name'] ?? '') ?>" 
                                   placeholder="Sealion" required>
                            <div class="help-text">Short name (e.g., Sealion, S-36)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" 
                               value="<?= htmlspecialchars($submarine['designation'] ?? '') ?>" 
                               placeholder="USS Sealion (SS-195)">
                        <div class="help-text">Full designation (e.g., USS Sealion (SS-195))</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Class Info</label>
                        <input type="text" name="class_info" class="form-control" 
                               value="<?= htmlspecialchars($submarine['class_info'] ?? '') ?>" 
                               placeholder="Sargo class submarine completed in 1939 by Electric Boat in Groton, CT.">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Captain</label>
                            <input type="text" name="last_captain" class="form-control" 
                                   value="<?= htmlspecialchars($submarine['last_captain'] ?? '') ?>" 
                                   placeholder="LCDR Richard G. Voge">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Lost</label>
                            <input type="text" name="date_lost" class="form-control" 
                                   value="<?= htmlspecialchars($submarine['date_lost'] ?? '') ?>" 
                                   placeholder="1941-12-10 or December 10, 1941">
                            <div class="help-text">Any format (YYYY-MM-DD recommended)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" 
                               value="<?= htmlspecialchars($submarine['location'] ?? '') ?>" 
                               placeholder="Cavite, Philippine Islands">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fatalities</label>
                        <textarea name="fatalities" class="form-control" rows="2"><?= htmlspecialchars($submarine['fatalities'] ?? '') ?></textarea>
                        <div class="help-text">e.g., "4 men were killed in the attack..."</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cause of Loss</label>
                        <input type="text" name="cause" class="form-control" 
                               value="<?= htmlspecialchars($submarine['cause'] ?? '') ?>" 
                               placeholder="Destroyed by Japanese bombs">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Era *</label>
                            <select name="era" class="form-select" required>
                                <option value="pre-wwi" <?= $submarine['era'] === 'pre-wwi' ? 'selected' : '' ?>>Pre-WWI</option>
                                <option value="wwi" <?= $submarine['era'] === 'wwi' ? 'selected' : '' ?>>WWI</option>
                                <option value="interwar" <?= $submarine['era'] === 'interwar' ? 'selected' : '' ?>>Interwar</option>
                                <option value="wwii" <?= $submarine['era'] === 'wwii' ? 'selected' : '' ?>>WWII</option>
                                <option value="post-wwii" <?= $submarine['era'] === 'post-wwii' ? 'selected' : '' ?>>Post-WWII</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year Lost *</label>
                            <input type="number" name="year_lost" class="form-control" 
                                   value="<?= $submarine['year_lost'] ?>" 
                                   min="1900" max="2100" required>
                            <div class="help-text">For sorting/filtering</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loss Narrative</label>
                        <textarea name="loss_narrative" class="form-control" rows="8"><?= $submarine['loss_narrative'] !== null ? htmlspecialchars($submarine['loss_narrative']) : '' ?></textarea>
                        <div class="help-text">Detailed account of the boat's loss (multiple paragraphs OK)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prior History</label>
                        <textarea name="prior_history" class="form-control" rows="6"><?= $submarine['prior_history'] !== null ? htmlspecialchars($submarine['prior_history']) : '' ?></textarea>
                        <div class="help-text">Service history before loss, crew fates after (optional)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Photo URL</label>
                        <input type="text" name="photo_url" class="form-control" 
                               value="<?= $submarine['photo_url'] !== null ? htmlspecialchars($submarine['photo_url']) : '' ?>" 
                               placeholder="https://example.com/photo.jpg">
                        <div class="help-text">Optional: URL to photo of the submarine</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="admin-eternal-patrol.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <?= $isEdit ? 'Update' : 'Add' ?> Submarine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
