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
    'boat_number' => 'SS-25',
    'name' => 'E-2',
    'designation' => 'USS E-2 (SS-25)',
    'class_info' => 'E class submarine completed in February of 1912 by Fore River Shipbuilding Company in Quincy, MA. It had originally been named the Sturgeon.',
    'last_captain' => 'LT Charles M. Cooke',
    'date_lost' => '15 January 1916',
    'location' => 'New York Navy Yard',
    'fatalities' => 'Four dead and seven men injured.',
    'cause' => 'Explosion',
    'loss_narrative' => 'E-2 was in dry dock when she suffered a violent explosion. She had been conducting testing for a new nickel battery. The hope had been to eliminate the danger of chlorine gas that would be generated when sea water came in contact with lead-acid batteries. There were 32 men aboard at the time including the crew and contractors.

In March of 1916, E-2 was placed out of commission for use as a laboratory. She was used for continued testing of the Edison Storage Battery.

On 25 March 1918, E-2 was recommissioned and served in training and experimental work. She then conducted six anti-submarine patrols along the East Coast. She received a commendation for two of her patrols which were unusually long for a boat of her size.

She sailed from New London to Norfolk where she was placed in ordinary in 1921 and decommissioned in 1922.

LT Cooke was then assigned as captain of the USS S-5 (SS-110) which was lost in 1920. (Below.)',
    'prior_history' => 'After commissioning in 1912, E-2 served along the Atlantic coast and in the Gulf of Mexico. She participated in training exercises until she sailed to the New York Navy Yard for overhaul in 1916.',
    'era' => 'pre-wwi',
    'year_lost' => 1916,
];

try {
    $stmt->execute($data);
    echo "✓ USS E-2 (SS-25) successfully added to lost_submarines table\n";
    echo 'Record ID: '.$pdo->lastInsertId()."\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
