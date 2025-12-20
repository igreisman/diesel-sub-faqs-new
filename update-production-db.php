<?php
/**
 * Production Database Update Script
 * Run this ONCE on the server to create the lost_submarines table and import data
 * 
 * Access via: https://your-domain.com/update-production-db.php
 * Delete this file after successful execution
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Database Update</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}";
echo ".success{color:#0f0;} .error{color:#f00;} .info{color:#ff0;}</style></head><body>";

echo "<h1>Lost Submarines Database Update</h1>";
echo "<p class='info'>Starting database update...</p>";

try {
    // Step 1: Check if table exists
    echo "<h2>Step 1: Checking if lost_submarines table exists...</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'lost_submarines'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p class='info'>Table exists. Dropping and recreating...</p>";
        $pdo->exec("DROP TABLE lost_submarines");
        echo "<p class='success'>✓ Old table dropped</p>";
    } else {
        echo "<p class='info'>Table does not exist. Creating new...</p>";
    }
    
    // Step 2: Create table
    echo "<h2>Step 2: Creating lost_submarines table...</h2>";
    $createTableSQL = file_get_contents(__DIR__ . '/database/lost_submarines.sql');
    $pdo->exec($createTableSQL);
    echo "<p class='success'>✓ Table created successfully</p>";
    
    // Step 3: Import data
    echo "<h2>Step 3: Importing submarine data...</h2>";
    $importDataSQL = file_get_contents(__DIR__ . '/database/import_lost_submarines.sql');
    $pdo->exec($importDataSQL);
    echo "<p class='success'>✓ Data imported successfully</p>";
    
    // Step 4: Verify
    echo "<h2>Step 4: Verifying import...</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lost_submarines");
    $result = $stmt->fetch();
    echo "<p class='success'>✓ Total submarines in database: {$result['count']}</p>";
    
    $stmt = $pdo->query("SELECT boat_number, name, date_lost FROM lost_submarines ORDER BY date_lost");
    echo "<h3>Imported submarines:</h3><ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>{$row['boat_number']} - {$row['name']} (Lost: {$row['date_lost']})</li>";
    }
    echo "</ul>";
    
    echo "<h2 class='success'>✓ Database update completed successfully!</h2>";
    echo "<p class='info'>IMPORTANT: Delete this file (update-production-db.php) from the server now.</p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'>✗ Error occurred:</h2>";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check that the database files exist in the /database/ directory.</p>";
}

echo "</body></html>";
?>
