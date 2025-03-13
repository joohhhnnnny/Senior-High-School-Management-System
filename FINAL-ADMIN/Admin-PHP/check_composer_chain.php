<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];

// Check PHP version
$results['php_version'] = [
    'version' => PHP_VERSION,
    'version_id' => PHP_VERSION_ID,
    'meets_requirements' => PHP_VERSION_ID >= 50600
];

// Check vendor directory structure
$vendorDir = realpath(__DIR__ . '/../vendor');
$composerDir = $vendorDir . '/composer';
$autoloadReal = $composerDir . '/autoload_real.php';

$results['files'] = [
    'vendor_dir' => [
        'path' => $vendorDir,
        'exists' => is_dir($vendorDir),
        'readable' => is_readable($vendorDir)
    ],
    'composer_dir' => [
        'path' => $composerDir,
        'exists' => is_dir($composerDir),
        'readable' => is_readable($composerDir)
    ],
    'autoload_real' => [
        'path' => $autoloadReal,
        'exists' => file_exists($autoloadReal),
        'readable' => is_readable($autoloadReal),
        'size' => file_exists($autoloadReal) ? filesize($autoloadReal) : 0,
        'first_100_chars' => file_exists($autoloadReal) ? substr(file_get_contents($autoloadReal), 0, 100) : ''
    ]
];

// Check if PHPMailer files exist
$phpmailerBase = $vendorDir . '/phpmailer/phpmailer/src';
$results['phpmailer'] = [
    'base_dir' => [
        'path' => $phpmailerBase,
        'exists' => is_dir($phpmailerBase)
    ],
    'files' => [
        'PHPMailer.php' => file_exists($phpmailerBase . '/PHPMailer.php'),
        'SMTP.php' => file_exists($phpmailerBase . '/SMTP.php'),
        'Exception.php' => file_exists($phpmailerBase . '/Exception.php')
    ]
];

echo json_encode($results, JSON_PRETTY_PRINT);