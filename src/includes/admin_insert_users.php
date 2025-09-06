<?php
 //insert_users.php
define('PAGE_TITLE', 'Insert users');
define('PAGE_NAME', 'Insert users');
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running ". PAGE_NAME . " with POST data: " . print_r($_POST, true));

if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $output = '';
    $message = '';
    $timestamp = time();
        
    $id_users = mysqli_real_escape_string($f_link, $_POST['id_users']);
    $id_users_hold = mysqli_real_escape_string($f_link, $_POST['id_users_hold']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $username = mysqli_real_escape_string($f_link, $_POST['username']);
    $address = mysqli_real_escape_string($f_link, $_POST['address']);
    $roles = mysqli_real_escape_string($f_link, $_POST['roles']);
    $u_password = 'changeme'; // need to encode this
    $passwordHash = password_hash($u_password, PASSWORD_DEFAULT);
    // ferror_log("Password hash =" . $passwordHash);

    // Need to check that a user with that username or email address already exist
    if($_POST["update"] == "update") {
        $sql = "
        UPDATE users 
        SET id_users = '$id_users',
        name ='$name',
        username = '$username',
        address = '$address',
        roles = '$roles'
        WHERE id_users='".$_POST["id_users_hold"]."'";
        $message = "user $name updated";
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO users(id_users, name, username, address, roles, password)
        VALUES('$id_users','$name', '$username', '$address', '$roles', '$passwordHash');
        ";
        $message = "user $name inserted";
    }
    $referred = $_SERVER['HTTP_REFERER'];
    
    try {
        if(mysqli_query($f_link, $sql)) {
            $output .= '<label class="text-success">' . $message . '</label>';
            $query = parse_url($referred, PHP_URL_QUERY);
            $referred = str_replace(array('?', $query), '', $referred);
            $output .= '<p><a href="'.$referred.'">Return</a></p>';
            echo $output;
        }
    } catch (mysqli_sql_exception $e) {
        $message = "Failed";
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        ferror_log("Running SQL:" . trim(preg_replace('/\s+/', ' ', $sql)));
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            $output .= '<p class="text-danger">Duplicate Entry Error: A user with this ID or name already exists. Please use a different ID or name.</p>';
        } else {
            $output .= '<p class="text-danger">' . $message . '. Error Code: ' . $mysql_errno . ' - Details: ' . htmlspecialchars($error_message ?? '') . '</p>';
        }
        
        $output .= '<p><a href="'.$referred.'">Return</a></p>';
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
    <div><p align="center" class="text-danger">You should not be here.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
 ?>
