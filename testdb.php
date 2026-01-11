<?php

require 'config/database.php';
echo DB_HOST, ' ', DB_NAME, ' ', DB_USERNAME, "\n";
$stmt = $pdo->query('SHOW DATABASES');
print_r($stmt->fetchAll());
