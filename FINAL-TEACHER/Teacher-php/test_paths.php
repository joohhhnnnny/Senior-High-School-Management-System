<?php
$css_path = "../Teacher-css/attendance_management.css";
$real_path = realpath($css_path);

echo "<pre>";
echo "CSS Path: $css_path\n";
echo "Real Path: $real_path\n";
echo "File exists: " . (file_exists($css_path) ? "Yes" : "No") . "\n";
echo "Current directory: " . getcwd() . "\n";
echo "</pre>";
?>
