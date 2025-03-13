<?php
// Enable detailed error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

// Start clean output buffer
ob_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');

require_once '../Portal-Main/conn.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Add email sending function
function sendProfessorCredentials($email, $professorId, $password, $fullname) {
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

        // SMTP options for development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('internationalstatecollegeph@gmail.com', 'ISCP Admin');
        $mail->addAddress($email, $fullname);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to ISCP - Your Professor Account Credentials';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #0066cc;'>Welcome to ISCP!</h2>
                    <p>Dear {$fullname},</p>
                    <p>Your professor application has been approved. Here are your login credentials:</p>
                    
                    <div style='background-color: #ffffff; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0066cc;'>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Password:</strong> {$password}</p>
                    </div>
                    
                    <p style='color: #dc3545;'><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
                    
                    <p>Best regards,<br>ISCP Administration</p>
                </div>
            </body>
            </html>";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

try {
    // Log incoming request
    error_log("\n=== New Approval Request ===");
    error_log("Time: " . date('Y-m-d H:i:s'));
    error_log("Raw input: " . file_get_contents('php://input'));

    // Parse input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id'])) {
        throw new Exception('Invalid or missing ID in request');
    }

    $id = intval($input['id']);
    error_log("Processing ID: $id");

    // Connect to database
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Begin transaction
    $conn->begin_transaction();

    // First verify the application exists and can be approved
    $checkSql = "SELECT 
        pa.id AS professorID,
        pa.fullname,
        pa.email,
        pa.phoneNumber,
        ppa.id AS pending_id,
        ppa.status
    FROM professorapply pa 
    INNER JOIN professorpendingapply ppa ON pa.id = ppa.professorID 
    WHERE ppa.id = ? 
    AND ppa.status IN ('pending', '')
    FOR UPDATE";

    $stmt = $conn->prepare($checkSql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Query failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Application not found or already processed");
    }

    $professor = $result->fetch_assoc();
    error_log("Processing application for: " . json_encode($professor));

    // Update status first
    $updateSql = "UPDATE professorpendingapply SET status = 'approved' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt || !$updateStmt->bind_param("i", $id) || !$updateStmt->execute()) {
        throw new Exception("Failed to update status: " . ($updateStmt ? $updateStmt->error : $conn->error));
    }

    if ($updateStmt->affected_rows === 0) {
        throw new Exception("No rows were updated");
    }

    error_log("Status updated successfully");

    // Generate credentials and create professor account
    $plainPassword = bin2hex(random_bytes(5));
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Insert professor account
    $insertSql = "INSERT INTO professor (professorID, fullname, email, password, phoneNumber, address) VALUES (?, ?, ?, ?, ?, '')";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception("Insert prepare failed: " . $conn->error);
    }

    $insertStmt->bind_param("sssss",
        $professor['professorID'],
        $professor['fullname'],
        $professor['email'],
        $hashedPassword,
        $professor['phoneNumber']
    );

    if (!$insertStmt->execute()) {
        throw new Exception("Failed to create account: " . $insertStmt->error);
    }

    // After successful account creation and before commit, send email
    if (!sendProfessorCredentials($professor['email'], $professor['id'], $plainPassword, $professor['fullname'])) {
        error_log("Failed to send credentials email to: " . $professor['email']);
        // Optionally throw exception if email is critical
        // throw new Exception("Failed to send credentials email");
    } else {
        error_log("Credentials email sent successfully to: " . $professor['email']);
    }

    // Verify final status
    $verifySql = "SELECT status FROM professorpendingapply WHERE id = ?";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->bind_param("i", $id);
    $verifyStmt->execute();
    $finalStatus = $verifyStmt->get_result()->fetch_assoc();

    if ($finalStatus['status'] !== 'approved') {
        throw new Exception("Status verification failed");
    }

    // Commit transaction
    $conn->commit();
    error_log("Transaction committed successfully");

    // Send success response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Professor approved successfully and credentials sent',
        'data' => [
            'id' => $id,
            'status' => 'approved',
            'email' => $professor['email']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    
    if (isset($conn)) {
        $conn->rollback();
    }

    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($insertStmt)) $insertStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($verifyStmt)) $verifyStmt->close();
    if (isset($conn)) $conn->close();
}
?>
