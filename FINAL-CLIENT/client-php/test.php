<?php
header('Content-Type: application/json');

$result = [
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'php_version' => PHP_VERSION,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME']
];

echo json_encode($result, JSON_PRETTY_PRINT);