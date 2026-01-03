<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = 'Edit About Section';
$page_description = 'Admin: Edit Eternal Patrol About Text';
require_once 'config/database.php';

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$message = '';
$error = '';

// Create table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value LONGTEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $aboutText = $_POST['about_text'] ?? '';
        
        // Insert or update
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('eternal_patrol_about', ?) 
                              ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$aboutText, $aboutText]);
        
        $message = "About text updated successfully!";
    } catch (PDOException $e) {
        $error = "Error saving: " . $e->getMessage();
    }
}

// Get current text
$aboutText = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'eternal_patrol_about'");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        $aboutText = $result['setting_value'];
    } else {
        // Default text if not set
        $aboutText = '<p>Each Memorial Day, United States Submarine Veterans read the list of the boats lost in World War 2. It is called with the "Tolling of the Boats." The reading of the names of the lost boats typically includes the number of sailors lost with each one. Since the ceremony was created by WW2 sub vets, the focus was on their era. Recently, as more of the sub vets are from post WW2 service, the list has included the four submarines that we have lost since the war. Although they were not lost in active combat, we acknowledge that these sailors also gave their lives in the service of their country.</p>
                    
<p>However, this reading has not usually included the submarines we lost prior to WW2. We had an average of one submarine incident (sinking or grounding) every 27 months from the time of the USS Holland purchase in 1900 until the outbreak of WW2 in 1941. However, a number of those boats sank without loss of life and were salvaged and recommissioned. Therefore, we made an arbitrary decision about which boats to include in our list of those lost prior to WW2. We only included the eleven US Navy submarines lost prior to WW2 with loss of life or where the boat was not salvaged. The boats we did not include are listed in an appendix.</p>
                    
<p><strong>So how did this particular project come about?</strong><br>
This project came about for the USS Pampanito (SS-383), at the maritime museum in San Francisco. The Mare Island base of the USSVI sub vets normally holds their "Tolling of the Boats" at the Pampanito. This document is an effort to make the list more inclusive and to give it more texture, more depth and, hopefully, make it more interesting. Although this document is too lengthy to be read in full at the ceremony, we hope it might provide more information for anyone who might be interested.</p>
                    
<p>Some of the stories of the lost submarines are interesting or particularly tragic. Most of them had interesting histories prior to their last patrols. That is the sort of thing we wanted to convey. For example, one boat rescued gold bars and silver coins from banks in the Philippines, only to have one gold bar go "missing" on the way home. One submarine sank a Japanese carrier but wasn\'t immediately aware of it. The submarine was long gone before the carrier went down. The stories of the sister ships USS Squalus/Sailfish and the USS Sculpin are particularly ironic and sad. There was also the frequent tension between what captains thought they sank and what they got credit for in the postwar audit.</p>
                    
<p><strong>How is the document organized?</strong><br>
The prewar losses that met our criteria are listed in section 1. Section 2 details the WW2 losses and postwar losses are in section 3. The listings are generally in order of the dates the boats were lost. That isn\'t an exact sort since we still don\'t always know the exact dates of the losses.</p>
                    
<p>For each listing, we start with basic information about the boat such as the class and building shipyard. Next, we describe the last patrol and what we know about the submarine\'s loss. Then we go back and summarize its prior history.</p>
                    
<p>The officers\' photographs, unless otherwise noted, are those of the last commanding officers. Although the majority of the captains, and their crews, were lost when the boat went down, not all perished. In two cases, the captains are listed with two different boats. In four cases the boats went aground and the entire crews were rescued. In a few more cases, captains were on the bridge when the boat was sunk and they, along with a few other crew members were able to make it to safety.</p>
                    
<p>The pictures of the submarines, again unless otherwise noted, are those of the lost boats. Obviously, there may not be much difference between boats of the same classes, but there are huge differences between our first class of submarines, such as the A-7, and the nuclear-powered boats. Manitowoc boats were launched sideways and that process looks very different. Therefore, we included the photos of many of the boats.</p>
                    
<p>Like so many other professions, sailors - particularly on submarines - speak a very odd language. Hopefully, Appendix B translates most of that jargon into a reasonable version of English.</p>
                    
<p><strong>Thanks.</strong><br>
My thanks go to Diane Cooper for the idea which we then expanded. Her guidance and suggestions throughout were most helpful. Suggestions and reminders from others are also appreciated.</p>
                    
<p>The greatest thanks go to my wife, Sue, for putting up with my strange obsession. A benefit of this project may have been to get me out of her hair a couple days per week. However, I do realize that I still try her patience at times.</p>
                    
<p><strong>Dedication.</strong><br>
This is dedicated to all submariners, particularly those who gave their lives for their countries, in times of war and in keeping the peace.</p>';
    }
} catch (PDOException $e) {
    $error = "Error loading text: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Section - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Admin: Eternal Patrol About Section</span>
            <div>
                <a href="admin-eternal-patrol.php" class="btn btn-sm btn-outline-light me-2">Back to List</a>
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
            <div class="card-header">
                <h5 class="mb-0">Edit "About the Tolling of the Boats" Section</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="about_text" class="form-label">About Text (HTML allowed)</label>
                        <textarea class="form-control" id="about_text" name="about_text" rows="20"><?= htmlspecialchars($aboutText) ?></textarea>
                        <div class="form-text">You can use HTML tags for formatting (p, strong, br, etc.)</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="eternal-patrol.php" class="btn btn-secondary" target="_blank">
                            <i class="bi bi-eye"></i> Preview Page
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        tinymce.init({
            selector: '#about_text',
            height: 500,
            menubar: false,
            plugins: 'lists link code',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link | code',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px }'
        });
    </script>
</body>
</html>
