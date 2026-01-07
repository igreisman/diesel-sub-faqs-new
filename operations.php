<?php
session_start();
require_once 'config/database.php';

$page_title = 'Submarine Operations';
$page_description = 'Information about US submarine operations, tactics, and missions during various conflicts';

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h1 class="display-4 mb-3">
                <i class="fas fa-anchor"></i> Submarine Operations
            </h1>
            <p class="lead">
                Explore the fascinating world of submarine operations, tactics, and missions throughout history.
            </p>
        </div>
    </div>

    <!-- Introduction -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="h3 mb-3"><i class="fas fa-book-open"></i> About Submarine Operations</h2>
                    <p>
                        US submarines have played crucial roles in various conflicts throughout history, particularly during World War II. 
                        Their operations ranged from reconnaissance and intelligence gathering to direct combat engagements and support missions.
                    </p>
                    <p>
                        This page provides information about the various types of submarine operations, tactical approaches, 
                        and the evolution of submarine warfare doctrine.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Operation Types -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="h3 mb-4"><i class="fas fa-list-ul"></i> Types of Operations</h2>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-crosshairs"></i> War Patrols</h5>
                </div>
                <div class="card-body">
                    <p>
                        Combat patrols were the primary mission of WWII submarines. Boats would patrol assigned areas, 
                        hunting enemy shipping and naval vessels. Patrols typically lasted 30-60 days.
                    </p>
                    <ul>
                        <li>Ship and tanker hunting</li>
                        <li>Naval engagement</li>
                        <li>Commerce disruption</li>
                        <li>Area denial</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-binoculars"></i> Reconnaissance</h5>
                </div>
                <div class="card-body">
                    <p>
                        Submarines were ideal for covert reconnaissance missions, gathering intelligence on enemy 
                        positions, movements, and installations.
                    </p>
                    <ul>
                        <li>Beach reconnaissance for amphibious landings</li>
                        <li>Photo reconnaissance</li>
                        <li>Harbor surveillance</li>
                        <li>Weather reporting</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-life-ring"></i> Lifeguard Duty</h5>
                </div>
                <div class="card-body">
                    <p>
                        During air raids, submarines stationed near enemy territory rescued downed airmen from the water, 
                        saving hundreds of lives.
                    </p>
                    <ul>
                        <li>Stationed near bombing targets</li>
                        <li>Aircrew rescue operations</li>
                        <li>Surface ship rescue</li>
                        <li>Over 500 airmen saved in WWII</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-truck"></i> Supply & Transport</h5>
                </div>
                <div class="card-body">
                    <p>
                        Submarines transported supplies, personnel, and special forces to and from isolated or 
                        enemy-held areas.
                    </p>
                    <ul>
                        <li>Guerrilla support in Philippines</li>
                        <li>Special forces insertion</li>
                        <li>Ammunition and supplies transport</li>
                        <li>Evacuations of key personnel</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-bomb"></i> Mine Laying</h5>
                </div>
                <div class="card-body">
                    <p>
                        Some submarines were equipped to lay mines in enemy waters, creating hazards for 
                        enemy shipping lanes and harbors.
                    </p>
                    <ul>
                        <li>Harbor entrances</li>
                        <li>Shipping lanes</li>
                        <li>Strategic waterways</li>
                        <li>Covert deployment</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-broadcast-tower"></i> Communication & Coordination</h5>
                </div>
                <div class="card-body">
                    <p>
                        Submarines served as radio relay stations and coordination platforms for multi-boat 
                        operations and intelligence gathering.
                    </p>
                    <ul>
                        <li>Wolf pack coordination</li>
                        <li>Radio relay</li>
                        <li>Intelligence transmission</li>
                        <li>Weather reporting</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tactical Approaches -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h3 class="h4 mb-0"><i class="fas fa-chess"></i> Tactical Approaches</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-water"></i> Submerged Attack</h5>
                            <p>
                                The classic approach: submerged approach using periscope observations, 
                                calculating firing solutions, and launching torpedoes while avoiding detection.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-moon"></i> Night Surface Attack</h5>
                            <p>
                                Particularly effective in the Pacific, submarines would surface at night to use their 
                                deck guns and higher speed for attacks and repositioning.
                            </p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <h5><i class="fas fa-users"></i> Wolf Pack Tactics</h5>
                            <p>
                                Coordinated attacks by multiple submarines, sharing intelligence and attacking 
                                convoys from multiple angles simultaneously.
                            </p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <h5><i class="fas fa-eye-slash"></i> Evasion & Escape</h5>
                            <p>
                                Silent running, deep diving, use of thermal layers, and noise discipline to 
                                evade depth charge attacks and enemy detection.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related FAQs -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="h4 mb-0"><i class="fas fa-question-circle"></i> Related FAQs</h3>
                </div>
                <div class="card-body">
                    <p>For more detailed information about submarine operations, see these FAQ categories:</p>
                    <div class="list-group">
                        <a href="category.php?cat=Operating US Subs in WW2" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-right"></i> Operating US Subs in WW2
                        </a>
                        <a href="category.php?cat=Attacks and Battles, Small and Large" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-right"></i> Attacks and Battles, Small and Large
                        </a>
                        <a href="category.php?cat=Life Aboard WW2 US Subs" class="list-group-item list-group-item-action">
                            <i class="fas fa-chevron-right"></i> Life Aboard WW2 US Subs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Box -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="card bg-light shadow">
                <div class="card-body">
                    <h3 class="h4 mb-3"><i class="fas fa-chart-bar"></i> WWII Pacific Theater Statistics</h3>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-white rounded">
                                <h4 class="text-primary mb-0">1,560</h4>
                                <p class="mb-0 small">Enemy Ships Sunk</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-white rounded">
                                <h4 class="text-success mb-0">52</h4>
                                <p class="mb-0 small">US Submarines Lost</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-white rounded">
                                <h4 class="text-info mb-0">288</h4>
                                <p class="mb-0 small">Active Submarines</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-white rounded">
                                <h4 class="text-warning mb-0">1,682</h4>
                                <p class="mb-0 small">War Patrols</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-center mb-0 mt-3 small text-muted">
                        <em>US submarines accounted for approximately 55% of all Japanese shipping losses during WWII, 
                        while representing only 2% of the US Navy.</em>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Resources -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="h4 mb-3"><i class="fas fa-external-link-alt"></i> Additional Resources</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <a href="eternal-patrol.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-flag-usa"></i> Eternal Patrol
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="incidents.php" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-exclamation-triangle"></i> Incidents Database
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="memorial.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-monument"></i> Memorial Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
