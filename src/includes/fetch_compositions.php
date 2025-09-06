<?php  
 //fetch_compositions.php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running fetch_compositions.php");

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(isset($_POST["catalog_number"])) {
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $sql = "SELECT * FROM compositions WHERE catalog_number = '".$catalog_number."'";
    ferror_log("Fetching composition details for catalog number: " . $catalog_number);
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    echo json_encode($rowList);
} else {
    // Log the search request
    if (isset($_POST["submitButton"])) {
        ferror_log("SEARCH_BUTTON:".$_POST["submitButton"]);
    }
    if(isset($_POST["search"])) {
        ferror_log("SEARCH: ". $_POST["search"]);
    }
    echo '
        <div class="panel panel-default">
            <div class="table-responsive scrolling-data">
                <table class="table table-hover tablesort" id="cpdatatable">
                <caption class="title">Available compositions</caption>
                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th style="width: 50px;"></th>
                    <th data-tablesort-type="string">Catalog no. <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Composer <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Arranger <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="number">Grade <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Genre <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Ensemble <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Enabled <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="number">Parts <i class="fa fa-sort" aria-hidden="true"></i></th>
                </tr>
                </thead>
                <tbody>';

    if (isset($_POST["submitButton"])) {
        $jcode = array("[","]");
        $pcode = array("(",")");
        $extra_search = "";
        if(!empty($_POST["search"])){
            $search = mysqli_real_escape_string($f_link, $_POST['search']);
            $extra_search .= " MATCH(c.name, c.description, c.composer, c.arranger, c.comments)
            AGAINST( '".$search."' IN NATURAL LANGUAGE MODE)";
        }
        if(!empty($_POST["ensemble"])){
            $result = str_replace($jcode, $pcode, mysqli_real_escape_string($f_link, $_POST['ensemble']));
            $result = str_replace('\"', "'", $result);
            ferror_log("ENSEMBLE: ". $result);
            if (!empty($extra_search)) { $extra_search .= " AND ";}
            $extra_search .= " ensemble in ".$result;
        } else ferror_log("No ensemble");
        if(!empty($_POST["genre"])){
            $result = str_replace($jcode, $pcode, mysqli_real_escape_string($f_link, $_POST['genre']));
            $result = str_replace('\"', "'", $result);
            if (!empty($extra_search)) { $extra_search .= " AND ";}
            ferror_log("GENRE: ". $result);
            $extra_search .= " AND genre in ".$result;
        } else ferror_log("No genre");
        ferror_log("Search: ".$extra_search);
        foreach ($_POST as $key => $value)
            ferror_log($key.'='.$value);
        if (!empty($extra_search)) {
            $extra_search = " WHERE " . $extra_search;
        }
        $sql = "SELECT c.catalog_number,
                       c.name,
                       c.description,
                       c.composer,
                       c.arranger,
                       c.grade,
                       g.name genre,
                       COUNT(p.id_part_type) as parts,
                       e.name ensemble,
                       c.enabled
                FROM   compositions c
                JOIN   genres g      
                ON     c.genre = g.id_genre
                JOIN   ensembles e   
                ON     c.ensemble = e.id_ensemble
                LEFT OUTER JOIN parts p 
                ON  c.catalog_number = p.catalog_number
                ". $extra_search ."
                GROUP BY c.catalog_number
                ORDER BY c.catalog_number;";
                /*
                MATCH(c.name, c.description, c.composer, c.arranger, c.comments)
                AGAINST( '".$search."' IN NATURAL LANGUAGE MODE)
                                AND ensemble IN ('TC','CC') */
    } else {
        $sql = "SELECT c.catalog_number,
                       c.name,
                       c.description,
                       c.composer,
                       c.arranger,
                       g.name genre,
                       COUNT(p.id_part_type) as parts,
                       e.name ensemble,
                       c.grade,
                       c.enabled
                FROM   compositions c
                JOIN   genres g
                ON     c.genre = g.id_genre
                JOIN   ensembles e
                ON     c.ensemble = e.id_ensemble
                LEFT OUTER JOIN parts p
                ON     c.catalog_number = p.catalog_number
                GROUP  BY c.catalog_number
                ORDER BY c.last_update DESC;";
    }
    ferror_log("Running search SQL: " .trim(preg_replace('/\s+/', ' ', $sql)));
    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
    while ($rowList = mysqli_fetch_array($res)) {
        $catalog_number = $rowList['catalog_number'];
        $name = $rowList['name'];
        $description = $rowList['description'];
        $composer = $rowList['composer'];
        $arranger = $rowList['arranger'];
        $genre = $rowList['genre'];
        $grade = $rowList['grade'];
        $partscount = $rowList['parts'];
        $ensemble = $rowList['ensemble'];
        $enabled = $rowList['enabled'];
        if ($partscount == NULL) {
            $partscount = 0;
        } else {
            $partscount = intval($partscount);
        }
        if ($partscount > 0) {
            $partscountclass = "table-success";
        } else {
            $partscountclass = "table-secondary";
        }
        echo '<tr data-id="'.$catalog_number.'">
                    <td><input type="radio" name="composition_select" value="'.$catalog_number.'" class="form-check-input select-radio"></td>
                    <td>'.$catalog_number.'</td>
                    <td><strong><a href="#" class="view_data" data-id="'.$catalog_number.'">'.$name.'</a></strong></td>
                    <td>'.$composer.'</td>
                    <td>'.$arranger.'</td>
                    <!-- DESCRIPTION: '.$description.'-->
                    <td>'.$grade.'</td>
                    <td>'.$genre.'</td>
                    <td>'.$ensemble.'</td>
                    <td>'. (($enabled == 1) ? "Yes" : "No") .'</td>
                    <td class="'.$partscountclass.'">'.$partscount.'</td>
                </tr>
                ';
    }
    echo '
                </tbody>
                </table>
            </div><!-- table-responsive -->
        </div><!-- class panel -->
       ';
    ferror_log("Fetch compositions returned ".mysqli_num_rows($res). " rows.");

}
mysqli_close($f_link);
?>