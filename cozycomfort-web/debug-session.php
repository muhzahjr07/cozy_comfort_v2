<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'session_status' => session_status(),
    'headers_sent' => headers_sent()
], JSON_PRETTY_PRINT);
?>

