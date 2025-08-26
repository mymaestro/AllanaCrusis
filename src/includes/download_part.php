<?php
require_once(__DIR__ . '/includes/config.php');
// Add your authentication/authorization checks here

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('No file specified.');
}

$filename = basename($_GET['file']); // Prevent directory traversal
$filepath = ORGPRIVATE . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('File not found.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;