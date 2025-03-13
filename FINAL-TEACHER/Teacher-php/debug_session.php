<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Current Session Data:\n";
print_r($_SESSION);
echo "</pre>";
?>
