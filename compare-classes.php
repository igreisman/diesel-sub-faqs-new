<?php
$page_title = 'Comparison of Submarine Classes';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Comparison of Submarine Classes</li>
        </ol>
    </nav>

    <h1 class="mb-4">Comparison of Submarine Classes</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>Class</th>
                    <th>Surfaced Displacement<br><small>(long tons)</small></th>
                    <th>Length</th>
                    <th>Beam</th>
                    <th>Max. Surface Speed<br><small>(knots)</small></th>
                    <th>Range<br><small>(nautical miles)</small></th>
                    <th>Test Depth<br><small>(feet)</small></th>
                    <th>Complement<br><small>(officers &amp; enlisted)</small></th>
                    <th>Torpedoes</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>A Class</td><td>107</td><td>64 ft.</td><td>12 ft.</td><td>8</td><td>200</td><td>75</td><td>7</td><td>3</td></tr>
                <tr><td>E Class</td><td>287</td><td>135 ft. 3 in.</td><td>14 ft. 7 in.</td><td>13</td><td>2,090</td><td>200</td><td>20</td><td>8</td></tr>
                <tr><td>F Class</td><td>330</td><td>142 ft. 7 in.</td><td>15 ft. 5 in.</td><td>14</td><td>2,500</td><td>200</td><td>22</td><td>8</td></tr>
                <tr><td>H Class</td><td>358</td><td>150 ft. 4 in.</td><td>15 ft. 10 in.</td><td>14</td><td>2,500</td><td>200</td><td>25</td><td>8</td></tr>
                <tr><td>O Class</td><td>521</td><td>172 ft. 3 in.</td><td>18 ft. 1 in.</td><td>14</td><td>5,500</td><td>200</td><td>29</td><td>8</td></tr>
                <tr><td>R Class</td><td>569</td><td>186 ft. 2 in.</td><td>18 ft.</td><td>12.5</td><td>4,700</td><td>200</td><td>33</td><td>8</td></tr>
                <tr><td>S-1 Class</td><td>876</td><td>231 ft.</td><td>21 ft. 10 in.</td><td>15</td><td>5,500</td><td>200</td><td>42</td><td>12</td></tr>
                <tr><td>S-4 Class</td><td>876</td><td>231 ft.</td><td>21 ft. 10 in.</td><td>15</td><td>5,500</td><td>200</td><td>42</td><td>12</td></tr>
                <tr><td>S-18 Class</td><td>930</td><td>219 ft. 3 in.</td><td>20 ft. 8 in.</td><td>13</td><td>3,420</td><td>200</td><td>43</td><td>12</td></tr>
                <tr><td>S-42 Class</td><td>963</td><td>225 ft. 4 in.</td><td>20 ft. 11 in.</td><td>12.5</td><td>2,510</td><td>200</td><td>43</td><td>12</td></tr>
                <tr><td>S-48 Class</td><td>903</td><td>240 ft.</td><td>21 ft. 11 in.</td><td>14.5</td><td>5,000</td><td>200</td><td>38</td><td>12</td></tr>
                <tr><td>Argonaut</td><td>2,710</td><td>381 ft.</td><td>33 ft. 9 in.</td><td>15</td><td>8,000</td><td>300</td><td>80</td><td>16</td></tr>
                <tr><td>Porpoise</td><td>1,316</td><td>287 ft.</td><td>13 ft. 9 in.</td><td>19.5</td><td>6,000</td><td>250</td><td>54</td><td>16</td></tr>
                <tr><td>Sargo</td><td>1,450</td><td>310 ft. 6 in.</td><td>26 ft. 10 in.</td><td>21</td><td>11,000</td><td>250</td><td>59</td><td>24</td></tr>
                <tr><td>Tambor</td><td>1,475</td><td>307 ft. 2 in.</td><td>27 ft. 3 in.</td><td>20.4</td><td>11,000</td><td>250</td><td>60</td><td>24</td></tr>
                <tr><td>Gato</td><td>1,526</td><td>311 ft. 9 in.</td><td>27 ft. 3 in.</td><td>21</td><td>11,000</td><td>300</td><td>80</td><td>24</td></tr>
                <tr><td>Balao</td><td>1,525</td><td>311 ft. 9 in.</td><td>27 ft. 3 in.</td><td>20.25</td><td>11,000</td><td>400</td><td>81</td><td>24</td></tr>
                <tr><td>Tench</td><td>1,570</td><td>311 ft. 8 in.</td><td>27 ft. 3 in.</td><td>20.25</td><td>11,000</td><td>400</td><td>81</td><td>28</td></tr>
                <tr><td>Skipjack</td><td>3,070</td><td>251 ft. 9 in.</td><td>31 ft. 8 in.</td><td>15 / 29*</td><td>Unlimited**</td><td>700</td><td>85</td><td>24</td></tr>
                <tr><td>Permit</td><td>3,705</td><td>278 ft. 6 in.</td><td>31 ft. 8 in.</td><td>15 / 28*</td><td>Unlimited**</td><td>1,300</td><td>94</td><td>23</td></tr>
            </tbody>
        </table>
    </div>

    <p class="mt-3"><small>* Nuclear-powered submarines are typically slower on the surface than submerged. Both maximum speeds are listed for those classes.</small></p>
    <p><small>** Nuclear-powered submarines are listed as having unlimited range. The nuclear fuel lasts for years. Patrols are limited by the amount of food and crew morale.</small></p>
</div>

<?php include 'includes/footer.php'; ?>
