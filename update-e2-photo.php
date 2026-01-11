<?php

require_once 'config/database.php';

$sql = "UPDATE lost_submarines 
        SET photo_url = :photo_url 
        WHERE boat_number = 'SS-25' AND name = 'E-2'";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute(['photo_url' => 'images/e2-captain-cooke.jpg']);
    echo "✓ Photo URL updated for USS E-2 (SS-25)\n";
    echo "Photo: images/e2-captain-cooke.jpg\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
