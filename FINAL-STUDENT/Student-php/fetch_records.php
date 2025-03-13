<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'getStudentRecords.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode(getStudentRecords($_SESSION['user_id']));
} else {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'No user ID in session'
    ]);
}