<?php
// delete_compositions.php
// Called from the compositions page
// Remove a composition and all its parts
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

// Check user roles - only allow librarians or administrators
$u_admin = FALSE;
$u_librarian = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
}

if (!$u_librarian && !$u_admin) {
    ferror_log("Unauthorized access attempt to delete_compositions.php by user: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous'));
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

ferror_log("Running delete_compositions.php with id=" . $_POST["catalog_number"]);

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_POST['catalog_number'])) {
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);

    try {
        // Start transaction to ensure all deletions succeed or fail together
        mysqli_autocommit($f_link, FALSE);
        
        // Step 1: Delete all part_collections entries for parts of this composition
        $sql_part_collections = "DELETE pc FROM part_collections pc 
                               INNER JOIN parts p ON pc.catalog_number_key = p.catalog_number 
                               AND pc.id_part_type_key = p.id_part_type 
                               WHERE p.catalog_number = ?";
        ferror_log("Delete part_collections SQL: " . $sql_part_collections);
        
        $stmt_pc = mysqli_prepare($f_link, $sql_part_collections);
        mysqli_stmt_bind_param($stmt_pc, "s", $catalog_number);
        mysqli_stmt_execute($stmt_pc);
        $part_collections_deleted = mysqli_stmt_affected_rows($stmt_pc);
        mysqli_stmt_close($stmt_pc);

        ferror_log("Deleted " . $part_collections_deleted . " part_collections entries for catalog_number: " . $catalog_number);
        
        // Step 2: Delete all parts for this composition
        $sql_parts = "DELETE FROM parts WHERE catalog_number = ?";
        ferror_log("Delete parts for this composition: " . $catalog_number);
        
        $stmt_parts = mysqli_prepare($f_link, $sql_parts);
        mysqli_stmt_bind_param($stmt_parts, "s", $catalog_number);
        mysqli_stmt_execute($stmt_parts);
        $parts_deleted = mysqli_stmt_affected_rows($stmt_parts);
        mysqli_stmt_close($stmt_parts);
        ferror_log("Deleted " . $parts_deleted . " parts for catalog_number: " . $catalog_number);
        
        // Step 3: Delete the composition itself
        $sql_composition = "DELETE FROM compositions WHERE catalog_number = ?";
        ferror_log("Delete the composition: " . $catalog_number);
        
        $stmt_comp = mysqli_prepare($f_link, $sql_composition);
        mysqli_stmt_bind_param($stmt_comp, "s", $catalog_number);
        mysqli_stmt_execute($stmt_comp);
        $compositions_deleted = mysqli_stmt_affected_rows($stmt_comp);
        mysqli_stmt_close($stmt_comp);
        ferror_log("Deleted " . $compositions_deleted . " composition with catalog_number: " . $catalog_number);
        
        // Commit the transaction
        mysqli_commit($f_link);
        mysqli_autocommit($f_link, TRUE);
        
        echo json_encode(['success' => true,
            'message' => 'Composition with catalog number ' . $catalog_number . ' and all its associated parts (' . $parts_deleted . ' parts, ' . $part_collections_deleted . ' part collections) deleted successfully.']);
            
    } catch (mysqli_sql_exception $e) {
        // Rollback transaction on error
        mysqli_rollback($f_link);
        mysqli_autocommit($f_link, TRUE);
        
        $error_code = $e->getCode();
        $error_message = $e->getMessage();
        ferror_log("Transaction failed with error code " . $error_code . ": " . $error_message);

        if ($error_code == 1451) { // 1451 = foreign key constraint
            echo json_encode([
                'success' => false,
                'error' => 'Delete failed with ' . $error_code . ': '. $catalog_number . ' is still referenced in another table. MSG: ' . $error_message
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