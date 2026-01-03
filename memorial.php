<?php
require_once 'config/database.php';

// Get statistics for memorial page
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_boats,
        SUM(CASE WHEN date_lost_sort IS NOT NULL AND date_lost_sort < '1941-12-07' THEN 1 ELSE 0 END) as pre_ww2,
        SUM(CASE WHEN date_lost_sort >= '1941-12-07' AND date_lost_sort <= '1945-09-02' THEN 1 ELSE 0 END) as wwii,
        SUM(CASE WHEN date_lost_sort IS NOT NULL AND date_lost_sort > '1945-09-02' THEN 1 ELSE 0 END) as post_wwii
    FROM lost_submarines
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eternal Patrol - In Memory of Those Lost at Sea</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
            color: #e0e0e0;
            font-family: 'Georgia', serif;
            min-height: 100vh;
        }
        
        .memorial-hero {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            padding: 80px 20px;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.05) 0%, transparent 70%);
        }
        
        .memorial-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.3)"/></svg>');
            background-size: 100px 100px;
            opacity: 0.2;
            animation: drift 60s linear infinite;
        }
        
        @keyframes drift {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }
        
        .memorial-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .anchor-symbol {
            font-size: 4rem;
            opacity: 0.7;
            margin-bottom: 2rem;
            color: #8b9dc3;
            filter: drop-shadow(0 0 10px rgba(139, 157, 195, 0.3));
        }
        
        h1 {
            font-size: 3rem;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
            color: #fff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        .dedication {
            font-size: 1.3rem;
            line-height: 2;
            font-style: italic;
            margin: 2rem 0;
            color: #b8c5d6;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .quote {
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 3rem 0;
            padding: 2rem;
            border-left: 3px solid #8b9dc3;
            background: rgba(139, 157, 195, 0.05);
            color: #d0d7e0;
        }
        
        .quote-author {
            text-align: right;
            margin-top: 1rem;
            font-style: italic;
            color: #8b9dc3;
        }
        
        .stats-section {
            margin: 4rem 0;
            padding: 3rem 2rem;
            background: rgba(255, 255, 255, 0.03);
            border-top: 1px solid rgba(139, 157, 195, 0.2);
            border-bottom: 1px solid rgba(139, 157, 195, 0.2);
        }
        
        .stat-item {
            text-align: center;
            margin: 1rem 0;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 300;
            color: #8b9dc3;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9fa8b5;
            margin-top: 0.5rem;
        }
        
        .continue-btn {
            display: inline-block;
            margin-top: 3rem;
            padding: 15px 50px;
            background: transparent;
            border: 2px solid #8b9dc3;
            color: #8b9dc3;
            text-decoration: none;
            font-size: 1.1rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            font-family: 'Georgia', serif;
        }
        
        .continue-btn:hover {
            background: rgba(139, 157, 195, 0.1);
            border-color: #fff;
            color: #fff;
            box-shadow: 0 0 20px rgba(139, 157, 195, 0.3);
        }
        
        .divider {
            width: 60px;
            height: 2px;
            background: #8b9dc3;
            margin: 2rem auto;
            opacity: 0.5;
        }
        
        .fade-in {
            animation: fadeIn 1.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .footer-note {
            text-align: center;
            padding: 3rem 2rem;
            font-size: 0.95rem;
            color: #7a8491;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="memorial-hero">
        <div class="memorial-content fade-in">
            <div class="anchor-symbol">⚓</div>
            
            <h1>Eternal Patrol</h1>
            
            <div class="divider"></div>
            
            <p class="dedication">
                In memory of the submarines and sailors<br>
                who departed on patrol and never returned home
            </p>
            
            <div class="quote">
                <p>
                    "To those who gave their lives in defense of their country in submarines, 
                    this tribute is dedicated. They shall not grow old, as we that are left grow old. 
                    Age shall not weary them, nor the years condemn. At the going down of the sun, 
                    and in the morning, we will remember them."
                </p>
                <div class="quote-author">
                    — Adapted from "For the Fallen" by Laurence Binyon
                </div>
            </div>
            
            <div class="stats-section">
                <div class="row">
                    <div class="col-md-4 stat-item">
                        <span class="stat-number"><?php echo $stats['total_boats']; ?></span>
                        <div class="stat-label">Submarines Lost</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <span class="stat-number"><?php echo $stats['wwii']; ?></span>
                        <div class="stat-label">World War II</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <span class="stat-number"><?php echo $stats['pre_ww2'] + $stats['post_wwii']; ?></span>
                        <div class="stat-label">Other Eras</div>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="eternal-patrol.php" class="continue-btn">View Memorial List</a>
                <a href="incidents.php" class="continue-btn">Other Incidents</a>
            </div>
            
            <div class="footer-note">
                Each submarine represents not just a vessel, but the courage, sacrifice, and dedication<br>
                of the officers and crew who served aboard her. They remain on eternal patrol,<br>
                forever remembered in the hearts of a grateful nation.
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
