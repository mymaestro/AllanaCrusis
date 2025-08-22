<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

if(isset($_POST["user_role"])) {
    $u_librarian = (($_POST["user_role"] == 'librarian') !== FALSE ? TRUE : FALSE);
} else {
    $u_librarian = FALSE;
}

ferror_log("RUNNING fetch_instruments_table.php");

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(isset($_POST["id_instrument"])) {
    $id_instrument = mysqli_escape_string($f_link, $_POST["id_instrument"]);
    $sql = "SELECT * FROM instruments WHERE id_instrument = '".$id_instrument."'";
    ferror_log("Getting details for instrument ID: " . $id_instrument);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
} elseif(isset($_POST["instrument_list"])) {
    $sql = "SELECT `id_instrument`, `name` FROM instruments WHERE `enabled` = 1 ORDER BY collation;";
    ferror_log("Fetching instrument list");
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    $opt = "<select class='form-select form-control' aria-label='Select instrument' id='default_instrument' name='default_instrument'>";
    while($rowList = mysqli_fetch_array($res)) {
        $id_instrument = $rowList['id_instrument'];
        $instrument_name = $rowList['name'];
        $opt .= '
        <option value="'.$id_instrument.'">'.$instrument_name.'</option>';
    }
    $opt .= '
    </select>';
    mysqli_close($f_link);
    echo $opt;
} else { 
    echo '            <div class="panel panel-default">
               <div class="table-responsive scrolling-data">
                    <table class="table table-hover">
                    <caption class="title">Available instruments</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Score order</th>
                        <th>Name</th>
                        <th>Family</th>
                        <th>Description</th>
                        <th>Enabled</th>
                    </tr>
                    </thead>
                    <tbody>';
    $sql = "SELECT * FROM instruments ORDER BY collation;";
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $id_instrument = $rowList['id_instrument'];
        $collation = $rowList['collation'];
        $name = $rowList['name'];
        $family = $rowList['family'];
        $description = $rowList['description'];
        $enabled = $rowList['enabled'];
        echo '<tr data-id="'.$id_instrument.'">
                    <td><input type="radio" name="instrument_select" value="'.$id_instrument.'" class="form-check-input select-radio"></td>
                    <td>'.$collation.'</td>
                    <td><strong><a href="#" class="view_data" data-id="'.$id_instrument.'">'.$name.'</a></strong></td>
                    <td>'.$family.'</td>
                    <td>'.$description.'</td>
                    <td>'. (($enabled == 1) ? "Yes" : "No") .'</td>
                </tr>
                ';
    }
    echo '
                </tbody>
                </table>
            </div><!-- table-responsive -->
        </div><!-- panel -->
        ';
    ferror_log("Fetch instruments returned ".mysqli_num_rows($res). " rows.");
}
mysqli_close($f_link);
?>