<?php

// Simple email sign-up backend for coming-soon.html
// Save emails to a file (emails.txt) in the same directory
header('Content-Type: application/json');

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $file = __DIR__.'/emails.txt';
        $entry = $email."\t".date('c')."\n";
        file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Thank you! We will notify you when we launch.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    }

    exit;
}
// If not POST, show nothing
http_response_code(405);

exit;
