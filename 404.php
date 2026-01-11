<?php
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Diesel-Electric Submarine FAQs');
}
$page_title = 'Page Not Found';

require_once __DIR__.'/includes/header.php';
?>

<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <h1 class="h3 mb-3">We couldn't find that FAQ</h1>
          <p class="mb-4">Sorry, the page you're looking for doesn't exist or may have been moved.</p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="/index.php">Browse FAQs</a>
            <a class="btn btn-outline-secondary" href="/search.php">Search the site</a>
            <a class="btn btn-outline-warning" href="/welcome.html">Back to Welcome</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__.'/includes/footer.php'; ?>
