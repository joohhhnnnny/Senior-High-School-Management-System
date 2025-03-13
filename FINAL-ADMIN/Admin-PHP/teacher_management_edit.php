<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'conn.php';
    
    // For GET requests (fetching teacher details)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['id'])) {
            throw new Exception('Teacher ID is required');
        }

        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $query = "SELECT * FROM professor WHERE id = '$id'";
        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            throw new Exception('Teacher not found');
        }

        $teacher = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'data' => $teacher
        ]);
        exit;
    }

    // For POST requests (updating teacher details)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            throw new Exception('Teacher ID is required');
        }

        $id = mysqli_real_escape_string($conn, $data['id']);
        $fullname = mysqli_real_escape_string($conn, $data['fullname']);
        $email = mysqli_real_escape_string($conn, $data['email']);
        $phoneNumber = isset($data['phoneNumber']) ? mysqli_real_escape_string($conn, $data['phoneNumber']) : null;

        $query = "UPDATE professor SET 
            fullname = '$fullname',
            email = '$email',
            phoneNumber = " . ($phoneNumber ? "'$phoneNumber'" : "NULL") . "
            WHERE id = '$id'";

        if (!mysqli_query($conn, $query)) {
            throw new Exception(mysqli_error($conn));
        }

        // Always return success even if no rows were affected
        // (might mean no changes were needed)
        echo json_encode([
            'success' => true,
            'message' => 'Teacher updated successfully'
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>