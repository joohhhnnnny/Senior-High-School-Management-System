<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing Database Connection:\n\n";

try {
    require_once 'conn.php';
    
    if (!isset($conn)) {
        throw new Exception("Connection variable not set");
    }
    
    if (!($conn instanceof mysqli)) {
        throw new Exception("Connection is not a valid MySQL connection");
    }
    
    if (!$conn->ping()) {
        throw new Exception("Database connection failed ping test");
    }
    
    echo "Connection successful!\n\n";
    
    // Test a simple query
    $result = $conn->query("SHOW TABLES");
    echo "Available tables:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    // Show database name
    echo "\nCurrent database: " . $conn->database . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

if (isset($conn)) {
    $conn->close();
    echo "\nConnection closed.";
}
echo "</pre>";
?>
