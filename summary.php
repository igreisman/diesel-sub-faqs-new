<?php
$page_title = 'Summary of Lost Submarines';
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Summary of Lost Submarines</li>
        </ol>
    </nav>

    <h1 class="mb-4">Summary of Lost Submarines</h1>

    <h2 class="h5 mt-4 mb-2">Section 1 &mdash; Pre-WW2 Losses</h2>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Hull #</th><th>Date Lost</th><th>Cause</th><th>Sailors Lost</th></tr>
            </thead>
            <tbody>
                <tr><td>1-1</td><td>USS F-4</td><td>SS-23</td><td>1915-03-25</td><td>Foundered</td><td>21</td></tr>
                <tr><td>1-2</td><td>USS E-2</td><td>SS-25</td><td>1916-01-15</td><td>Explosion</td><td>4</td></tr>
                <tr><td>1-3</td><td>USS A-7</td><td>SS-8</td><td>1917-07-24</td><td>Explosion</td><td>7</td></tr>
                <tr><td>1-4</td><td>USS F-1</td><td>SS-20</td><td>1917-12-17</td><td>Collision</td><td>19</td></tr>
                <tr><td>1-5</td><td>USS H-1</td><td>SS-28</td><td>1920-03-12</td><td>Grounding</td><td>4</td></tr>
                <tr><td>1-6</td><td>USS S-5</td><td>SS-110</td><td>1920-09-01</td><td>Foundered</td><td>None</td></tr>
                <tr><td>1-7</td><td>USS O-5</td><td>SS-66</td><td>1923-10-28</td><td>Collision</td><td>3</td></tr>
                <tr><td>1-8</td><td>USS S-51</td><td>SS-162</td><td>1925-09-25</td><td>Collision</td><td>33</td></tr>
                <tr><td>1-9</td><td>USS S-4</td><td>SS-109</td><td>1927-12-17</td><td>Collision</td><td>40</td></tr>
                <tr><td>1-10</td><td>USS Squalus</td><td>SS-192</td><td>1939-05-23</td><td>Foundered</td><td>26</td></tr>
                <tr><td>1-11</td><td>USS O-9</td><td>SS-70</td><td>1941-06-20</td><td>Foundered</td><td>33</td></tr>
            </tbody>
        </table>
    </div>

    <h2 class="h5 mt-4 mb-2">Section 2 &mdash; WW2 Losses</h2>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Hull #</th><th>Date Lost</th><th>Cause</th><th>Sailors Lost</th></tr>
            </thead>
            <tbody>
                <tr><td>2-1</td><td>USS Sealion</td><td>SS-195</td><td>1941-12-10</td><td>Bombed in harbor</td><td>4 +1</td></tr>
                <tr><td>2-2</td><td>USS S-36</td><td>SS-141</td><td>1942-01-20</td><td>Grounding</td><td>None</td></tr>
                <tr><td>2-3</td><td>USS S-26</td><td>SS-131</td><td>1942-01-24</td><td>Collision</td><td>46</td></tr>
                <tr><td>2-4</td><td>USS Shark</td><td>SS-174</td><td>1942-02-11</td><td>ASW forces</td><td>58</td></tr>
                <tr><td>2-5</td><td>USS Perch</td><td>SS-176</td><td>1942-03-03</td><td>Scuttled</td><td>6</td></tr>
                <tr><td>2-6</td><td>USS S-27</td><td>SS-132</td><td>1942-06-19</td><td>Grounding</td><td>None</td></tr>
                <tr><td>2-7</td><td>USS Grunion</td><td>SS-216</td><td>1942-08-??</td><td>Uncertain</td><td>70</td></tr>
                <tr><td>2-8</td><td>USS S-39</td><td>SS-144</td><td>1942-08-16</td><td>Grounding</td><td>None</td></tr>
                <tr><td>2-9</td><td>USS Argonaut</td><td>SS-166</td><td>1943-01-10</td><td>ASW forces</td><td>105</td></tr>
                <tr><td>2-10</td><td>USS Amberjack</td><td>SS-219</td><td>1943-02-16</td><td>Air and ASW forces</td><td>74</td></tr>
                <tr><td>2-11</td><td>USS Grampus</td><td>SS-207</td><td>1943-03-05</td><td>ASW forces</td><td>71</td></tr>
                <tr><td>2-12</td><td>USS Triton</td><td>SS-201</td><td>1943-03-15</td><td>ASW forces</td><td>74</td></tr>
                <tr><td>2-13</td><td>USS Pickerel</td><td>SS-177</td><td>1943-04-03</td><td>Air and ASW forces</td><td>74</td></tr>
                <tr><td>2-14</td><td>USS Grenadier</td><td>SS-210</td><td>1943-04-22</td><td>Scuttled</td><td>4</td></tr>
                <tr><td>2-15</td><td>USS Runner</td><td>SS-275</td><td>1943-05-??</td><td>Uncertain</td><td>78</td></tr>
                <tr><td>2-16</td><td>USS R-12</td><td>SS-89</td><td>1943-06-12</td><td>Foundered</td><td>42</td></tr>
                <tr><td>2-17</td><td>USS Grayling</td><td>SS-209</td><td>1943-09-09</td><td>Rammed</td><td>76</td></tr>
                <tr><td>2-18</td><td>USS Pompano</td><td>SS-181</td><td>1943-09-15</td><td>Mine</td><td>76</td></tr>
                <tr><td>2-19</td><td>USS Cisco</td><td>SS-290</td><td>1943-09-28</td><td>Air and ASW forces</td><td>76</td></tr>
                <tr><td>2-20</td><td>USS S-44</td><td>SS-155</td><td>1943-10-07</td><td>Naval gunfire</td><td>56</td></tr>
                <tr><td>2-21</td><td>USS Wahoo</td><td>SS-238</td><td>1943-10-11</td><td>Air and ASW forces</td><td>80</td></tr>
                <tr><td>2-22</td><td>USS Dorado</td><td>SS-248</td><td>1943-10-12</td><td>Uncertain</td><td>76</td></tr>
                <tr><td>2-23</td><td>USS Corvina</td><td>SS-226</td><td>1943-11-16</td><td>Torpedoed</td><td>82</td></tr>
                <tr><td>2-24</td><td>USS Sculpin</td><td>SS-191</td><td>1943-11-19</td><td>Scuttled</td><td>63</td></tr>
                <tr><td>2-25</td><td>USS Capelin</td><td>SS-289</td><td>1943-12-09</td><td>Uncertain</td><td>78</td></tr>
                <tr><td>2-26</td><td>USS Scorpion</td><td>SS-278</td><td>1944-01-15?</td><td>Mine</td><td>76</td></tr>
                <tr><td>2-27</td><td>USS Grayback</td><td>SS-208</td><td>1944-02-26</td><td>Aircraft</td><td>80</td></tr>
                <tr><td>2-28</td><td>USS Trout</td><td>SS-202</td><td>1944-02-29</td><td>ASW forces</td><td>81</td></tr>
                <tr><td>2-29</td><td>USS Tullibee</td><td>SS-284</td><td>1944-03-26</td><td>Own torpedo</td><td>79</td></tr>
                <tr><td>2-30</td><td>USS Gudgeon</td><td>SS-211</td><td>1944-04-18</td><td>Uncertain</td><td>78</td></tr>
                <tr><td>2-31</td><td>USS Herring</td><td>SS-233</td><td>1944-06-01</td><td>Shore gunfire</td><td>84</td></tr>
                <tr><td>2-32</td><td>USS Golet</td><td>SS-361</td><td>1944-06-14</td><td>ASW forces</td><td>82</td></tr>
                <tr><td>2-33</td><td>USS S-28</td><td>SS-133</td><td>1944-07-04</td><td>Foundered</td><td>50</td></tr>
                <tr><td>2-34</td><td>USS Robalo</td><td>SS-273</td><td>1944-07-26</td><td>Mine</td><td>81</td></tr>
                <tr><td>2-35</td><td>USS Flier</td><td>SS-250</td><td>1944-08-13</td><td>Mine</td><td>78</td></tr>
                <tr><td>2-36</td><td>USS Harder</td><td>SS-257</td><td>1944-08-24</td><td>ASW forces</td><td>79</td></tr>
                <tr><td>2-37</td><td>USS Seawolf</td><td>SS-197</td><td>1944-10-03</td><td>Friendly fire</td><td>99</td></tr>
                <tr><td>2-38</td><td>USS Escolar</td><td>SS-294</td><td>1944-10-17</td><td>Mine</td><td>82</td></tr>
                <tr><td>2-39</td><td>USS Darter</td><td>SS-227</td><td>1944-10-24</td><td>Grounding</td><td>None</td></tr>
                <tr><td>2-40</td><td>USS Shark II</td><td>SS-314</td><td>1944-10-24</td><td>ASW forces</td><td>87</td></tr>
                <tr><td>2-41</td><td>USS Tang</td><td>SS-306</td><td>1944-10-24</td><td>Own torpedo</td><td>78</td></tr>
                <tr><td>2-42</td><td>USS Albacore</td><td>SS-218</td><td>1944-11-07</td><td>Mine</td><td>86</td></tr>
                <tr><td>2-43</td><td>USS Growler</td><td>SS-215</td><td>1944-11-08</td><td>Uncertain</td><td>85</td></tr>
                <tr><td>2-44</td><td>USS Scamp</td><td>SS-277</td><td>1944-11-11</td><td>ASW forces</td><td>83</td></tr>
                <tr><td>2-45</td><td>USS Swordfish</td><td>SS-193</td><td>1945-01-12</td><td>Uncertain</td><td>89</td></tr>
                <tr><td>2-46</td><td>USS Barbel</td><td>SS-316</td><td>1945-02-04</td><td>Aircraft</td><td>81</td></tr>
                <tr><td>2-47</td><td>USS Kete</td><td>SS-369</td><td>1945-03-21</td><td>Unknown</td><td>87</td></tr>
                <tr><td>2-48</td><td>USS Trigger</td><td>SS-237</td><td>1945-03-28</td><td>Air and ASW forces</td><td>89</td></tr>
                <tr><td>2-49</td><td>USS Snook</td><td>SS-279</td><td>1945-04-08</td><td>Unknown</td><td>84</td></tr>
                <tr><td>2-50</td><td>USS Lagarto</td><td>SS-371</td><td>1945-05-03</td><td>ASW forces</td><td>84</td></tr>
                <tr><td>2-51</td><td>USS Bonefish</td><td>SS-223</td><td>1945-06-18</td><td>ASW forces</td><td>85</td></tr>
                <tr><td>2-52</td><td>USS Bullhead</td><td>SS-332</td><td>1945-08-06</td><td>Aircraft</td><td>84</td></tr>
            </tbody>
        </table>
    </div>

    <h2 class="h5 mt-4 mb-2">Section 3 &mdash; Post-WW2 Losses</h2>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Hull #</th><th>Date Lost</th><th>Cause</th><th>Sailors Lost</th></tr>
            </thead>
            <tbody>
                <tr><td>3-1</td><td>USS Cochino</td><td>SS-345</td><td>1949-08-26</td><td>Battery explosion</td><td>6 (from Tusk)</td></tr>
                <tr><td>3-2</td><td>USS Stickleback</td><td>SS-415</td><td>1958-05-29</td><td>Collision</td><td>None</td></tr>
                <tr><td>3-3</td><td>USS Thresher</td><td>SSN-593</td><td>1963-04-10</td><td>Uncertain</td><td>127</td></tr>
                <tr><td>3-4</td><td>USS Scorpion</td><td>SSN-589</td><td>1968-05-22</td><td>Uncertain</td><td>99</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
