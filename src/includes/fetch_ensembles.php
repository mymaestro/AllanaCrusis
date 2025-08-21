<?php  
 //fetch_ensembles.php
require_once('config.php');
require_once('functions.php');
ferror_log("Running fetch_ensemble.php");

if(isset($_POST["user_role"])) {
    $u_librarian = (($_POST["user_role"] == 'librarian') !== FALSE ? TRUE : FALSE);
} else {
    $u_librarian = FALSE;
}

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (isset($_POST["id_ensemble"])) $id_ensemble = mysqli_escape_string($f_link, $_POST["id_ensemble"]);

if(isset($id_ensemble)) {
    $sql = "SELECT * FROM ensembles WHERE id_ensemble = '".$id_ensemble ."'";
    ferror_log("Fetching ensemble details for ID: " . $id_ensemble);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);  
    echo json_encode($rowList);
} else { // Show ensemble table
    echo '<div class="panel panel-default">
    <div class="table-responsive scrolling-data">
        <table class="table table-hover">
                <caption class="title">Available ensembles</caption>
                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th style="width: 50px;"></th>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Enabled</th>
                </tr>
                </thead>
                <tbody>';
    $sql = "SELECT * FROM ensembles ORDER BY id_ensemble;";
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $id_ensemble = $rowList['id_ensemble'];
        $title = $rowList['name'];
        $description = $rowList['description'];
        $link = $rowList['link'];
        $enabled = $rowList['enabled'];
        echo '<tr data-id="'.$id_ensemble.'">
                    <td><input type="radio" name="ensemble_select" value="'.$id_ensemble.'" class="form-check-input select-radio"></td>
                    <td>'.$id_ensemble.'</td>
                    <td><strong><a href="#" class="view_data" name="view" data-id="'.$id_ensemble.'">'.$title.'</strong></td>
                    <td>'.$description.'</td>
                    <td>'. (($enabled == 1) ? "Yes" : "No") .'</td>
                </tr>
                ';
    }
    echo '
                </tbody>
                </table>
            </div><!-- table-responsive -->
        </div><!-- class panel -->
       ';
    ferror_log("Fetch ensembles returned ".mysqli_num_rows($res). " rows.");
}
mysqli_close($f_link);
?>
