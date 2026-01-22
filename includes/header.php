<!DOCTYPE html>
<html lang="en">
<head>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-7QFL9JV7MF"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-7QFL9JV7MF');
        </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title.' - '.SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Comprehensive FAQs about diesel-electric submarines, focusing on WWII US submarines'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Date Preferences Script -->
    <script src="/assets/js/date-preferences.js" defer></script>
<?php if (strpos($_SERVER['SCRIPT_NAME'], 'videos') !== false): ?>
<style>
    .video-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: center;
    }
    .video-card {
        background: #0a2239;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        padding: 1rem;
        width: 350px;
        max-width: 100%;
    }
    .video-card h3 {
        font-size: 1.1rem;
        margin: 0.5rem 0 0.2rem 0;
        color: #fff;
    }
    .video-card p {
        color: #ccc;
        font-size: 0.95rem;
    }
    .channel-promo {
        text-align: center;
        margin: 2rem 0 1rem 0;
    }
    .channel-promo a {
        margin: 0 1rem;
        display: inline-block;
    }
    .channel-promo img {
        width: 36px;
        height: 36px;
        vertical-align: middle;
        filter: invert(1);
    }
</style>
<?php endif; ?>
</head>
<body>
<?php
$navCategories = [];
    if (isset($pdo)) {
        try {
            $navCategories = $pdo->query('SELECT name FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();
        } catch (Exception $e) {
            $navCategories = [];
        }
    }

    if (empty($navCategories)) {
        $navCategories = [
            ['name' => 'US WW2 Subs in General'],
            ['name' => 'Hull and Compartments'],
            ['name' => 'Operating US Subs in WW2'],
            ['name' => 'Life Aboard WW2 US Subs'],
            ['name' => 'Who Were the Crews Aboard WW2 US Subs'],
            ['name' => 'Attacks and Battles, Small and Large'],
        ];
    }
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <?php $isAdmin = isset($_SESSION['admin_logged_in']) && true === $_SESSION['admin_logged_in']; ?>
                        <a class="nav-link" href="<?php echo $isAdmin ? '/glossary-admin.php' : '/glossary.php'; ?>">Glossary</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about.php">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="recordsDropdown" role="button" data-bs-toggle="dropdown">
                            Historical Records
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="eternal-patrol.php">
                                <i class="fas fa-flag-usa"></i> Eternal Patrol
                            </a></li>
                            <li><a class="dropdown-item" href="incidents.php">
                                <i class="fas fa-exclamation-triangle"></i> Incidents Database
                            </a></li>
                            <li><a class="dropdown-item" href="operations.php">
                                <i class="fas fa-anchor"></i> Operations Guide
                            </a></li>
                            <li><a class="dropdown-item" href="memorial.php">
                                <i class="fas fa-monument"></i> Memorial Page
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="feedbackDropdown" role="button" data-bs-toggle="dropdown">
                            Community
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="feedback.php">
                                <i class="fas fa-pencil-alt"></i> Share Feedback
                            </a></li>
                            <li><a class="dropdown-item" href="feedback-dashboard.php">
                                <i class="fas fa-chart-line"></i> Community Dashboard
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="feedback.php?type=new_faq">
                                <i class="fas fa-plus"></i> Suggest New FAQ
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item" id="date-preferences-container">
                        <!-- Date preferences widget will be inserted here by JavaScript -->
                    </li>
                <?php if ($isAdmin) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/release-checklist.php"><i class="fas fa-clipboard-check"></i> Release Checklist</a></li>
                            <li><a class="dropdown-item" href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
