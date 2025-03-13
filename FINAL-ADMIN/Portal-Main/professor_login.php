<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

try {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    $stmt = $conn->prepare("SELECT * FROM professor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $professor = $result->fetch_assoc();
        if (password_verify($password, $professor['password'])) {
            // Set all required session variables
            $_SESSION['teacher_logged_in'] = true;
            $_SESSION['professor_id'] = $professor['id'];
            $_SESSION['professor_name'] = $professor['fullname'];
            $_SESSION['professor_email'] = $professor['email'];
            $_SESSION['user_type'] = 'teacher';

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'redirect' => '/CST5-PROJECT/FINAL-TEACHER/Teacher-html/teacher_dashboard.php'
            ]);
            exit;
        }
    }

    throw new Exception('Invalid email or password');

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
