<?php
 //update_scoreorder.php
define('PAGE_TITLE', 'Update score order');
define('PAGE_NAME', 'Update score order');
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("RUNNING update_instruments_scoreorder.php with POST ". print_r($_POST, true));
if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $output = '';
    $message = '';
    $updated = 0;
    $timestamp = time();
    $newScoreOrder = $_POST['instrument'];
    $collation = 10;
    $output = '<label class="text-success">';

    foreach ($newScoreOrder as $id_instrument) {
        $sql = "UPDATE instruments
                SET collation = " . $collation;
        $sql .= "
        WHERE id_instrument = " . $id_instrument . ";";
        $message = "Data updated.";
        ferror_log("Running SQL: ". $sql);
        if(mysqli_query($f_link, $sql) or die("Error: update query failed.")) {
            $updated++;
            $collation = $collation + 10;
        } else {
            $message = "Updating " . $id_instrument . " failed.";
            $output .= $message;
        }
    }
    $output .= $updated . ' record(s) updated.</label>';
    $referred = $_SERVER['HTTP_REFERER'];
    $query = parse_url($referred, PHP_URL_QUERY);
    $referred = str_replace(array('?', $query), '', $referred);
    echo '<p><a href="/instruments">Refresh</a></p>';
    echo $output;
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
