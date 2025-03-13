<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Enhanced logging with file path check
$logFile = dirname(__FILE__) . '/approve_error.log';
if (!is_writable(dirname($logFile))) {
    error_log("Log directory is not writable");
}
ini_set('log_errors', 1);
ini_set('error_log', $logFile);

// Log the start of the request
error_log("=== New Approval Request " . date('Y-m-d H:i:s') . " ===");

// Verify PHP version and extensions
error_log("PHP Version: " . PHP_VERSION);
error_log("Loaded Extensions: " . implode(', ', get_loaded_extensions()));

// Add debugging information
$vendorDir = realpath(__DIR__ . '/../vendor');
$autoloadFile = $vendorDir . '/autoload.php';

// Check if files exist and are readable
$debug = [
    'vendor_dir' => [
        'path' => $vendorDir,
        'exists' => is_dir($vendorDir),
        'readable' => is_readable($vendorDir)
    ],
    'autoload' => [
        'path' => $autoloadFile,
        'exists' => file_exists($autoloadFile),
        'readable' => is_readable($autoloadFile)
    ]
];

if (!file_exists($autoloadFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'Composer autoload file not found',
        'debug' => $debug
    ]);
    exit;
}

// Capture any output before autoload
ob_start();
require $autoloadFile;
$autoloadOutput = ob_get_clean();

if (!empty($autoloadOutput)) {
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected output during autoload',
        'debug' => [
            'output' => $autoloadOutput,
            'files' => $debug
        ]
    ]);
    exit;
}

require_once '../Portal-Main/conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Add transaction logging
function logTransaction($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $log .= " - Data: " . print_r($data, true);
    }
    error_log($log);
}

// Add this new function for generating random password
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Add this new function for sending email
function sendCredentialsEmail($email, $studentId, $password, $fullname) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'internationalstatecollegeph@gmail.com';
        $mail->Password = 'bxsx evfu okmv myjr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Add SMTP options for development environment
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients - using dynamic student data
        $mail->setFrom('internationalstatecollegeph@gmail.com', 'ISCP Admin');
        $mail->addAddress($email, $fullname);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to ISCP - Your Account Credentials';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #0066cc;'>Welcome to ISCP!</h2>
                    <p>Dear {$fullname},</p>
                    <p>Your enrollment has been approved. Here are your login credentials:</p>
                    
                    <div style='background-color: #ffffff; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0066cc;'>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Password:</strong> {$password}</p>
                    </div>
                    
                    <p style='color: #dc3545;'><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
                    
                    <p>Best regards,<br>ISCP Administration</p>
                </div>
            </body>
            </html>
        ";

        // Direct send and return approach matching test_email.php
        if ($mail->send()) {
            error_log("Email sent successfully to: $email");
            return true;
        } else {
            error_log("Email sending failed to: $email - " . $mail->ErrorInfo);
            return false;
        }

    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        throw new Exception("Failed to send credentials email: " . $mail->ErrorInfo);
    }
}

try {
    if (ob_get_length()) ob_clean();
    
    // Log raw input
    $raw_input = file_get_contents('php://input');
    logTransaction("Raw input received", $raw_input);
    
    $data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON decode error: ' . json_last_error_msg());
    }
    
    if (!isset($data['id'])) {
        throw new Exception('No ID provided');
    }

    $id = intval($data['id']);
    logTransaction("Processing approval for ID", $id);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get student data from both tables
        $sql = "SELECT spe.*, sa.fullname, sa.email, sa.birthdate, sa.gender, 
                       sa.address, sa.phoneNumber, sa.birthcert, sa.form138
                FROM studentpendingenroll spe 
                JOIN studentapply sa ON spe.studentID = sa.id 
                WHERE spe.studentID = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $data['id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Student application not found for ID: " . $data['id']);
        }
        
        $student = $result->fetch_assoc();
        $stmt->close();

        // Log student data for debugging
        error_log("Found student data: " . print_r($student, true));

        // Check if student is already approved
        if ($student['status'] === 'approved') {
            throw new Exception("Student application is already approved");
        }

        // Update status to approved
        $update_sql = "UPDATE studentpendingenroll SET status = 'approved' WHERE studentID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $data['id']);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update status: " . $update_stmt->error);
        }
        $update_stmt->close();

        // Create student account
        $plainPassword = generateRandomPassword();
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        $year_strand = $student['yearLevel'] . '-' . strtoupper($student['strand']);
        
        // Check if student already exists using id
        $check_sql = "SELECT id FROM student WHERE id = ? OR studentID = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", 
            $student['id'],
            $student['id']
        );
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Student already exists in the system");
        }
        $check_stmt->close();
        
        // Insert into student table with correct column names
        $insert_sql = "INSERT INTO student (studentID, fullname, yearLevel, year_strand, email, password, address, phoneNumber) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssssss",
            $student['id'],
            $student['fullname'],
            $student['yearLevel'],
            $year_strand,
            $student['email'],
            $hashedPassword,
            $student['address'],
            $student['phoneNumber']
        );
        
        // Insert into student table and get the new ID
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create student account: " . $insert_stmt->error);
        }
        $new_student_id = $conn->insert_id;
        $insert_stmt->close();

        // Get a valid section and its schedule if it exists
        if ($student['strand'] === 'HUMSS') {
            // For HUMSS students, get section with schedule
            $section_query = "SELECT s.id as section_id, MIN(sch.id) as schedule_id
                            FROM sections s
                            LEFT JOIN schedules sch ON s.id = sch.section_id
                            WHERE s.yearLevel = ? 
                            AND s.strand = ?
                            GROUP BY s.id
                            LIMIT 1";
        } else {
            // For other strands (ICT, AAD), get section only
            $section_query = "SELECT id as section_id
                            FROM sections 
                            WHERE yearLevel = ? 
                            AND strand = ? 
                            LIMIT 1";
        }
                       
        $section_stmt = $conn->prepare($section_query);
        $section_stmt->bind_param("ss", 
            $student['yearLevel'],
            $student['strand']
        );
        
        if ($section_stmt->execute()) {
            $section_result = $section_stmt->get_result();
            if ($section_result->num_rows > 0) {
                $section_data = $section_result->fetch_assoc();
                
                // Always insert with schedule_id as optional
                $insert_section_sql = "INSERT INTO student_sections 
                                     (student_id, section_id, schedule_id) 
                                     VALUES (?, ?, ?)";
                                     
                $insert_section_stmt = $conn->prepare($insert_section_sql);
                $schedule_id = ($student['strand'] === 'HUMSS' && isset($section_data['schedule_id'])) 
                    ? $section_data['schedule_id'] 
                    : null;
                
                $insert_section_stmt->bind_param("iii", 
                    $new_student_id,
                    $section_data['section_id'],
                    $schedule_id
                );
                
                if (!$insert_section_stmt->execute()) {
                    error_log("Warning: Could not assign section to student: " . $insert_section_stmt->error . 
                             " New Student ID: " . $new_student_id . 
                             " Section ID: " . $section_data['section_id'] . 
                             " Schedule ID: " . ($schedule_id ?? 'NULL'));
                }
                $insert_section_stmt->close();
            }
        }
        $section_stmt->close();

        // Send email
        if (!sendCredentialsEmail($student['email'], $student['id'], $plainPassword, $student['fullname'])) {
            throw new Exception("Failed to send credentials email");
        }

        // Add transaction logging before each major database operation
        logTransaction("Starting database operations");
        
        // Before committing transaction
        logTransaction("Committing transaction");
        $conn->commit();
        
        // Send success response with more details
        echo json_encode([
            'success' => true,
            'message' => 'Application approved successfully',
            'studentId' => $student['studentID'],
            'details' => [
                'name' => $student['fullname'],
                'yearLevel' => $student['yearLevel'],
                'strand' => $student['strand']
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Approval Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => [
                'requestId' => $data['id'] ?? 'not provided',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
    
} catch (Exception $e) {
    logTransaction("Error occurred", $e->getMessage());
    
    if ($conn->connect_errno) {
        logTransaction("Database connection error", $conn->connect_error);
    }
    
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
    ob_end_flush();
}
?>