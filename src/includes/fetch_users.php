<?php  
 //fetch_users.php
require_once('config.php');
require_once('functions.php');
ferror_log("Running fetch_users.php with id_users=". $_POST["id_users"]);
if(isset($_POST["id_users"])) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql = "SELECT * FROM users WHERE id_users = '".$_POST["id_users"]."'";
    ferror_log("Running SQL: " . $sql);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);  
    echo json_encode($rowList);
    ferror_log("Fetch users returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
}
?>
