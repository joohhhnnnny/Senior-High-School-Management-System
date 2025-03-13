<?php
// Start output buffering immediately
ob_start();

// Prevent any HTML or error output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Function to send JSON response and exit
function sendJsonResponse($status, $message, $debug = null) {
    // Clear any buffered output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($debug !== null && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $response['debug'] = $debug;
    }
    
    echo json_encode($response);
    exit;
}

try {
    // Require database connection
    require_once 'conn.php';
    
    // Verify POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate inputs
    if (empty($_POST['fullName']) || empty($_POST['phone']) || empty($_POST['email']) || empty($_FILES['resume'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize inputs
    $fullName = filter_var($_POST['fullName'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM professorapply WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception('Email already exists in our system');
    }

    // Handle file upload
    $resume = $_FILES['resume'];
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    if (!in_array($resume['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only PDF and Word documents are allowed');
    }

    // Check file size (limit to 10MB for safety)
    $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
    if ($_FILES['resume']['size'] > $maxFileSize) {
        throw new Exception('File size exceeds limit of 10MB');
    }

    // Validate file type with more detailed checking
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['resume']['tmp_name']);
    finfo_close($finfo);

    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    if (!in_array($mimeType, $allowedMimeTypes)) {
        throw new Exception('Invalid file type. Detected: ' . $mimeType);
    }

    // Update upload directory path for InfinityFree
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/FINAL-CLIENT/FINAL-Program-images/professor/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!is_writable($uploadDir)) {
        throw new Exception('Upload directory is not writable. Path: ' . $uploadDir);
    }

    // Validate file upload
    if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $_FILES['resume']['error']);
    }

    // Generate unique filename
    $fileInfo = pathinfo($_FILES['resume']['name']);
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9]/", "_", $fileInfo['filename']) 
              . '.' . $fileInfo['extension'];
    $uploadPath = $uploadDir . $fileName;

    // Add more detailed file upload logging
    if (!move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)) {
        $uploadError = error_get_last();
        error_log("Upload failed. Error: " . print_r($uploadError, true));
        error_log("Target path: " . $uploadPath);
        error_log("File permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
        throw new Exception('Failed to save file: ' . ($uploadError['message'] ?? 'Unknown error'));
    }

    // Begin transaction
    $conn->begin_transaction();

    // Insert into professorapply table
    $stmt = $conn->prepare("INSERT INTO professorapply (fullname, phoneNumber, email, resume) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $phone, $email, $fileName);
    
    if (!$stmt->execute()) {
        // Delete uploaded file if database insertion fails
        if(file_exists($uploadPath)) unlink($uploadPath);
        throw new Exception('Failed to insert application data');
    }
    
    $professorID = $conn->insert_id;

    // Insert into professorpendingapply table
    $currentDate = date('Y-m-d');
    $status = 'pending';
    $stmt = $conn->prepare("INSERT INTO professorpendingapply (professorID, email, status, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $professorID, $email, $status, $currentDate);
    
    if (!$stmt->execute()) {
        // Delete uploaded file if pending insertion fails
        if(file_exists($uploadPath)) unlink($uploadPath);
        throw new Exception('Failed to insert pending application data');
    }

    // Commit transaction
    $conn->commit();

    sendJsonResponse('success', 'Your application has been submitted successfully!');

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    error_log("Application Error: " . $e->getMessage());
    
    // Clean up uploaded file if exists
    if (isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    sendJsonResponse('error', $e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>