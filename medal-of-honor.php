<?php
require_once 'config/database.php';
require_once 'includes/header.php';
?>
<div class="container mt-5">
    <h1 class="mb-4">U.S. Submarine Medal of Honor Recipients</h1>
    <p class="lead">The following U.S. Navy submariners were awarded the Medal of Honor for extraordinary heroism and self-sacrifice in the line of duty. Their actions represent the highest traditions of naval service and courage under the most perilous conditions.</p>

    <div class="list-group mb-5">
        <div class="list-group-item mb-4">
            <h3>Henry Breault</h3>
            <p><strong>Rank/Unit:</strong> Torpedoman Second Class, USS O-5<br>
            <strong>Date Awarded:</strong> June 27, 1921</p>
            <p><strong>Citation Summary:</strong><br>
            While O-5 sank following a collision in the Panama Canal, Breault voluntarily returned into the flooding submarine chamber to rescue a shipmate despite almost certain death. His selfless actions saved the life of his shipmate at the cost of great personal risk.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>John Philip Cromwell</h3>
            <p><strong>Rank/Unit:</strong> Captain (posthumous), Submarine Force, U.S. Navy (assigned to USS Sculpin support)<br>
            <strong>Date Awarded:</strong> October 31, 1943</p>
            <p><strong>Citation Summary:</strong><br>
            During World War II, Captain Cromwell chose to remain aboard an enemy-controlled submarine rather than risk divulging vital secret information. Refusing rescue when sea pressure dropped and capture was imminent, he gave his life to protect crucial Allied intelligence.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>Samuel D. Dealey</h3>
            <p><strong>Rank/Unit:</strong> Commander, USS Harder<br>
            <strong>Date Awarded:</strong> March 6, 1945</p>
            <p><strong>Citation Summary:</strong><br>
            Commander Dealey displayed extraordinary heroism commanding Harder against Japanese shipping. His aggressive tactics resulted in multiple successful attacks, sinking or damaging numerous enemy vessels under hazardous conditions, demonstrating fearless leadership.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>Eugene B. Fluckey</h3>
            <p><strong>Rank/Unit:</strong> Rear Admiral (then Commander), USS Barb<br>
            <strong>Date Awarded:</strong> January 14, 1946</p>
            <p><strong>Citation Summary:</strong><br>
            For outstanding service in WWII, Fluckey’s innovative and daring patrols in Barb inflicted heavy enemy losses, including uniquely audacious operations close to enemy shores. His tactics exemplified exceptional courage and skill.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>Howard W. Gilmore</h3>
            <p><strong>Rank/Unit:</strong> Commander, USS Growler<br>
            <strong>Date Awarded:</strong> February 16, 1943 (posthumous)</p>
            <p><strong>Citation Summary:</strong><br>
            During a surface engagement with enemy forces, Commander Gilmore ordered “Take her down!” — sacrificing himself to save his ship and crew. His decisive action at great personal cost exemplified supreme devotion to duty.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>Richard H. O’Kane</h3>
            <p><strong>Rank/Unit:</strong> Rear Admiral (then Lieutenant), USS Tang<br>
            <strong>Date Awarded:</strong> December 7, 1945</p>
            <p><strong>Citation Summary:</strong><br>
            Lieutenant O’Kane was honored for exceptional leadership and submarine warfare prowess. As Executive Officer and later Commander aboard Tang, his tactical expertise contributed to Tang’s distinguished record of sinking enemy ships under intense danger.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>Lawson P. Ramage</h3>
            <p><strong>Rank/Unit:</strong> Commander, USS Parche<br>
            <strong>Date Awarded:</strong> May 11, 1944</p>
            <p><strong>Citation Summary:</strong><br>
            In the South China Sea, Commander Ramage led Parche through perilous shallow waters to attack strongly defended Japanese convoys. His bold action and repeated surface attacks displayed tenacious aggressiveness in the face of heavy enemy fire.</p>
        </div>
        <div class="list-group-item mb-4">
            <h3>George L. Street III</h3>
            <p><strong>Rank/Unit:</strong> Captain, USS Tirante<br>
            <strong>Date Awarded:</strong> April 18, 1945</p>
            <p><strong>Citation Summary:</strong><br>
            While commanding Tirante in hostile Japanese waters, Captain Street navigated extreme peril beneath minefields and heavy defenses to strike effectively at the enemy. His courage and skill brought vital successes against daunting odds.</p>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var items = document.querySelectorAll('.list-group-item');
    items.forEach(function(item) {
        item.style.cursor = 'pointer';
    });
});
</script>
