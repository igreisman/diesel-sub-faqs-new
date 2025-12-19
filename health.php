<?php
// Simple health check endpoint
require_once 'config/database.php';

header('Content-Type: text/plain');

echo "PHP is working!";
echo "\nPHP Version: " . phpversion();
echo "\nServer: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as faq_count FROM faqs");
    $count = $stmt->fetchColumn();
    echo "\n✅ Database connection: SUCCESS";
    echo "\n📊 FAQs in database: " . $count;
} catch (Exception $e) {
    echo "\n❌ Database connection: " . $e->getMessage();
}

echo "\n\n🚀 Submarine FAQ App Status: READY";
?>