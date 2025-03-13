<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];

$vendorDir = realpath(__DIR__ . '/../vendor');
$autoloadFile = $vendorDir . '/autoload.php';

$results['vendor_directory'] = $vendorDir;
$results['autoload_file'] = $autoloadFile;
$results['file_exists'] = file_exists($autoloadFile);
$results['is_readable'] = is_readable($autoloadFile);
$results['directory_contents'] = [];

// Check vendor directory contents
if (is_dir($vendorDir)) {
    $results['directory_contents'] = scandir($vendorDir);
}

try {
    require $autoloadFile;
    $results['autoload_status'] = 'success';
    $results['loaded_classes'] = get_declared_classes();
} catch (Throwable $e) {
    $results['autoload_status'] = 'error';
    $results['error_message'] = $e->getMessage();
    $results['stack_trace'] = $e->getTraceAsString();
}

echo json_encode($results, JSON_PRETTY_PRINT);