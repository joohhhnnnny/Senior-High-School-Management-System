<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting and logging to file
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Debug logging
error_log("Executing fetchStudentProfile.php");
error_log("Current directory: " . __DIR__);
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);
error_log("Script filename: " . $_SERVER['SCRIPT_FILENAME']);

// Updated paths for InfinityFree environment
$possiblePaths = [
    __DIR__ . '/conn.php',
    __DIR__ . '/../../conn.php',
    $_SERVER['DOCUMENT_ROOT'] . '/conn.php',
    $_SERVER['DOCUMENT_ROOT'] . '/FINAL-STUDENT/Student-php/conn.php',
    // Add InfinityFree specific paths
    dirname($_SERVER['DOCUMENT_ROOT']) . '/conn.php',
    dirname(dirname(__FILE__)) . '/conn.php'
];

$connFound = false;
$lastError = '';

foreach ($possiblePaths as $path) {
    error_log("Checking path: " . $path);
    if (file_exists($path)) {
        error_log("Found conn.php at: " . $path);
        try {
            require_once $path;
            if (isset($conn) && $conn instanceof mysqli) {
                $connFound = true;
                error_log("Successfully connected to database");
                break;
            } else {
                error_log("conn.php found but no valid connection established at: " . $path);
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            error_log("Error loading conn.php at {$path}: " . $lastError);
        }
    }
}

if (!$connFound) {
    error_log("Failed to find or connect using conn.php. Last error: " . $lastError);
    die("Database connection failed. Please check error logs for details.");
}

// Debug session variables
error_log("Session data in fetchStudentProfile.php: " . print_r($_SESSION, true));

// Check for both user_id and user_type instead of student_id
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../../FINAL-ADMIN/Portal-Main/main.php");
    exit();
}

// Use user_id instead of student_id
try {
    $student_id = $_SESSION['user_id'];  // Changed from student_id to user_id
    $query = "SELECT * FROM student WHERE id = ?";

    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        throw new Exception("No student found with ID: " . $student_id);
    }

    $stmt->close();
} catch (Exception $e) {
    $error_message = $e->getMessage();
    $student = null;
}

if (isset($conn)) {
    $conn->close();
}