<?php
// delete_partcollections.php
// Deletes a part collection entry based on catalog number, part type key, and instrument key.
// Called from the part collections page partcollections.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

// Check user roles - only allow librarians
$u_admin = FALSE;
$u_librarian = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
}

if (!$u_librarian) {
    ferror_log("Unauthorized access attempt to delete_partcollections.php by user: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous'));
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

ferror_log("Running delete_partcollections.php with id=". $_POST["catalog_number_key"] . ":" . $_POST["id_part_type_key"] . ":". $_POST["id_part_type"]);
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$catalog_number_key = mysqli_real_escape_string($f_link, $_POST['catalog_number_key']);
$id_part_type_key = mysqli_real_escape_string($f_link, $_POST['id_part_type_key']);
$id_instrument_key = mysqli_real_escape_string($f_link, $_POST['id_instrument_key']);

if(isset($id_instrument_key) && isset($id_part_type_key) && isset($catalog_number_key) )  {
    $output = '';
    $message = '';
    $timestamp = time();
    $table_name = "part_collections";
    if($catalog_number_key != '') {
        $sql = "
        DELETE FROM " . $table_name . " 
        WHERE catalog_number_key = '" . $catalog_number_key . "' AND id_part_type_key = " . $id_part_type_key . " AND id_instrument_key = " . $id_instrument_key .";";
        ferror_log("Deleting part collection entry: " . $catalog_number_key . ", " . $id_part_type_key . ", " . $id_instrument_key);
        $message = 'Data deleted';
        if(mysqli_query($f_link, $sql)) {
            $output .= '<label class="text-success">' . $message . '</label>';
            ferror_log("output: ". $output);
        } else {
            $message = "Delete failed";
            $error_message = mysqli_error($f_link);
            $output .= '<p class="text-danger">' . $message . '. Error: ' . $error_message . '</p>
                   ';
            ferror_log("Error: " . $error_message);
            ferror_log("output: ". $output);
        }
        $referred = $_SERVER['HTTP_REFERER'];
        $query = parse_url($referred, PHP_URL_QUERY);
        $referred = str_replace(array('?', $query), '', $referred);
        echo $output;
        echo '<p><a href="'.$referred.'">Return</a></p>';
    } else {
        $created = date('Y-m-d H:i:s.u');
        $sql = "";
        ferror_log("Delete SQL (N/A): " . $sql);
        $message = 'No data deleted';
        $output .= '<p class="text-danger">' . $message . '. Error: ' . $error_message . '</p>
          ';
        echo '<p><a href="'.$referred.'">Return</a></p>';
        echo $output;
        ferror_log("Error: " . $error_message);
    }
}
mysqli_close($f_link);
?>
