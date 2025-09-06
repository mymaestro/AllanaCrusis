<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_parttypes.php with POST ". print_r($_POST, true));
if (isset($_POST["id_part_type"])) {
    $output = '
    <div class="table-responsive">
    <table class="table table-striped table-condensed">';

    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_part_type = mysqli_real_escape_string($f_link, $_POST["id_part_type"]);

    $sql = "SELECT t.id_part_type           'Part Type ID',
                   t.name                   'Name',
                   if(t.enabled = 1, 'Yes', 'No') 'Enabled',
                   t.collation              'Sort order',
                   t.family                 'Family',
                   t.description            'Description',
                   t.default_instrument     'Default instrument',
                   t.is_part_collection     '# instruments on this part'
            FROM   part_types t
            WHERE  id_part_type = '".$id_part_type."'";

    ferror_log("Getting details for part type with ID: ".$id_part_type);
    if ($res = mysqli_query($f_link, $sql)) {
        $col = 0;
        while ($fieldinfo = mysqli_fetch_field($res)) {
            $fields[$col] =  $fieldinfo -> name;
            $col++;
        }
        while ($rowList = mysqli_fetch_array($res, MYSQLI_NUM)) {
            for ($row = 0; $row < $col; $row++) {
                $output .= '<tr><td><strong>'. $fields[$row] . '</strong></td>';
                $output .= '<td id="'.$fields[$row].'-data">'. $rowList[$row] . '</td></tr>';
            }
        }
    }
    $output .= '
        </table>
    </div>
    ';
    echo $output;
    mysqli_close($f_link);
}
?>
