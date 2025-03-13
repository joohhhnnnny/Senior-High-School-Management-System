<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

echo "<h1>Connection Test</h1>";
echo "<pre>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n\n";

$conn_path = __DIR__ . '/conn.php';
echo "Looking for conn.php at: " . $conn_path . "\n";

if (file_exists($conn_path)) {
    echo "conn.php found!\n";
    try {
        // Get contents of conn.php (removing sensitive info)
        $conn_contents = file_get_contents($conn_path);
        echo "conn.php contents (credentials hidden):\n";
        echo preg_replace('/(["]\K[^"]+(?=["]))/','****', $conn_contents) . "\n\n";
        
        require_once $conn_path;
        if (isset($conn)) {
            if ($conn instanceof mysqli) {
                echo "Database connection successful!\n";
                echo "Connected to: " . $conn->host_info . "\n";
                echo "Connection details:\n";
                echo "Server version: " . $conn->server_info . "\n";
                echo "Character set: " . $conn->character_set_name() . "\n";
            } else {
                echo "Error: \$conn is not a mysqli instance\n";
                echo "Type of \$conn: " . gettype($conn) . "\n";
            }
        } else {
            echo "Error: \$conn variable not set after including conn.php\n";
        }
    } catch (Throwable $e) {
        echo "Connection Error Details:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
} else {
    echo "conn.php not found!\n";
    echo "Make sure conn.php exists in: " . dirname($conn_path) . "\n";
}
echo "</pre>";