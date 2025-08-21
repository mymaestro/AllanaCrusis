<?php  
 //fetch_parttypes.php
require_once('config.php');
require_once('functions.php');
ferror_log("Running fetch_parttypes.php");

if(isset($_POST["user_role"])) {
    $u_librarian = (($_POST["user_role"] == 'librarian') !== FALSE ? TRUE : FALSE);
} else {
    $u_librarian = FALSE;
}

ferror_log(print_r($_POST, true));

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(isset($_POST["id_part_type"])) {
    $id_part_type = mysqli_real_escape_string($f_link, $_POST["id_part_type"]);
    $sql = "SELECT * FROM part_types WHERE id_part_type = '".$id_part_type."'";
    ferror_log("Fetching part type with ID: " . $id_part_type);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    $output = json_encode($rowList);
    ferror_log("JSON: " . $output);
    echo $output;
} else {
    echo '            <div class="panel panel-default">
           <div class="table-responsive scrolling-data">
                <table class="table table-hover">
                <caption class="title">Available part types</caption>
                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Instrument</th>
                    <th>Family</th>
                    <th>Description</th>
                    <th>Part collection</th>
                    <th>Enabled</th>
                </tr>
                </thead>
                <tbody>';
    $sql = "SELECT * FROM part_types ORDER BY collation;";
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $id_part_type = $rowList['id_part_type'];
        $collation = $rowList['collation'];
        $name = $rowList['name'];
        $family = $rowList['family'];
        $description = $rowList['description'];
        $default_instrument = $rowList['default_instrument'];
        $is_part_collection = $rowList['is_part_collection'];
        $enabled = $rowList['enabled'];
        echo '<tr data-id="'.$id_part_type.'" >
                    <td><input type="radio" name="part_type_select" value="'.$id_part_type.'" class="form-check-input select-radio"></td>
                    <td>'.$collation.'</td>
                    <td><strong><a href="#" class="view_data" name="view" data-id="'.$id_part_type.'">'.$name.'</a></strong></td>
                    <td class="instrument_'.$default_instrument.' text-muted">'.$default_instrument.'</td>
                    <td class="text-muted">'.$family.'</td>
                    <td>'.$description.'</td>
                    <td>'. (($is_part_collection > 0) ? "Yes" : "No") .'</td>
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
}
mysqli_close($f_link);
?>
