<?php
 //insert_ensembles.php
define('PAGE_TITLE', 'Insert ensembles');
define('PAGE_NAME', 'InsertEnsembles');
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Insert ensembles POST ".print_r($_POST, true));
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if(!empty($_POST)) {
    ferror_log("RUNNING insert_ensembles.php with id_ensemble=". $_POST["id_ensemble"]);
    $output = '';
    $message = '';
    $timestamp = time();
    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $id_ensemble = mysqli_real_escape_string($f_link, $_POST['id_ensemble']);
    $id_ensemble_hold = mysqli_real_escape_string($f_link, $_POST['id_ensemble_hold']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $link = mysqli_real_escape_string($f_link, $_POST['link']);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE ensembles 
        SET id_ensemble = '$id_ensemble',
        name ='$name',
        description = '$description',
        link = '$link',
        enabled = '$enabled'
        WHERE id_ensemble='".$_POST["id_ensemble_hold"]."'";
        $message = "Ensemble $name updated";
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO ensembles(id_ensemble, name, description, link, enabled)
        VALUES('$id_ensemble','$name', '$description', '$link', $enabled);
        ";
        $message = "Ensemble $name inserted";
    }
    $referred = $_SERVER['HTTP_REFERER'];
    
    try {
        if(mysqli_query($f_link, $sql)) {
            $output .= '<label class="alert alert-success">' . $message . '</label>';
            $query = parse_url($referred, PHP_URL_QUERY);
            $referred = str_replace(array('?', $query), '', $referred);
            echo '<p><a href="'.$referred.'">Return</a></p>';
            echo $output;
        }
    } catch (mysqli_sql_exception $e) {
        $message = "Insert/update ensemble failed.";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");

         // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: An ensemble with this ID already exists. Please use a different ID.</p>';
        } else {
            $output .= '<p class="text-danger">' . $message . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message) . '</p>';
        }
        
        echo '<p><a href="'.$referred.'">Return</a></p>';
        echo $output;
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
