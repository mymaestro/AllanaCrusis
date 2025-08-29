
<?php
// fetch_distribution_zips.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running fetch_distribution_zips.php");

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zip_filename'])) {
    $zip_filename = $_POST['zip_filename'];
    // Generate a secure token for this ZIP
    $token = bin2hex(random_bytes(16)); // 32-char token
    $expires_at = date('Y-m-d H:i:s', strtotime('+2 days'));
    $id_user = null;
    if (isset($_SESSION['username'])) {
        $user_stmt = mysqli_prepare($f_link, "SELECT id_users FROM users WHERE username = ?");
        mysqli_stmt_bind_param($user_stmt, "s", $_SESSION['username']);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user_row = mysqli_fetch_assoc($user_result)) {
            $id_user = $user_row['id_users'];
        }
        mysqli_stmt_close($user_stmt);
    }
    // Insert token into the database (id_playgram and id_section are unknown/null here)
    $stmt = mysqli_prepare($f_link, "INSERT INTO download_tokens (token, id_playgram, id_section, zip_filename, expires_at, id_user) VALUES (?, NULL, NULL, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $token, $zip_filename, $expires_at, $id_user);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $download_link = '/d/' . $token;
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode([
            'success' => true,
            'download_link' => $download_link,
            'token' => $token,
            'zip_filename' => $zip_filename,
            'expires_at' => $expires_at
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create token.'
        ]);
    }
    mysqli_close($f_link);
    exit;
}

// Default: GET - list ZIPs
$sql = "SELECT zip_filename, MAX(expires_at) AS latest_expiration
        FROM download_tokens
        WHERE zip_filename LIKE '%.zip'
        GROUP BY zip_filename
        ORDER BY zip_filename";

$result = mysqli_query($f_link, $sql);
$zips = [];
while ($row = mysqli_fetch_assoc($result)) {
    $zips[] = [
        'zip_filename' => $row['zip_filename'],
        'latest_expiration' => $row['latest_expiration']
    ];
}

header('Content-Type: application/json');
echo json_encode($zips);

mysqli_close($f_link);
/* 
    $zip_url = ORGPARTDISTRO . $zip_filename;

    // Generate a secure token for this ZIP
    $token = bin2hex(random_bytes(16)); // 32-char token
    $expires_at = date('Y-m-d H:i:s', strtotime('+2 days'));
    // Get user ID from database using session username
    $id_user = null;
    if (isset($_SESSION['username'])) {
        $user_stmt = mysqli_prepare($f_link, "SELECT id_users FROM users WHERE username = ?");
        mysqli_stmt_bind_param($user_stmt, "s", $_SESSION['username']);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user_row = mysqli_fetch_assoc($user_result)) {
            $id_user = $user_row['id_users'];
        }
        mysqli_stmt_close($user_stmt);
    }
    // Insert token into the database
    $stmt = mysqli_prepare($f_link, "INSERT INTO download_tokens (token, id_playgram, id_section, zip_filename, expires_at, id_user) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siissi", $token, $playgram_id, $section_id, $zip_filename, $expires_at, $id_user);
    mysqli_stmt_execute($stmt);

    $download_link = '/d/' . $token;

    return [
        'success' => true,
        'data' => [
            'zip_url' => $zip_url,
            'filename' => $zip_filename,
            'part_count' => $added_count,
            'skipped_files' => $skipped_files,
            'token' => $token,
            'download_link' => $download_link
        ]
    ];
 */
?>
