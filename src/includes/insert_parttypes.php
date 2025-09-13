<?php
 //insert_parttypes.php
define('PAGE_TITLE', 'Insert part types');
define('PAGE_NAME', 'Insert part types');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running insert_parttypes.php with POST data: ". print_r($_POST, true));
if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $output = '';
    $message = '';
    $timestamp = time();
    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $is_part_collection =  mysqli_real_escape_string($f_link, $_POST["is_part_collection"]);
    if (empty($is_part_collection)) {
        $is_part_collection = "NULL";
    } else {
        $is_part_collection = "'" . $is_part_collection . "'";
    }
    $id_part_type = mysqli_real_escape_string($f_link, $_POST['id_part_type']);
    $collation = mysqli_real_escape_string($f_link, $_POST['collation']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $default_instrument = mysqli_real_escape_string($f_link, $_POST['default_instrument']);
    $family = mysqli_real_escape_string($f_link, $_POST['family']);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE part_types 
        SET name ='$name',
        description = '$description',
        collation = $collation,
        family = '$family',
        default_instrument = '$default_instrument',
        is_part_collection = $is_part_collection,
        enabled = $enabled
        WHERE id_part_type='".$_POST["id_part_type"]."'";
        $message = 'Data Updated';
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO part_types(collation, name, description, family, default_instrument, is_part_collection, enabled)
        VALUES($collation, '$name', '$description', '$family', $default_instrument, $is_part_collection, $enabled);
        ";
        $message = 'Data Inserted';
    }
    ferror_log("Running parttypes SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
    $referred = $_SERVER['HTTP_REFERER'];
    $referred .= "/#" . $id_part_type;
    
    try {
        if(mysqli_query($f_link, $sql)) {
            $output .= '<label class="text-success">' . $message . '</label>';
            $query = parse_url($referred, PHP_URL_QUERY);
            $referred = str_replace(array('?', $query), '', $referred);
            
            // Echo the output for success display
            echo $output;
        }
    } catch (mysqli_sql_exception $e) {
        $message = "Failed";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: A part type with this ID or name already exists. Please use a different ID or name.</p>';
        } else {
            $output .= '<p class="text-danger">' . $message . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message ?? '') . '</p>';
        }
        
        // Echo the output for error display
        echo $output;
    }
    mysqli_close($f_link);
} else {
    require_once(__DIR__ . "/header.php");
    echo '<body>
';
    require_once(__DIR__ . "/navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Part types menu.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
 ?>
