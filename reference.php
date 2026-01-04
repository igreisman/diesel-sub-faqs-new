<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="text-center mb-5">
            <h1 class="display-4"><i class="fas fa-book-open"></i> Reference & Research</h1>
            <p class="lead">Deep dives into submarine development, operations, and technical details</p>
            <p class="text-muted">Comprehensive reference articles covering submarine classes, wartime operations, and technical innovations</p>
        </div>
    </div>

    <!-- Reference Categories Grid -->
    <div class="row g-4">
        <!-- Submarine Classes & Development -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 reference-card">
                <div class="card-body text-center">
                    <div class="reference-icon mb-3">
                        <i class="fas fa-ship fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Submarine Classes</h5>
                    <p class="card-text">Explore the evolution of US submarine design from early A-class boats through nuclear-powered vessels. Includes specifications, capabilities, and development history.</p>
                    <a href="submarine-classes.php" class="btn btn-primary mt-auto">View Classes</a>
                </div>
            </div>
        </div>

        <!-- Operations & Tactics -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 reference-card">
                <div class="card-body text-center">
                    <div class="reference-icon mb-3">
                        <i class="fas fa-crosshairs fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Operations & Tactics</h5>
                    <p class="card-text">Midway forward bases, wolfpack tactics, lifeguard operations, and the torpedo development crisis. Learn how US submarines operated in WW2.</p>
                    <a href="operations.php" class="btn btn-success mt-auto">View Operations</a>
                </div>
            </div>
        </div>

        <!-- Technical Reference -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 reference-card">
                <div class="card-body text-center">
                    <div class="reference-icon mb-3">
                        <i class="fas fa-cog fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Technical Details</h5>
                    <p class="card-text">In-depth technical information about submarine systems, weapons, sensors, and engineering. From diesel engines to periscopes to torpedoes.</p>
                    <a href="technical.php" class="btn btn-info mt-auto">View Technical</a>
                </div>
            </div>
        </div>

        <!-- Research Notes -->
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 reference-card">
                <div class="card-body text-center">
                    <div class="reference-icon mb-3">
                        <i class="fas fa-microscope fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">Research Notes</h5>
                    <p class="card-text">Special research topics including the Manitowoc submarine shipyard, code breaking operations, and historical research methodologies.</p>
                    <a href="research.php" class="btn btn-warning mt-auto">View Research</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Resources Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h4><i class="fas fa-info-circle"></i> About These References</h4>
                    <p>These reference articles are compiled from extensive research into US submarine history, particularly focusing on World War II operations. The information includes:</p>
                    <ul>
                        <li><strong>Submarine Classes:</strong> Detailed progression from early experimental boats to modern nuclear submarines, with specifications and capabilities</li>
                        <li><strong>Operations:</strong> How submarines were deployed, supported, and operated in combat zones during WW2</li>
                        <li><strong>Technical:</strong> Engineering details about how submarine systems worked and evolved over time</li>
                        <li><strong>Research:</strong> Special topics and deep dives into specific aspects of submarine history</li>
                    </ul>
                    <p class="mb-0">This content supplements the main FAQ sections and provides more comprehensive coverage of specific topics.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div class="row mt-4 mb-5">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-question-circle"></i> FAQs</h6>
                    <p class="card-text small">Quick answers to common questions</p>
                    <a href="index.php" class="btn btn-sm btn-outline-primary">Browse FAQs</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-book"></i> Glossary</h6>
                    <p class="card-text small">Submarine terminology and definitions</p>
                    <a href="glossary.php" class="btn btn-sm btn-outline-primary">View Glossary</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-anchor"></i> Memorial</h6>
                    <p class="card-text small">Submarines and crews lost at sea</p>
                    <a href="memorial.php" class="btn btn-sm btn-outline-primary">View Memorial</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.reference-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid rgba(0,0,0,0.125);
}

.reference-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.reference-icon {
    padding: 20px;
}

.reference-card .btn {
    width: 100%;
}

.hero-section {
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.card.bg-light {
    border: 2px solid #e9ecef;
}
</style>

<?php require_once 'includes/footer.php'; ?>
