<?php
$page_title = 'Torpedo Problems';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Torpedo Problems</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-9">
            <h1 class="mb-4">Torpedo Problems</h1>

            <p class="lead">We had three main problems with our torpedoes at the beginning of the war. They had not been detected earlier because of inadequate or non-existent testing. It took almost two full years to identify all the problems, to get the torpedo designers to admit to them and then to fix them:</p>

            <ul>
                <li class="mb-2">Torpedoes ran too deep and would pass harmlessly under the target.</li>
                <li class="mb-2">The magnetic exploder rarely worked correctly &mdash; the torpedo would pass under the target harmlessly or explode too soon. They might explode as they approached the target or as soon as the safeties were off.</li>
                <li class="mb-2">When the first problem was fixed and the second solved by turning the magnetic exploder off, we found that the torpedo didn&rsquo;t always explode after hitting the target. The exploder deformed in the collision with the target. Captains knew the torpedo was a dud, rather than a miss, because they could hear the collision and could see the geyser from the crushed air flask. However, there was no high-order explosion of the warhead to be heard.</li>
                <li class="mb-2">A fourth problem was that the torpedo would sometimes run in a circle and would come back at the firing submarine. There were probably around 23 circular runs during the war. We lost two &mdash; and possibly more &mdash; of our submarines to circular runs of their own torpedoes.</li>
            </ul>

            <p>Our torpedo problems have been described as scandalous. Sufficient tests could have identified all of these problems. One argument against more tests was that we didn&rsquo;t have enough torpedoes to conduct them. That makes little sense. Instead, we chose to test them in combat, and then didn&rsquo;t believe the results as reported by the captains. We wasted torpedoes and endangered the submarine crews.</p>

            <p>It is interesting to note that German submarines experienced similar problems with their torpedoes. They had similar issues but from different causes. However, the biggest difference is that ADM D&ouml;nitz believed his captains and was able to get the problems fixed.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
