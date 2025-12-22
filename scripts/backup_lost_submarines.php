<?php
require_once __DIR__ . '/../config/database.php';

$timestamp = date('Ymd_His');
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$filename = $backupDir . "/lost_submarines_backup_{$timestamp}.sql";

try {
    $stmt = $pdo->query("SELECT * FROM lost_submarines");
    $rows = $stmt->fetchAll();

    $out = "-- Backup of lost_submarines table generated on {$timestamp}\n";
    $out .= "-- Rows: " . count($rows) . "\n\n";

    foreach ($rows as $r) {
        // Prepare values, escape single quotes
        $vals = [];
        $cols = ['boat_number','name','designation','class_info','last_captain','date_lost','location','fatalities','cause','loss_narrative','prior_history','era','year_lost','photo_url'];
        foreach ($cols as $c) {
            $v = $r[$c] ?? null;
            if ($v === null) {
                $vals[] = 'NULL';
            } else {
                $escaped = str_replace("'", "\\'", $v);
                $vals[] = "'" . $escaped . "'";
            }
        }
        $out .= "INSERT INTO lost_submarines (" . implode(",", $cols) . ") VALUES (" . implode(",", $vals) . ");\n";
    }

    file_put_contents($filename, $out);
    echo "Backup written to: $filename\n";
} catch (Exception $e) {
    echo "Backup failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>
