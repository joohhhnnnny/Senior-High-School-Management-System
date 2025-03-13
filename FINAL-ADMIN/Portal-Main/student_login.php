<?php
session_start();
include 'conn.php';

header('Content-Type: application/json');
error_log("Student login attempt - Starting session: " . session_id());

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Please provide both email and password';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            if (password_verify($password, $student['password'])) {
                // Clear any existing session data
                $_SESSION = array();
                
                // Set session variables
                $_SESSION['user_id'] = $student['id'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['fullname'] = $student['fullname'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['yearLevel'] = $student['yearLevel'];
                $_SESSION['strand'] = $student['year_strand'];

                // Debug session
                error_log("Session variables set: " . print_r($_SESSION, true));

                session_write_close(); // Ensure session is written

                $response = [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'redirect' => '/CST5-PROJECT/FINAL-STUDENT/Student-html/profStud.php'  // Simplified URL
                ];
                
                echo json_encode($response);
                exit();
            } else {
                $response['message'] = 'Invalid password';
            }
        } else {
            $response['message'] = 'Email not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error occurred';
        error_log("Database error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
