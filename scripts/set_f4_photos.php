<?php
require_once __DIR__ . '/../config/database.php';
try {
    $pdo->exec("ALTER TABLE lost_submarines ADD COLUMN IF NOT EXISTS photo_url_2 VARCHAR(500) NULL");
} catch (PDOException $e) {
    // MySQL 5.7 doesn't support IF NOT EXISTS for add column; ignore duplicate column errors
    if (strpos($e->getMessage(), 'Duplicate column') === false) {
        echo "ALTER ERROR: " . $e->getMessage() . "\n";
    }
}
try {
    $stmt = $pdo->prepare("UPDATE lost_submarines SET photo_url = ?, photo_url_2 = ? WHERE id = ?");
    $stmt->execute(['images/F4-boat-source.png', 'images/f4-captain-source.jpg', 5]);
    echo "UPDATE OK\n";
} catch (PDOException $e) {
    echo "UPDATE ERROR: " . $e->getMessage() . "\n";
}
