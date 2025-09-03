<?php
// check_parts_files.php
// Checks for parts with image_path not null and verifies file existence

require_once __DIR__ . '/../src/includes/config.php';
require_once __DIR__ . '/../src/includes/functions.php';

// Set the directory where part files are stored
$parts_dir = rtrim(ORGPRIVATE, '/') . '/parts/'; // ORGPRIVATE is an absolute path that should end with slash

// Connect to DB
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT p.id_part_type, p.catalog_number, p.name, p.image_path, pt.name AS part_type_name
    FROM parts p
    LEFT JOIN part_types pt ON p.id_part_type = pt.id_part_type
    WHERE p.image_path IS NOT NULL AND p.image_path != ''";
$result = mysqli_query($f_link, $sql);

if (!$result) {
    die('Query failed: ' . mysqli_error($f_link));
}

$found = [];
$missing = [];

while ($row = mysqli_fetch_assoc($result)) {
    $file_path = $parts_dir . $row['image_path'];
    $part_type = $row['part_type_name'] ?? '';
    if (file_exists($file_path)) {
        $found[] = $row['image_path'];
        echo "FOUND: {$row['catalog_number']}: {$part_type} - {$row['name']} -- {$row['image_path']}\n";
    } else {
        $missing[] = $row['image_path'];
        echo "MISSING: {$row['catalog_number']}: {$part_type} - {$row['name']} -- {$row['image_path']}\n";
        // Prompt for confirmation before updating
        echo "Set image_path to NULL for this part? (y/N/q): ";
        $handle = fopen('php://stdin', 'r');
        $line = trim(fgets($handle));
        if (strtolower($line) === 'y') {
            $update_sql = "UPDATE parts SET image_path = NULL WHERE catalog_number = ? AND id_part_type = ?";
            $update_stmt = mysqli_prepare($f_link, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'si', $row['catalog_number'], $row['id_part_type']);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            echo "Updated.\n";
        } elseif (strtolower($line) === 'q') {
            echo "Quitting.\n";
            fclose($handle);
            break;
        } else {
            echo "Skipped.\n";
        }
        fclose($handle);
    }
}

mysqli_close($f_link);

// Optionally, output summary
echo "\nSummary:\n";
echo "Found files: " . count($found) . "\n";
echo "Missing files: " . count($missing) . "\n";
?>
