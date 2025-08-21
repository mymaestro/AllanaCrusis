<?php  
 //fetch_genres.php
require_once('config.php');
require_once('functions.php');
ferror_log("Running fetch_genres.php with id=". $_POST["id_genre"]);
if(isset($_POST["id_genre"])) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_genre = mysqli_escape_string($f_link, $_POST["id_genre"]);
    $sql = "SELECT * FROM genres WHERE id_genre = '".$id_genre."'";
    ferror_log("Getting details for genre ID: " . $id_genre);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
    ferror_log("Fetch genres returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
}
?>
