<?php
require_once('config.php');
require_once('functions.php');

// Get search parameters
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$genre = isset($_POST['genre']) ? $_POST['genre'] : 0;
$ensemble = isset($_POST['ensemble']) ? $_POST['ensemble'] : '';
$grade = isset($_POST['grade']) ? floatval($_POST['grade']) : 0;
$duration = isset($_POST['duration']) ? $_POST['duration'] : '';
$composer = isset($_POST['composer']) ? trim($_POST['composer']) : '';
$random = isset($_POST['random']) ? $_POST['random'] : 0;

// Build SQL query
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Handle random composition request
if ($random == 1) {
    $sql = "SELECT c.catalog_number, c.name, c.composer, c.arranger, c.duration, c.grade, 
                   g.name AS genre_name, c.genre, e.name AS ensemble_name, c.ensemble,
                   COUNT(p.catalog_number) AS parts_count
            FROM compositions c 
            LEFT JOIN genres g ON c.genre = g.id_genre 
            LEFT JOIN ensembles e ON c.ensemble = e.id_ensemble
            LEFT JOIN parts p ON c.catalog_number = p.catalog_number
            WHERE c.enabled = 1
            GROUP BY c.catalog_number
            ORDER BY RAND() LIMIT 1";
    
    ferror_log("Picking a random composition.");
    
    $res = mysqli_query($f_link, $sql);
    $results = [];
    
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $results[] = $row;
        }
    }
    
    mysqli_close($f_link);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}

$sql = "SELECT c.catalog_number, c.name, c.composer, c.arranger, c.duration, c.grade, 
               g.name AS genre_name, c.genre, e.name AS ensemble_name, c.ensemble,
               COUNT(p.catalog_number) AS parts_count
        FROM compositions c 
        LEFT JOIN genres g ON c.genre = g.id_genre 
        LEFT JOIN ensembles e ON c.ensemble = e.id_ensemble
        LEFT JOIN parts p ON c.catalog_number = p.catalog_number
        WHERE c.enabled = 1";

// Add search text filter
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($f_link, $search);
    $sql .= " AND (c.name LIKE '%$search_escaped%' 
                   OR c.composer LIKE '%$search_escaped%' 
                   OR c.arranger LIKE '%$search_escaped%'
                   OR c.catalog_number LIKE '%$search_escaped%')";
}

// Add genre filter
if ($genre > 0) {
    $sql .= " AND c.genre = '" . mysqli_real_escape_string($f_link, $genre) . "'";
}

// Add ensemble filter  
if ($ensemble > 0) {
    $sql .= " AND c.ensemble = '" . mysqli_real_escape_string($f_link, $ensemble) . "'";
}

// Add grade filter
if ($grade > 0) {
    $sql .= " AND c.grade = " . floatval($grade);
}

// Add duration filter
if (!empty($duration)) {
    switch ($duration) {
        case 'short':
            $sql .= " AND c.duration > 0 AND c.duration <= 180"; // 0-3 minutes
            break;
        case 'medium':
            $sql .= " AND c.duration > 180 AND c.duration <= 480"; // 3-8 minutes
            break;
        case 'long':
            $sql .= " AND c.duration > 480"; // 8+ minutes
            break;
    }
}

// Add composer filter
if (!empty($composer)) {
    $composer_escaped = mysqli_real_escape_string($f_link, $composer);
    $sql .= " AND c.composer = '$composer_escaped'";
}

$sql .= " GROUP BY c.catalog_number ORDER BY c.name LIMIT 100"; // Limit results to prevent overwhelming

ferror_log("The search SQL: " . trim(preg_replace('/\s+/', ' ', $sql)));

$res = mysqli_query($f_link, $sql);
$results = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = $row;
    }
}

mysqli_close($f_link);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($results);
?>
