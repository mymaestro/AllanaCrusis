<?php  
 //fetch_sections.php
require_once('config.php');
require_once('functions.php');
$section_id = intval($_POST['id_section']);

ferror_log("Running fetch_sections.php with id=". $section_id);
if(isset($section_id)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql = "SELECT * FROM sections WHERE id_section = '".$section_id."'";
    ferror_log("SQL: ". $sql);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
    ferror_log("Fetch sections returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
}
?>
