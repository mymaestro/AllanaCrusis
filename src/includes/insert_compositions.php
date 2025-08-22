<?php
 //insert_compositions.php
define('PAGE_TITLE', 'Insert compositions');
define('PAGE_NAME', 'Insert compositions');
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if(!empty($_POST)) {
    ferror_log("RUNNING insert_compositions.php with catalog_number=". $_POST["catalog_number"]);
    ferror_log("POST ". print_r($_POST, true));
    $output = '';
    $message = '';
    $timestamp = time();
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $composer = mysqli_real_escape_string($f_link, $_POST['composer']);
    $arranger = mysqli_real_escape_string($f_link, $_POST['arranger']);
    $editor = mysqli_real_escape_string($f_link, $_POST['editor']);
    $publisher = mysqli_real_escape_string($f_link, $_POST['publisher']);
    $genre = mysqli_real_escape_string($f_link, $_POST['genre']);
    $ensemble = mysqli_real_escape_string($f_link, $_POST['ensemble']);
    $comments = mysqli_real_escape_string($f_link, $_POST['comments']);
    $performance_notes = mysqli_real_escape_string($f_link, $_POST['performance_notes']);
    $storage_location = mysqli_real_escape_string($f_link, $_POST['storage_location']);
    $provenance = mysqli_real_escape_string($f_link, $_POST['provenance']);
    $listening_example_link = mysqli_real_escape_string($f_link, $_POST['listening_example_link']);
    $image_path = mysqli_real_escape_string($f_link, $_POST['image_path']);
    $checked_out = mysqli_real_escape_string($f_link, $_POST['checked_out']);
    $paper_size = mysqli_real_escape_string($f_link, $_POST['paper_size']);

    // Special handling for numbers and dates and columns that can be NULL
    /*
     * Have a look at insert_parts.php to understand how this NULL stuff
     * can be properly handled much more efficiently using PHP functions
     * designed for that purpose
     */
    $grade = mysqli_real_escape_string($f_link, $_POST['grade']);
    if (empty($grade)) {
        $grade = "NULL";
    } else {
        $grade = "'" . $grade . "'";
    }

    $last_performance_date = mysqli_real_escape_string($f_link, $_POST['last_performance_date']);
    if (empty($last_performance_date)) {
        $last_performance_date = "NULL";
    } else {
        $last_performance_date = "'" . $last_performance_date . "'";
    }

    $duration = mysqli_real_escape_string($f_link, $_POST['duration']);
    if (empty($duration)) {
        $duration = "NULL";
    }
    
    $date_acquired = mysqli_real_escape_string($f_link, $_POST['date_acquired']);
    if (empty($date_acquired)) {
        $date_acquired = "NULL";
    } else {
        $date_acquired = "'" . $date_acquired . "'";
    }

    $cost = mysqli_real_escape_string($f_link, $_POST['cost']);
    if (empty($cost)) {
        $cost = "NULL";
    }

    $last_inventory_date = mysqli_real_escape_string($f_link, $_POST['last_inventory_date']);
    if (empty($last_inventory_date)) {
        $last_inventory_date = "NULL";
    } else {
        $last_inventory_date = "'" . $last_inventory_date . "'";
    }

    $windrep_link = mysqli_real_escape_string($f_link, $_POST['windrep_link']);
    if (empty($windrep_link)) {
        $windrep_link = "NULL";
    } else {
        $windrep_link = "'" . $windrep_link . "'";
    }

    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    ferror_log("POST enabled=".$enabled);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE compositions 
        SET catalog_number='$catalog_number',
        name ='$name',
        description = '$description',
        composer = '$composer',
        arranger = '$arranger',
        editor = '$editor',
        publisher = '$publisher',
        genre = '$genre',
        ensemble = '$ensemble', 
        grade = $grade,
        last_performance_date = $last_performance_date,
        duration = $duration,
        comments = '$comments',
        performance_notes = '$performance_notes',
        storage_location = '$storage_location',
        provenance = '$provenance',
        date_acquired = $date_acquired,
        cost = $cost,
        listening_example_link = '$listening_example_link',
        checked_out = '$checked_out',
        paper_size = '$paper_size',
        last_inventory_date = $last_inventory_date,
        image_path = '$image_path',
        windrep_link = $windrep_link,
        last_update = CURRENT_TIMESTAMP(),
        enabled = $enabled
        WHERE catalog_number='".$_POST["catalog_number_hold"]."'";
        $message = 'Composition '.$catalog_number.' updated';
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO compositions(catalog_number, name, description, composer, arranger, editor, publisher, genre, ensemble, grade, last_performance_date, duration, comments, performance_notes, storage_location, provenance, date_acquired, cost, listening_example_link, checked_out, paper_size, image_path, windrep_link, last_inventory_date, last_update, enabled)
        VALUES('$catalog_number', '$name', '$description', '$composer', '$arranger', '$editor', '$publisher', '$genre', '$ensemble', $grade, $last_performance_date, $duration, '$comments', '$performance_notes', '$storage_location', '$provenance', $date_acquired, $cost, '$listening_example_link', '$checked_out', '$paper_size', '$image_path', $windrep_link, $last_inventory_date, CURRENT_TIMESTAMP(), $enabled);
        ";
        $message = 'Composition '.$catalog_number.' added';
    }
    ferror_log("Running SQL ". $sql);
    $referred = $_SERVER['HTTP_REFERER'];
    if(mysqli_query($f_link, $sql)) {
        $output .= '<label class="text-success">' . $message . '</label>';
        echo $output;
    } else {
        $message = "Failed";
        $error_message = mysqli_error($f_link);
        $output .= '<p class="text-danger">' . $message . '. Error: ' . $error_message . '</p>
           ';
        echo $output;
        ferror_log("Error: " . $error_message);
    }
 } else {
    require_once(__DIR__ . "/header.php");
    echo '<body>
';
    require_once(__DIR__ . "/navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You should not be here.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
mysqli_close($f_link);
?>
