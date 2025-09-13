<?php  
/* Called by parts.php to fetch parts data as JSON */
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

header('Content-Type: application/json');

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$f_link) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if(isset($_POST['id_part_type']) && (isset($_POST['catalog_number']))) { 
    // User selects edit part - keep existing logic for editing
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $id_part_type = mysqli_real_escape_string($f_link, $_POST['id_part_type']);
    ferror_log("Edit part:". $catalog_number . " / " . $id_part_type);

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
    $res = mysqli_query($f_link, $sql);
    $rowList = mysqli_fetch_array($res);
    $part_data =  json_encode($rowList);
    ferror_log("Part data retrieved for catalog number: " . $catalog_number . " and part type: " . $id_part_type);
    $sql = "SELECT * FROM part_collections WHERE catalog_number_key = '" . $catalog_number . "' AND id_part_type_key = " . $id_part_type .";";
    ferror_log("Getting details for part collections for catalog number: " . $catalog_number . " and part type: " . $id_part_type);
    $instrument_data = array();
    $res = mysqli_query($f_link, $sql);
    while($rowList = mysqli_fetch_array($res)) {
        $instrument_data[] = $rowList;
    }
    
    $instrument_list = json_encode($instrument_data);
    $return = json_encode('{"part":' . $part_data . ',"instruments":' . $instrument_list . "}");
    echo $return;

} elseif (isset($_POST['catalog_number'])) { 
    // Get parts data for a specific catalog number
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    ferror_log("Get parts for catalog number " . $catalog_number);

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
                    CASE 
                        WHEN p.image_path IS NULL OR p.image_path = '' THEN 'No'
                        ELSE 'Yes'
                    END AS image_path,
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

    $res = mysqli_query($f_link, $sql);
    if (!$res) {
        echo json_encode(['error' => 'Parts query failed: ' . mysqli_error($f_link)]);
        exit;
    }
    $parts = array();
    while ($rowList = mysqli_fetch_array($res)) {
        $parts[] = $rowList;
    }
    
    echo json_encode([
        'catalog_number' => $catalog_number,
        'parts' => $parts
    ]);

} elseif (isset($_POST['action']) && $_POST['action'] == 'compositions_table') {
    // Get compositions table data - show ALL compositions, not just those with parts
    ferror_log("Fetching compositions table data.");
    $sql = "SELECT c.catalog_number,
                   c.name title,
                   c.composer composer,
                   c.arranger arranger,
                   COALESCE(COUNT(p.catalog_number), 0) parts
            FROM   compositions c
            LEFT JOIN parts p
            ON     c.catalog_number = p.catalog_number
            GROUP BY c.catalog_number, c.name, c.composer, c.arranger
            ORDER BY c.catalog_number;";
    
    $res = mysqli_query($f_link, $sql);
    if (!$res) {
        echo json_encode(['error' => 'Compositions table query failed: ' . mysqli_error($f_link)]);
        exit;
    }
    $compositions = array();
    while ($rowList = mysqli_fetch_array($res)) {
        $compositions[] = $rowList;
    }
    
    echo json_encode([
        'compositions' => $compositions
    ]);

} else { 
    // Get compositions list for menu - show ALL compositions, not just those with parts
    $sql = "SELECT c.catalog_number,
                   c.name title,
                   c.composer composer,
                   c.arranger arranger,
                   COALESCE(COUNT(p.catalog_number), 0) parts
            FROM   compositions c
            LEFT JOIN parts p
            ON     c.catalog_number = p.catalog_number
            GROUP BY c.catalog_number, c.name, c.composer, c.arranger
            ORDER BY c.name;";
    ferror_log("Fetching compositions list for menu.");

    $res = mysqli_query($f_link, $sql);
    if (!$res) {
        echo json_encode(['error' => 'Compositions list query failed: ' . mysqli_error($f_link)]);
        exit;
    }
    $compositions = array();
    while ($rowList = mysqli_fetch_array($res)) {
        $compositions[] = $rowList;
    }
    
    echo json_encode([
        'compositions' => $compositions
    ]);
}

mysqli_close($f_link);
?>
