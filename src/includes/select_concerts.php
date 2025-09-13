<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_concerts.php with POST ". print_r($_POST, true));
if (isset($_POST["id_concert"])) {
    $output = '
    <div class="table-responsive">
    <table class="table table-striped table-condensed">';
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_concert = mysqli_real_escape_string($f_link, $_POST['id_concert']);
    $sql = "SELECT c.id_concert 'Concert ID',
                   c.performance_date 'Date',
                   c.venue 'Venue',
                   c.conductor 'Conductor',
                   c.notes 'Description'
            FROM   concerts c
            WHERE  c.id_concert = '$id_concert'";
    ferror_log("Getting details for concert with id: ".$id_concert);
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
