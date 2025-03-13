<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

function searchFiles($dir, $pattern) {
    $results = [];
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $content = file_get_contents($file->getPathname());
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $lines = file($file->getPathname());
                foreach ($matches[0] as $match) {
                    $lineNum = 1;
                    $pos = 0;
                    foreach ($lines as $line) {
                        if (strpos($line, $match[0]) !== false) {
                            $results[] = [
                                'file' => str_replace('\\', '/', $file->getPathname()),
                                'line' => $lineNum,
                                'content' => trim($line),
                                'match' => $match[0]
                            ];
                        }
                        $lineNum++;
                    }
                }
            }
        }
    }
    
    return $results;
}

try {
    $projectRoot = dirname(dirname(__FILE__));
    $pattern = '/[g]\d{2}_(?:first|second)sem/i';
    
    $results = searchFiles($projectRoot, $pattern);
    
    echo json_encode([
        'success' => true,
        'references' => $results,
        'message' => count($results) . ' references found'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
