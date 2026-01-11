<?php
require_once 'config/database.php';

require_once 'includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Video Gallery</h1>
    <p class="lead">Watch educational videos about diesel-electric submarines.</p>

    <div class="row">
        <?php
        $videosDir = __DIR__.'/videos/';
$videos = [];

if (is_dir($videosDir)) {
    $files = scandir($videosDir);
    foreach ($files as $file) {
        if ('.' !== $file && '..' !== $file && preg_match('/\.(mp4|webm|ogg)$/i', $file)) {
            $videos[] = $file;
        }
    }
}

if (empty($videos)) {
    echo '<div class="col-12"><p class="text-muted">No videos available at this time.</p></div>';
} else {
    foreach ($videos as $video) {
        $videoPath = 'videos/'.rawurlencode($video);
        $videoTitle = pathinfo($video, PATHINFO_FILENAME);
        $videoTitle = str_replace(['_', '-'], ' ', $videoTitle);
        ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($videoTitle); ?></h5>
                            <video width="100%" controls class="mb-3">
                                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
                <?php
    }
}
?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
