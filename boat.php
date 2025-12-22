<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Suppress htmlspecialchars deprecation warnings for null values
error_reporting(E_ALL & ~E_DEPRECATED);

$boat_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM lost_submarines WHERE id = ?");
    $stmt->execute([$boat_id]);
    $boat = $stmt->fetch();
    
    if (!$boat) {
        header('Location: eternal-patrol.php');
        exit;
    }
    
    // Convert all null values to empty strings to avoid deprecation warnings
    foreach ($boat as $key => $value) {
        if ($value === null) {
            $boat[$key] = '';
        }
    }
} catch (Exception $e) {
    die("Error loading boat: " . htmlspecialchars($e->getMessage()));
}
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="eternal-patrol.php">Eternal Patrol</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($boat['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="display-5 mb-3">
                        <?php echo htmlspecialchars($boat['designation'] ?: $boat['name']); ?>
                    </h1>
                    
                    <?php if ($boat['photo_url']): ?>
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($boat['photo_url']); ?>" 
                             alt="<?php echo htmlspecialchars($boat['name']); ?>" 
                             class="img-fluid rounded">
                    </div>
                    <?php endif; ?>

                    <?php if ($boat['class_info']): ?>
                    <p><?php echo htmlspecialchars($boat['class_info']); ?></p>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <?php if ($boat['last_captain']): ?>
                        <div class="col-md-6">
                            <strong>Last Captain:</strong><br>
                            <?php echo htmlspecialchars($boat['last_captain']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($boat['date_lost']): ?>
                        <div class="col-md-6">
                            <strong>Date Lost:</strong><br>
                            <span class="locale-date" data-date="<?php echo htmlspecialchars($boat['date_lost']); ?>"><?php echo htmlspecialchars($boat['date_lost']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($boat['location']): ?>
                        <div class="col-md-6">
                            <strong>Location:</strong><br>
                            <?php echo htmlspecialchars($boat['location']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($boat['fatalities']): ?>
                        <div class="col-md-6">
                            <strong>Fatalities:</strong><br>
                            <?php echo htmlspecialchars($boat['fatalities']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($boat['cause']): ?>
                        <div class="col-12">
                            <strong>Cause:</strong><br>
                            <?php echo htmlspecialchars($boat['cause']); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($boat['loss_narrative']): ?>
                    <h4 class="mt-4">Loss Narrative</h4>
                    <div class="narrative-text">
                        <?php echo nl2br(htmlspecialchars($boat['loss_narrative'])); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($boat['prior_history']): ?>
                    <h4 class="mt-4">Prior History</h4>
                    <div class="history-text">
                        <?php echo nl2br(htmlspecialchars($boat['prior_history'])); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                    <div class="mt-4">
                        <a href="admin/edit-lost-boat.php?id=<?php echo $boat['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Facts</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php if ($boat['year_lost']): ?>
                        <li class="mb-2"><strong>Year:</strong> <?php echo $boat['year_lost']; ?></li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <strong>Era:</strong> 
                            <?php 
                            $era_labels = [
                                'pre-wwi' => 'Pre-WWI',
                                'wwi' => 'World War I',
                                'interwar' => 'Interwar Period',
                                'wwii' => 'World War II',
                                'post-wwii' => 'Post-WWII'
                            ];
                            echo $era_labels[$boat['era']] ?? $boat['era'];
                            ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Navigation</h5>
                </div>
                <div class="card-body">
                    <a href="eternal-patrol.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-arrow-left"></i> Back to Eternal Patrol
                    </a>
                    <a href="eternal-patrol.php?era=<?php echo $boat['era']; ?>" class="btn btn-outline-secondary w-100">
                        View All <?php echo $era_labels[$boat['era']] ?? $boat['era']; ?> Losses
                    </a>
                </div>
            </div>
        </div>
    </div>
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
