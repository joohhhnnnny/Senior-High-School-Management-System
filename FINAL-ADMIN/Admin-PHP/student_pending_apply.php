<?php
require_once '../Portal-Main/conn.php';

// Add debugging
error_log("Starting student_pending_apply.php");

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!$conn->ping()) {
        error_log("Database connection failed");
        throw new Exception("Database connection lost");
    }
    error_log("Database connection successful");

    // Log query
    $sql = "SELECT 
            spe.id,
            spe.studentID,
            sa.fullname,
            spe.yearLevel,
            UPPER(spe.strand) as strand,
            spe.status,
            spe.date
        FROM studentpendingenroll spe
        LEFT JOIN studentapply sa ON spe.studentID = sa.id
        ORDER BY spe.date DESC";
    
    error_log("Executing query: " . $sql);
    $result = $conn->query($sql);
    
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        throw new Exception("Query failed: " . $conn->error);
    }

    $applications = [];
    while ($row = $result->fetch_assoc()) {
        $applications[] = [
            'id' => intval($row['id']),
            'studentID' => $row['studentID'],
            'fullname' => $row['fullname'] ?? 'N/A',
            'yearLevel' => $row['yearLevel'],
            'strand' => strtoupper($row['strand']),
            'status' => $row['status'],
            'date' => $row['date']
        ];
    }
    error_log("Found " . count($applications) . " applications");

    $stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
        FROM studentpendingenroll")->fetch_assoc();
    
    error_log("Response data: " . json_encode([
        'success' => true,
        'data' => $applications,
        'stats' => $stats
    ]));

    echo json_encode([
        'success' => true,
        'data' => $applications,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Error in student_pending_apply.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>