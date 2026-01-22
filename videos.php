<?php include 'config/database.php'; ?>
<?php include 'includes/header.php'; ?>
<main class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <h1 class="mb-4">Latest Videos</h1>
      <div class="video-grid">
        <div class="video-card">
          <h3>Latest Uploads</h3>
          <div class="ratio ratio-16x9" style="border-radius:8px; overflow:hidden; height:588px; max-width:100%;">
            <iframe src="https://www.youtube.com/embed/6ZY0GTpc9YE" allowfullscreen style="border:0; height:100%; width:100%;"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include 'includes/footer.php'; ?>