<?php
header('Content-Type: application/json');

$status = [
    'timestamp' => time(),
    'included_files' => get_included_files(),
    'opcache_status' => function_exists('opcache_get_status') ? opcache_get_status(false) : 'OPCache not available'
];

echo json_encode($status, JSON_PRETTY_PRINT);
?>
