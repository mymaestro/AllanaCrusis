<?php
 //insert_partcollections.php
define('PAGE_TITLE', 'Insert part collections');
define('PAGE_NAME', 'Insert part collections');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running insert_partcollections.php with update = ".print_r($_POST, true));

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_POST['catalog_number_key'])) $catalog_number_key = mysqli_real_escape_string($f_link, $_POST['catalog_number_key']);
if (isset($_POST['id_part_type_key'])) $id_part_type_key = mysqli_real_escape_string($f_link, $_POST['id_part_type_key']);
// id_instrument_key could be an array. Handle in the add block
if (isset($_POST['id_instrument_key'])) {
    if (!is_array($_POST['id_instrument_key'])) {
        $id_instrument_key = mysqli_real_escape_string($f_link, $_POST['id_instrument_key']);
    } else {
        $id_instrument_key = $_POST['id_instrument_key'];
    }
}

// Special handling for numbers and dates and columns that can be NULL
if (isset($_POST['name'])) $name = mysqli_real_escape_string($f_link, $_POST['name']);
if (empty($name)) {
    $name = "NULL";
} else {
    $name = "'" . $name . "'";
}
ferror_log("Name is ". $name);

if (isset($_POST['description'])) $description = mysqli_real_escape_string($f_link, $_POST['description']);
if (empty($description)) {
    $description = "NULL";
} else {
    $description = "'" . $description . "'";
}

if (isset($catalog_number_key) && isset($id_part_type_key) && isset($id_instrument_key)) {
    $output = '';
    $message = '';
    $timestamp = time();

    // Update?
    if($_POST["update"] == "update") {
        if (isset($_POST['catalog_number_key_hold'])) $catalog_number_key_hold = mysqli_real_escape_string($f_link, $_POST['catalog_number_key_hold']);
        if (isset($_POST['id_part_type_key_hold'])) $id_part_type_key_hold = mysqli_real_escape_string($f_link, $_POST['id_part_type_key_hold']);
        if (isset($_POST['id_instrument_key_hold'])) $id_instrument_key_hold = mysqli_real_escape_string($f_link, $_POST['id_instrument_key_hold']);
        if (isset($catalog_number_key_hold) && isset($id_part_type_key_hold) && isset($id_instrument_key_hold)) {
            $sql = "UPDATE part_collections
            SET catalog_number_key = '$catalog_number_key',
                id_part_type_key = '$id_part_type_key',
                id_instrument_key = '$id_instrument_key',
                name =$name,
                description = $description,
                last_update = CURRENT_TIMESTAMP()
            WHERE catalog_number_key='".$catalog_number_key_hold."'
            AND   id_part_type_key='".$id_part_type_key_hold."'
            AND   id_instrument_key = '".$id_instrument_key_hold."';";

            $message = 'Data Updated';
            try {
                if(mysqli_query($f_link, $sql)) {
                    echo '<p class="text-success">Updated '  . $catalog_number_key .':'.$id_part_type_key .':'. $id_instrument_key . ' successfully.</p>';
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = $e->getMessage();
                $mysql_errno = $e->getCode();
                
                ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
                
                // Check for specific error types
                if ($mysql_errno == 1062) {
                    echo '<p class="text-danger">Duplicate Entry Error: A part collection with this key combination already exists for ' . $catalog_number_key .':'.$id_part_type_key .':'. $id_instrument_key . '.</p>';
                } else {
                    echo '<p class="text-danger">Error updating ' . $catalog_number_key .':'.$id_part_type_key .':'. $id_instrument_key . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message ?? '') . '</p>';
                }
            }
        }
    } elseif($_POST["update"] == "add") {
        ferror_log("Adding parts... ");
        foreach($_POST['id_instrument_key'] as $id_instrument_key_num) {
            $id_instrument_key = mysqli_real_escape_string($f_link, $id_instrument_key_num);
            ferror_log("Adding id_part_type = ".$id_instrument_key);
            $sql = "
            INSERT INTO part_collections(catalog_number_key, id_part_type_key, id_instrument_key, name, description, last_update)
            VALUES('$catalog_number_key', '$id_part_type_key', '$id_instrument_key', $name, $description, CURRENT_TIMESTAMP() );
            ";
            try {
                if(mysqli_query($f_link, $sql)) {
                    echo '<p class="text-success">Inserted ' . $id_part_type_key . ' successfully.</p>';
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = $e->getMessage();
                $mysql_errno = $e->getCode();
                
                ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
                
                // Check for specific error types
                if ($mysql_errno == 1062) {
                    echo '<p class="text-danger">Duplicate Entry Error: A part collection with this key combination already exists for ' . $id_part_type . '.</p>';
                } else {
                    echo '<p class="text-danger">Error inserting ' . $id_part_type . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message ?? '') . '</p>';
                }
            }
        } // foreach
        $referred = $_SERVER['HTTP_REFERER'];
        $query = parse_url($referred, PHP_URL_QUERY);
        $referred = str_replace(array('?', $query), '', $referred);
        echo '<p><a href="'.$referred.'">Return</a></p>';
        // end loop
    } // update button = add
} else { // empty
    require_once(__DIR__ . "/header.php");
    echo '<body>
';
    require_once(__DIR__ . "/navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Part collections menu.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
}
mysqli_close($f_link);
?>