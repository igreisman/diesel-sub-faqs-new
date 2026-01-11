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
    'boat_number' => 'SS-162',
    'name' => 'S-51',
    'designation' => 'USS S-51 (SS-162)',
    'class_info' => 'S-48 class submarine completed in June of 1922 by the Lake Torpedo Boat Company in Bridgeport CT.',
    'last_captain' => 'LT Rodney Dobson',
    'date_lost' => '25 September 1925',
    'location' => 'Near Block Island',
    'fatalities' => '33 with three survivors.',
    'cause' => 'Collision',
    'loss_narrative' => 'S-51 was sailing near Block Island, on the surface with running lights on. The merchant steamship SS City of Rome spotted the single masthead light of S-51 but couldn\'t determine the direction of the boat. S-51 spotted the masthead light and green running light of the City of Rome. S-51 held her course as she was required to do by the rules of that time. She had the right of way and was to remain on a predictable course.

City of Rome altered course and could then see the red running light of S-51. At that point, City of Rome realized that the ships were on collision courses. The steamship turned and backed her engines, but it was too late. It was 22 minutes after City of Rome first spotted S-51\'s masthead light when the steamship rammed the submarine. Only three of the submarine crew escaped before the boat sank.

The courts found both vessels to be at fault. The City of Rome was faulted for not slowing when in doubt about the other vessel\'s course and for not signaling her change in course. S-51 was faulted for not having proper running lights.

The Navy claimed that it was not reasonable for submarines to follow the rules of the road because of their inherent limitations. The judge disagreed and suggested that if that were true, perhaps submarine operations needed to be conducted in areas without other shipping.

NOTE: One challenge for submarines in peacetime is that they are low to the water and difficult to see. Even if the running lights can be seen, the configuration of the lights appears to be that of a much smaller vessel, such as a fishing boat. Running lights are rarely used during wartime.',
    'prior_history' => 'S-51 was initially based at New London, CT as part of Submarine Division 4 (SUBDIV4). She participated in routine peacetime training. In January of 1924, she sailed to the Panama Canal Zone to participate in winter fleet maneuvers. After visiting various islands in the Caribbean, S-51 returned to New York City on 30 April 1924. She then resumed her normal training cycles.',
    'era' => 'interwar',
    'year_lost' => 1925,
];

try {
    $stmt->execute($data);
    echo "✓ USS S-51 (SS-162) successfully added to lost_submarines table\n";
    echo 'Record ID: '.$pdo->lastInsertId()."\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
