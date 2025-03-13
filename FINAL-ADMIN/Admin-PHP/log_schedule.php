<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the timezone
date_default_timezone_set('Asia/Manila');

// Define log file path
$logFile = __DIR__ . '/add_schedulemodal.log';

try {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    
    // Log the raw data first
    error_log("[Raw Input] " . $rawData . "\n", 3, $logFile);
    
    // Parse the JSON data
    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    // Format the log entry
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    // Append to log file
    if (file_put_contents($logFile, $logEntry, FILE_APPEND) === false) {
        throw new Exception('Failed to write to log file');
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Log entry created successfully'
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("[ERROR] " . $e->getMessage() . "\n", 3, $logFile);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
