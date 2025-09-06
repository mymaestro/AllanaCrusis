<?php
// Cleanup script for expired tokens (both password reset and email verification)
// This should be run periodically (e.g., via cron job)

require_once(__DIR__ . "/../src/includes/config.php");
require_once(__DIR__ . "/../src/includes/functions.php");

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$currentDate = date("U");

// Delete all expired tokens (both password reset and email verification)
$sql = "DELETE FROM password_reset WHERE password_reset_expires < ?";

if ($stmt = mysqli_prepare($f_link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $currentDate);
    $result = mysqli_stmt_execute($stmt);
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    
    if ($result) {
        ferror_log("Cleanup: Removed $affected_rows expired tokens (password reset and email verification)");
        echo "Cleanup successful: Removed $affected_rows expired tokens.\n";
    } else {
        ferror_log("Cleanup: Error removing expired tokens");
        echo "Cleanup failed: Error removing expired tokens.\n";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($f_link);
?>
