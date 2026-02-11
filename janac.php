<?php
$page_title = 'JANAC';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">JANAC</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-9">
            <h1 class="mb-4">JANAC</h1>

            <p class="lead">JANAC was the Joint Army Navy Assessment Committee convened at the end of the World War 2 to confirm Japanese and American shipping losses. The process also assigned credit for the Japanese losses to the appropriate American forces and vice versa.</p>

            <p>It was useful in that it helped us to know how and where some of our submarines were lost. It also confirmed or disallowed credit for shipping that our submarines claimed to have sunk. However, many captains &ndash; perhaps even most &ndash; believe that they lost credit for ships they knew they had sunk.</p>

            <p>There were many reasons that JANAC had different, usually lower, totals for tonnage sunk than captains believed:</p>

            <ul>
                <li class="mb-2">Theodore Roscoe (<em>United States Submarine Operations in World War II</em>; Naval Institute Press; 1949, page 526) says that &ldquo;when it came to &lsquo;recognition&rsquo;, in the great majority of instances [submariners] had hit the nail &hellip; on the head.&rdquo; However, sometimes captains overestimated the types and sizes of the ships they were attacking. That is somewhat understandable when looking through a narrow periscope lens in the heat of battle. Destroyers were sometimes thought to be cruisers and heavy cruisers were estimated to be battleships.</li>
                <li class="mb-2">These estimates were often tempered by the information we could gain by reading coded Japanese messages. We could confirm that a ship was sunk if it was mentioned as lost or if it simply disappeared from all message traffic. That meant that we could also confirm, based on messages, that a ship was not lost or just damaged.</li>
                <li class="mb-2">Sometimes ships appeared to be in a different class than what they were. For example, Roscoe describes the <em>Akitsu</em> class of ships that was designed to be CVEs or escort carriers. However, the Japanese used them as transports and classified them as freighters. It was a challenge to reconcile the reported sinkings of CVEs with what Japan called freighters.</li>
                <li class="mb-2">Roscoe reports that &ldquo;the Japanese records were found to be incomplete or in numerous cases unreliable.&rdquo; This may be related to the fact that the Japanese put little emphasis on convoy duty and anti-submarine warfare until late in the war.</li>
                <li class="mb-2">Our torpedo problems, particularly those that exploded prematurely, left captains with the impression that they had hit their targets. When the boat surfaced, after being held down for some time by escorts, and the target was no longer seen, the assumption was that the target had been sunk.</li>
                <li class="mb-2">In addition, nothing under 500 tons displacement was included in Japanese records. That is why captains were often correct; they didn&rsquo;t always get the credit for all of the shipping that they believed they had sunk.</li>
                <li class="mb-2">Occasionally captains received additional credits due to the JANAC audit.</li>
                <li class="mb-2">Normal errors in trying to reconcile two very different sets of records using different dates and times.</li>
            </ul>

            <p>In spite of the differences, we included the JANAC results for information and comparison.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
