<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running fetch_playgram_distribution.php");

// Check if user has permission
$u_librarian = FALSE;
$u_admin = FALSE;
if (isset($_SESSION['username'])) {
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
}

if (!$u_librarian && !$u_admin) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Librarian privileges required.']);
    exit;
}

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified.']);
    exit;
}

$action = $_POST['action'];
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

switch($action) {
    case 'load_playgram':
        if (!isset($_POST['playgram_id'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID required.']);
            exit;
        }
        
        $playgram_id = intval($_POST['playgram_id']);
        $result = loadPlaygramData($f_link, $playgram_id);
        echo json_encode($result);
        break;
        
    case 'generate_zips':
        if (!isset($_POST['playgram_id'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID required.']);
            exit;
        }
        
        $playgram_id = intval($_POST['playgram_id']);
        $result = generateAllSectionZips($f_link, $playgram_id);
        echo json_encode($result);
        break;
        
    case 'generate_section_zip':
        if (!isset($_POST['playgram_id']) || !isset($_POST['section_id'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID and Section ID required.']);
            exit;
        }
        
        $playgram_id = intval($_POST['playgram_id']);
        $section_id = intval($_POST['section_id']);
        $result = generateSectionZip($f_link, $playgram_id, $section_id);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

mysqli_close($f_link);

function loadPlaygramData($f_link, $playgram_id) {
    // Get playgram info
    $sql = "SELECT id_playgram, name, description FROM playgrams WHERE id_playgram = ? AND enabled = 1";
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        return ['success' => false, 'message' => 'Playgram not found.'];
    }
    
    $playgram = mysqli_fetch_assoc($result);
    
    // Get compositions in playgram with parts info
    $sql = "SELECT 
                pi.comp_order,
                pi.catalog_number,
                c.name as composition_name,
                c.composer,
                COUNT(p.id_part_type) as total_parts,
                COUNT(CASE WHEN p.image_path IS NOT NULL AND p.image_path != '' THEN 1 END) as parts_with_pdf,
                COUNT(CASE WHEN p.image_path IS NULL OR p.image_path = '' THEN 1 END) as parts_without_pdf
            FROM playgram_items pi
            JOIN compositions c ON pi.catalog_number = c.catalog_number
            LEFT JOIN parts p ON c.catalog_number = p.catalog_number AND p.originals_count > 0
            WHERE pi.id_playgram = ? AND c.enabled = 1
            GROUP BY pi.comp_order, pi.catalog_number, c.name, c.composer
            ORDER BY pi.comp_order";
    
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $compositions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $compositions[] = $row;
    }
    
    // Get sections with parts info for this playgram
    $sql = "SELECT 
                s.id_section,
                s.name as section_name,
                COUNT(DISTINCT CONCAT(p.catalog_number, '-', p.id_part_type)) as total_parts,
                COUNT(DISTINCT CASE WHEN p.image_path IS NOT NULL AND p.image_path != '' 
                     THEN CONCAT(p.catalog_number, '-', p.id_part_type) END) as parts_with_pdf,
                COUNT(DISTINCT CASE WHEN p.image_path IS NULL OR p.image_path = '' 
                     THEN CONCAT(p.catalog_number, '-', p.id_part_type) END) as parts_without_pdf
            FROM sections s
            JOIN section_part_types spt ON s.id_section = spt.id_section
            JOIN part_types pt ON spt.id_part_type = pt.id_part_type
            JOIN parts p ON pt.id_part_type = p.id_part_type
            JOIN playgram_items pi ON p.catalog_number = pi.catalog_number
            JOIN compositions c ON pi.catalog_number = c.catalog_number
            WHERE pi.id_playgram = ? AND s.enabled = 1 AND c.enabled = 1 AND p.originals_count > 0
            GROUP BY s.id_section, s.name
            ORDER BY s.name";
    
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sections = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
    }
    
    return [
        'success' => true,
        'data' => [
            'playgram' => $playgram,
            'compositions' => $compositions,
            'sections' => $sections
        ]
    ];
}

function generateAllSectionZips($f_link, $playgram_id) {
    // Get playgram name
    $sql = "SELECT name FROM playgrams WHERE id_playgram = ?";
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $playgram = mysqli_fetch_assoc($result);
    
    if (!$playgram) {
        return ['success' => false, 'message' => 'Playgram not found.'];
    }
    
    $playgram_name = sanitizeFilename($playgram['name']);
    
    // Get all sections that have parts in this playgram
    $sql = "SELECT DISTINCT s.id_section, s.name as section_name
            FROM sections s
            JOIN section_part_types spt ON s.id_section = spt.id_section
            JOIN part_types pt ON spt.id_part_type = pt.id_part_type
            JOIN parts p ON pt.id_part_type = p.id_part_type
            JOIN playgram_items pi ON p.catalog_number = pi.catalog_number
            JOIN compositions c ON pi.catalog_number = c.catalog_number
            WHERE pi.id_playgram = ? AND s.enabled = 1 AND c.enabled = 1 
                AND p.originals_count > 0 AND p.image_path IS NOT NULL AND p.image_path != ''
            ORDER BY s.name";
    
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    mysqli_stmt_execute($stmt);
    $sections_result = mysqli_stmt_get_result($stmt);
    
    $zip_files = [];
    $generation_log = [];
    
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $zip_result = generateSectionZip($f_link, $playgram_id, $section['id_section']);
        
        if ($zip_result['success']) {
            $zip_files[] = [
                'section_name' => $section['section_name'],
                'url' => $zip_result['data']['zip_url'],
                'filename' => $zip_result['data']['filename'],
                'part_count' => $zip_result['data']['part_count']
            ];
            $generation_log[] = "Generated ZIP for " . $section['section_name'] . " (" . $zip_result['data']['part_count'] . " parts)";
        } else {
            $generation_log[] = "Error generating ZIP for " . $section['section_name'] . ": " . $zip_result['message'];
        }
    }
    
    return [
        'success' => true,
        'data' => [
            'zip_files' => $zip_files,
            'log' => $generation_log
        ]
    ];
}

function generateSectionZip($f_link, $playgram_id, $section_id) {
    // Get playgram and section info
    $sql = "SELECT pg.name as playgram_name, s.name as section_name 
            FROM playgrams pg, sections s 
            WHERE pg.id_playgram = ? AND s.id_section = ?";
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $playgram_id, $section_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $info = mysqli_fetch_assoc($result);
    ferror_log("Playgram info: " . json_encode($info));

    if (!$info) {
        return ['success' => false, 'message' => 'Playgram or section not found.'];
    }
    
    $playgram_name = sanitizeFilename($info['playgram_name']);
    $section_name = sanitizeFilename($info['section_name']);
    
    // Get all parts for this section in this playgram
    $sql = "SELECT 
                pi.comp_order,
                c.name as composition_name,
                pt.name as part_name,
                p.image_path,
                p.catalog_number,
                p.id_part_type
            FROM playgram_items pi
            JOIN compositions c ON pi.catalog_number = c.catalog_number
            JOIN parts p ON c.catalog_number = p.catalog_number
            JOIN part_types pt ON p.id_part_type = pt.id_part_type
            JOIN section_part_types spt ON pt.id_part_type = spt.id_part_type
            JOIN sections s ON spt.id_section = s.id_section
            WHERE pi.id_playgram = ? AND s.id_section = ? 
                AND c.enabled = 1 AND p.originals_count > 0 
                AND p.image_path IS NOT NULL AND p.image_path != ''
            ORDER BY pi.comp_order, pt.collation, pt.name";
    
    $stmt = mysqli_prepare($f_link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $playgram_id, $section_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $parts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $parts[] = $row;
    }
    
    if (empty($parts)) {
        return ['success' => false, 'message' => 'No parts with PDF files found for this section.'];
    }
    
    // Create ZIP file
    $zip_filename = $playgram_name . '_' . $section_name . '_Parts.zip';
    $zip_path = __DIR__ . '/' . ORGDIST . $zip_filename;
    
    // Ensure distributions directory exists
    $distributions_dir = __DIR__ . '/' . ORGDIST;
    if (!is_dir($distributions_dir)) {
        if (!mkdir($distributions_dir, 0755, true)) {
            return ['success' => false, 'message' => 'Could not create distributions directory.'];
        }
    }
    
    $zip = new ZipArchive();
    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        return ['success' => false, 'message' => 'Could not create ZIP file.'];
    }
    
    $added_count = 0;
    $skipped_files = [];
    
    foreach ($parts as $part) {
        $source_path = __DIR__ . '/' . ORGPRIVATE . $part['image_path'];

        ferror_log("Processing part: " . $part['part_name'] . " (source: " . $source_path . ")");
        
        if (file_exists($source_path)) {
            // Create new filename: Order - Composition - Part.pdf
            $order = str_pad($part['comp_order'], 2, '0', STR_PAD_LEFT);
            $composition = sanitizeFilename($part['composition_name']);
            $part_name = sanitizeFilename($part['part_name']);
            $new_filename = $order . ' - ' . $composition . ' - ' . $part_name . '.pdf';

            ferror_log("Adding file to ZIP: " . $new_filename);

            if ($zip->addFile($source_path, $new_filename)) {
                $added_count++;
            } else {
                $skipped_files[] = $part['part_name'] . ' (could not add to ZIP)';
            }
        } else {
            $skipped_files[] = $part['part_name'] . ' (file not found: ' . $part['image_path'] . ')';
            ferror_log("File not found: " . $part['image_path'] . " at source path: " . $source_path);
        }
    }
    
    $zip->close();
    
    if ($added_count == 0) {
        unlink($zip_path);
        return ['success' => false, 'message' => 'No PDF files could be added to ZIP.'];
    }

    $zip_url = ORGPARTDISTRO . $zip_filename;

    return [
        'success' => true,
        'data' => [
            'zip_url' => $zip_url,
            'filename' => $zip_filename,
            'part_count' => $added_count,
            'skipped_files' => $skipped_files
        ]
    ];
}

function sanitizeFilename($filename) {
    // Remove or replace characters that are problematic in filenames
    $filename = preg_replace('/[\/\\\:*?"<>|]/', '', $filename);
    $filename = preg_replace('/\s+/', '_', $filename);
    $filename = trim($filename, '_');
    return $filename;
}
?>
