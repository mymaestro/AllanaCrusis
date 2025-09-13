
<?php
// fetch_distribution_zips.php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running fetch_distribution_zips.php");

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zip_filename']) && isset($_POST['id_playgram']) && isset($_POST['id_section'])) {
    $zip_filename = $_POST['zip_filename'];
    $id_playgram = $_POST['id_playgram'];
    $id_section = $_POST['id_section'];

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
    // Insert token into the database with looked-up id_playgram and id_section
    $stmt = mysqli_prepare($f_link, "INSERT INTO download_tokens (token, id_playgram, id_section, zip_filename, expires_at, id_user) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siissi", $token, $id_playgram, $id_section, $zip_filename, $expires_at, $id_user);
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
            'expires_at' => $expires_at,
            'id_playgram' => $id_playgram,
            'id_section' => $id_section
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
$sql = "SELECT zip_filename, 
        MAX(expires_at) AS latest_expiration,
        id_playgram,
        id_section
        FROM download_tokens
        GROUP BY zip_filename
        ORDER BY zip_filename";

$result = mysqli_query($f_link, $sql);
$zips = [];
while ($row = mysqli_fetch_assoc($result)) {
    $zips[] = [
        'zip_filename' => $row['zip_filename'],
        'latest_expiration' => $row['latest_expiration'],
        'id_playgram' => $row['id_playgram'],
        'id_section' => $row['id_section']
    ];
}

header('Content-Type: application/json');
echo json_encode($zips);

mysqli_close($f_link);

?>
