<?php

header('Content-Type: application/json');

require_once '../config/database.php';

if ('GET' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);

    exit;
}

try {
    $categories = $pdo->query('
        SELECT id, name, slug, description, sort_order
        FROM categories
        ORDER BY sort_order ASC, name ASC
    ')->fetchAll();

    $faqsStmt = $pdo->prepare("
        SELECT id, category_id, title, slug, question, answer, tags, updated_at
        FROM faqs
        WHERE status = 'published'
        ORDER BY display_order ASC, title ASC
    ");
    $faqsStmt->execute();
    $faqs = $faqsStmt->fetchAll();

    // Map categories for quick lookup and grouping
    $grouped = [];
    foreach ($categories as $cat) {
        $grouped[$cat['id']] = [
            'id' => (int) $cat['id'],
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'description' => $cat['description'],
            'sort_order' => (int) $cat['sort_order'],
            'faqs' => [],
        ];
    }

    $uncategorized = [];
    foreach ($faqs as $faq) {
        $entry = [
            'id' => (int) $faq['id'],
            'title' => $faq['title'],
            'slug' => $faq['slug'],
            'question' => $faq['question'],
            'answer' => $faq['answer'],
            'tags' => $faq['tags'],
            'updated_at' => $faq['updated_at'],
        ];

        if (isset($grouped[$faq['category_id']])) {
            $grouped[$faq['category_id']]['faqs'][] = $entry;
        } else {
            $uncategorized[] = $entry;
        }
    }

    $responseCategories = array_values($grouped);
    if (!empty($uncategorized)) {
        $responseCategories[] = [
            'id' => 0,
            'name' => 'Uncategorized',
            'slug' => 'uncategorized',
            'description' => 'FAQs without a category',
            'sort_order' => 9999,
            'faqs' => $uncategorized,
        ];
    }

    echo json_encode([
        'success' => true,
        'generated_at' => date(DATE_ATOM),
        'categories' => $responseCategories,
        'faq_count' => count($faqs),
    ]);
} catch (Exception $e) {
    error_log('FAQ simple feed error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load FAQs right now',
    ]);
}
