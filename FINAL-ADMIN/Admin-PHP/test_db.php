<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: text/plain');

echo "Testing database connection...\n\n";

try {
    if (!$conn->ping()) {
        throw new Exception("Database connection lost");
    }
    echo "Database connection successful\n";

    $sql = "SELECT COUNT(*) as count FROM student";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $count = $result->fetch_assoc()['count'];
    echo "Found {$count} students in database\n";

    if ($count > 0) {
        $sample = $conn->query("SELECT * FROM student LIMIT 1");
        echo "\nSample record:\n";
        print_r($sample->fetch_assoc());
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "MySQL Error: " . $conn->error . "\n";
}
