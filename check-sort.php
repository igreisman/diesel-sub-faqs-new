<?php
require_once 'config/database.php';
header('Content-Type: text/plain');

$stmt = $pdo->query("
    SELECT id, boat_number, name, date_lost, date_lost_sort
    FROM lost_submarines 
    ORDER BY date_lost_sort ASC, boat_number ASC
    LIMIT 10
");

$boats = $stmt->fetchAll();

foreach ($boats as $boat) {
    echo sprintf(
        "ID: %s | Boat: %-10s | Name: %-20s | Date Lost: %-20s | Sort: %s\n",
        str_pad($boat['id'], 3, ' ', STR_PAD_LEFT),
        $boat['boat_number'],
        $boat['name'],
        $boat['date_lost'],
        $boat['date_lost_sort'] ?? 'NULL'
    );
}
