<?php
// includes/fetch_sections_list.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("RUNNING fetch_sections_list.php");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT id_section, name FROM sections ORDER BY name";
$res = mysqli_query($f_link, $sql);
$sections = [];
while ($row = mysqli_fetch_assoc($res)) {
    $sections[] = $row;
}
ferror_log("Fetch sections list returned ".mysqli_num_rows($res). " rows.");
mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode($sections);
?>