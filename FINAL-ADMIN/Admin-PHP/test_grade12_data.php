<?php
require_once dirname(__DIR__) . '/Portal-Main/conn.php';

$query = "
    SELECT s.*, sec.strand, sec.yearLevel
    FROM schedules s
    JOIN sections sec ON s.section_id = sec.id
    WHERE sec.yearLevel = '12'
    AND sec.strand = 'HUMSS'
    ORDER BY s.semester, s.start_time";

$result = $conn->query($query);
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>