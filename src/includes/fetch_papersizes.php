<?php  
 //fetch_papersizes.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running fetch_papersizes.php with id=". $_POST["id_paper_size"]);
if(isset($_POST["id_paper_size"])) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_paper_size = mysqli_escape_string($f_link, $_POST["id_paper_size"]);
    $sql = "SELECT * FROM paper_sizes WHERE id_paper_size = '".$id_paper_size."'";
    ferror_log("Getting details for paper size ID: " . $id_paper_size);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
    ferror_log("Fetch paper sizes returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
}
?>
