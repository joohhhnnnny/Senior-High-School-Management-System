<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Test connection script running");

try {
    require_once '../Portal-Main/conn.php';
    
    // Add more detailed diagnostics
    $diagnostics = [
        'success' => true,
        'message' => 'Connection test successful',
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'],
        'database' => [
            'connected' => ($conn instanceof mysqli),
            'host_info' => $conn->host_info ?? 'Not available',
            'client_info' => $conn->client_info ?? 'Not available'
        ],
        'server_info' => [
            'document_root' => $_SERVER['DOCUMENT_ROOT'],
            'script_path' => $_SERVER['SCRIPT_FILENAME'],
            'host' => $_SERVER['HTTP_HOST']
        ]
    ];
    
    echo json_encode($diagnostics, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Test connection error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
