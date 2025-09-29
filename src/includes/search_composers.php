<?php
// search_composers.php - Backend support for composer autocomplete
define('PAGE_TITLE', 'Search Composers');
define('PAGE_NAME', 'Search Composers');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = isset($input['query']) ? trim($input['query']) : '';
$limit = isset($input['limit']) ? (int)$input['limit'] : 10;

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$f_link) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Search for composers in existing compositions
// Use DISTINCT to avoid duplicates and ORDER BY frequency of use
$search_query = mysqli_real_escape_string($f_link, $query);
$sql = "
    SELECT 
        composer,
        COUNT(*) as usage_count,
        GROUP_CONCAT(DISTINCT name ORDER BY name SEPARATOR ' | ') as compositions
    FROM compositions 
    WHERE composer IS NOT NULL 
    AND composer != '' 
    AND (
        composer LIKE '%{$search_query}%'
        OR SOUNDEX(composer) = SOUNDEX('{$search_query}')
        OR composer REGEXP '[[:<:]]" . mysqli_real_escape_string($f_link, $query) . "[[:>:]]'
    )
    GROUP BY composer
    ORDER BY 
        CASE WHEN composer LIKE '{$search_query}%' THEN 1 ELSE 2 END,
        usage_count DESC,
        composer ASC
    LIMIT {$limit}
";

$result = mysqli_query($f_link, $sql);

if (!$result) {
    ferror_log("Search composers query failed: " . mysqli_error($f_link));
    echo json_encode(['error' => 'Search failed']);
    exit;
}

$suggestions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $suggestions[] = [
        'composer' => $row['composer'],
        'name_display' => $row['composer'], // For compatibility with normalization JS
        'usage_count' => (int)$row['usage_count'],
        'compositions' => explode(' | ', $row['compositions'])
    ];
}

// Also search arrangers if no composers found
if (count($suggestions) < $limit) {
    $remaining_limit = $limit - count($suggestions);
    $sql_arrangers = "
        SELECT 
            arranger as composer,
            COUNT(*) as usage_count,
            GROUP_CONCAT(DISTINCT name ORDER BY name SEPARATOR ' | ') as compositions
        FROM compositions 
        WHERE arranger IS NOT NULL 
        AND arranger != '' 
        AND (
            arranger LIKE '%{$search_query}%'
            OR SOUNDEX(arranger) = SOUNDEX('{$search_query}')
            OR arranger REGEXP '[[:<:]]" . mysqli_real_escape_string($f_link, $query) . "[[:>:]]'
        )
        GROUP BY arranger
        ORDER BY 
            CASE WHEN arranger LIKE '{$search_query}%' THEN 1 ELSE 2 END,
            usage_count DESC,
            arranger ASC
        LIMIT {$remaining_limit}
    ";
    
    $result_arrangers = mysqli_query($f_link, $sql_arrangers);
    
    if ($result_arrangers) {
        while ($row = mysqli_fetch_assoc($result_arrangers)) {
            // Check if we already have this name as a composer
            $exists = false;
            foreach ($suggestions as $existing) {
                if (strtolower($existing['composer']) === strtolower($row['composer'])) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $suggestions[] = [
                    'composer' => $row['composer'],
                    'name_display' => $row['composer'] . ' (arr.)',
                    'usage_count' => (int)$row['usage_count'],
                    'compositions' => explode(' | ', $row['compositions'])
                ];
            }
        }
    }
}

mysqli_close($f_link);
echo json_encode($suggestions);
?>