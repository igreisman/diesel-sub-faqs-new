<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = 'Edit Submarine';
$page_description = 'Admin: Edit submarine details';
require_once 'config/database.php';

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';
$error = '';
$submarine = null;
$isEdit = isset($_GET['id']);

// Get previous and next submarine IDs for navigation
$prior_id = null;
$next_id = null;
if ($isEdit) {
    $boat_id = (int)$_GET['id'];
    $order_sql = "SELECT id FROM lost_submarines ORDER BY display_order ASC, boat_number ASC";
    $order_stmt = $pdo->query($order_sql);
    $boat_ids = $order_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $current_index = array_search($boat_id, $boat_ids);
    $prior_id = $current_index !== false && $current_index > 0 ? $boat_ids[$current_index - 1] : null;
    $next_id = $current_index !== false && $current_index < count($boat_ids) - 1 ? $boat_ids[$current_index + 1] : null;
}

// Handle image uploads
function handleImageUpload($file, $prefix) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only images allowed.');
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '-' . uniqid() . '.' . $extension;
    $uploadPath = __DIR__ . '/images/' . $filename;
    
    // Create images directory if it doesn't exist
    if (!is_dir(__DIR__ . '/images')) {
        mkdir(__DIR__ . '/images', 0755, true);
    }
    
    // Process and resize image
    list($width, $height) = getimagesize($file['tmp_name']);
    $maxWidth = 1200;
    $maxHeight = 1200;
    
    // Calculate new dimensions
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }
    
    // Create image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $source = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            throw new Exception('Unsupported image type');
    }
    
    // Create new image
    $dest = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefilledrectangle($dest, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save image
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($dest, $uploadPath, 85);
            break;
        case 'image/png':
            imagepng($dest, $uploadPath, 8);
            break;
        case 'image/gif':
            imagegif($dest, $uploadPath);
            break;
        case 'image/webp':
            imagewebp($dest, $uploadPath, 85);
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($dest);
    
    return 'images/' . $filename;
}

// Function to find first empty image field
function getFirstEmptyImageField($submarine) {
    if (empty($submarine)) {
        return 'image1';
    }
    for ($i = 1; $i <= 10; $i++) {
        $field = 'image' . $i;
        if (empty($submarine[$field])) {
            return $field;
        }
    }
    return 'image10'; // Default to last if all are full
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['prefilled'])) {
    $boat_number = trim($_POST['boat_number'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $date_lost = trim($_POST['date_lost'] ?? '');
    $date_lost_sort = trim($_POST['date_lost_sort'] ?? '');
    
    // If date_lost_sort is empty, try to parse date_lost
    if (empty($date_lost_sort) && !empty($date_lost)) {
        $ts = strtotime($date_lost);
        if ($ts !== false) {
            $date_lost_sort = date('Y-m-d', $ts);
        }
    }
    
    // Validate date_lost_sort is a valid date
    if (!empty($date_lost_sort)) {
        $parts = explode('-', $date_lost_sort);
        if (count($parts) === 3) {
            if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
                // Invalid date, set to NULL
                $date_lost_sort = null;
            }
        } else {
            // Invalid format
            $date_lost_sort = null;
        }
    } else {
        $date_lost_sort = null;
    }
    
    $location = trim($_POST['location'] ?? '');
    $last_captain = trim($_POST['last_captain'] ?? '');
    $loss_narrative = trim($_POST['loss_narrative'] ?? '');
    
    // Handle image uploads or use existing URLs
    try {
        $photo_boat = handleImageUpload($_FILES['photo_boat_file'] ?? null, 'boat') 
            ?? trim($_POST['photo_boat'] ?? '');
        $photo_captain = handleImageUpload($_FILES['photo_captain_file'] ?? null, 'captain') 
            ?? trim($_POST['photo_captain'] ?? '');
        
        // Get the target image field for extra photo
        $extraImageField = $_POST['extra_image_field'] ?? 'image1';
        $extraImageValue = handleImageUpload($_FILES['photo_extra_file'] ?? null, 'extra') 
            ?? trim($_POST['photo_url_extra'] ?? '');
        $extraImageSubtitle = trim($_POST['extra_image_subtitle'] ?? '');
    } catch (Exception $e) {
        $error = 'Image upload error: ' . $e->getMessage();
    }
    
    // Validation
    if (empty($boat_number) || empty($name) || empty($designation)) {
        $error = 'Boat number, name, and designation are required.';
    } else {
        try {
            if ($isEdit) {
                // Update existing submarine
                $id = (int)$_GET['id'];
                
                // Build the update query dynamically to include extra image if provided
                $updateFields = "boat_number = ?, name = ?, designation = ?, date_lost = ?, date_lost_sort = ?,
                                 location = ?, last_captain = ?, loss_narrative = ?,
                                 photo_boat = ?, photo_captain = ?";
                $params = [
                    $boat_number, $name, $designation, $date_lost, $date_lost_sort,
                    $location, $last_captain, $loss_narrative,
                    $photo_boat, $photo_captain
                ];
                
                // Add extra image field if provided
                if (!empty($extraImageValue) && !empty($extraImageField)) {
                    $updateFields .= ", {$extraImageField} = ?";
                    $params[] = $extraImageValue;
                    
                    // Add subtitle if provided
                    $subtitleField = $extraImageField . '_subtitle';
                    $updateFields .= ", {$subtitleField} = ?";
                    $params[] = $extraImageSubtitle;
                }
                
                $params[] = $id; // Add ID for WHERE clause
                
                $stmt = $pdo->prepare("
                    UPDATE lost_submarines 
                    SET {$updateFields}
                    WHERE id = ?
                ");
                $stmt->execute($params);
                $message = 'Submarine updated successfully!';
            } else {
                // Insert new submarine
                $insertFields = "boat_number, name, designation, date_lost, date_lost_sort, location, last_captain, loss_narrative, photo_boat, photo_captain, display_order";
                $insertPlaceholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0";
                $params = [
                    $boat_number, $name, $designation, $date_lost, $date_lost_sort,
                    $location, $last_captain, $loss_narrative,
                    $photo_boat, $photo_captain
                ];
                
                // Add extra image field if provided
                if (!empty($extraImageValue) && !empty($extraImageField)) {
                    $insertFields .= ", {$extraImageField}";
                    $insertPlaceholders .= ", ?";
                    $params[] = $extraImageValue;
                    
                    // Add subtitle if provided
                    $subtitleField = $extraImageField . '_subtitle';
                    $insertFields .= ", {$subtitleField}";
                    $insertPlaceholders .= ", ?";
                    $params[] = $extraImageSubtitle;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO lost_submarines 
                    ({$insertFields})
                    VALUES ({$insertPlaceholders})
                ");
                $stmt->execute($params);
                $message = 'Submarine added successfully!';
                header('Location: admin-eternal-patrol.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle prefilled data from parser
if (isset($_POST['prefilled']) && $_POST['prefilled'] == '1') {
    $submarine = [
        'boat_number' => $_POST['boat_number'] ?? '',
        'name' => $_POST['name'] ?? '',
        'designation' => $_POST['designation'] ?? '',
        'last_captain' => $_POST['captain_name'] ?? '',
        'date_lost' => $_POST['date_lost'] ?? '',
        'location' => $_POST['location'] ?? '',
        'loss_narrative' => $_POST['description'] ?? '',
        'photo_boat' => '',
        'photo_captain' => '',
        // 'photo_url_extra' => ''
    ];
}
// Load submarine data for editing
elseif ($isEdit) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM lost_submarines WHERE id = ?");
        $stmt->execute([$id]);
        $submarine = $stmt->fetch();
        
        if (!$submarine) {
            $error = 'Submarine not found.';
        } else {
            // Determine which image field to use for "Extra Photo"
            $extraImageField = getFirstEmptyImageField($submarine);
            $extraImageFieldNum = (int)str_replace('image', '', $extraImageField);
        }
    } catch (PDOException $e) {
        $error = 'Error loading submarine: ' . $e->getMessage();
    }
} else {
    $extraImageField = 'image1';
    $extraImageFieldNum = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit Lost Boat' : 'Add Lost Boat' ?> - Diesel-Electric Submarine FAQs</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #e0e0e0;
            padding-bottom: 50px;
        }
        .container {
            max-width: 800px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .form-label {
            color: #e0e0e0;
            font-weight: 500;
        }
        .form-control, .form-select {
            background: #2d2d2d;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background: #2d2d2d;
            border-color: #3498db;
            color: #e0e0e0;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        .card {
            background: #2d2d2d;
            border: 1px solid #444;
        }
        .alert {
            border-radius: 8px;
        }
        .save-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }
        .save-notification.hiding {
            animation: slideOut 0.3s ease-in;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">⚓ <?= $isEdit ? 'Edit' : 'Add Lost Boat' ?></h1>
                    <p class="mb-0 text-muted"><?= $isEdit ? 'Update submarine details' : 'Add a new lost submarine' ?></p>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($isEdit): ?>
                        <?php if ($prior_id): ?>
                            <a href="admin-eternal-patrol-edit.php?id=<?php echo $prior_id; ?>" class="btn btn-outline-light" title="Previous submarine">
                                ←
                            </a>
                        <?php endif; ?>
                        <?php if ($next_id): ?>
                            <a href="admin-eternal-patrol-edit.php?id=<?php echo $next_id; ?>" class="btn btn-outline-light" title="Next submarine">
                                →
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="admin-eternal-patrol.php" class="btn btn-secondary">List</a>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div id="saveNotification" class="alert alert-success alert-dismissible fade show save-notification" role="alert">
                <strong>✓ Success!</strong><br>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="hideNotification()"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div id="errorNotification" class="alert alert-danger alert-dismissible fade show save-notification" role="alert">
                <strong>✗ Error!</strong><br>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="hideNotification()"></button>
        <?php endif; ?>

        <?php if (!$isEdit || $submarine): ?>
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="boat_number" class="form-label">Boat Number *</label>
                                <input type="text" class="form-control" id="boat_number" name="boat_number" 
                                       value="<?= htmlspecialchars($submarine['boat_number'] ?? '') ?>" 
                                       placeholder="SS-195" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($submarine['name'] ?? '') ?>" 
                                       placeholder="Sealion" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="designation" class="form-label">Full Designation *</label>
                            <input type="text" class="form-control" id="designation" name="designation" 
                                   value="<?= htmlspecialchars($submarine['designation'] ?? '') ?>" 
                                   placeholder="USS Sealion (SS-195)" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_lost" class="form-label">Date Lost</label>
                            <input type="text" class="form-control" id="date_lost" name="date_lost" 
                                   value="<?= htmlspecialchars($submarine['date_lost'] ?? '') ?>" 
                                   placeholder="December 10, 1941">
                        </div>

                        <div class="mb-3">
                            <label for="date_lost_sort" class="form-label">Date Lost Sort (YYYY-MM-DD)</label>
                            <input type="text" class="form-control" id="date_lost_sort" name="date_lost_sort" 
                                   value="<?= htmlspecialchars($submarine['date_lost_sort'] ?? '') ?>" 
                                   placeholder="1941-12-10" pattern="\d{4}-\d{2}-\d{2}">
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($submarine['location'] ?? '') ?>" 
                                   placeholder="Cavite Navy Yard, Manila Bay, Philippines">
                        </div>

                        <div class="mb-3">
                            <label for="last_captain" class="form-label">Last Captain</label>
                            <input type="text" class="form-control" id="last_captain" name="last_captain" 
                                   value="<?= htmlspecialchars($submarine['last_captain'] ?? '') ?>" 
                                   placeholder="LT Richard G. Voge">
                        </div>

                        <div class="mb-3">
                            <label for="loss_narrative" class="form-label">Loss Narrative</label>
                            <textarea class="form-control" id="loss_narrative" name="loss_narrative" 
                                      rows="8" placeholder="Describe the circumstances of the submarine's loss..."><?= htmlspecialchars($submarine['loss_narrative'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Images & Subtitles</label>
                            <div id="dynamic-images-section"></div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="add-image-btn">Add Image + Subtitle</button>
                            <small class="text-muted d-block mt-1">You may add up to 10 images with subtitles. Supported: JPEG/PNG/GIF/WebP</small>
                        </div>
                        <script>
                        let imageCount = 0;
                        function addImageField(img = '', subtitle = '') {
                            if (imageCount >= 10) return;
                            imageCount++;
                            const section = document.getElementById('dynamic-images-section');
                            const div = document.createElement('div');
                            div.className = 'row mb-2 align-items-end';
                            div.innerHTML = `
                                <div class="col-md-5">
                                    <input type="file" class="form-control" name="image${imageCount}_file" accept="image/*">
                                    <input type="hidden" name="image${imageCount}" value="${img}">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="image${imageCount}_subtitle" placeholder="Subtitle" value="${subtitle}">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-image-btn">Remove</button>
                                </div>
                            `;
                            section.appendChild(div);
                            div.querySelector('.remove-image-btn').onclick = function() {
                                section.removeChild(div);
                                imageCount--;
                            };
                        }
                        document.getElementById('add-image-btn').onclick = function() {
                            addImageField();
                        };
                        // Optionally, prepopulate with existing images/subtitles from PHP
                        <?php for ($i = 1; $i <= 10; $i++):
                            $img = htmlspecialchars($submarine['image'.$i] ?? '');
                            $sub = htmlspecialchars($submarine['image'.$i.'_subtitle'] ?? '');
                            if ($img || $sub): ?>
                            addImageField("<?= $img ?>", "<?= $sub ?>");
                        <?php endif; endfor; ?>
                        </script>
                        <div class="mb-3">
                            <label for="photo_boat" class="form-label">Boat Photo</label>
                            <div class="photo-upload-zone" id="photo_boat_zone" data-field="photo_boat">
                                <div class="upload-content">
                                    <i class="bi bi-cloud-upload" style="font-size: 2rem;"></i>
                                    <p class="mb-2">Drag & drop image here or click to browse</p>
                                    <small class="text-muted">Max 1200x1200px, JPEG/PNG/GIF/WebP</small>
                                </div>
                                <div class="preview-container" style="display: none;">
                                    <img class="preview-image" src="" alt="Preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image">Remove</button>
                                </div>
                            </div>
                            <input type="file" id="photo_boat_file" name="photo_boat_file" accept="image/*" style="display: none;">
                            <input type="hidden" id="photo_boat" name="photo_boat" value="<?= htmlspecialchars($submarine['photo_boat'] ?? '') ?>">
                            <div class="form-text">Optional: Photo of the submarine</div>
                        </div>

                        <div class="mb-3">
                            <label for="photo_captain" class="form-label">Captain Photo</label>
                            <div class="photo-upload-zone" id="photo_captain_zone" data-field="photo_captain">
                                <div class="upload-content">
                                    <i class="bi bi-cloud-upload" style="font-size: 2rem;"></i>
                                    <p class="mb-2">Drag & drop image here or click to browse</p>
                                    <small class="text-muted">Max 1200x1200px, JPEG/PNG/GIF/WebP</small>
                                </div>
                                <div class="preview-container" style="display: none;">
                                    <img class="preview-image" src="" alt="Preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image">Remove</button>
                                </div>
                            </div>
                            <input type="file" id="photo_captain_file" name="photo_captain_file" accept="image/*" style="display: none;">
                            <input type="hidden" id="photo_captain" name="photo_captain" value="<?= htmlspecialchars($submarine['photo_captain'] ?? '') ?>">
                            <div class="form-text">Optional: Photo of the captain</div>
                        </div>

                        <?php 
                        // Display existing additional images
                        if (isset($submarine)) {
                            $hasImages = false;
                            for ($i = 1; $i <= 10; $i++) {
                                $imageField = 'image' . $i;
                                if (!empty($submarine[$imageField])) {
                                    $hasImages = true;
                                    break;
                                }
                            }
                            
                            if ($hasImages) {
                                echo '<div class="mb-4"><h5>Existing Additional Images</h5>';
                                for ($i = 1; $i <= 10; $i++) {
                                    $imageField = 'image' . $i;
                                    $subtitleField = $imageField . '_subtitle';
                                    
                                    if (!empty($submarine[$imageField])) {
                                        ?>
                                        <div class="card mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <strong>Image <?php echo $i; ?></strong>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <img src="<?php echo htmlspecialchars($submarine[$imageField]); ?>" 
                                                             alt="Image <?php echo $i; ?>" 
                                                             class="img-fluid rounded">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <p class="mb-1"><strong>Subtitle:</strong></p>
                                                        <p class="text-muted"><?php echo htmlspecialchars($submarine[$subtitleField] ?? 'No subtitle'); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                echo '</div>';
                            }
                        }
                        ?>

                        <div class="mb-3">
                            <label for="photo_url_extra" class="form-label">
                                <?php echo isset($extraImageFieldNum) ? "Image {$extraImageFieldNum}" : 'Extra Photo'; ?>
                            </label>
                            <input type="hidden" name="extra_image_field" value="<?php echo htmlspecialchars($extraImageField ?? 'image1'); ?>">
                            <div class="photo-upload-zone" id="photo_extra_zone" data-field="photo_url_extra">
                                <div class="upload-content">
                                    <i class="bi bi-cloud-upload" style="font-size: 2rem;"></i>
                                    <p class="mb-2">Drag & drop image here or click to browse</p>
                                    <small class="text-muted">Max 1200x1200px, JPEG/PNG/GIF/WebP</small>
                                </div>
                                <div class="preview-container" style="display: none;">
                                    <img class="preview-image" src="" alt="Preview">
                                    <button type="button" class="btn btn-sm btn-danger remove-image">Remove</button>
                                </div>
                            </div>
                            <input type="file" id="photo_url_extra_file" name="photo_extra_file" accept="image/*" style="display: none;">
                            <input type="hidden" id="photo_url_extra" name="photo_url_extra" value="<?php 
                                if (isset($submarine) && isset($extraImageField)) {
                                    echo htmlspecialchars($submarine[$extraImageField] ?? '');
                                }
                            ?>">
                            <div class="form-text">Optional: Additional photo</div>
                        </div>

                        <div class="mb-4">
                            <label for="extra_image_subtitle" class="form-label">
                                <?php echo isset($extraImageFieldNum) ? "Image {$extraImageFieldNum} Subtitle" : 'Extra Photo Subtitle'; ?>
                            </label>
                            <input type="text" class="form-control" id="extra_image_subtitle" name="extra_image_subtitle" 
                                value="<?php 
                                    if (isset($submarine) && isset($extraImageField)) {
                                        $subtitleField = $extraImageField . '_subtitle';
                                        echo htmlspecialchars($submarine[$subtitleField] ?? '');
                                    }
                                ?>" 
                                placeholder="Enter a caption or description for this image">
                            <div class="form-text">Optional: Caption or description for the extra photo</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="admin-eternal-patrol.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .photo-upload-zone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 2px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .photo-upload-zone:hover {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.05);
        }
        
        .photo-upload-zone.drag-over {
            border-color: #28a745 !important;
            background: rgba(40, 167, 69, 0.3) !important;
            border-style: dashed !important;
            border-width: 4px !important;
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.5) !important;
        }
        
        .upload-content {
            padding: 28px;
            background: rgba(248, 249, 250, 0.05);
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .upload-content * {
            pointer-events: none;
        }
        
        .photo-upload-zone.drag-over .upload-content {
            background: rgba(40, 167, 69, 0.25);
            color: #28a745;
        }
        
        .photo-upload-zone.drag-over .upload-content p,
        .photo-upload-zone.drag-over .upload-content small {
            color: #fff !important;
            font-weight: bold;
        }
        
        .preview-container {
            position: relative;
            display: inline-block;
            margin: 0;
        }
        
        .preview-image {
            border-radius: 4px;
            margin-bottom: 10px;
            display: block;
            max-width: 500px;
            height: auto;
        }
        
        .remove-image {
            margin-top: 10px;
        }
    </style>
    
    <script>
    // Auto-hide notification after 5 seconds
    function hideNotification() {
        const notification = document.getElementById('saveNotification') || document.getElementById('errorNotification');
        if (notification) {
            notification.classList.add('hiding');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing drag and drop');
        
        // Prevent default drag/drop behavior on the entire page
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
            console.log('Document dragover');
        }, false);
        
        document.addEventListener('drop', function(e) {
            e.preventDefault();
            console.log('Document drop');
        }, false);
        
        // Auto-dismiss notifications after 5 seconds
        const notification = document.getElementById('saveNotification') || document.getElementById('errorNotification');
        if (notification) {
            setTimeout(hideNotification, 5000);
        }
        // Initialize all photo upload zones
        const zones = document.querySelectorAll('.photo-upload-zone');
        console.log('Found ' + zones.length + ' upload zones');
        
        zones.forEach(zone => {
            const fieldName = zone.dataset.field;
            console.log('Initializing zone for:', fieldName);
            const fileInput = document.getElementById(fieldName + '_file');
            const hiddenInput = document.getElementById(fieldName);
            const uploadContent = zone.querySelector('.upload-content');
            const previewContainer = zone.querySelector('.preview-container');
            const previewImage = zone.querySelector('.preview-image');
            const removeBtn = zone.querySelector('.remove-image');
            
            // Check if all required elements exist
            if (!fileInput || !hiddenInput || !uploadContent || !previewContainer || !previewImage || !removeBtn) {
                console.error('Missing elements for zone:', fieldName, {
                    fileInput: !!fileInput,
                    hiddenInput: !!hiddenInput,
                    uploadContent: !!uploadContent,
                    previewContainer: !!previewContainer,
                    previewImage: !!previewImage,
                    removeBtn: !!removeBtn
                });
                return; // Skip this zone
            }
            
            console.log('All elements found for:', fieldName);
            
            // Load existing image if present
            if (hiddenInput.value) {
                showPreview(hiddenInput.value);
            }
            
            // Click to browse
            zone.addEventListener('click', (e) => {
                if (!e.target.classList.contains('remove-image')) {
                    fileInput.click();
                }
            });
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });
            
            // Drag and drop events
            zone.addEventListener('dragenter', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Drag enter:', fieldName);
                zone.classList.add('drag-over');
            });
            
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'copy';
                zone.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', (e) => {
                // Only remove highlight when leaving the zone completely
                if (!zone.contains(e.relatedTarget)) {
                    console.log('Drag leave:', fieldName);
                    zone.classList.remove('drag-over');
                }
            });
            
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('drag-over');
                
                if (e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];
                    
                    // Validate file type
                    if (file.type.startsWith('image/')) {
                        fileInput.files = e.dataTransfer.files;
                        handleFile(file);
                    } else {
                        alert('Please drop an image file.');
                    }
                }
            });
            
            // Remove button
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.value = '';
                hiddenInput.value = '';
                uploadContent.style.display = 'block';
                previewContainer.style.display = 'none';
                previewImage.src = '';
            });
            
            function handleFile(file) {
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    showPreview(e.target.result);
                };
                reader.readAsDataURL(file);
            }
            
            function showPreview(src) {
                previewImage.src = src;
                uploadContent.style.display = 'none';
                previewContainer.style.display = 'block';
            }
        });
    });
    </script>
</body>
</html>
