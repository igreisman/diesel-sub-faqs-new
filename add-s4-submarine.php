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
    'boat_number' => 'SS-109',
    'name' => 'S-4',
    'designation' => 'USS S-4 (SS-109)',
    'class_info' => 'S-4 was the first of the S-4 class submarines, completed in November of 1919 by the Portsmouth Naval Shipyard in Kittery Maine.',
    'last_captain' => 'LCDR R. K. Jones',
    'date_lost' => '17 December 1927',
    'location' => 'Off Provincetown, MA',
    'fatalities' => '40',
    'cause' => 'Collision',
    'loss_narrative' => 'S-4 was surfacing after a submerged run over a measured mile. She was accidentally rammed by the Coast Guard vessel Paulding while it was on rum patrol. Paulding stopped and put over small boats. They reported only a small amount of oil and bubbles, which lent some hope for possible rescue.

There were six men alive in the forward torpedo room. They were communicating with the rescue team by tapping on the hull. However, bad weather prevented their rescue before the oxygen in the boat ran out. As a result, all 40 members of the crew were lost.

S-4 was raised in March of 1928. It would then be used as a test vessel for rescue operations. She would be decommissioned in 1933 and scuttled in 1936.',
    'prior_history' => 'After acceptance, S-4 sailed to Havana, Cuba for operations in the Gulf of Mexico. She was then transferred to operations off the coast of New England. In November of 1920, she sailed to meet her assigned unit, Submarine Division 12, to embark on what was to be the longest voyage by submarines of the time. They sailed from New England, via the Panama Canal and Pearl Harbor, to Cavite in the Philippines. The boats arrived at Cavite on 1 December 1921.

S-4 operated from Cavite until 1924. From there, the division was assigned to the U. S. West Coast, arriving at Mare Island, CA on 30 December 1924. For the next few years, S-4 operated primarily from San Francisco, San Pedro and San Diego. In early 1927, she sailed to the Canal Zone where she operated for a couple months before sailing on to New London, CT. For the remainder of that year, she operated off the New England coast.',
    'era' => 'interwar',
    'year_lost' => 1927,
];

try {
    $stmt->execute($data);
    echo "✓ USS S-4 (SS-109) successfully added to lost_submarines table\n";
    echo 'Record ID: '.$pdo->lastInsertId()."\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
