<?php
//fetch_playgram_items.php
// This script fetches the playgram items for a given concert ID and returns them as HTML
// It expects a POST request with 'id_concert' parameter
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

if (isset($_POST['id_concert'])) {
    $id_concert = intval($_POST['id_concert']);
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Get the playgram for this concert
    $sql = "SELECT id_playgram FROM concerts WHERE id_concert = ? LIMIT 1";
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_concert);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $id_playgram = $row ? intval($row['id_playgram']) : 0;
    mysqli_stmt_close($stmt);

    $options = "";
    if ($id_playgram) {
        // Get catalog_numbers from playgram_items for this playgram
        $sql = "SELECT pi.catalog_number, c.name, c.composer, c.arranger
                FROM playgram_items pi
                JOIN compositions c ON pi.catalog_number = c.catalog_number
                WHERE pi.id_playgram = ?
                ORDER BY c.name";
        $stmt2 = mysqli_prepare($f_link, $sql);
        mysqli_stmt_bind_param($stmt2, "i", $id_playgram);
        mysqli_stmt_execute($stmt2);
        $res = mysqli_stmt_get_result($stmt2);
        while ($row = mysqli_fetch_assoc($res)) {
            $comp_catno = htmlspecialchars($row['catalog_number']);
            $comp_name = htmlspecialchars($row['name']);
            $comp_composer = htmlspecialchars($row['composer']);
            $comp_arranger = htmlspecialchars($row['arranger']);
            $comp_display = $comp_name . " - " . $comp_catno;
            if ($comp_composer || $comp_arranger) $comp_display .= ' (';
            if ($comp_composer && $comp_arranger) $comp_display .= $comp_composer . ", arr. " . $comp_arranger . ")";
            if (!$comp_composer && $comp_arranger) $comp_display .= "arr. " . $comp_arranger . ")";
            if ($comp_composer && !$comp_arranger) $comp_display .= $comp_composer . ")";
            $options .= "<option value='$comp_catno'>$comp_display</option>";
        }
        mysqli_stmt_close($stmt2);
    } else {
        $options = "<option value=''>No playgram items found for this concert</option>";
    }
    echo $options;
    mysqli_close($f_link);
}
?>