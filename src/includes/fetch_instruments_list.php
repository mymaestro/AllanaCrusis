<?php
// index.php?action=fetch_instruments_list
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("RUNNING fetch_instruments_list.php");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT id_instrument, name, family FROM instruments WHERE enabled = 1 ORDER BY collation, name";
$res = mysqli_query($f_link, $sql);
$instruments = [];
while ($row = mysqli_fetch_assoc($res)) {
    $instruments[] = $row;
}
ferror_log("Fetch instruments list returned ".mysqli_num_rows($res). " rows.");
mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode($instruments);
?>