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
    'boat_number' => 'SS-28',
    'name' => 'H-1',
    'designation' => 'USS H-1 (SS-28)',
    'class_info' => 'H-1 was the lead boat of the H class submarines and was completed in December of 1913 by Union Iron Works of San Francisco, CA. It had originally been named the Seawolf.',
    'last_captain' => 'LCDR James Webb',
    'date_lost' => '12 March 1920',
    'location' => 'Magdalena Bay, Mexico',
    'fatalities' => 'Four died trying to swim to shore, 22 were rescued.',
    'cause' => 'Grounding',
    'loss_narrative' => 'On 6 January 1920, H-1 began her journey back to San Pedro. She transited the Panama Canal on 20 February. On 12 March, she ran aground on a shoal off Magdalena Bay, Baja California. Four men, including the captain, died trying to reach shore. The diesel freighter Mazatlán tried, without success, to pull the boat off the rocks. The Mazatlán then carried the 22 survivors to San Pedro arriving on 18 March. The repair ship USS Vestal pulled H-1 off the rocks on 24 March, but the submarine sank in less than an hour.

Further salvage attempts were abandoned. H-1 was sold for scrap in June of 1920, but was never recovered. The wreck was located and identified in 2019.',
    'prior_history' => 'H-1 was first assigned to Torpedo Flotilla 2, Pacific Fleet. Based in San Pedro, CA, she travelled the West Coast from Southern California to lower British Columbia. She frequently sailed with sister ships USS H-2 (SS-29) and USS H-3 (SS-30). In October of 1917, she sailed to New London, CT. There she patrolled Long Island Sound for the rest of WW1. She often patrolled with officer students from the Submarine School.',
    'era' => 'interwar',
    'year_lost' => 1920
];

try {
    $stmt->execute($data);
    echo "✓ USS H-1 (SS-28) successfully added to lost_submarines table\n";
    echo "Record ID: " . $pdo->lastInsertId() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
