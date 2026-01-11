<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    $action = $_GET['action'] ?? 'categories';

    switch ($action) {
        case 'categories':
            // Get all categories
            $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categories);

            break;

        case 'faqs':
            $category_id = $_GET['category_id'] ?? null;

            if ($category_id) {
                // Get FAQs for specific category
                $stmt = $pdo->prepare('
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    WHERE f.category_id = ? 
                    ORDER BY f.question
                ');
                $stmt->execute([$category_id]);
            } else {
                // Get all FAQs
                $stmt = $pdo->query('
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    ORDER BY c.name, f.question
                ');
            }

            $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($faqs);

            break;

        case 'search':
            $query = $_GET['q'] ?? '';
            if ($query) {
                $stmt = $pdo->prepare('
                    SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    JOIN categories c ON f.category_id = c.id 
                    WHERE (f.question LIKE ? OR f.answer LIKE ?)
                    AND f.category_id IS NOT NULL
                    AND c.name IS NOT NULL
                    ORDER BY f.question
                ');
                $stmt->execute(["%{$query}%", "%{$query}%"]);
                $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($faqs);
            } else {
                echo json_encode([]);
            }

            break;

        case 'stats':
            // Get site statistics
            $faqCount = $pdo->query('SELECT COUNT(*) FROM faqs')->fetchColumn();
            $categoryCount = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();

            echo json_encode([
                'total_faqs' => $faqCount,
                'total_categories' => $categoryCount,
                'status' => 'online',
            ]);

            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: '.$e->getMessage()]);
}
