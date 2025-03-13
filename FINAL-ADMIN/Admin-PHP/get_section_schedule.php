<?php
header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

try {
    $yearLevel = $_GET['yearLevel'] ?? null;
    $strand = $_GET['strand'] ?? null;
    $semester = $_GET['semester'] ?? null;

    if (!$yearLevel) {
        throw new Exception('Year level is required');
    }

    $sql = "
        SELECT 
            s.id,
            s.day_of_week,
            s.start_time,
            s.end_time,
            s.room,
            sec.strand,
            sec.section_name,
            sub.subject_title,
            CONCAT(p.firstName, ' ', p.lastName) as professor_name
        FROM schedules s
        JOIN sections sec ON s.section_id = sec.id
        JOIN subjects sub ON s.subject_id = sub.id
        LEFT JOIN professor p ON s.professor_id = p.professorID
        WHERE sec.yearLevel = ?
    ";

    $params = [$yearLevel];
    $types = "s";

    if ($strand) {
        $sql .= " AND sec.strand = ?";
        $params[] = $strand;
        $types .= "s";
    }

    if ($semester) {
        $sql .= " AND s.semester = ?";
        $params[] = $semester;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $schedules
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
