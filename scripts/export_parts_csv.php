<?php
// export_parts_csv.php
// Export a CSV of all referenced PDF files with composition and part info

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/includes/functions.php';

$link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Query for all referenced PDFs with composition and part info
$sql = "SELECT p.image_path AS PDF, c.name AS Composition, pt.name AS Part
    FROM parts p
    LEFT JOIN compositions c ON p.catalog_number = c.catalog_number
    LEFT JOIN part_types pt ON p.id_part_type = pt.id_part_type
    WHERE p.image_path IS NOT NULL AND p.image_path != '' AND p.image_path LIKE '%.pdf'";
$res = mysqli_query($link, $sql);

$fp = fopen('php://stdout', 'w');
fputcsv($fp, ['PDF', 'Composition', 'Part']);
while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($fp, [$row['PDF'], $row['Composition'], $row['Part']]);
}
fclose($fp);
