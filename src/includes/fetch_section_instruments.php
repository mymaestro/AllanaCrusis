<?php
// index.php?action=fetch_section_instruments
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
$section_id = intval($_POST['section_id']);

ferror_log("RUNNING fetch_section_instruments.php for section_id: $section_id");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT id_instrument FROM section_instruments WHERE id_section = $section_id";
$res = mysqli_query($f_link, $sql);
$assigned = [];
while ($row = mysqli_fetch_assoc($res)) {
    $assigned[] = $row['id_instrument'];
}
ferror_log("Fetch section instruments returned ".mysqli_num_rows($res). " rows.");
mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode($assigned);
?>