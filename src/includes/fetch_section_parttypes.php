<?php
// includes/fetch_section_parttypes.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
$section_id = intval($_POST['section_id']);

ferror_log("RUNNING fetch_section_parttypes.php for section_id: $section_id");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT id_part_type FROM section_part_types WHERE id_section = $section_id";
$res = mysqli_query($f_link, $sql);
$assigned = [];
while ($row = mysqli_fetch_assoc($res)) {
    $assigned[] = $row['id_part_type'];
}
ferror_log("Fetch section part types returned ".mysqli_num_rows($res). " rows.");
mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode($assigned);
?>