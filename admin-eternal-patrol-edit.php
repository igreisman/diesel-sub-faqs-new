<?php
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['prefilled'])) {
    $boat_number = trim($_POST['boat_number'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $date_lost = trim($_POST['date_lost'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $last_captain = trim($_POST['last_captain'] ?? '');
    $loss_narrative = trim($_POST['loss_narrative'] ?? '');
    
    // Handle image uploads or use existing URLs
    try {
        $photo_boat = handleImageUpload($_FILES['photo_boat_file'] ?? null, 'boat') 
            ?? trim($_POST['photo_boat'] ?? '');
        $photo_captain = handleImageUpload($_FILES['photo_captain_file'] ?? null, 'captain') 
            ?? trim($_POST['photo_captain'] ?? '');
        $photo_url_extra = handleImageUpload($_FILES['photo_extra_file'] ?? null, 'extra') 
            ?? trim($_POST['photo_url_extra'] ?? '');
    } catch (Exception $e) {
        $error = 'Image upload error: ' . $e->getMessage();
    }
    
    // Calculate era based on date_lost
    $era = 'ww2'; // Default to During WW2
    if (!empty($date_lost)) {
        $date_timestamp = strtotime($date_lost);
        if ($date_timestamp !== false) {
            $pearl_harbor = strtotime('1941-12-07');
            $japan_surrender = strtotime('1945-08-15');
            
            if ($date_timestamp < $pearl_harbor) {
                $era = 'pre-ww2';
            } elseif ($date_timestamp > $japan_surrender) {
                $era = 'post-ww2';
            }
        }
    }
    
    // Validation
    if (empty($boat_number) || empty($name) || empty($designation)) {
        $error = 'Boat number, name, and designation are required.';
    } else {
        try {
            if ($isEdit) {
                // Update existing submarine
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("
                    UPDATE lost_submarines 
                    SET boat_number = ?, name = ?, designation = ?, date_lost = ?, 
                        location = ?, era = ?, last_captain = ?, loss_narrative = ?,
                        photo_boat = ?, photo_captain = ?, photo_url_extra = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $boat_number, $name, $designation, $date_lost,
                    $location, $era, $last_captain, $loss_narrative,
                    $photo_boat, $photo_captain, $photo_url_extra, $id
                ]);
                $message = 'Submarine updated successfully!';
            } else {
                // Insert new submarine
                $stmt = $pdo->prepare("
                    INSERT INTO lost_submarines 
                    (boat_number, name, designation, date_lost, location, era, last_captain, loss_narrative, photo_boat, photo_captain, photo_url_extra, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
                ");
                $stmt->execute([
                    $boat_number, $name, $designation, $date_lost,
                    $location, $era, $last_captain, $loss_narrative,
                    $photo_boat, $photo_captain, $photo_url_extra
                ]);
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
        'photo_url_extra' => ''
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
        }
    } catch (PDOException $e) {
        $error = 'Error loading submarine: ' . $e->getMessage();
    }
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
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">⚓ <?= $isEdit ? 'Edit Lost Boat' : 'Add Lost Boat' ?></h1>
                    <p class="mb-0 text-muted"><?= $isEdit ? 'Update submarine details' : 'Add a new lost submarine' ?></p>
                </div>
                <div>
                    <a href="admin-eternal-patrol.php" class="btn btn-secondary">← Back to List</a>
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

                        <div class="mb-3">
                            <label for="photo_url_extra" class="form-label">Extra Photo</label>
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
                            <input type="file" id="photo_extra_file" name="photo_extra_file" accept="image/*" style="display: none;">
                            <input type="hidden" id="photo_url_extra" name="photo_url_extra" value="<?= htmlspecialchars($submarine['photo_url_extra'] ?? '') ?>">
                            <div class="form-text">Optional: Additional photo</div>
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
            background: transparent;
        }
        
        .photo-upload-zone.drag-over {
            border-color: #0d6efd;
            background: #cfe2ff;
            border-style: solid;
        }
        
        .upload-content {
            pointer-events: none;
            padding: 28px;
            background: #f8f9fa;
            border-radius: 6px;
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
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all photo upload zones
        const zones = document.querySelectorAll('.photo-upload-zone');
        
        zones.forEach(zone => {
            const fieldName = zone.dataset.field;
            const fileInput = document.getElementById(fieldName + '_file');
            const hiddenInput = document.getElementById(fieldName);
            const uploadContent = zone.querySelector('.upload-content');
            const previewContainer = zone.querySelector('.preview-container');
            const previewImage = zone.querySelector('.preview-image');
            const removeBtn = zone.querySelector('.remove-image');
            
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
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('drag-over');
            });
            
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
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
