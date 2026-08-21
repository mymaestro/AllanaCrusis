<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

header('Content-Type: application/json');

$is_librarian = isset($_SESSION['roles']) && strpos($_SESSION['roles'], 'librarian') !== false;
if (!$is_librarian) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Librarian privileges required.']);
    exit;
}

$catalog_number = trim($_POST['catalog_number'] ?? '');
$action = $_POST['action'] ?? '';
if ($catalog_number === '' || !in_array($action, ['create_zip', 'generate_download_token', 'update_token_email'], true)) {
    echo json_encode(['success' => false, 'message' => 'A composition and valid action are required.']);
    exit;
}

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($action === 'update_token_email') {
    $token = $_POST['token'] ?? '';
    $email = trim($_POST['email'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $token) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Valid token and email are required.']);
    } else {
        $stmt = mysqli_prepare($f_link, 'UPDATE download_tokens SET email = ? WHERE token = ? AND catalog_number = ?');
        mysqli_stmt_bind_param($stmt, 'sss', $email, $token, $catalog_number);
        mysqli_stmt_execute($stmt);
        $updated = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => $updated === 1, 'message' => $updated === 1 ? 'Recipient saved.' : 'Download token was not found.']);
    }
} elseif ($action === 'create_zip') {
    echo json_encode(createCompositionZip($f_link, $catalog_number));
} else {
    $zip_filename = basename($_POST['zip_filename'] ?? '');
    echo json_encode(generateCompositionDownloadToken($f_link, $catalog_number, $zip_filename));
}

mysqli_close($f_link);

function createCompositionZip($f_link, $catalog_number) {
    $sql = "SELECT c.name AS composition_name, pt.name AS part_name, p.image_path
            FROM compositions c
            JOIN parts p ON p.catalog_number = c.catalog_number
            JOIN part_types pt ON pt.id_part_type = p.id_part_type
            WHERE c.catalog_number = ? AND c.enabled = 1
              AND p.originals_count > 0
              AND p.image_path IS NOT NULL AND p.image_path != ''
            ORDER BY pt.collation, pt.name";
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $catalog_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $parts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $parts[] = $row;
    }
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    if (empty($parts)) {
        return ['success' => false, 'message' => 'No parts with PDF files were found for this composition.'];
    }

    $composition_name = $parts[0]['composition_name'];
    $zip_filename = compositionDistributionFilename($catalog_number . ' - ' . $composition_name) . '_Parts.zip';
    $distribution_path = rtrim(ORGPRIVATE, '/') . '/distributions/';
    $zip_path = $distribution_path . $zip_filename;
    if (!is_dir($distribution_path) && !mkdir($distribution_path, 0755, true)) {
        return ['success' => false, 'message' => 'Could not create distributions directory.'];
    }

    $zip = new ZipArchive();
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['success' => false, 'message' => 'Could not create ZIP file.'];
    }

    $added_count = 0;
    $skipped_files = [];
    $parts_path = rtrim(ORGPRIVATE, '/') . '/parts/';
    foreach ($parts as $part) {
        $source_path = $parts_path . ltrim($part['image_path'], '/\\');
        $part_filename = compositionDistributionFilename($part['part_name']) . '.pdf';
        if (file_exists($source_path) && $zip->addFile($source_path, $part_filename)) {
            if (method_exists($zip, 'setCompressionName')) {
                @$zip->setCompressionName($part_filename, ZipArchive::CM_STORE);
            }
            $added_count++;
        } else {
            $skipped_files[] = $part['part_name'];
        }
    }

    if (!$zip->close() || $added_count === 0) {
        if (file_exists($zip_path)) {
            unlink($zip_path);
        }
        return ['success' => false, 'message' => 'No PDF files could be added to ZIP.'];
    }

    return ['success' => true, 'data' => [
        'filename' => $zip_filename,
        'part_count' => $added_count,
        'skipped_files' => $skipped_files
    ]];
}

function generateCompositionDownloadToken($f_link, $catalog_number, $zip_filename) {
    $distribution_path = rtrim(ORGPRIVATE, '/') . '/distributions/';
    if ($zip_filename === '' || !file_exists($distribution_path . $zip_filename)) {
        return ['success' => false, 'message' => 'ZIP file does not exist.'];
    }

    $token = bin2hex(random_bytes(16));
    $expires_at = date('Y-m-d H:i:s', strtotime('+' . DOWNLOAD_TOKEN_EXPIRY_DAYS . ' days'));
    $user_id = null;
    if (isset($_SESSION['username'])) {
        $user_stmt = mysqli_prepare($f_link, 'SELECT id_users FROM users WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($user_stmt, 's', $_SESSION['username']);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user_row = mysqli_fetch_assoc($user_result)) {
            $user_id = $user_row['id_users'];
        }
        mysqli_free_result($user_result);
        mysqli_stmt_close($user_stmt);
    }

    $stmt = mysqli_prepare($f_link, 'INSERT INTO download_tokens (token, catalog_number, zip_filename, expires_at, id_user) VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'ssssi', $token, $catalog_number, $zip_filename, $expires_at, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return ['success' => true, 'data' => [
        'filename' => $zip_filename,
        'token' => $token,
        'download_link' => '/d/' . $token,
        'expires_at' => $expires_at
    ]];
}

function compositionDistributionFilename($filename) {
    $filename = preg_replace('/[^A-Za-z0-9._ -]+/', '_', (string)$filename);
    return trim(preg_replace('/\s+/', ' ', $filename), ' .-_');
}