<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../Portal-Main/conn.php';

echo "Testing enrollment data fetch...\n\n";

try {
    // Test connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Database connection successful!\n";

    // Test query
    $sql = "SELECT * FROM studentpendingenroll LIMIT 5";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    echo "\nQuery successful! Found " . $result->num_rows . " records:\n";
    while ($row = $result->fetch_assoc()) {
        echo "\n--- Record ---\n";
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
