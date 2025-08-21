<?php
 //update_scoreorder.php
define('PAGE_TITLE', 'Update score order');
define('PAGE_NAME', 'Update score order');
require_once('config.php');
require_once('functions.php');
if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    ferror_log("RUNNING update_scoreorder.php with id_part_type=". $_POST["id_part_type"]);
    $output = '';
    $message = '';
    $updated = 0;
    $timestamp = time();
    $newScoreOrder = $_POST['part'];
    $collation = 10;
    $output = '<label class="text-success">';

    foreach ($newScoreOrder as $id_part_type) {
        $sql = "UPDATE part_types
                SET collation = " . $collation;
        $sql .= "
        WHERE id_part_type = " . $id_part_type . ";";
        $message = "Data updated.";
        ferror_log("Running SQL: ". $sql);
        if(mysqli_query($f_link, $sql) or die("Error: update query failed.")) {
            $updated++;
            $collation = $collation + 10;
        } else {
            $message = "Updating " . $id_part_type . " failed.";
            $output .= $message;
        }
    }
    $output .= $updated . ' record(s) updated.</label>';
    $referred = $_SERVER['HTTP_REFERER'];
    $query = parse_url($referred, PHP_URL_QUERY);
    $referred = str_replace(array('?', $query), '', $referred);
    echo '<p><a href="parttypes.php">Refresh</a></p>';
    echo $output;
    mysqli_close($f_link);
 } else {
    require_once("header.php");
    echo '<body>
';
    require_once("navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Part types menu.</p></div>';
    require_once("footer.php");
    echo '</body>';
 }
 ?>
