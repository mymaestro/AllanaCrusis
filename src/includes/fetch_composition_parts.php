<?php  
 // index.php?action=fetch_composition_parts
 // Get parts array for composition_instrumentation.php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running fetch_composition_parts.php with id=". $_POST["catalog_number"]);

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);

if(isset($catalog_number)) {
    $sql = "SELECT catalog_number, id_part_type FROM parts WHERE catalog_number = '" . $catalog_number ."';";
    ferror_log("Fetching compositions for catalog number: " . $catalog_number);
    $res = mysqli_query($f_link, $sql);
    $counter = 0;
    $jsondata = "{";
    while($rowList = mysqli_fetch_array($res)) {
        $counter += 1;
        $part_type = $rowList['id_part_type'];
        $jsondata .= '"'.$counter.'": "'. $part_type . '",';
    }
    $jsondata = rtrim($jsondata, ',');
    $jsondata .= '}'.PHP_EOL;
    echo $jsondata;
    ferror_log("Fetch composition_parts returned ".mysqli_num_rows($res). " rows.");

}
mysqli_close($f_link);
?>
