<?php  
/* Called by parts.php to display parts table */
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

if(isset($_POST["user_role"])) {
    $u_librarian = (($_POST["user_role"] == 'librarian') !== FALSE ? TRUE : FALSE);
} else {
    $u_librarian = FALSE;
}
ferror_log("Running fetch_parts.php");
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(isset($_POST['id_part_type']) && (isset($_POST['catalog_number']))) { // User selects edit part
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $id_part_type = mysqli_real_escape_string($f_link, $_POST['id_part_type']);
    $sql = "SELECT  p.catalog_number,
                    p.id_part_type,
                    p.name,
                    p.description,
                    t.default_instrument,
                    p.is_part_collection,
                    p.paper_size,
                    p.page_count,
                    p.image_path,
                    p.originals_count,
                    p.copies_count,
                    p.last_update
            FROM    parts p
            JOIN    part_types t  ON p.id_part_type = t.id_part_type
            WHERE   p.catalog_number = '" . $catalog_number . "' AND p.id_part_type = " . $id_part_type .";";
    ferror_log("Get part details for catalog number: " . $catalog_number . " and part type: " . $id_part_type);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    $part_data =  json_encode($rowList);

    $sql = "SELECT * FROM part_collections WHERE catalog_number_key = '" . $catalog_number . "' AND id_part_type_key = " . $id_part_type .";";
    ferror_log("SQL: ". $sql);
    $instrument_data = array();
    $res = mysqli_query($f_link, $sql);
    while($rowList = mysqli_fetch_array($res)) {
        $instrument_data[] = $rowList;
    }
    
    $instrument_list = json_encode($instrument_data);

    $return = json_encode('{"part":' . $part_data . ',"instruments":' . $instrument_list . "}");
    ferror_log("Returning JSON for part: " . $catalog_number . " / " . $id_part_type);
    echo $return;

} elseif (isset($_POST['catalog_number'])) { // Create a table of parts for this catalog number, user selected a composition to see its parts
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    ferror_log("Get parts for catalog number " . $catalog_number);
    echo '<div class="panel"><div class="row border-bottom">';
    echo '<div class="col-auto"><div class="bg-white"><h4 id="composition_header">Composition parts</h4></div></div>';
    if ($u_librarian) { echo '<div class="col-auto"><form method="post" id="instr_data_'.$catalog_number.'" action="composition_instrumentation.php"><input type="hidden" name="catalog_number" value="'.$catalog_number.'" /><input type="submit" name="compositions" value="Instrumentation" id="'.$catalog_number.'" class="btn btn-warning instr_data" />
        </form></div>';
        }
    echo '</div><!-- row -->
    <div class="row">
    <div class="col-12 bg-white vh-100 overflow-auto h-auto">
    <div class="table-responsive">
         <table class="table table-hover">
         <caption class="title">Parts for '.$catalog_number.'</caption>
         <thead>
         <tr>
             <th>Part type</th>
             <th>Name</th>
             <th>Description</th>
             <th>Paper size</th>
             <th>Pages</th>
             <th>Originals</th>
             <th>Copies</th>
             <th>Instruments</th>
         </tr>
         </thead>
         <tbody>';
    $sql = "SELECT  p.catalog_number,
                    p.id_part_type,
                    y.name part_type,
                    y.collation,
                    p.name,
                    p.description,
                    p.is_part_collection,
                    p.paper_size,
                    z.name paper,
                    p.page_count,
                    p.image_path,
                    p.originals_count,
                    p.copies_count,
                    COUNT(c.id_instrument_key) instruments
            FROM   parts p
            LEFT JOIN  part_collections c 
            ON c.catalog_number_key = p.catalog_number
            AND c.id_part_type_key = p.id_part_type
            JOIN   part_types y
            ON     y.id_part_type = p.id_part_type
            JOIN   paper_sizes z
            ON     z.id_paper_size = p.paper_size
            WHERE  p.catalog_number = '".$catalog_number ."'
            GROUP BY p.catalog_number, p.id_part_type
            ORDER BY y.collation;";
    ferror_log("Retrieving parts data for catalog number: " . $catalog_number);
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $catalog_number = $rowList['catalog_number'];
        $id_part_type = $rowList['id_part_type'];
        $part_type = $rowList['part_type'];
        $is_part_collection = $rowList['is_part_collection'];
        $instruments_count = $rowList['instruments'];
        $paper = $rowList['paper'];
        $page_count = $rowList['page_count'];
        $name = $rowList['name'];
        $description = $rowList['description'];
        $originals_count = $rowList['originals_count'];
        $copies_count = $rowList['copies_count'];
        echo '<tr>
        <td>'.$part_type.'</td>
        <td>'.$name.'</td>
        <td>'.$description.'</td>
        <td>'.$paper.'</td>
        <td>'.$page_count.'</td>
        <td>'.$originals_count.'</td>
        <td>'.$copies_count.'</td>
        <td>'.$instruments_count .'</td>';
        if ($u_librarian) {
    echo '
            <td><input type="button" name="delete" value="Delete" id="' . $catalog_number . '-' . $id_part_type . '" class="btn btn-danger btn-sm delete_data" /></td>
            <td><input type="button" name="edit" value="Edit" id="' . $catalog_number . '-' . $id_part_type . '" class="btn btn-primary btn-sm edit_data" /></td>';
    }
    echo '
            <td><input type="button" name="view" value="View" id="' . $catalog_number . '-' . $id_part_type  . '" class="btn btn-secondary btn-sm view_data" /></td>
        </tr>
        ';
    }
    echo '
        </tbody>
    </table>
    </div><!-- table-responsive -->
    </div>
    </div>
    </div>
    </div>
    ';
} elseif (isset($_POST['not_catalog_number'])) {
    ferror_log("You don't belong here.");
    echo '
    <div class="panel panel-default">
    <div class="table-responsive">
        <table class="table table-hover tablesort" id="cpdatatable">
        <caption class="title">Available compositions</caption>
        <thead>
        <tr>
            <th data-tablesort-type="string">Catalog number <i class="fa fa-sort" aria-hidden="true"></i></th>
            <th data-tablesort-type="string">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
            <th data-tablesort-type="string">Composer <i class="fa fa-sort" aria-hidden="true"></i></th>
            <th data-tablesort-type="string">Arranger <i class="fa fa-sort" aria-hidden="true"></i></th>
            <th data-tablesort-type="number">Parts <i class="fa fa-sort" aria-hidden="true"></i></th>
        </tr>
        </thead>
        <tbody>';
    $sql = "SELECT p.catalog_number,
                   c.name title,
                   c.composer composer,
                   c.arranger arranger,
                   COUNT(*) parts
            FROM   parts p
            JOIN   compositions c
            ON     p.catalog_number = c.catalog_number
            GROUP BY p.catalog_number;";
    ferror_log("Fetching parts count data for all compositions");
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $catalog_number = $rowList['catalog_number'];
        $name = $rowList['title'];
        $composer = $rowList['composer'];
        $arranger = $rowList['arranger'];
        $partscount = $rowList['parts'];
        echo '<tr>
        <td>'.$catalog_number.'</td>
        <td>'.$name.'</td>
        <td>'.$composer.'</td>
        <td>'.$arranger.'</td>';
        if ( $partscount > 0 ) {
        echo '
                <td><button type="button" name="parts_data" id="catno_'.$catalog_number.'" class="btn btn-info btn-sm parts_data">'. $partscount.' parts</button></td>';
        } else {
        echo '
                <td class="text-muted">0 parts</td>';
        }
        if ($u_librarian) { echo '
            <td><form method="post" id="instr_data_'.$catalog_number.'" action="composition_instrumentation.php"><input type="hidden" name="catalog_number" value="'.$catalog_number.'" />
            <input type="submit" name="compositions" value="Instrumentation" id="'.$catalog_number.'" class="btn btn-warning btn-sm instr_data" /></form></td>'.PHP_EOL;
        }
        echo '
            </tr>
            ';
    }
    echo '
        </tbody>
        </table>
    </div><!-- table-responsive -->
    </div><!-- class panel -->' . PHP_EOL;
} else { // Get parts to list on the menu by composition
    $sql = "SELECT p.catalog_number,
                   c.name title,
                   c.composer composer,
                   c.arranger arranger,
                   COUNT(*) parts
            FROM   parts p
            JOIN   compositions c
            ON     p.catalog_number = c.catalog_number
            GROUP BY p.catalog_number;";
    ferror_log("Fetching parts count data for all compositions");
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $catalog_number = $rowList['catalog_number'];
        $name = $rowList['title'];
        $composer = $rowList['composer'];
        $arranger = $rowList['arranger'];
        $partscount = $rowList['parts'];
        echo '<a href="#" class="list-group-item list-group-item-action" name="parts_data" id="catno_'.$catalog_number.'">'.$catalog_number.': <strong>'.$name.'</strong></br> <span class="text-muted">'.$composer;
        if (!empty($arranger)) {
            echo ' arr. ' .$arranger;
        }
        echo ' ('.$partscount.' parts)</span></a>';
echo '
    </div>
    </div>
    ';
}
echo '
</div><!-- table-responsive -->
</div><!-- class panel -->
';

}
mysqli_close($f_link);
?>
