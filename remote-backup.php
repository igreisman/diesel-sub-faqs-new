<?php

/**
 * Remote Backup Generator
 * Upload this file to your production server and access via browser or curl
 * It will generate a database backup and save it to backups/ directory.
 */

// Security: Require a secret key
$SECRET_KEY = 'hilds-78kdI3-ur73kldf-92jdls'; // Change this to a strong secret key
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);

    exit('Access denied');
}

require_once 'config/database.php';

// Create backups directory if it doesn't exist
$backup_dir = __DIR__.'/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$timestamp = date('Ymd_His');
$backup_file = $backup_dir.'/prod_backup_'.$timestamp.'.sql';

// Get all tables
$tables = [];
$result = $pdo->query('SHOW TABLES');
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

// Start backup
$output = "-- Database Backup\n";
$output .= '-- Generated: '.date('Y-m-d H:i:s')."\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    // Get CREATE TABLE statement
    $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
    $output .= "-- Table: {$table}\n";
    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $output .= $create['Create Table'].";\n\n";

    // Get table data
    $rows = $pdo->query("SELECT * FROM `{$table}`");
    if ($rows->rowCount() > 0) {
        $output .= "-- Data for table: {$table}\n";
        $output .= "LOCK TABLES `{$table}` WRITE;\n";

        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(function ($val) use ($pdo) {
                return null === $val ? 'NULL' : $pdo->quote($val);
            }, array_values($row));

            $output .= "INSERT INTO `{$table}` VALUES (".implode(', ', $values).");\n";
        }

        $output .= "UNLOCK TABLES;\n\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

// Save to file
file_put_contents($backup_file, $output);

// Output result
header('Content-Type: text/plain');
echo "✅ Backup created successfully!\n";
echo 'File: '.basename($backup_file)."\n";
echo 'Size: '.number_format(filesize($backup_file))." bytes\n";
echo "\nDownload: ".basename($backup_file)."\n";
