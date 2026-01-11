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
    'boat_number' => 'SS-66',
    'name' => 'O-5',
    'designation' => 'USS O-5 (SS-66)',
    'class_info' => 'O class submarine completed in June of 1918 by Fore River Shipbuilding Company in Quincy, MA.',
    'last_captain' => null,
    'date_lost' => '28 October 1923',
    'location' => 'Off Bahia Limon, Panama',
    'fatalities' => 'Three, 16 escaped.',
    'cause' => 'Collision',
    'loss_narrative' => 'On 23 October 1923, O-5 was preparing to enter the Canal when she was rammed by the United Fruit Company steamer Abangarez. The O-5 had a ten-foot hole opened on her starboard side. She sank quickly in 42 feet of water. 16 men escaped from the submarine and three died. Two others were trapped forward in the torpedo room.

Salvage efforts began immediately. A day later, after a couple failed attempts, a tug, local engineers and divers were able to lift the bow far enough to get it out of the water. The two trapped sailors were then able to escape through the torpedo room hatch.

One of the trapped men, Torpedoman Second Class Henry Breault could have escaped when the boat first went down. However, he chose to assist a shipmate and remained inside the sunken submarine. For his "heroism and devotion to duty" on this occasion, Breault was awarded the Medal of Honor. He was the first submariner to receive the Medal of Honor and the only enlisted man to receive the award for heroism while serving as a submariner. Seven submarine commanders received the award during World War II.

The O-5 was initially held responsible for the collision. That would be reversed by a Court of Naval Inquiry. However, in 1932, a federal judge ruled that the O-5 was, in fact, responsible.',
    'prior_history' => 'O-5 was one of a class of 16 boats designed for coastal defense in WW1. During the final months of that war, O-5 patrolled off the East Coast, from Cape Cod MA to Key West, FL. On 6 October 1918, she was in the Brooklyn Navy Yard when LTJG William J. Sharkey noticed that the batteries were giving off toxic gasses. He notified the captain, LCDR George Trevor, and they both went to investigate. The batteries then exploded. LTJG Sharkey was killed outright and LCDR Trevor was fatally injured. LTJG Sharkey was awarded the Navy Cross.

After repairs, O-5 departed on 3 November 1918 for European waters as part of a 20-submarine contingent. However, before the boats reached the Azores, the war had ended. The O-5 was then assigned to duties at the Submarine School at New London. In 1923, O-5 was assigned to Coco Solo in the Panama Canal Zone for a brief tour.',
    'era' => 'interwar',
    'year_lost' => 1923,
];

try {
    $stmt->execute($data);
    echo "✓ USS O-5 (SS-66) successfully added to lost_submarines table\n";
    echo 'Record ID: '.$pdo->lastInsertId()."\n";
    echo "\nNote: O-5 was salvaged and crew rescued - includes Medal of Honor story\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
