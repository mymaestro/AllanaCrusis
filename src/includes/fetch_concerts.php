<?php  
 //fetch_concerts.php
require_once('config.php');
require_once('functions.php');
ferror_log("Running fetch_concerts.php with id=". $_POST["id_concert"]);

if(isset($_POST["id_concert"])) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_concert = mysqli_real_escape_string($f_link, $_POST["id_concert"]);
    $sql = "SELECT * FROM concerts WHERE id_concert = $id_concert ;";
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
    ferror_log("Fetch concerts returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
};
?>
