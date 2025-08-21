<?php
require_once('config.php');
require_once('functions.php');
ferror_log("Running select_ensembles.php with id=". $_POST["id_ensemble"]);
if (isset($_POST["id_ensemble"])) {
    $output = '
    <div class="table-responsive">
    <table class="table table-striped table-condensed">';
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_ensemble = mysqli_real_escape_string($f_link, $_POST["id_ensemble"]);
    $sql = "SELECT e.id_ensemble        'Ensemble ID',
                   e.name               'Name',
                   if(e.enabled = 1, 'Yes', 'No') 'Enabled',
                   e.description        'Description',
                   e.link               'URL'
            FROM   ensembles e
            WHERE  e.id_ensemble = '".$id_ensemble."'";

    ferror_log("Getting details for ensemble with id: ".$id_ensemble);
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
