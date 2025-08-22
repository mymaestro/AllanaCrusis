<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

$id_section = isset($_POST["id_section"]) ? intval($_POST["id_section"]) : 0;

ferror_log("Running select_sections.php with id=". $id_section);

if ($id_section > 0) {
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    $sql = "SELECT s.*, u.name AS leader_name, COUNT(spt.id_part_type) AS parttype_count
        FROM sections s
        LEFT JOIN users u ON s.section_leader = u.id_users
        LEFT JOIN section_part_types spt ON s.id_section = spt.id_section
        WHERE s.id_section = $id_section";

    ferror_log("Getting section detail for section " . $id_section);
    $res = mysqli_query($f_link, $sql);
    $output .= '
    <div class="table-responsive">
        <table class="table">';
    while($rowList = mysqli_fetch_array($res)) {
        $output .= '
            <tr>
                <td><h4 class="text-primary">'.$rowList["id_section"].'</h4></td>
                <td><h4 class="text-info">'.$rowList["name"].'</h4></td>
            </tr>
            <tr>
                <td><label>Description</label></td>
                <td>'.$rowList["description"].'</td>
            </tr>
            <tr>
                <td><label>Section leader</label></td>
                <td>'.$rowList["leader_name"].'</td>
            </tr>
            <tr>
                <td><label>Part types</label></td>
                <td>'.intval($rowList['parttype_count']).'</td>
            </tr>
            <tr>
                <td><label>Enabled</label></td>
                <td>'. (($rowList["enabled"] == 1) ? "Yes" : "No") .'</td>
            </tr>
            ';
    }
    $output .= '
        </table>
    </div>
    ';
    echo $output;
    ferror_log("Retrieved ".mysqli_num_rows($res)." rows for section " . $id_section);
    mysqli_close($f_link);
}
?>
