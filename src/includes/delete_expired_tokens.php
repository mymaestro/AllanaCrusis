<?php  
 //delete_expired_tokens.php
 //
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running delete_expired_tokens.php - authorized user: " . $_SESSION['username']);
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get a list of ZIP files that should be deleted (tokens are expired or used)
// Only get ZIPs that have NO active (unused and not expired) tokens remaining
$sql = "SELECT DISTINCT dt1.zip_filename 
        FROM download_tokens dt1 
        WHERE dt1.zip_filename IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM download_tokens dt2 
            WHERE dt2.zip_filename = dt1.zip_filename 
            AND dt2.used = 0 
            AND dt2.expires_at > NOW()
        )";

$res = mysqli_query($f_link, $sql);
$expired_zips = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $expired_zips[] = $row['zip_filename'];
    }
}

// Delete ZIP files from filesystem before cleaning up database records
$deleted_files = 0;
$failed_deletions = 0;
foreach ($expired_zips as $zip_filename) {
    $distrPath = rtrim(ORGPRIVATE, '/') . '/distributions/';
    $zip_path = $distrPath . $zip_filename;
    if (file_exists($zip_path)) {
        if (unlink($zip_path)) {
            ferror_log("Successfully deleted ZIP file: " . $zip_filename);
            $deleted_files++;
        } else {
            ferror_log("Failed to delete ZIP file: " . $zip_filename);
            $failed_deletions++;
        }
    } else {
        ferror_log("ZIP file not found (already deleted?): " . $zip_filename);
    }
}

ferror_log("ZIP file cleanup summary: {$deleted_files} deleted, {$failed_deletions} failed");

// Clean up download_tokens (fix: expires_at not expires)
$sql = "DELETE FROM download_tokens
    WHERE used = 1 OR expires_at < NOW()";

ferror_log("Cleaning up download_tokens");
$tokens_deleted = 0;
$cleanup_success = false;
if(mysqli_query($f_link, $sql)) {
    $tokens_deleted = mysqli_affected_rows($f_link);
    ferror_log("Download tokens cleaned up successfully. {$tokens_deleted} tokens removed.");
    $cleanup_success = true;
} else {
    $error_message =  mysqli_error($f_link);
    ferror_log("Download token cleanup failed with error: ". $error_message);
}

mysqli_close($f_link);

// Return JSON response with cleanup summary
$response = [
    'success' => $cleanup_success && $failed_deletions == 0,
    'tokens_deleted' => $tokens_deleted,
    'files_deleted' => $deleted_files,
    'failed_deletions' => $failed_deletions,
    'zip_files_processed' => count($expired_zips),
    'message' => $cleanup_success ? 
        "Cleanup completed: {$tokens_deleted} tokens and {$deleted_files} ZIP files removed" : 
        "Cleanup completed with errors: {$failed_deletions} file deletions failed"
];

header('Content-Type: application/json');
echo json_encode($response);
?>
