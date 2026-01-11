<?php

require_once __DIR__.'/../config/database.php';

try {
    $stmt = $pdo->prepare('SELECT id, boat_number, name, designation, date_lost, era FROM lost_submarines WHERE designation = ? OR boat_number = ? LIMIT 1');
    $stmt->execute(['USS F-4 (SS-23)', 'SS-23']);
    $row = $stmt->fetch();
    if ($row) {
        echo "Found row:\n";
        echo json_encode($row, JSON_PRETTY_PRINT)."\n";

        exit(0);
    }
    echo "No matching row found.\n";

    exit(2);
} catch (Exception $e) {
    echo 'Verify failed: '.$e->getMessage()."\n";

    exit(1);
}
