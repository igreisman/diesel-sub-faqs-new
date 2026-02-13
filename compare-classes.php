<?php
$page_title = 'Compare Classes';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compare Classes</li>
        </ol>
    </nav>

    <h1 class="mb-4">Compare Classes</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>Class</th>
                    <th>Disp<sup>1</sup></th>
                    <th>Length</th>
                    <th>Beam</th>
                    <th>Speed<sup>2</sup></th>
                    <th>Range<sup>3</sup></th>
                    <th>Depth<sup>4</sup></th>
                    <th>Crew<sup>5</sup></th>
                    <th>Torp<sup>6</sup></th>
                </tr>
            </thead>
            <tbody>
                <tr><td>A Class</td><td>107</td><td>64'</td><td>12'</td><td>8</td><td>200</td><td>75</td><td>7</td><td>3</td></tr>
                <tr><td>E Class</td><td>287</td><td>135' 3"</td><td>14' 7"</td><td>13</td><td>2,090</td><td>200</td><td>20</td><td>8</td></tr>
                <tr><td>F Class</td><td>330</td><td>142' 7"</td><td>15' 5"</td><td>14</td><td>2,500</td><td>200</td><td>22</td><td>8</td></tr>
                <tr><td>H Class</td><td>358</td><td>150' 4"</td><td>15' 10"</td><td>14</td><td>2,500</td><td>200</td><td>25</td><td>8</td></tr>
                <tr><td>O class</td><td>521</td><td>172' 3"</td><td>18' 1"`</td><td>14</td><td>5,500</td><td>200</td><td>29</td><td>8</td></tr>
                <tr><td>R Class</td><td>569</td><td>186' 2"</td><td>18'</td><td>12.5</td><td>4,700</td><td>200</td><td>33</td><td>8</td></tr>
                <tr><td>S-1 Class</td><td>876</td><td>231'</td><td>21' 10"</td><td>15</td><td>5,500</td><td>200</td><td>42</td><td>12</td></tr>
                <tr><td>S-4 Class</td><td>876</td><td>231 ft</td><td>21' 10"</td><td>15</td><td>5,500</td><td>200</td><td>42</td><td>12</td></tr>
                <tr><td>S-18 Class</td><td>930</td><td>219' 3"</td><td>20' 8"</td><td>13</td><td>3,420</td><td>200</td><td>43</td><td>12</td></tr>
                <tr><td>S-42 Class</td><td>963</td><td>225' 4"</td><td>20' 11"</td><td>12.5</td><td>2,510</td><td>200</td><td>43</td><td>12</td></tr>
                <tr><td>S-48 Class</td><td>903</td><td>240'</td><td>21' 11"</td><td>14.5</td><td>5,000</td><td>200</td><td>38</td><td>12</td></tr>
                <tr><td>Argonaut</td><td>2,710</td><td>381'</td><td>33' 9"</td><td>15</td><td>8,000</td><td>300</td><td>80</td><td>16</td></tr>
                <tr><td>Porpoise</td><td>1,316</td><td>287'</td><td>13' 9"</td><td>19.5</td><td>6,000</td><td>250</td><td>54</td><td>16</td></tr>
                <tr><td>Sargo</td><td>1,450</td><td>310' 6"</td><td>26' 10"</td><td>21</td><td>11,000</td><td>250</td><td>59</td><td>24</td></tr>
                <tr><td>Tambor</td><td>1,475</td><td>307' 2"</td><td>27'3"</td><td>20.4</td><td>11,000</td><td>250</td><td>60</td><td>24</td></tr>
                <tr><td>Gato</td><td>1,526</td><td>311' 9"</td><td>27' 3"</td><td>21</td><td>11,000</td><td>300</td><td>80</td><td>24</td></tr>
                <tr><td>Balao</td><td>1,525</td><td>311' 9"</td><td>27' 3"</td><td>20.25</td><td>11,000</td><td>400</td><td>81</td><td>24</td></tr>
                <tr><td>Tench</td><td>1,570</td><td>311' 8"</td><td>27' 3"</td><td>20.25</td><td>11,000</td><td>400</td><td>81</td><td>28</td></tr>
                <tr><td>Skipjack</td><td>3,070</td><td>251' 9"</td><td>31' 8"</td><td>15/29</td><td>Unlimited<sup>8</sup></td><td>700</td><td>85</td><td>24</td></tr>
                <tr><td>Permit</td><td>3,705</td><td>278' 6"</td><td>31' 8"</td><td>15/28</td><td>Unlimited<sup>8</sup></td><td>1,300</td><td>94</td><td>23</td></tr>
            </tbody>
        </table>
    </div>

    <h2 class="mt-4 mb-3">Notes</h2>
    <ul>
        <li>1-Displacement in long tons.</li>
        <li>2-Maximum surface speed in knots.</li>
        <li>3-Range in nautical miles.</li>
        <li>4-Test depth, usually about two thirds of design or calculated crush depth.</li>
        <li>5-Total complement, officers and enlisted.</li>
        <li>6-Total number of torpedoes the boat could carry.</li>
        <li>7-Nuclear powered submarines are typically slower on the surface than submerged. Both maximum surfaced and submerged speeds are listed for those classes.</li>
        <li>8-Nuclear powered submarines are listed as having unlimited range. The nuclear fuel lasts for years. Patrols are limited by the amount of food on board and crew morale.</li>
    </ul>

    <p class="small text-muted mt-4">Source: Compare Classes.docx</p>
</div>

<?php include 'includes/footer.php'; ?>
