<?php
header('Content-Type: application/json');
require_once '../database/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $requiredFields = ['section_id', 'subject_id', 'professor_id', 'day_of_week', 
                          'start_time', 'end_time', 'room', 'semester', 'school_year'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Check for schedule conflicts
        $conflict_check = "SELECT * FROM schedules WHERE 
            section_id = ? AND 
            day_of_week = ? AND 
            semester = ? AND 
            ((start_time BETWEEN ? AND ?) OR 
             (end_time BETWEEN ? AND ?))";
        
        $stmt = $conn->prepare($conflict_check);
        $stmt->bind_param("issssss", 
            $data['section_id'],
            $data['day_of_week'],
            $data['semester'],
            $data['start_time'],
            $data['end_time'],
            $data['start_time'],
            $data['end_time']
        );
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Schedule conflict detected for this time slot");
        }

        // Insert new schedule
        $insert = "INSERT INTO schedules (section_id, subject_id, professor_id, day_of_week, 
                                        start_time, end_time, room, semester, school_year) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("iiissssss", 
            $data['section_id'],
            $data['subject_id'],
            $data['professor_id'],
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['room'],
            $data['semester'],
            $data['school_year']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create schedule: " . $stmt->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Schedule created successfully',
            'id' => $stmt->insert_id
        ]);

    } else {
        // GET method - retrieve schedules
        $yearLevel = $_GET['yearLevel'] ?? null;
        $strand = $_GET['strand'] ?? null;
        $semester = $_GET['semester'] ?? null;

        $query = "
            SELECT 
                sc.id,
                sc.day_of_week,
                TIME_FORMAT(sc.start_time, '%H:%i') as start_time,
                TIME_FORMAT(sc.end_time, '%H:%i') as end_time,
                sc.room,
                sc.semester,
                sub.subject_title,
                sub.subject_type,
                sec.section_name,
                sec.strand,
                CONCAT(p.first_name, ' ', p.last_name) as professor_name
            FROM schedules sc
            JOIN sections sec ON sc.section_id = sec.id
            JOIN subjects sub ON sc.subject_id = sub.id
            LEFT JOIN professors p ON sc.professor_id = p.id
            WHERE sec.yearLevel = ?
            AND sec.strand = ?
            AND sc.semester = ?
            ORDER BY sc.start_time ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $yearLevel, $strand, $semester);
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
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
