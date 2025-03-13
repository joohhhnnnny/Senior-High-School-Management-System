<?php
header('Content-Type: application/json');
session_start();
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

try {
    require_once 'conn.php';
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('No user ID in session');
    }

    $id = intval($_SESSION['user_id']);
    
    // Get student info if not in session - note we search by id, not studentID
    if (!isset($_SESSION['student_info'])) {
        $stmt = $conn->prepare("SELECT * FROM student WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Student not found');
        }
        $_SESSION['student_info'] = $result->fetch_assoc();
    }

    $studentInfo = $_SESSION['student_info'];
    $studentID = $studentInfo['studentID']; // Use this for attendance queries
    $yearLevel = $studentInfo['yearLevel'];
    $strand = explode('-', $studentInfo['year_strand'])[1] ?? '';
    
    // Fix semester determination (June-October: first sem, November-March: second sem)
    $currentMonth = date('n');
    $currentSemester = ($currentMonth >= 6 && $currentMonth <= 10) ? 'first' : 'second';
    
    error_log("Current month: $currentMonth, determined semester: $currentSemester");
    
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $subject = isset($_GET['subject']) ? trim($_GET['subject']) : 'all';

    error_log("Processing request for student: " . $studentID . ", yearLevel: " . $yearLevel . ", strand: " . $strand);

    // Get subjects dynamically based on student's year level and strand
    $subjectsQuery = "
        SELECT DISTINCT s.subject_title, s.subject_type 
        FROM subjects s
        WHERE s.year_level = ? 
        AND s.strand = ?
        AND s.semester = 'first'  /* Force first semester for now */
        AND (s.subject_type = 'major' OR s.subject_type = 'minor')
        ORDER BY 
            CASE 
                WHEN s.subject_type = 'major' THEN 1 
                WHEN s.subject_type = 'minor' THEN 2 
                ELSE 3 
            END,
            s.subject_title ASC
    ";

    $stmt = $conn->prepare($subjectsQuery);
    if (!$stmt) {
        throw new Exception("Subject query prep failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $yearLevel, $strand);
    $stmt->execute();
    $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    error_log("Found " . count($subjects) . " subjects for student. YearLevel: $yearLevel, Strand: $strand, Semester: $currentSemester");

    // Modified attendance query to use student's info and match exact subject title
    $attendanceQuery = "
        SELECT 
            DATE(a.date) as date,
            a.status,
            a.subject_title,
            COALESCE(a.remarks, '') as remarks
        FROM student_attendance a
        WHERE a.studentID = ? 
        AND MONTH(a.date) = ? 
        AND YEAR(a.date) = ?
    ";

    $params = [$studentID, $month, $year];
    $types = "iii";
    
    if ($subject !== 'all') {
        $attendanceQuery .= " AND a.subject_title = ?";
        $params[] = $subject;
        $types .= "s";
    }
    
    $attendanceQuery .= " ORDER BY a.date ASC, TIME(a.date) ASC";
    
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Format dates for JSON
    foreach ($attendance as &$record) {
        $record['date'] = date('Y-m-d', strtotime($record['date']));
    }

    echo json_encode([
        'success' => true,
        'data' => $attendance,
        'subjects' => $subjects,
        'debug' => [
            'month' => $month,
            'year' => $year,
            'subject' => $subject,
            'student_id' => $studentID
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_attendance.php: " . $e->getMessage());
    error_log("Session data: " . print_r($_SESSION, true));
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch attendance data',
        'message' => $e->getMessage(),
        'debug' => [
            'session' => [
                'user_id' => $_SESSION['user_id'] ?? null,
                'has_student_info' => isset($_SESSION['student_info']),
                'student_info' => $_SESSION['student_info'] ?? null
            ],
            'sql_error' => $conn->error ?? null,
            'last_query' => $subjectsQuery ?? null
        ]
    ]);
}