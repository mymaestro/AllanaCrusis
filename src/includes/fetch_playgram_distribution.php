<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running fetch_playgram_distribution.php");

// Check if user has permission
$u_librarian = FALSE;
$u_admin = FALSE;
if (isset($_SESSION['username'])) {
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
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
    case 'update_token_email':
        // Update the email address for a given token
        if (!isset($_POST['token']) || !isset($_POST['email'])) {
            echo json_encode(['success' => false, 'message' => 'Token and email required.']);
            exit;
        }
        $token = $_POST['token'];
        $email = $_POST['email'];
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            echo json_encode(['success' => false, 'message' => 'Invalid token format.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit;
        }
        $stmt = mysqli_prepare($f_link, "UPDATE download_tokens SET email = ? WHERE token = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $token);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        break;
    case 'load_playgram':
        if (!isset($_POST['playgram_id'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID required.']);
            exit;
        }
        
        $playgram_id = intval($_POST['playgram_id']);
        $result = loadPlaygramData($f_link, $playgram_id);
        echo json_encode($result);
        break;

    case 'create_section_zip':
        if (!isset($_POST['playgram_id']) || !isset($_POST['section_id'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID and Section ID required.']);
            exit;
        }
        $playgram_id = intval($_POST['playgram_id']);
        $section_id = intval($_POST['section_id']);
        $result = createSectionZip($f_link, $playgram_id, $section_id);
        echo json_encode($result);
        break;
    case 'generate_download_token':
        if (!isset($_POST['playgram_id']) || !isset($_POST['section_id']) || !isset($_POST['zip_filename'])) {
            echo json_encode(['success' => false, 'message' => 'Playgram ID, Section ID, and ZIP filename required.']);
            exit;
        }
        $playgram_id = intval($_POST['playgram_id']);
        $section_id = intval($_POST['section_id']);
        $zip_filename = $_POST['zip_filename'];
        $result = generateDownloadTokenForZip($f_link, $playgram_id, $section_id, $zip_filename);
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
    
    // Get section_ids array from POST
    $section_ids = isset($_POST['section_ids']) && is_array($_POST['section_ids']) ? $_POST['section_ids'] : [];

    if (!empty($section_ids)) {
        // Filter by provided section IDs
        $in = implode(',', array_map('intval', $section_ids));
        $sql = "SELECT 
                s.id_section,
                s.name as section_name,
                COUNT(DISTINCT CONCAT(p.catalog_number, '-', p.id_part_type)) as total_parts,
                COUNT(DISTINCT CASE WHEN p.image_path IS NOT NULL AND p.image_path != '' 
                     THEN CONCAT(p.catalog_number, '-', p.id_part_type) END) as parts_with_pdf,
                COUNT(DISTINCT CASE WHEN p.image_path IS NULL OR p.image_path = '' 
                     THEN CONCAT(p.catalog_number, '-', p.id_part_type) END) as parts_without_pdf
            FROM sections s
            JOIN section_instruments si ON s.id_section = si.id_section
            JOIN part_types pt ON si.id_instrument = pt.default_instrument
            JOIN parts p ON pt.id_part_type = p.id_part_type
            JOIN playgram_items pi ON p.catalog_number = pi.catalog_number
            JOIN compositions c ON pi.catalog_number = c.catalog_number
            WHERE pi.id_playgram = ? AND s.enabled = 1 AND c.enabled = 1 AND p.originals_count > 0 AND s.id_section IN ($in)
            GROUP BY s.id_section, s.name
            ORDER BY s.name";
        $stmt = mysqli_prepare($f_link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $playgram_id);
    } else {
        // No sections
        $sections = [];
        goto finish_response;
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sections = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
    }
    finish_response:
    
    return [
        'success' => true,
        'data' => [
            'playgram' => $playgram,
            'compositions' => $compositions,
            'sections' => $sections
        ]
    ];
}

function createSectionZip($f_link, $playgram_id, $section_id) {
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
            JOIN section_instruments si ON pt.default_instrument = si.id_instrument
            JOIN sections s ON si.id_section = s.id_section
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
    $distrPath = rtrim(ORGPRIVATE, '/') . '/distributions/';
    $zip_path = $distrPath . $zip_filename;

    // Ensure distributions directory exists
    $distributions_dir = $distrPath;

    ferror_log("Checking distributions directory: " . $distributions_dir);
    
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
        $partsPath = rtrim(ORGPRIVATE, '/') . '/parts/'; // ORGPRIVATE is an absolute path that should end with slash
        $source_path = $partsPath . ltrim($part['image_path'], '/\\');

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

    return [
        'success' => true,
        'data' => [
            'filename' => $zip_filename,
            'part_count' => $added_count,
            'skipped_files' => $skipped_files
        ]
    ];
}

// Function to generate a download token for a given ZIP file
function generateDownloadTokenForZip($f_link, $playgram_id, $section_id, $zip_filename) {
    // Create ZIP file name from playgram_id and section_id
    $playgram_name = sanitizeFilename($playgram_id);
    $section_name = sanitizeFilename($section_id);
    $zip_filename = $playgram_name . '_' . $section_name . '_Parts.zip';  
    $distrPath = rtrim(ORGPRIVATE, '/') . '/distributions/';
    $zip_path = $distrPath . $zip_filename;

    if (!file_exists($zip_path)) {
        return ['success' => false, 'message' => 'ZIP file does not exist.'];
    };
    // Generate a secure token
    $token = bin2hex(random_bytes(16));
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 days'));
    $id_user = null;
    if (isset($_SESSION['username'])) {
        $user_stmt = mysqli_prepare($f_link, "SELECT id_users FROM users WHERE username = ?");
        mysqli_stmt_bind_param($user_stmt, "s", $_SESSION['username']);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user_row = mysqli_fetch_assoc($user_result)) {
            $id_user = $user_row['id_users'];
        }
        mysqli_stmt_close($user_stmt);
    }
    $stmt = mysqli_prepare($f_link, "INSERT INTO download_tokens (token, id_playgram, id_section, zip_filename, expires_at, id_user) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siissi", $token, $playgram_id, $section_id, $zip_filename, $expires_at, $id_user);
    mysqli_stmt_execute($stmt);
    $download_link = '/d/' . $token;
    return [
        'success' => true,
        'data' => [
            'filename' => $zip_filename,
            'token' => $token,
            'download_link' => $download_link,
            'id_playgram' => $playgram_id,
            'id_section' => $section_id,
            'expires_at' => $expires_at
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