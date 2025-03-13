<?php
session_start();
include '../../config/connection.php';

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM professor WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Verify data exists before setting session variables
        $_SESSION['teacher_logged_in'] = true;
        $_SESSION['professor_id'] = $row['professorID'] ?? '';
        $_SESSION['professor_name'] = $row['fullname'] ?? '';
        $_SESSION['professor_email'] = $row['email'] ?? '';
        $_SESSION['phone_number'] = !empty($row['phoneNumber']) ? $row['phoneNumber'] : 'Not provided';
        
        // Debug log
        error_log("Professor data: " . print_r($row, true));
        error_log("Session data: " . print_r($_SESSION, true));
        
        header("Location: ../Teacher-html/teacher_dashboard.php");
        exit();
    }
}

// If login fails
header("Location: ../../FINAL-ADMIN/Portal-Main/main.php?error=1");
exit();
?>
