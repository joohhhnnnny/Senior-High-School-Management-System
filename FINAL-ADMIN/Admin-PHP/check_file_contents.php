<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$vendorDir = realpath(__DIR__ . '/../vendor');
$autoloadFile = $vendorDir . '/autoload.php';

$results = [
    'file_path' => $autoloadFile,
    'exists' => file_exists($autoloadFile),
    'readable' => is_readable($autoloadFile),
    'file_size' => filesize($autoloadFile),
    'contents' => file_get_contents($autoloadFile),
    'first_100_chars' => substr(file_get_contents($autoloadFile), 0, 100)
];

echo json_encode($results, JSON_PRETTY_PRINT);