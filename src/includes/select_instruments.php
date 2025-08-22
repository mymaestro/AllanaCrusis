<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_instruments.php with id=". $_POST["id_instrument"]);
if (isset($_POST["id_instrument"])) {
    $output = '
    <div class="table-responsive">
    <table class="table table-striped table-condensed">';
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_instrument = mysqli_real_escape_string($f_link, $_POST["id_instrument"]);

    $sql = "SELECT i.id_instrument       'Instrument ID',
                   if(i.enabled = 1, 'Yes', 'No') 'Enabled',
                   i.collation           'Sort order',
                   i.name                'Name',
                   i.family              'Family',
                   i.description         'Description'
            FROM   instruments i
            WHERE  i.id_instrument = '".$id_instrument."'";

    ferror_log("Getting details for instrument with id: ".$id_instrument);
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
