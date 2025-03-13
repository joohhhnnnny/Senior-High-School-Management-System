<?php
require_once '../Portal-Main/conn.php';

function testDatabaseConnection() {
    global $conn;
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "\n";
        return false;
    }
    
    // Test basic database connectivity and version
    echo "✅ Database connection successful\n";
    echo "MySQL Version: " . $conn->server_info . "\n";
    echo "PHP Version: " . phpversion() . "\n";
    return true;
}

function testQueryExecution() {
    global $conn;
    
    $strand = 'HUMSS';
    $semester = 'first';
    $school_year = '2024-2025';
    
    echo "\n=== Testing Tables Structure ===\n";
    
    // Test sections table structure
    echo "\nSections Table Structure:\n";
    $result = $conn->query("DESCRIBE sections");
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']}\n";
    }
    
    // Test schedules table structure
    echo "\nSchedules Table Structure:\n";
    $result = $conn->query("DESCRIBE schedules");
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']}\n";
    }
    
    echo "\n=== Testing Data Presence ===\n";
    
    // Test sections data
    $sectionsQuery = "SELECT COUNT(*) as count, GROUP_CONCAT(DISTINCT strand) as strands, 
                     GROUP_CONCAT(DISTINCT yearLevel) as years 
                     FROM sections";
    $result = $conn->query($sectionsQuery);
    $row = $result->fetch_assoc();
    echo "\nSections Summary:\n";
    echo "Total sections: {$row['count']}\n";
    echo "Available strands: {$row['strands']}\n";
    echo "Year levels: {$row['years']}\n";
    
    // Test specific section
    echo "\nTesting for specific HUMSS Grade 12 section:\n";
    $stmt = $conn->prepare("SELECT * FROM sections WHERE strand = ? AND yearLevel = '12'");
    $stmt->bind_param("s", $strand);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($section = $result->fetch_assoc()) {
        echo "✅ Found section: \n";
        print_r($section);
    } else {
        echo "❌ No HUMSS Grade 12 section found\n";
    }
    
    // Test schedules data
    echo "\nSchedules Summary:\n";
    $schedulesQuery = "SELECT COUNT(*) as count, 
                      GROUP_CONCAT(DISTINCT semester) as semesters,
                      GROUP_CONCAT(DISTINCT day_of_week) as days
                      FROM schedules";
    $result = $conn->query($schedulesQuery);
    $row = $result->fetch_assoc();
    echo "Total schedules: {$row['count']}\n";
    echo "Semesters: {$row['semesters']}\n";
    echo "Days: {$row['days']}\n";
    
    // Add this debug section before the full query
    echo "\n=== Testing JOIN Relations ===\n";
    
    // Test schedules for section_id
    echo "\nTesting schedules for HUMSS section:\n";
    $sectionId = 14; // From the found section
    $debugQuery1 = "SELECT COUNT(*) as count FROM schedules WHERE section_id = ? AND semester = 'first'";
    $stmt = $conn->prepare($debugQuery1);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    echo "Schedules found for section_id $sectionId: $count\n";

    // Test subject relationships
    echo "\nTesting subjects in schedules:\n";
    $debugQuery2 = "
        SELECT DISTINCT sub.id, sub.subject_title 
        FROM schedules s
        JOIN subjects sub ON s.subject_id = sub.id
        WHERE s.section_id = ?
    ";
    $stmt = $conn->prepare($debugQuery2);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "Subjects found:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['subject_title']} (ID: {$row['id']})\n";
    }

    // Test professor relationships
    echo "\nTesting professors in schedules:\n";
    $debugQuery3 = "
        SELECT DISTINCT p.id, p.fullname 
        FROM schedules s
        JOIN professor p ON s.professor_id = p.id
        WHERE s.section_id = ?
    ";
    $stmt = $conn->prepare($debugQuery3);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "Professors found:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['fullname']} (ID: {$row['id']})\n";
    }

    echo "\n=== Testing Individual Relationships ===\n";

    // Test 1: Check if schedules exist for this specific section and semester
    $test1 = "SELECT COUNT(*) as count FROM schedules 
              WHERE section_id = ? AND semester = ? AND school_year = ?";
    $stmt = $conn->prepare($test1);
    $stmt->bind_param("iss", $sectionId, $semester, $school_year);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "\nSchedules for this section/semester: " . $result->fetch_assoc()['count'] . "\n";

    // Test 2: Check sections table data
    $test2 = "SELECT * FROM sections 
              WHERE strand = ? AND yearLevel = '12' AND school_year = ?";
    $stmt = $conn->prepare($test2);
    $stmt->bind_param("ss", $strand, $school_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $sectionData = $result->fetch_assoc();
    echo "\nSection data:\n";
    print_r($sectionData);

    // Test 3: Verify schedule-subject-professor links
    $test3 = "SELECT s.*, sub.subject_title, p.fullname 
              FROM schedules s 
              LEFT JOIN subjects sub ON s.subject_id = sub.id
              LEFT JOIN professor p ON s.professor_id = p.id
              WHERE s.section_id = ? AND s.semester = ?
              LIMIT 1";
    $stmt = $conn->prepare($test3);
    $stmt->bind_param("is", $sectionId, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "\nFirst schedule with relationships:\n";
    print_r($result->fetch_assoc());

    // Test full query
    echo "\n=== Testing Full Query ===\n";
    $query = "
        SELECT 
            s.id as schedule_id,
            s.day_of_week as day,
            TIME_FORMAT(s.start_time, '%H:%i') as start_time,
            TIME_FORMAT(s.end_time, '%H:%i') as end_time,
            s.room,
            sec.section_name,
            sub.subject_title AS subject_name,
            sub.subject_type,
            p.fullname AS professor_name,
            sec.strand,
            sec.yearLevel,
            sec.school_year
        FROM schedules s
        JOIN sections sec ON s.section_id = sec.id
        JOIN subjects sub ON s.subject_id = sub.id
        JOIN professor p ON s.professor_id = p.id
        WHERE sec.strand = ? 
        AND sec.yearLevel = '12'
        AND s.semester = ?
        AND sec.school_year = ?
    ";

    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sss", $strand, $semester, $school_year);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $schedules = $result->fetch_all(MYSQLI_ASSOC);

        echo "\nQuery Parameters:\n";
        echo "Strand: $strand\n";
        echo "Semester: $semester\n";
        echo "School Year: $school_year\n";
        echo "Number of records found: " . count($schedules) . "\n";
        
        if (count($schedules) > 0) {
            echo "\nFirst Schedule Record:\n";
            print_r($schedules[0]);
        }

        return true;
    } catch (Exception $e) {
        echo "❌ Query execution failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run tests
echo "=== Testing Grade 12 Schedules ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

if (testDatabaseConnection()) {
    echo "\nTesting query execution...\n";
    testQueryExecution();
}

// Test database character set
echo "\n=== Database Character Set Information ===\n";
$charset_query = "SHOW VARIABLES LIKE 'character_set%'";
$result = $conn->query($charset_query);
while ($row = $result->fetch_assoc()) {
    echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
}

// Test timezone settings
echo "\n=== Timezone Settings ===\n";
$timezone_query = "SELECT @@session.time_zone, @@system_time_zone";
$result = $conn->query($timezone_query);
$row = $result->fetch_array();
echo "Session timezone: " . $row[0] . "\n";
echo "System timezone: " . $row[1] . "\n";
echo "PHP timezone: " . date_default_timezone_get() . "\n";
?>