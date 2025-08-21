<?php
 //insert_papersizes.php
 define('PAGE_TITLE', 'Insert papersizes');
 define('PAGE_NAME', 'Insert papersizes');
require_once('config.php');
require_once('functions.php');
ferror_log("Insert paper sizes POST ".print_r($_POST, true));
if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $output = '';
    $message = '';
    $timestamp = time();
    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $id_paper_size = mysqli_real_escape_string($f_link, $_POST['id_paper_size']);
    $id_paper_size_hold = mysqli_real_escape_string($f_link, $_POST['id_paper_size_hold']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $horizontal = mysqli_real_escape_string($f_link, $_POST['horizontal']);
    $vertical = mysqli_real_escape_string($f_link, $_POST['vertical']);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE paper_sizes 
        SET id_paper_size = '$id_paper_size',
        name ='$name',
        description = '$description',
        horizontal = '$horizontal',
        vertical = '$vertical',
        enabled = '$enabled'
        WHERE id_paper_size='".$id_paper_size_hold."'";
        $message = "Paper size $name updated";
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO paper_sizes(id_paper_size, name, description, horizontal, vertical, enabled)
        VALUES('$id_paper_size', '$name', '$description', $horizontal, $vertical, $enabled);
        ";
        $message = "Paper size $name inserted";
    }
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
        $message = "Insert/update paper size failed.";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: A paper size with this ID or name already exists. Please use a different ID or name.</p>';
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
