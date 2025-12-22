<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: text/plain; charset=utf-8');
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM lost_submarines");
    $cnt = $stmt->fetch()['cnt'] ?? '0';
    echo "lost_submarines COUNT: " . $cnt . "\n";

    $q = $pdo->prepare("SELECT id, boat_number, name, designation, date_lost, era FROM lost_submarines WHERE designation = ? OR boat_number = ? LIMIT 1");
    $q->execute(['USS F-4 (SS-23)', 'SS-23']);
    $row = $q->fetch();
    if ($row) {
        echo "FOUND: " . json_encode($row) . "\n";
    } else {
        echo "F-4 not found in this DB connection.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>