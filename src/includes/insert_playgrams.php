<?php
 //insert_playgrams.php
define('PAGE_TITLE', 'Insert playgrams');
define('PAGE_NAME', 'Insert playgrams');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log(print_r($_POST,true));
if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $output = '';
    $message = '';
    $timestamp = time();

    $insert_mode = $_POST["update"]; // Either "add" or "update
    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $id_playgram = mysqli_real_escape_string($f_link, $_POST['id_playgram']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $performance_date = mysqli_real_escape_string($f_link, $_POST['performance_date']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    if( $insert_mode == "update") {
        $sql = "SELECT id_playgram_item, catalog_number
            FROM playgram_items
            WHERE id_playgram = $id_playgram ;";
        ferror_log("Get existing playgram items");
        $existing = [];
        $res = mysqli_query($f_link, $sql);
        while($rowList = mysqli_fetch_assoc($res)) {
            $existing[$rowList['catalog_number']] = $rowList['id_playgram_item'];
        }
        $sql = "
        UPDATE playgrams
            SET name = '$name',
            performance_date = '$performance_date',
            description = '$description',
            enabled = $enabled
        WHERE id_playgram = $id_playgram ;
        ";
        if(!mysqli_query($f_link, $sql)) {  
            $error_message = mysqli_error($f_link);
            ferror_log("Error updating playgram items " . $error_message);
        }
        // If any compositions are entered...
        if(isset($_POST["id_composition"])) {
            // Add new playgram items
            $comp_order = 1;
            foreach ($_POST['id_composition'] as $key => $value) {
                $catalog_number = mysqli_real_escape_string($f_link, $value);

                if (isset($existing[$catalog_number])) {
                    $id_playgram_item = $existing[$catalog_number];
                    ferror_log("54: Update existing " . $id_playgram_item);
                    // Update the order
                    $sql = "UPDATE playgram_items
                        SET comp_order = $comp_order
                        WHERE id_playgram_item = $id_playgram_item ;";
                    ferror_log("58: update comp order SQL ". trim(preg_replace('/\s+/', ' ', $sql)));

                    if(!mysqli_query($f_link, $sql)) {  
                        $error_message = mysqli_error($f_link);
                        ferror_log("Error updating playgram items " . $error_message);
                    } else { // else success
                        ferror_log("65: Covered, so Unsetting ". $id_playgram_item );
                        unset($existing[$catalog_number]);
                    };

                } else {
                    ferror_log("69: not existing, so add ". $catalog_number );
                    // New item
                    $sql = "INSERT INTO playgram_items
                        ( id_playgram, catalog_number, comp_order )
                        VALUES ( $id_playgram, '$catalog_number', $comp_order );";
                    if(!mysqli_query($f_link, $sql)) {  
                        $error_message = mysqli_error($f_link);
                        ferror_log("Error adding playgram items " . $error_message);
                    }
                }
                $comp_order++;
            }
            // Delete what's left
            if (!empty($existing)) {
                $playgram_delete = implode(",", array_map('intval', $existing));
                $sql = "DELETE from playgram_items
                    WHERE id_playgram_item IN ($playgram_delete) ;";
                ferror_log("83: Delete existing PGI SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
                if(!mysqli_query($f_link, $sql)) {  
                    $error_message = mysqli_error($f_link);
                    ferror_log("Error deleting playgram items " . $error_message);
                }
            }
        } else {
            // Delete what's left
            if (!empty($existing)) {
                $playgram_delete = implode(",", array_map('intval', $existing));
                $sql = "DELETE from playgram_items
                    WHERE id_playgram_item IN ($playgram_delete) ;";
                ferror_log("95: Delete existing (none left) SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
                if(!mysqli_query($f_link, $sql)) {  
                    $error_message = mysqli_error($f_link);
                    ferror_log("Error deleting playgram items " . $error_message);
                }
            }
        }
    } elseif( $insert_mode == "add") {
        $sql = "
        INSERT INTO playgrams(name, performance_date, description, enabled)
        VALUES('$name', '$performance_date', '$description', $enabled);
        ";
        ferror_log("107: Insert PG SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
        if(!mysqli_query($f_link, $sql)) {  
            $error_message = mysqli_error($f_link);
            // Send the error back, check if the error is due to a duplicate name
            if (strpos($error_message, 'Duplicate entry') !== false) {
                $message = "Playgram with this name already exists.";
            } else {
                $message = "Error inserting playgram: " . $error_message;
            }
            ferror_log("Error inserting playgram items " . $error_message);
        } else {
            $id_playgram = mysqli_insert_id($f_link);
            ferror_log("Inserted playgram with id " . $id_playgram);
            $message = "Playgram added successfully.";
        }
        // If any compositions are entered...
        if(isset($_POST["id_composition"])) {
            // Add new playgram items
            $comp_order = 1;
            foreach ($_POST['id_composition'] as $key => $value) {
                $catalog_number = mysqli_real_escape_string($f_link, $value);
                // New item
                $sql = "INSERT INTO playgram_items
                    (id_playgram, catalog_number, comp_order)
                    VALUES ( $id_playgram, '$catalog_number', $comp_order );";
                ferror_log("122: Insert PGI SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
                if(!mysqli_query($f_link, $sql)) {  
                    $error_message = mysqli_error($f_link);
                    ferror_log("Error adding playgram items " . $error_message);
                }
                $comp_order++;
            }
        }
    }
    $referred = $_SERVER['HTTP_REFERER'];
    $referred .= "/#" . $id_playgram;
    $output .= '<label class="text-success">' . $message . '</label>';
    $query = parse_url($referred, PHP_URL_QUERY);
    $referred = str_replace(array('?', $query), '', $referred);
     mysqli_close($f_link);
 } else {
    require_once(__DIR__ . "/header.php");
    echo '<body>
';
    require_once(__DIR__ . "/navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the playgrams menu.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
 ?>
