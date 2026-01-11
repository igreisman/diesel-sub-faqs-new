<?php

if (PHP_SESSION_NONE === session_status()) {
    session_start();
}
session_destroy();
header('Location: /category.php');

exit;
