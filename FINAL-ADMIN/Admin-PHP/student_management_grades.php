<?php
require_once '../Portal-Main/conn.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Student ID not provided');
    }

    $studentId = intval($_GET['id']);

    // First, get student's year level from student table
    $stmt = $conn->prepare("SELECT yearLevel FROM student WHERE id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Student not found');
    }

    $student = $result->fetch_assoc();
    $yearLevel = $student['yearLevel'];

    // Determine which tables to query based on year level
    $firstSemTable = "g{$yearLevel}_firstsem_grades";
    $secondSemTable = "g{$yearLevel}_secondsem_grades";

    // Get first semester grades
    $firstSemGrades = [];
    $stmt = $conn->prepare("SELECT subject_title, midterm_grade, finals_grade, final_rating, remarks 
                           FROM $firstSemTable 
                           WHERE studentID = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['semester'] = '1st Semester';
        $firstSemGrades[] = $row;
    }

    // Get second semester grades
    $secondSemGrades = [];
    $stmt = $conn->prepare("SELECT subject_title, midterm_grade, finals_grade, final_rating, remarks 
                           FROM $secondSemTable 
                           WHERE studentID = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['semester'] = '2nd Semester';
        $secondSemGrades[] = $row;
    }

    // Combine all grades
    $allGrades = array_merge($firstSemGrades, $secondSemGrades);

    // Calculate statistics
    $stats = [
        'passed' => 0,
        'failed' => 0,
        'incomplete' => 0,
        'withdrawn' => 0,
        'average' => 0
    ];

    $totalGrades = 0;
    $gradeCount = 0;

    foreach ($allGrades as $grade) {
        if ($grade['final_rating']) {
            $totalGrades += $grade['final_rating'];
            $gradeCount++;
        }
        $stats[$grade['remarks']]++;
    }

    $stats['average'] = $gradeCount > 0 ? round($totalGrades / $gradeCount, 2) : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'grades' => $allGrades,
            'stats' => $stats
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
