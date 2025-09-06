<?php  
 //delete_records.php
 // remodel to fit music library database
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

// Check user roles - only allow librarians or administrators
$u_admin = FALSE;
$u_librarian = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
}

if (!$u_librarian && !$u_admin) {
    ferror_log("Unauthorized access attempt to delete_records.php by user: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous'));
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

ferror_log("Running delete_records.php");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_POST["table_name"])) $table_name = mysqli_real_escape_string($f_link, $_POST['table_name']);
if (isset($_POST["table_key_name"])) $table_key_name = mysqli_real_escape_string($f_link, $_POST['table_key_name']);
if (isset($_POST["table_key"])) $table_key = mysqli_real_escape_string($f_link, $_POST['table_key']);

if (isset($table_name) && isset($table_key_name) && isset($table_key)) {
    $timestamp = time();
    header('Content-Type: application/json');
    ferror_log("Delete request for table=". $table_name . " table key=" . $table_key . " table key name=". $table_key_name);

    $sql = "DELETE FROM " . $table_name . " WHERE ".$table_key_name . " = ?";

    try {
        $stmt = mysqli_prepare($f_link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $table_key);  // i=integer, d=double, s=string, b=BLOB
        mysqli_stmt_execute($stmt);
        echo json_encode(['success' => true,
            'message' => $table_key ]);
    } catch ( mysqli_sql_exception $e) {
        $error_code = $e->getCode();
        $error_message = $e->getMessage();
        ferror_log("Ended with error code " . $error_code . ": " . $error_message);

        if ($error_code == 1451) { // 1451 = foreign key constraint
            echo json_encode([
                'success' => false,
                'error' => 'Delete failed with ' . $error_code . ': '. $table_key . ' is referenced in another table. MSG: ' . $error_message
            ]);
        } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database error code ' . $error_code . ': ' . $error_message
            ]);
        }
    }
}
mysqli_close($f_link);
?>