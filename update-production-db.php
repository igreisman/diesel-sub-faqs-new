<?php
/**
 * Production Database Update Script
 * Run this ONCE on the server to create the lost_submarines table and import data
 * 
 * Access via: https://your-domain.com/update-production-db.php
 * Delete this file after successful execution
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Database Update</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}";
echo ".success{color:#0f0;} .error{color:#f00;} .info{color:#ff0;}</style></head><body>";

echo "<h1>Lost Submarines Database Update</h1>";
echo "<p class='info'>Starting database update...</p>";

try {
    // Step 1: Check if table exists
    echo "<h2>Step 1: Checking if lost_submarines table exists...</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'lost_submarines'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p class='info'>Table exists. Dropping and recreating...</p>";
        $pdo->exec("DROP TABLE lost_submarines");
        echo "<p class='success'>✓ Old table dropped</p>";
    } else {
        echo "<p class='info'>Table does not exist. Creating new...</p>";
    }
    
    // Step 2: Create table
    echo "<h2>Step 2: Creating lost_submarines table...</h2>";
    $createTableSQL = "
CREATE TABLE IF NOT EXISTS lost_submarines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boat_number VARCHAR(20),
    name VARCHAR(255) NOT NULL,
    designation VARCHAR(50),
    class_info TEXT,
    last_captain VARCHAR(255),
    date_lost VARCHAR(100),
    location TEXT,
    fatalities TEXT,
    cause TEXT,
    loss_narrative TEXT,
    prior_history TEXT,
    era ENUM('pre-wwi', 'wwi', 'interwar', 'wwii', 'post-wwii') DEFAULT 'wwii',
    year_lost INT,
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_era (era),
    INDEX idx_year (year_lost),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTableSQL);
    echo "<p class='success'>✓ Table created successfully</p>";
    
    // Step 3: Import data
    echo "<h2>Step 3: Importing submarine data...</h2>";
    $importDataSQL = "
INSERT INTO lost_submarines (boat_number, name, designation, class_info, last_captain, date_lost, location, fatalities, cause, loss_narrative, prior_history, era, year_lost) VALUES

('SS-195', 'Sealion', 'USS Sealion (SS-195)', 
'Sargo class submarine completed in 1939 by Electric Boat in Groton, CT.',
'LCDR Richard G. Voge',
'1941-12-10',
'Cavite, Philippine Islands',
'4 men were killed in the attack and many others were wounded.',
'Destroyed by Japanese bombs',
'Sealion was at Cavite Navy Yard when the Japanese attacked on 10 December 1941. The boat was in dry dock undergoing a scheduled overhaul.  Although the yard was well prepared for an air raid it was not prepared for the reality of a coordinated attack using fifty-four bombers.  At about 12:30, the bombers swooped in at about 18,000 feet and dropped their bombs from there.  The attack lasted about an hour.  Two bombs directly hit the Sealion.  One blew the after portion of the conning tower into the after engine room.  The engines, maneuvering room and after torpedo room were flooded.  Many men were injured.  Some were killed instantly, some were mortally wounded and some were wounded, but lived.  Four men were killed in the attack and many others were wounded.  The submarine Seadragon (SS-194) was next to the Sealion and was damaged, but repaired.  It would have been possible to salvage the Sealion, but the decision was made that more could be gained by salvaging parts from the Sealion to use in the repair of other submarines and other equipment.  Sealion was decommissioned on 25 December 1941 and was further destroyed during the Japanese advance to prevent the Japanese from salvaging equipment from her.

Sealion was on her first war patrol. She was not credited with any sinkings.',
NULL,
'wwii',
1941),

('SS-141', 'S-36', 'USS S-36 (SS-141)',
'S class submarine completed in 1923 by Bethlehem Steel in Quincy, MA.',
'LCDR John R. McKnight, Jr.',
'1942-01-20',
'Makassar Strait',
'None.',
'Grounded',
'S-36 left Surabaya on 11 January 1942 on her second war patrol.  At 02:00 on 20 January, she was running on the surface on her normal batteries due to a problem with the high capacity battery.  She was looking for a convoy that had been reported.  A course had been set to take her through a known passage.  The weather was rough and visibility was very limited.  Lieutenant Junior Grade Schoenrock had been sent for the captain, LCDR McKnight.  But, before he arrived, the submarine suddenly ran aground.  The boat had apparently drifted to one side of the passage with the heavy currents.  The charted waters were poorly mapped.  The captain attempted to use a full power astern, pumping ballast overboard and reversing the propellers, but nothing worked.  When daylight came, the crew could see breakers and rocks only two hundred yards away and huge swells coming in.  To make things worse, the Japanese were known to be patrolling the area and they knew that a surfaced submarine would be an easy target.

An SOS was sent. There was no response from other U. S. submarines.  The Sargo (SS-188) and Saury (SS-189) were nearby, but either they did not receive the message or they experienced problems trying to reach S-36.  However, the Dutch responded and sent a small freighter, the Siborote, out to rescue the crew.  The submarine was abandoned in the evening of the second day of the grounding.  Four officers and thirty-eight men were evacuated.  The S-36 was scuttled.

The crew was taken to Surabaya where those who were from S boats were assigned to the S-39 (SS-144).  Lieutenant Schoenrock remained as executive officer of that boat.  The rest of the crew was evacuated to Fremantle, Australia by the light cruiser, USS Marblehead (CL-12).  The crew of S-36 was eventually reassembled in Brisbane, Australia and then sent home to New London, Connecticut.',
'The S-36 had one previous patrol.  She had left Manila on 8 December 1941 on the morning after the Japanese attacked Pearl Harbor.  She conducted a normal patrol and returned on 22 December 1941.  She had no contacts and was in the yard for repairs when she received her orders to leave from Surabaya.

S-36 was lost on her second war patrol.  She was not credited with any sinkings.',
'wwii',
1942),

('SS-131', 'S-26', 'USS S-26 (SS-131)',
'S class submarine completed in 1923 by Bethlehem Shipbuilding in Quincy, MA.',
'LCDR Earl Hawk',
'1942-01-24',
'Gulf of Panama',
'46 men were lost. The commanding officer, executive officer and one enlisted man survived. Three other men were in the base hospital at the time and were spared.',
'Collision',
'S-26 left Balboa (Panama City) in the Canal Zone on her second war patrol accompanied by the submarines S-21 (SS-126), S-29 (SS-134) and S-44 (SS-155).  They were escorted by the patrol craft PC-460.  Running lights were not being used in wartime conditions.  At 22:10, the PC-460 signaled by lamp that she was leaving the group.  Apparently, only S-21 received the message.  The escort then turned to leave the formation.  It appears that, for some reason, the PC turned more than 180 degrees and went past the course which would have been parallel and reciprocal.  Therefore, the patrol craft was now crossing through the formation of submarines she was escorting.

At 22:23, PC-460 collided with S-26 in the darkness.  The patrol craft hit the S-26 on the starboard side of the torpedo room.  The boat sank within seconds.  The three survivors were on the bridge at the time.  Salvage operations began immediately, but when divers reached the boat there were no signs of life.  Because of the depth of the water, neither rescue or salvage of the S-26 was possible.',
'Captain Hawk went on to a successful command in the USS Pompon (SS-267).  The executive officer, LT Robert E. M. Ward went on to successful commands in the USS Sailfish (SS-192) and then the USS Sea Leopard (SS-483).

S-26 was lost on her second war patrol.  She was not credited with any sinkings.',
'wwii',
1942),

('SS-176', 'Perch', 'USS Perch (SS-176)',
'Porpoise class submarine completed in 1936 by Electric Boat in Groton, CT.',
'LCDR David A. Hurt',
'1942-03-03',
'Makassar Strait',
'None at the time of the loss of the boat. However, nine of the 62-member crew died in captivity. Accounts differ on the size of the crew but seem to agree that 53 men were recovered at the end of the war.',
'Scuttled after extensive damage by enemy anti-submarine forces',
'On her second patrol, on 25 February 1942, Perch made a night surface attack on a small convoy.  However, the enemy''s fire was accurate and Perch herself was damaged.  On 1 March, Perch was spotted by a pair of Japanese destroyers.  The submarine dove and was another victim of poor charts.  She buried her bow in the mud at just 140 feet, was subjected to a heavy depth charging and suffered severe damage.  The destroyers became convinced that Perch was sunk.  Early the next morning, after repairs were made, Perch surfaced and was again spotted.  She dove and again hit the bottom, this time at 200 feet.  Another severe depth charging and more damage followed until the destroyers were again convinced that Perch was sunk.

After more repairs, Perch surfaced and headed towards the nearest friendly port.  However, she could only make five knots speed on one engine and was still leaking badly.  The captain was feeling very vulnerable and tried another dive.  However, so much water poured in that there was danger of sinking.  While repairs were being attempted, she was spotted again.  At this point LCDR Hurt decided there was just one way to save his crew.  He ordered the crew to abandon the ship and had the boat scuttled.

The entire crew was rescued although as many as nine perished in captivity.  They were first taken to the infamous Ofuma interrogation camp.  This was an illegal interrogation camp where submariners and pilots began their internment.  The Japanese normally did not notify the U. S. of prisoners in Ofuma until they were transferred to other camps.  Even though the men were later transferred to work in the Ashio mine, most of them were not reported to the U. S. as captured until the end of the war.',
'Previously, Perch had been at Cavite when the war broke out.  She departed that night for her first war patrol.  During this patrol, she fired torpedoes at a large freighter, but missed.  Later in the patrol she fired torpedoes at another freighter and two hit the target.  Although the maru was never heard from again, Perch did not get credit for the sinking.

Perch was lost on her second war patrol.  She was not credited with any sinkings.',
'wwii',
1942);
    ";
    $pdo->exec($importDataSQL);
    echo "<p class='success'>✓ Data imported successfully</p>";
    
    // Step 4: Verify
    echo "<h2>Step 4: Verifying import...</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lost_submarines");
    $result = $stmt->fetch();
    echo "<p class='success'>✓ Total submarines in database: {$result['count']}</p>";
    
    $stmt = $pdo->query("SELECT boat_number, name, date_lost FROM lost_submarines ORDER BY date_lost");
    echo "<h3>Imported submarines:</h3><ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>{$row['boat_number']} - {$row['name']} (Lost: {$row['date_lost']})</li>";
    }
    echo "</ul>";
    
    echo "<h2 class='success'>✓ Database update completed successfully!</h2>";
    echo "<p class='info'>IMPORTANT: Delete this file (update-production-db.php) from the server now.</p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>✗ Error occurred:</h2>";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check that the database files exist in the /database/ directory.</p>";
}

echo "</body></html>";
?>
