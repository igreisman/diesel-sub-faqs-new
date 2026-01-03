<?php
require_once 'config/database.php';
header('Content-Type: text/plain');

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM lost_submarines");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in lost_submarines table:\n\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
