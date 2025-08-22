<?php
 //insert_sections.php
 define('PAGE_TITLE', 'Insert sections');
 define('PAGE_NAME', 'Insert sections');
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running insert_sections.php with POST data: " . print_r($_POST, true));

if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $output = '';
    $message = '';
    $timestamp = time();
    
    $id_section = mysqli_real_escape_string($f_link, $_POST['id_section']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $description = mysqli_real_escape_string($f_link, $_POST['description']);
    $section_leader = mysqli_real_escape_string($f_link, $_POST['section_leader']);

    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $enabled = mysqli_real_escape_string($f_link, $enabled);

    // If _POST is update and id_section is set, update the section
    if($_POST["update"] == "update" && !empty($_POST['id_section'])) {
        $sql = "
        UPDATE sections 
        SET name ='$name',
        description = '$description',
        section_leader = '$section_leader',
        enabled = '$enabled'
        WHERE id_section='".$id_section."'";
        $message = 'Data Updated';
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO sections(name, description, section_leader, enabled)
        VALUES('$name', '$description', $section_leader, $enabled);
        ";
        $message = 'Data Inserted';
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
        $message = "Failed";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: A section with this name already exists. Please use a different name.</p>';
        } else {
            $output .= '<p class="text-danger">' . $message . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message) . '</p>';
        }
        
        echo '<p><a href="'.$referred.'">Return</a></p>';
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
    <div><p align="center" class="text-danger">You can get here only from the Paper sizes menu.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
 ?>
