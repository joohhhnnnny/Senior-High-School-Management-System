<?php
header('Content-Type: application/json');
require_once 'conn.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the incoming request
error_log("Schedule manage request received: " . file_get_contents('php://input'));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Received schedule data: " . print_r($data, true));
        
        // First, get the professor's professorID using the id
        $stmt = $conn->prepare("
            SELECT professorID 
            FROM professor 
            WHERE id = ?
        ");
        $stmt->execute([$data['professorId']]);
        $professor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$professor) {
            throw new Exception('Professor not found');
        }
        
        // Validate time format and logic
        $startTime = DateTime::createFromFormat('H:i', $data['timeStart']);
        $endTime = DateTime::createFromFormat('H:i', $data['timeEnd']);
        
        if (!$startTime || !$endTime) {
            throw new Exception('Invalid time format. Use HH:mm format');
        }
        
        // Convert to comparable format
        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr = $endTime->format('H:i:s');
        
        // Compare times
        if (strtotime($startTimeStr) >= strtotime($endTimeStr)) {
            throw new Exception('End time must be after start time');
        }
        
        // Update times in data array
        $data['timeStart'] = $startTimeStr;
        $data['timeEnd'] = $endTimeStr;
        
        // Validate required fields
        $requiredFields = ['professorId', 'subjectId', 'day', 'timeStart', 'timeEnd', 'room', 'semester', 'strand', 'yearLevel'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // First, find the correct section_id based on strand and year level
        $stmt = $conn->prepare("
            SELECT id 
            FROM sections 
            WHERE strand = ? 
            AND yearLevel = ?
            ORDER BY school_year DESC
            LIMIT 1
        ");
        
        $stmt->execute([
            $data['strand'],
            $data['yearLevel']
        ]);
        
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$section) {
            throw new Exception('No matching section found for strand: ' . $data['strand'] . ' and year level: ' . $data['yearLevel']);
        }
        
        // Log the found section for debugging
        error_log("Found section: " . print_r($section, true));
        
        // Simplified schedule conflict check since DB constraints will handle most cases
        // We just need to check for overlapping times that the constraints don't catch
        $stmt = $conn->prepare("
            SELECT s.*, 
                   sub.subject_title,
                   sec.strand
            FROM schedules s
            JOIN sections sec ON s.section_id = sec.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE (
                -- Check professor schedule overlap
                s.professor_id = ? AND 
                s.day_of_week = ? AND 
                s.semester = ? AND
                s.school_year = (SELECT school_year FROM sections WHERE id = ?) AND
                -- Check for time overlap
                (? < s.end_time AND ? > s.start_time)
            ) OR (
                -- Check room availability
                s.room = ? AND 
                s.day_of_week = ? AND 
                s.semester = ? AND
                s.school_year = (SELECT school_year FROM sections WHERE id = ?) AND
                -- Check for time overlap
                (? < s.end_time AND ? > s.start_time)
            )
        ");

        $schoolYear = $conn->query("SELECT school_year FROM sections WHERE id = " . $section['id'])->fetchColumn();
        
        $stmt->execute([
            $professor['professorID'],
            $data['day'],
            $data['semester'],
            $section['id'],
            $data['timeStart'],
            $data['timeEnd'],
            $data['room'],
            $data['day'],
            $data['semester'],
            $section['id'],
            $data['timeStart'],
            $data['timeEnd']
        ]);

        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($conflicts)) {
            $conflictDetails = array_map(function($conflict) {
                return sprintf(
                    "Conflict with %s at %s-%s in %s",
                    $conflict['subject_title'],
                    $conflict['start_time'],
                    $conflict['end_time'],
                    $conflict['strand']
                );
            }, $conflicts);
            
            throw new Exception('Schedule conflict detected: ' . implode(', ', $conflictDetails));
        }

        // Insert new schedule with the found section_id
        $stmt = $conn->prepare("
            INSERT INTO schedules (
                professor_id, subject_id, section_id, day_of_week, 
                start_time, end_time, room, semester, school_year
            ) VALUES (?, ?, ?, ?, TIME(?), TIME(?), ?, ?, (
                SELECT school_year FROM sections WHERE id = ?
            ))
        ");
        
        $result = $stmt->execute([
            $professor['professorID'], // Use professorID instead of id
            $data['subjectId'],
            $section['id'], // Use the found section_id
            $data['day'],
            $data['timeStart'],
            $data['timeEnd'],
            $data['room'],
            $data['semester'],
            $section['id'] // Pass section_id again for the subquery
        ]);
        
        if (!$result) {
            throw new Exception('Failed to insert schedule');
        }
        
        error_log("Schedule added successfully");
        echo json_encode([
            'success' => true,
            'message' => 'Schedule added successfully'
        ]);
        
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception('No schedule ID provided');
        
        $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if (!$result) {
            throw new Exception('Failed to delete schedule');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }
    
} catch(Exception $e) {
    error_log("Error in schedule management: " . $e->getMessage());
    
    // Improve error messages for constraint violations
    if (strpos($e->getMessage(), 'unique_section_time')) {
        $message = 'This section already has a class scheduled at this time';
    } else if (strpos($e->getMessage(), 'unique_room_time')) {
        $message = 'This room is already booked for this time slot';
    } else if (strpos($e->getMessage(), 'unique_professor_time')) {
        $message = 'This professor already has a class scheduled at this time';
    } else if (strpos($e->getMessage(), 'CONSTRAINT_1')) {
        $message = 'End time must be later than start time';
    } else {
        $message = $e->getMessage();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}

$conn = null;
?>
