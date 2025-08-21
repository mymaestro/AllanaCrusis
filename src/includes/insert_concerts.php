<?php
 //insert_concerts.php
define('PAGE_TITLE', 'Insert concerts');
define('PAGE_NAME', 'Insert concerts');
require_once('config.php');
require_once('functions.php');

ferror_log(print_r($_POST, true));

if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $output = '';
    $message = '';
    $timestamp = time();
    
    $id_playgram = mysqli_real_escape_string($f_link, $_POST['id_playgram']);
    $performance_date = mysqli_real_escape_string($f_link, $_POST['performance_date']);
    $venue = mysqli_real_escape_string($f_link, $_POST['venue']);
    $conductor = mysqli_real_escape_string($f_link, $_POST['conductor']);
    $id_concert = mysqli_real_escape_string($f_link, $_POST['id_concert']);
    $notes = mysqli_real_escape_string($f_link, $_POST['notes']);
    ferror_log("RUNNING insert_concerts.php with id_concert=". $id_concert . " at ". date("Y-m-d H:i:s", $timestamp));

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE concerts 
        SET id_playgram = '$id_playgram',
        performance_date ='$performance_date',
        venue = '$venue',
        conductor = '$conductor',
        notes = '$notes'
        WHERE id_concert = $id_concert ;";
        $message = 'Data Updated';
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO concerts(id_playgram, performance_date, venue, conductor, notes)
        VALUES($id_playgram, '$performance_date', '$venue', '$conductor', '$notes');
        ";
        $message = 'Data Inserted';
    }
    ferror_log("Insert concerts SQL ". trim(preg_replace('/\s+/', ' ', $sql)));
    $referred = $_SERVER['HTTP_REFERER'];
    
    try {
        if(mysqli_query($f_link, $sql)) {
            $output .= '<label class="text-success">' . $message . '</label>';
            $query = parse_url($referred, PHP_URL_QUERY);
            $referred = str_replace(array('?', $query), '', $referred);
            echo '<p><a href="'.$referred.'">Return</a></p>';
            echo $output;
        }
    } catch (mysqli_sql_exception $e) {
        $message = "Failed";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: A concert with this information already exists. Please check the data and try again.</p>';
        } else {
            $output .= '<p class="text-danger">' . $message . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message) . '</p>';
        }
        
        echo '<p><a href="'.$referred.'">Return</a></p>';
        echo $output;
    }
    mysqli_close($f_link);
 } else {
    require_once("header.php");
    echo '<body>
';
    require_once("navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Paper sizes menu.</p></div>';
    require_once("footer.php");
    echo '</body>';
 }
 ?>
