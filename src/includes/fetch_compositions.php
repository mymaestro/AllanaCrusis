<?php  
 //fetch_compositions.php
require_once(__DIR__ . "/../../config/config.php");
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
        <style>
        .filter-header {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6;
        }

        .filter-header th {
            background-color: #f8f9fa !important;
        }

        /* Ensure sticky headers have no gaps */
        .thead-light tr:first-child th {
            border-bottom: none !important;
        }
        
        .filter-header th {
            border-top: none !important;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .filter-input {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            width: 100%;
            min-width: 80px;
        }

        .filter-input:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .filter-select {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            width: 100%;
            min-width: 80px;
        }

        .no-results-message {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }
        </style>
        
        <div class="panel panel-default">
            <div class="table-responsive scrolling-data">
                <table class="table table-hover tablesort" id="cpdatatable">
                <caption class="title">Available compositions</caption>
                <thead class="thead-light">
                <tr style="position: sticky; top: 0; z-index: 3; background-color: #e9ecef; height: 40px;">
                    <th style="width: 50px; border-bottom: none; padding: 0.5rem;"></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Catalog no. <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Composer <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Arranger <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="number" style="border-bottom: none; padding: 0.5rem;">Grade <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Genre <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Ensemble <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string" style="border-bottom: none; padding: 0.5rem;">Enabled <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="number" style="border-bottom: none; padding: 0.5rem;">Parts <i class="fa fa-sort" aria-hidden="true"></i></th>
                </tr>
                
                <!-- Filter Row -->
                <tr class="filter-header" style="position: sticky; top: 40px; z-index: 2; background-color: #f8f9fa; height: 40px;">
                    <th style="width: 50px; border-top: none; padding: 0.5rem;">
                        <a href="#" onclick="clearAllFilters(); return false;" class="text-muted" style="text-decoration: none; font-size: 0.875rem;" title="Click to clear all filters">
                            <i class="fas fa-eraser"></i> Clear
                        </a>
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="catalog" placeholder="M001..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="name" placeholder="Title..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="composer" placeholder="Composer..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="arranger" placeholder="Arranger..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <select class="filter-select" data-column="grade">
                            <option value="">All</option>
                            <option value="0.0">0.0</option>
                            <option value="0.5">0.5</option>
                            <option value="1.0">1.0</option>
                            <option value="1.5">1.5</option>
                            <option value="2.0">2.0</option>
                            <option value="2.5">2.5</option>
                            <option value="3.0">3.0</option>
                            <option value="3.5">3.5</option>
                            <option value="4.0">4.0</option>
                            <option value="4.5">4.5</option>
                            <option value="5.0">5.0</option>
                            <option value="5.5">5.5</option>
                            <option value="6.0">6.0</option>
                        </select>
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="genre" placeholder="Genre..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="text" class="filter-input" data-column="ensemble" placeholder="Ensemble..." />
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <select class="filter-select" data-column="enabled">
                            <option value="">All</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </th>
                    <th style="border-top: none; padding: 0.5rem;">
                        <input type="number" class="filter-input" data-column="parts" placeholder="0+" min="0" />
                    </th>
                </tr>
                </thead>
                <tbody id="compositions-tbody">';

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
                
                <!-- No results message -->
                <div id="no-results" class="no-results-message" style="display: none;">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <p>No compositions match your current filters</p>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearAllFilters()">
                        Clear Filters
                    </button>
                </div>
            </div><!-- table-responsive -->
        </div><!-- class panel -->
       ';
    ferror_log("Fetch compositions returned ".mysqli_num_rows($res). " rows.");

}
mysqli_close($f_link);
?>