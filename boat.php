<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Get boat ID from URL

$boat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch all boat IDs in eternal-patrol order
$order_sql = "SELECT id FROM lost_submarines WHERE date_lost_sort IS NOT NULL ORDER BY display_order ASC, boat_number ASC";
$order_stmt = $pdo->query($order_sql);
$boat_ids = $order_stmt->fetchAll(PDO::FETCH_COLUMN);

$current_index = array_search($boat_id, $boat_ids);
$prior_id = $current_index !== false && $current_index > 0 ? $boat_ids[$current_index - 1] : null;
$next_id = $current_index !== false && $current_index < count($boat_ids) - 1 ? $boat_ids[$current_index + 1] : null;

if (!$boat_id) {
    header('Location: eternal-patrol.php');
    exit;
}

// Fetch boat details
$stmt = $pdo->prepare("SELECT * FROM lost_submarines WHERE id = ?");
$stmt->execute([$boat_id]);
$boat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$boat) {
    header('Location: eternal-patrol.php');
    exit;
}

$pageTitle = htmlspecialchars($boat['designation'] ?? $boat['name']);
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="eternal-patrol.php">Eternal Patrol</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($boat['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3 justify-content-between gap-2">
                <h1 class="display-4 mb-0" style="font-size:2.5rem;"><?php echo htmlspecialchars($boat['designation'] ?? $boat['name']); ?></h1>
                <div class="d-flex flex-row gap-2 ms-3">
                    <?php if ($prior_id): ?>
                        <a href="boat.php?id=<?php echo $prior_id; ?>" class="btn btn-outline-secondary px-2" title="Prior"><span aria-hidden="true">&larr;</span></a>
                    <?php endif; ?>
                    <?php if ($next_id): ?>
                        <a href="boat.php?id=<?php echo $next_id; ?>" class="btn btn-outline-secondary px-2" title="Next"><span aria-hidden="true">&rarr;</span></a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <a href="admin-eternal-patrol-edit.php?id=<?php echo $boat_id; ?>" class="btn btn-warning px-2" title="Edit">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if ($boat['boat_number']): ?>
                            <p><strong>Hull Number:</strong> <?php echo htmlspecialchars($boat['boat_number']); ?></p>
                            <?php endif; ?>
                            <?php if ($boat['class_info']): ?>
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($boat['class_info']); ?></p>
                            <?php endif; ?>
                            <?php if ($boat['last_captain']): ?>
                            <p><strong>Last Captain:</strong> <?php echo htmlspecialchars($boat['last_captain']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($boat['date_lost']): ?>
                            <p><strong>Date Lost:</strong> <?php echo htmlspecialchars($boat['date_lost']); ?></p>
                            <?php endif; ?>
                            <?php if ($boat['location']): ?>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($boat['location']); ?></p>
                            <?php endif; ?>
                            <?php if ($boat['fatalities']): ?>
                            <p><strong>Fatalities:</strong> <?php echo htmlspecialchars($boat['fatalities']); ?></p>
                            <?php endif; ?>
                            <?php if ($boat['cause']): ?>
                            <p><strong>Cause:</strong> <?php echo htmlspecialchars($boat['cause']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($boat['loss_narrative']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Loss Narrative</h3>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($boat['loss_narrative'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($boat['prior_history']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Prior History</h3>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($boat['prior_history'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="eternal-patrol.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Eternal Patrol
                </a>
            </div>
        </div>

        <div class="col-lg-4">
            <?php if (!empty($boat['photo_boat'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-0">Submarine Photo</h3>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($boat['photo_boat']); ?>" 
                         alt="<?php echo htmlspecialchars($boat['designation'] ?? $boat['name']); ?>" 
                         class="img-fluid rounded">
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($boat['photo_captain'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-0">Captain Photo</h3>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($boat['photo_captain']); ?>" 
                         alt="<?php echo htmlspecialchars($boat['last_captain']); ?>" 
                         class="img-fluid rounded">
                    <?php if ($boat['last_captain']): ?>
                    <p class="small mt-2 mb-0"><?php echo htmlspecialchars($boat['last_captain']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            // Display additional images (image1 through image10)
            for ($i = 1; $i <= 10; $i++) {
                $imageField = 'image' . $i;
                $subtitleField = 'image' . $i . '_subtitle';
                
                if (!empty($boat[$imageField])) {
                    ?>
                    <div class="card mb-4">
                        <?php if (!empty($boat[$subtitleField])): ?>
                        <div class="card-header">
                            <h3 class="h6 mb-0"><?php echo htmlspecialchars($boat[$subtitleField]); ?></h3>
                        </div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <img src="<?php echo htmlspecialchars($boat[$imageField]); ?>" 
                                 alt="<?php echo htmlspecialchars($boat[$subtitleField] ?? 'Additional photo'); ?>" 
                                 class="img-fluid rounded">
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Quick Facts</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($boat['year_lost'])): ?>
                    <p class="small mb-0"><strong>Year Lost:</strong> <?php echo htmlspecialchars($boat['year_lost']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
