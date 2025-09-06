<?php  
 //delete_parts.php
 //
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
// Check user roles - only allow librarians
$u_admin = FALSE;
$u_librarian = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
}

if (!$u_librarian) {
    ferror_log("Unauthorized access attempt to delete_parts.php by user: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous'));
    http_response_code(403);
    exit;
}

ferror_log("Running delete_parts.php with id=". $_POST["catalog_number"] . ":" . $_POST["id_part_type"]);
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (isset($_POST['catalog_number'])) $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
if (isset($_POST['id_part_type'])) $id_part_type = mysqli_real_escape_string($f_link, $_POST['id_part_type']);

if(isset($id_part_type) && (isset($catalog_number))) {
    // Clean up instruments in part_collections for this part
    $sql = "DELETE FROM part_collections 
    WHERE catalog_number_key = '".$catalog_number."'
    AND id_part_type_key = '".$id_part_type."';";
    
    ferror_log("Cleaning up instruments in part_collections for: ". $id_part_type . " for " . $catalog_number);
    if(mysqli_query($f_link, $sql)) {
        ferror_log("Part collection(s) removed for ".$catalog_number." and ".$id_part_type.".");
    } else {
        $error_message =  mysqli_error($f_link);
        ferror_log("Part collection delete failed with error: ". $error_message);
    }
    $sql = "DELETE FROM parts
    WHERE catalog_number = '" . $catalog_number . "'
    AND id_part_type = " . $id_part_type .";";
    
    if(mysqli_query($f_link, $sql)) {
        ferror_log("DELETEd part ".$catalog_number.":".$id_part_type." successfully.");
    } else {
        $error_message = mysqli_error($f_link);
        ferror_log("Part delete failed with error: " . $error_message);
    }
}
?>
