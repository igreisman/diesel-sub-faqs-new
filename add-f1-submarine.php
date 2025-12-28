<?php
require_once 'config/database.php';

$sql = "INSERT INTO lost_submarines (
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
)";

$stmt = $pdo->prepare($sql);

$data = [
    'boat_number' => 'SS-20',
    'name' => 'F-1',
    'designation' => 'USS F-1 (SS-20)',
    'class_info' => 'F class submarine completed in June of 1912 by Union Iron Works in San Francisco, CA. It had originally been named the Carp.',
    'last_captain' => 'LT Alfred E. Montgomery',
    'date_lost' => '17 December 1917',
    'location' => 'Off Pt. Loma, San Diego',
    'fatalities' => '19. Three men were rescued.',
    'cause' => 'Collision',
    'loss_narrative' => 'While engaged in exercises off Point Loma, USS F-1 and USS F-3 (SS-22) collided. F-1\'s port side was torn open forward of the engine room and she sank in seconds. Only three men were rescued by the submarines operating with F-1. The other 19 were lost.

LT Montgomery continued to serve in various capacities in submarines, including command of the USS R-20 (SS-97) and PCO of USS S-32 (SS-137). In 1922 he went to flight training and was designated a Naval aviator. He served in various aviation assignments and commands until retiring as a vice admiral in 1951.',
    'prior_history' => 'After commissioning and sea trials, F-1 was assigned to the First Submarine Group, Pacific Torpedo Flotilla. She briefly held the record for the deepest dive, to 283 feet. In late 1912, F-1 slipped her mooring at Port Watsonville and grounded on a nearby beach. Two men died in the incident.

From July of 1914 to November 1915, she was based in Honolulu for developmental operations. F-1 was placed in ordinary from March of 1916 until June of 1917. When she was recommissioned, F-1 was part of the Patrol Force, Pacific, based in San Pedro, CA until she was lost.',
    'era' => 'wwi',
    'year_lost' => 1917
];

try {
    $stmt->execute($data);
    echo "✓ USS F-1 (SS-20) successfully added to lost_submarines table\n";
    echo "Record ID: " . $pdo->lastInsertId() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
