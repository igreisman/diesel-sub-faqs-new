<?php
require 'config/database.php';
echo 'Host: ', DB_HOST, '<br>';
echo 'DB: ', DB_NAME, '<br>';
$r = $pdo->query("SELECT DATABASE() db, COUNT(*) c FROM glossary");
print_r($r->fetch());
