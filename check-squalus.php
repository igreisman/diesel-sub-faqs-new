<?php

require_once 'config/database.php';
header('Content-Type: text/plain');

$stmt = $pdo->query("
    SELECT id, boat_number, name, designation
    FROM lost_submarines 
    WHERE name LIKE '%Squalus%' OR designation LIKE '%Squalus%'
");

$boats = $stmt->fetchAll();

foreach ($boats as $boat) {
    echo 'ID: '.$boat['id']."\n";
    echo 'Boat Number: '.$boat['boat_number']."\n";
    echo 'Name: '.$boat['name']."\n";
    echo 'Designation: '.$boat['designation']."\n";
    echo "---\n";
}
