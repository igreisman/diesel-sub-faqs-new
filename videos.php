<?php include 'config/database.php'; ?>
<?php include 'includes/header.php'; ?>
<main class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <h1 class="mb-4">Latest Videos</h1>
      <!-- <div class="channel-promo">
        <span>Subscribe for daily videos:</span>
        <a href="https://www.youtube.com/@diesel_subs" target="_blank" rel="noopener">
          <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/youtube.svg" alt="YouTube"> YouTube
        </a>
      </div> -->
      <div class="video-grid">
        <div class="video-card">
          <h3>Latest Uploads</h3>
          <div class="ratio ratio-16x9" style="border-radius:8px; overflow:hidden;">
            <iframe src="https://www.youtube.com/embed/6ZY0GTpc9YE" allowfullscreen style="border:0;"></iframe>
          </div>
          <h3>Featured Short</h3>
          <p>Watch our best-performing YouTube Short about diesel-electric submarines.</p>
        </div>
      </div>
    </div>
  </div>
</main>
<?php include 'includes/footer.php'; ?>