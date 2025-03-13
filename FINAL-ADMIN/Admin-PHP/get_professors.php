<?php
header('Content-Type: application/json');
require_once '../Portal-Main/conn.php';

try {
    $sql = "SELECT professorID, fullname, email FROM professor ORDER BY email ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed");
    }

    $professors = [];
    while ($row = $result->fetch_assoc()) {
        $professors[] = $row;
    }

    echo json_encode([
        'success' => true,
        'professors' => $professors
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
