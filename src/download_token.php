<?php
// download_token.php: Handles /d/{token} secure ZIP downloads
// Route: /d/{token} (via public/index.php)

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';


// Extract token from URL (assume routed as /d/{token})
$token = null;
if (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    // Try to extract from PATH_INFO if routed that way
    if (isset($_SERVER['PATH_INFO'])) {
        $parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        if (count($parts) === 2 && $parts[0] === 'd') {
            $token = $parts[1];
        }
    }
}

ferror_log("Download request for token=$token from IP=$ip, Referer=$referer, UA=$user_agent, Time=" . date('Y-m-d H:i:s'));

if (!$token || !preg_match('/^[a-f0-9]{32}$/', $token)) {
    ferror_log("Invalid or missing download token.");
    http_response_code(400);
    echo 'Invalid or missing download token.';
    exit;
}

// Use MySQLi connection
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = mysqli_prepare($f_link, 'SELECT * FROM download_tokens WHERE token = ?');
mysqli_stmt_bind_param($stmt, 's', $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    ferror_log("Download token not found.");
    http_response_code(404);
    echo 'Download token not found.';
    exit;
}

if ($row['used']) {
    ferror_log("Download token has already been used.");
    http_response_code(403);
    echo 'This download link has been used.';
    exit;
}

if (strtotime($row['expires_at']) < time()) {
    ferror_log("Download token expired at " . $row['expires_at']);
    http_response_code(403);
    echo 'This download link has expired.';
    exit;
}

// Reconstruct the full path to the ZIP file from zip_filename
$zip_filename = $row['zip_filename'];
$zip_path = __DIR__ . '/includes/' . ORGDIST . $zip_filename;
if (!file_exists($zip_path)) {
    ferror_log("ZIP file not found: " . $zip_path);
    http_response_code(410);
    echo 'File no longer available.';
    exit;
}

// Mark token as used
$update = mysqli_prepare($f_link, 'UPDATE download_tokens SET used = 1 WHERE token = ?');
mysqli_stmt_bind_param($update, 's', $token);
mysqli_stmt_execute($update);
mysqli_stmt_close($update);
mysqli_close($f_link);

ferror_log("Serving ZIP file: " . $zip_path);
// Serve ZIP file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
header('Content-Length: ' . filesize($zip_path));
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile($zip_path);
exit;
