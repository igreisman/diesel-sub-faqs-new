<?php

require_once 'config/database.php';

$sql = 'INSERT INTO lost_submarines (
    boat_number, 
    name, 
    designation, 
    class_info, 
    last_captain, 
    date_lost, 
    location, 
    fatalities, 
    cause, 
    loss_narrative, 
    prior_history, 
    era, 
    year_lost
) VALUES (
    :boat_number,
    :name,
    :designation,
    :class_info,
    :last_captain,
    :date_lost,
    :location,
    :fatalities,
    :cause,
    :loss_narrative,
    :prior_history,
    :era,
    :year_lost
)';

$stmt = $pdo->prepare($sql);

$data = [
    'boat_number' => 'SS-110',
    'name' => 'S-5',
    'designation' => 'USS S-5 (SS-110)',
    'class_info' => 'S-4 class submarine completed in March of 1920 by the Portsmouth Naval Shipyard in Kittery, Maine.',
    'last_captain' => 'LCDR Charles M. Cooke, Jr.',
    'date_lost' => '1 September 1920',
    'location' => 'Delaware Capes',
    'fatalities' => 'None',
    'cause' => 'Foundered',
    'loss_narrative' => 'S-5 was undergoing full power sea trials in the Atlantic Ocean. She was off the Delaware Capes. She had begun a test dive when water began to flood the submarine. In those days, the main induction valve was not closed until the engines were shut down. However, the chief of the watch was distracted and was late in shutting the valve. When he realized what had happened, he quickly jerked the valve lever which jammed the valve open.

Water had entered multiple compartments. However, the valves in most compartments had been shut. That still left the torpedo room valve open and the space flooded. The bilges had settled on the bottom making it impossible to pump out the other spaces. The boat was now on the bottom in 180 feet of water with little hope of rescue.

However, the crew reasoned that, if they could move water forward and blow ballast aft, they might be able to get the stern far enough out of the water to be seen. However, there was a danger of chlorine gas if they moved sea water into the battery compartment. The crew hoped to move water into the battery compartment and then quickly close the watertight door before too much chlorine gas reached the rest of the boat. The basic plan worked, sending approximately 17 feet of the stern out of the water.

The crew then struggled, over the next 36 hours, to cut a hole of about three inches in diameter in the hull. They were then able to get the attention of the passing steamship SS Alanthus. With help from the Alanthus and the SS General G. W. Goethais they were able to cut a larger hole, about two feet in diameter, in the hull. The entire crew was able to escape the next day. Later attempts to refloat and tow S-5 to shallow waters failed.

LCDR Cooke would continue to serve in various assignments until he retired in 1948 as a vice admiral.',
    'prior_history' => null,
    'era' => 'interwar',
    'year_lost' => 1920,
];

try {
    $stmt->execute($data);
    echo "✓ USS S-5 (SS-110) successfully added to lost_submarines table\n";
    echo 'Record ID: '.$pdo->lastInsertId()."\n";
    echo "\nNote: This is the same Charles M. Cooke who commanded USS E-2 (SS-25) in 1916.\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
