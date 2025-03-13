<?php
header('Content-Type: application/json');

$result = [
    'opcache_enabled' => false,
    'cache_cleared' => false,
    'message' => ''
];

if (function_exists('opcache_reset')) {
    $result['opcache_enabled'] = true;
    $result['cache_cleared'] = opcache_reset();
    $result['message'] = $result['cache_cleared'] ? 
        'OPCache successfully cleared' : 
        'Failed to clear OPCache';
} else {
    $result['message'] = 'OPCache is not enabled on this server';
}

// Clear PHP file status cache
clearstatcache(true);

echo json_encode($result);
?>
