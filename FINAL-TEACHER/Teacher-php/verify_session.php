<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Session Data:\n";
print_r($_SESSION);

echo "\nTesting Database Connection:\n";
try {
    require_once 'conn.php';
    echo "Database connected successfully\n";
    
    if (isset($_SESSION['professor_id'])) {
        $professor_id = $_SESSION['professor_id'];
        echo "\nTesting Query for Professor ID: $professor_id\n";
        
        $query = "SELECT COUNT(*) as total FROM schedules WHERE professor_id = $professor_id";
        $result = $conn->query($query);
        
        if ($result === false) {
            echo "Query failed: " . $conn->error;
        } else {
            $row = $result->fetch_assoc();
            echo "Total subjects found: " . $row['total'];
        }
    } else {
        echo "No professor_id found in session";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
?>
