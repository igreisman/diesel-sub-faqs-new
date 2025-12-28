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
    'boat_number' => 'SS-8',
    'name' => 'A-7',
    'designation' => 'USS A-7 (SS-8)',
    'class_info' => 'A class submarine completed in October of 1903 by the Crescent Shipyard in Elizabeth, NJ. It had originally been named the Shark.',
    'last_captain' => 'LTJG Arnold Marcus',
    'date_lost' => '24 July 1917',
    'location' => 'Manilla Bay',
    'fatalities' => 'Seven',
    'cause' => 'Explosion',
    'loss_narrative' => 'A-7 was lost due to a gasoline explosion while on patrol in Manila Bay. Shortly after her engine was overhauled, there was an incident when gasoline fumes ignited and exploded. After battling the blaze, the captain ordered everyone topside and into the boats alongside. LTJG Marcus and most of the crew succumbed to their injuries the next day. The last remaining crewman died on 1 August.

A-7 was placed in ordinary on 1 April 1918 and decommissioned on 12 December 1918. She was used as a target in 1921.',
    'prior_history' => 'A-7\'s first assignments were at the Naval Torpedo Station in Newport, RI. In 1907, she was assigned to the First Submarine Flotilla at the U. S. Naval Academy.

In 1908, she was taken to the New York Naval Yard where she, along with USS A-6 (SS-7) (Porpoise), were decommissioned and placed aboard the collier Caesar for the trip to Cavite in the Philippines. They were recommissioned at Cavite on 14 August 1908. There A-7 conducted training and underwent maintenance and repairs. During WW1, while still operating out of Cavite, A-7 carried out patrols of the entrance to Manila Bay.',
    'era' => 'wwi',
    'year_lost' => 1917
];

try {
    $stmt->execute($data);
    echo "✓ USS A-7 (SS-8) successfully added to lost_submarines table\n";
    echo "Record ID: " . $pdo->lastInsertId() . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
