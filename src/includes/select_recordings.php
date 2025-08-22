<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_recordings.php with id=". $_POST["id_recording"]);
if (isset($_POST["id_recording"])) {
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_recording = mysqli_real_escape_string($f_link, $_POST["id_recording"]);
    $sql = "SELECT * FROM recordings WHERE id_recording = $id_recording";

    $sql = "SELECT
        r.id_recording       AS id_recording,
        c.catalog_number     AS catalog_number,
        c.name               AS composition_name,
        r.name               AS name,
        r.ensemble           AS ensemble,
        r.id_ensemble        AS id_ensemble,
        r.composer           AS composer,
        r.arranger           AS arranger,
        r.link               AS link,
        con.performance_date AS date,
        con.venue            AS venue,
        con.notes            AS concert_notes,
        r.enabled            AS enabled
    FROM recordings r
    LEFT JOIN compositions c ON r.catalog_number = c.catalog_number
    LEFT JOIN concerts con   ON r.id_concert = con.id_concert
    WHERE r.id_recording = $id_recording
    ORDER BY con.performance_date DESC, r.id_recording; ";

    ferror_log("Getting details for recording with ID: ".$id_recording);
    $res = mysqli_query($f_link, $sql);
    $output .= '
    <div class="table-responsive">
        <table class="table">';
    while($rowList = mysqli_fetch_array($res)) {
        $link = $rowList["link"];
        $date = $rowList["date"];
        $output .= '
            <tr>
                <td><h4 class="text-primary">'.$rowList["id_recording"].'</h4></td>
                <td><h4 class="text-info">(' . $rowList["catalog_number"] . ') '.$rowList["name"].'</h4></td>
            </tr>
            <tr>
                <td><label>Play</label></td>
                <td><audio controls>
			<source src="'. ORGRECORDINGS . $date. '/'.$link . '">
                    Your browser does not support the audio element.
                    </audio></td>
            </tr>
            <tr>
                <td><label>Ensemble</label></td>
                <td>'.$rowList["ensemble"] .'</td>
            </tr>
            <tr>
                <td><label>File name</label></td>
                <td>'.$link.'</td>
            </tr>
            <tr>
                <td><label>Information</label></td>
                <td>'.$rowList["concert_notes"] .'</td>
            </tr>
            <tr>
                <td><label>Composer</label></td>
                <td>'.$rowList["composer"] .'</td>
            </tr>
            <tr>
                <td><label>Arranger</label></td>
                <td>'.$rowList["arranger"] .'</td>
            </tr>
            <tr>
                <td><label>Date</label></td>
                <td>'.$rowList["date"] .'</td>
            </tr>
            <tr>
                <td><label>Venue</label></td>
                <td>'.$rowList["venue"] .'</td>
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
