<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent any output
ob_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "iscp";

try {
    // Log connection attempt
    error_log("Attempting database connection to {$servername} with user {$username}");
    
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    if (!$conn->set_charset("utf8")) {
        error_log("Error loading character set utf8: " . $conn->error);
    }

    error_log("Database connection successful");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Clean any potential output from connection
ob_clean();
?>