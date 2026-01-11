<?php

require_once 'config/database.php';

$sql = "UPDATE lost_submarines 
        SET photo_url_2 = 'images/e2-captain-cooke.jpg' 
        WHERE boat_number = 'SS-25' AND name = 'E-2'";

try {
    $pdo->exec($sql);
    echo "✓ Captain photo moved to photo_url_2 field for USS E-2\n";
    echo "The captain photo will now display in the same position as F-4\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
