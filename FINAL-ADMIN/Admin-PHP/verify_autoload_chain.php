<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$results = [];

// Add file content check
$vendorDir = realpath(__DIR__ . '/../vendor');
$autoloadFile = $vendorDir . '/autoload.php';
$autoloadRealFile = $vendorDir . '/composer/autoload_real.php';

$results['files'] = [
    'vendor_dir_exists' => is_dir($vendorDir),
    'autoload_exists' => file_exists($autoloadFile),
    'autoload_real_exists' => file_exists($autoloadRealFile)
];

if (file_exists($autoloadRealFile)) {
    $results['autoload_real_content'] = htmlspecialchars(file_get_contents($autoloadRealFile));
}

try {
    // Step 1: Load autoload.php
    $vendorDir = realpath(__DIR__ . '/../vendor');
    $autoloadFile = $vendorDir . '/autoload.php';
    
    $results['step1'] = [
        'message' => 'Loading autoload.php',
        'file' => $autoloadFile
    ];
    
    require $autoloadFile;
    $results['step1']['status'] = 'success';

    // Step 2: Verify PHPMailer class loading
    $results['step2'] = [
        'message' => 'Verifying PHPMailer classes'
    ];
    
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    $results['step2']['status'] = 'success';
    $results['step2']['class_exists'] = true;

    // Step 3: Test basic PHPMailer initialization
    $results['step3'] = [
        'message' => 'Testing PHPMailer initialization'
    ];
    
    $mailer->isSMTP();
    $mailer->Host = 'smtp.gmail.com';
    $results['step3']['status'] = 'success';

} catch (Throwable $e) {
    $results['error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);