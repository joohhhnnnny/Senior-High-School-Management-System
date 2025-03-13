<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conn.php';

function getStudentRecords($userId) {
    global $conn;
    
    $records = array(
        'status' => false,
        'data' => array(),
        'message' => ''
    );

    try {
        error_log("Starting getStudentRecords for user_id: " . $userId);

        // First get the studentID from the user_id
        $userQuery = "SELECT studentID, yearLevel, year_strand 
                     FROM student 
                     WHERE id = ?";
        
        $stmt = $conn->prepare($userQuery);
        if (!$stmt) {
            throw new Exception("User query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $userData = $userResult->fetch_assoc();
        $stmt->close();

        if (!$userData) {
            throw new Exception("Student data not found for user_id: " . $userId);
        }

        $studentID = $userData['studentID'];
        $yearLevel = $userData['yearLevel'];
        $strand = explode('-', $userData['year_strand'])[1];

        error_log("Found studentID: " . $studentID . " for user_id: " . $userId);

        // Get subjects and grades using studentID
        $query = "SELECT DISTINCT
            s.subject_title, 
            s.year_level, 
            s.semester,
            s.subject_type,
            sg.final_grade,
            CASE 
                WHEN sg.final_grade IS NULL THEN 'Incomplete'
                WHEN sg.final_grade >= 75 THEN 'Passed'
                ELSE 'Failed'
            END as remarks
        FROM subjects s
        LEFT JOIN student_grades sg ON 
            s.subject_title = sg.subject_title 
            AND sg.studentID = ?
            AND s.semester = sg.semester
            AND s.year_level = sg.yearLevel
        WHERE s.year_level = ? 
            AND (s.strand = ? OR s.subject_type = 'minor')
        GROUP BY s.subject_title, s.year_level, s.semester, s.subject_type, sg.final_grade
        ORDER BY s.semester, s.subject_title";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Main query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("iss", $studentID, $yearLevel, $strand);
        
        if (!$stmt->execute()) {
            throw new Exception("Main query execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $records['data'][] = [
                    'subject_title' => $row['subject_title'],
                    'yearLevel' => $row['year_level'],
                    'semester' => $row['semester'],
                    'final_grade' => $row['final_grade'] ?? '',
                    'remarks' => $row['remarks']
                ];
            }
            $records['status'] = true;
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Error in getStudentRecords: " . $e->getMessage());
        $records['message'] = $e->getMessage();
    }

    return $records;
}
