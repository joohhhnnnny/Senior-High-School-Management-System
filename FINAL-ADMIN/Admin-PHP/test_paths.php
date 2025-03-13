<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Script Path: " . __FILE__ . "\n";
echo "Directory Path: " . __DIR__ . "\n";
echo "Parent Directory: " . dirname(__DIR__) . "\n";
echo "Connection File Path: " . dirname(__DIR__) . '/Portal-Main/conn.php' . "\n";
echo "File exists check: " . (file_exists(dirname(__DIR__) . '/Portal-Main/conn.php') ? 'Yes' : 'No') . "\n";
echo "</pre>";
?>