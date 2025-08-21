<?php  
 //fetch_recordings.php
require_once('config.php');
require_once('functions.php');

if(isset($_POST["user_role"])) {
    $u_librarian = (($_POST["user_role"] == 'librarian') !== FALSE ? TRUE : FALSE);
} else {
    $u_librarian = FALSE;
}

ferror_log("Running fetch_recordings.php");
ferror_log(print_r($_POST, true));

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(isset($_POST["id_recording"])) {  // EDIT
    // Fetch a specific recording for editing
    $id_recording = mysqli_real_escape_string($f_link, $_POST['id_recording']);
    ferror_log("Fetching recording with id=". $id_recording);
    $sql = "SELECT
        r.id_recording       AS id_recording,
        r.id_concert         AS id_concert,
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
    WHERE id_recording = ?";

    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_recording);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);

} elseif(isset($_POST["catalog_number"])) { // CHOOSE COMPOSITION
    // Get composition from the catalog number
    // Called ajax when user selects a composition in the edit form
    ferror_log("with catalog_number=". $_POST["catalog_number"]); 
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $sql = "SELECT catalog_number, composer, arranger FROM compositions WHERE catalog_number = '" . $catalog_number ."';";
    ferror_log("Getting catalog_number, composer, arranger data for catalog number: " . $catalog_number);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);

} elseif(isset($_POST["id_concert"]) && isset($_POST["catalog_number"])) {
    // Get the playgram item order for a specific concert and catalog number
    $id_concert = mysqli_real_escape_string($f_link, $_POST['id_concert']);
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    ferror_log("with id_concert=". $id_concert . " and catalog_number=". $catalog_number);

    $sql = "
    SELECT pi.comp_order
    FROM recordings r
    JOIN concerts c ON r.id_concert = c.id_concert
    JOIN playgram_items pi ON c.id_playgram = pi.id_playgram AND r.catalog_number = pi.catalog_number
    WHERE r.catalog_number = ? AND r.id_concert = ?
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $catalog_number, $id_concert);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row['comp_order'];
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);

    if (empty($orders)) {
        echo json_encode(['success' => true, 'orders' => [], 'message' => 'No order found for this catalog_number and concert.']);
    } elseif (count($orders) === 1) {
        echo json_encode(['success' => true, 'orders' => $orders, 'message' => 'One order found.']);
    } else {
        echo json_encode(['success' => true, 'orders' => $orders, 'message' => 'Multiple orders found.']);
    }

} else {
    // Fetch all recordings for display in the table
    echo '            <div class="panel panel-default">
        <div class="table-responsive scrolling-data">
                <table class="table table-hover tablesort tablesearch-table" id="cpdatatable">
                <caption class="title">Available recordings</caption>';
    echo '<thead class="thead-light" style="position: sticky; top: 0; z-index: 1;"><tr>
        <th style="width: 50px;"></th>
        <th data-tablesort-type="string">Composition <i class="fa fa-sort" aria-hidden="true"></i></th>
        <th data-tablesort-type="string">Ensemble description<i class="fa fa-sort" aria-hidden="true"></i></th>
        <th data-tablesort-type="date">Date <i class="fa fa-sort" aria-hidden="true"></i></th>
        <th data-tablesort-type="string">Composer <i class="fa fa-sort" aria-hidden="true"></i></th>
        <th data-tablesort-type="string">Arranger <i class="fa fa-sort" aria-hidden="true"></i></th>
        <th data-tablesort-type="string">Venue <i class="fa fa-sort" aria-hidden="true"></i></th>
        <th>Enabled</th>
      </tr></thead>
      <tbody>';

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
    ORDER BY con.performance_date DESC, r.id_recording; ";

    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));

    while ($rowList = mysqli_fetch_array($res)) {
        $id_recording     = $rowList["id_recording"];
        $catalog_number   = $rowList["catalog_number"];
        $composition_name = htmlspecialchars($rowList["composition_name"]);
        $name             = htmlspecialchars($rowList["name"]);
        $ensemble         = htmlspecialchars($rowList["ensemble"]);
        $id_ensemble      = $rowList["id_ensemble"];
        $composer         = htmlspecialchars($rowList["composer"]);
        $arranger         = htmlspecialchars($rowList["arranger"]);
        $date             = $rowList["date"];
        $venue            = htmlspecialchars($rowList["venue"]);
        $notes            = nl2br(htmlspecialchars($rowList["concert_notes"]));
        $link             = htmlspecialchars($rowList["link"]);
        $enabled          = $rowList["enabled"] ? "Yes" : "No";

        $the_name = $name ; 
        if ( $composition_name == "" ) {
            $the_name = $the_name . "*";
        };

        echo '<tr data-id="'. $id_recording .'">
        <td><input type="radio" name="record_select" value="'.$id_recording.'" class="form-check-input select-radio"></td>
        <td><strong><a href="#" class="view_data" name="view" data-id="'.$id_recording.'">'.$the_name.'</a></strong></td><!-- '.$composition_name.' -->
        <td>'.$ensemble.'</td>
        <td>'.$date.'</td>
        <td>'.$composer.'</td>
        <td>'.$arranger.'</td>
        <td>'.$venue.'</td>
        <td>'. (($enabled == 1) ? "Yes" : "No") .'</td>
        </tr>';
    }
    echo '
            </tbody>
            </table>
        </div><!-- table-responsive -->
    </div><!-- class panel -->
    ';
    ferror_log("Fetch recordings returned ".mysqli_num_rows($res). " rows.");
}
mysqli_close($f_link);
?>