<?php
require_once 'conn.php';

// Prevent any HTML output
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->begin_transaction();
        
        // Set upload directory using the allowed path
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/FINAL-CLIENT/FINAL-Program-images/student/';
        
        // Ensure the directory exists within allowed path
        if (!is_dir($uploadDir)) {
            // Create directory recursively
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Handle birth certificate upload
        $birthcert_filename = '';
        if (isset($_FILES['birthCertificate'])) {
            $birthCert = $_FILES['birthCertificate'];
            $birthcert_filename = 'birthcert_' . time() . '_' . bin2hex(random_bytes(8)) . '.jpg';
            $birthCertPath = $uploadDir . $birthcert_filename;

            if (!move_uploaded_file($birthCert['tmp_name'], $birthCertPath)) {
                throw new Exception('Error uploading birth certificate');
            }
        }

        // Handle Form 138 upload
        $form138_filename = '';
        if (isset($_FILES['form138'])) {
            $form138 = $_FILES['form138'];
            $form138_filename = 'form138_' . time() . '_' . bin2hex(random_bytes(8)) . '.jpg';
            $form138Path = $uploadDir . $form138_filename;

            if (!move_uploaded_file($form138['tmp_name'], $form138Path)) {
                throw new Exception('Error uploading Form 138');
            }
        }

        // Insert into studentapply table
        $sql_student = "INSERT INTO studentapply (fullname, birthdate, gender, address, phoneNumber, 
                       email, yearLevel, strand, birthcert, form138) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_student = $conn->prepare($sql_student);
        
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        
        $stmt_student->bind_param("ssssssssss", 
            $_POST['fullName'],
            $_POST['birthdate'],
            $_POST['gender'],
            $_POST['address'],
            $phone,
            $_POST['email'],
            $_POST['yearLevel'],
            $_POST['strand'],
            $birthcert_filename,
            $form138_filename
        );
        
        if (!$stmt_student->execute()) {
            throw new Exception('Error inserting student data: ' . $stmt_student->error);
        }
        
        $studentID = $conn->insert_id;

        // Insert into parent_guardian table
        $sql_parent = "INSERT INTO parent_guardian (fullname, phoneNumber, studentID) 
                      VALUES (?, ?, ?)";

        $stmt_parent = $conn->prepare($sql_parent);
        $parent_contact = preg_replace('/[^0-9]/', '', $_POST['parentContact']);
        
        $stmt_parent->bind_param("ssi", 
            $_POST['parentfullName'],
            $parent_contact,
            $studentID
        );

        if (!$stmt_parent->execute()) {
            throw new Exception('Error inserting parent data: ' . $stmt_parent->error);
        }

        // Insert into studentpendingenroll table
        $sql_pending = "INSERT INTO studentpendingenroll (studentID, yearLevel, strand, status) 
                VALUES (?, ?, ?, 'pending')";

        $stmt_pending = $conn->prepare($sql_pending);
        $stmt_pending->bind_param("iss", 
            $studentID,
            $_POST['yearLevel'],
            $_POST['strand']
        );

        if (!$stmt_pending->execute()) {
            throw new Exception('Error inserting enrollment data: ' . $stmt_pending->error);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully!']);
        
    } catch (Exception $e) {
        $conn->rollback();
        
        // Clean up any uploaded files if there's an error
        if (isset($birthCertPath) && file_exists($birthCertPath)) {
            unlink($birthCertPath);
        }
        if (isset($form138Path) && file_exists($form138Path)) {
            unlink($form138Path);
        }
        
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt_student)) $stmt_student->close();
        if (isset($stmt_parent)) $stmt_parent->close();
        if (isset($stmt_pending)) $stmt_pending->close();
        $conn->close();
    }
    exit();
}
?>