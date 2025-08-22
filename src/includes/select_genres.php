<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_genres.php with id=". $_POST["id_genre"]);
if (isset($_POST["id_genre"])) {
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_genre = mysqli_real_escape_string($f_link, $_POST["id_genre"]);
    $sql = "SELECT * FROM genres WHERE id_genre = '".$id_genre."'";
    ferror_log("Getting details for genre with id: ".$id_genre);
    $res = mysqli_query($f_link, $sql);
    $output .= '
    <div class="table-responsive">
        <table class="table">';
    while($rowList = mysqli_fetch_array($res)) {
        $output .= '
            <tr>
                <td><h4 class="text-primary">'.$rowList["id_genre"].'</h4></td>
                <td><h4 class="text-info">'.$rowList["name"].'</h4></td>
            </tr>
            <tr>
                <td><label>Description</label></td>
                <td>'.$rowList["description"].'</td>
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
    mysqli_close($f_link);
}
?>
