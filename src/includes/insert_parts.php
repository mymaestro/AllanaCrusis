<?php
 //insert_parts.php
define('PAGE_TITLE', 'Insert parts');
define('PAGE_NAME', 'Insert parts');
require_once('config.php');
require_once('functions.php');

// Include PHPdfer for PDF metadata handling
$phpdfer_available = false;

if (file_exists('../PHPdfer/PHPdfer.php') && file_exists('../PHPdfer/MetadataDirector.php') && file_exists('../PHPdfer/MetadataBuilder.php')) {
    ferror_log("PHPdfer library found at: " . __DIR__ . "/PHPdfer/");
    require_once('../PHPdfer/PHPdfer.php');
    require_once('../PHPdfer/MetadataDirector.php');
    require_once('../PHPdfer/MetadataBuilder.php');
    $phpdfer_available = true;
} else {
    ferror_log("PHPdfer library not found at: " . __DIR__ . "/PHPdfer/");
    // Do not die; allow upload to continue without PDF metadata
}

function updatePartPDFMetadata($partFilePath, $partData) {    
    global $phpdfer_available;

    if (!$phpdfer_available) {
        ferror_log("PHPdfer is not available, skipping metadata update for: " . $partFilePath);
        // Return the original file path if PHPdfer is not available
        return $partFilePath;
    }

    $phpdfer = new PHPdfer\PHPdfer();
    
    $metadata = [
        'TITLE' => $partData['name'],
        'AUTHOR' => $partData['composer'],
        'SUBJECT' => $partData['subject'] ?? '',
        'KEYWORDS' => implode(', ', [
            $partData['catalog_number'],
            $partData['part_type'] ?? ''
        ]),
        'CREATOR' => ORGNAME,
        'MOD_DATE' => date('Y-m-d H:i:s'),
        'CREATION_DATE' => date('Y-m-d H:i:s')
    ];

    try {
        $phpdfer->changeMetadata($partFilePath, $metadata, true);
        
        // The new file will be named phpdfer_[original_name].pdf
        $newFileName = $phpdfer->getOutputFilePath();
        if (!$newFileName) {
            throw new Exception("Failed to retrieve the output file path from PHPdfer.");
        }
        return $newFileName;
    } catch (Exception $e) {
        error_log("Failed to update PDF metadata: " . $e->getMessage());
        throw $e;
    }
}

// Settings
$maxFileSize = 20 * 1024 * 1024; // 20 MB
// You might need to adjust these settings in your php.ini file as well
//ini_set('upload_max_filesize', '20M');
//ini_set('post_max_size', '20M');

$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');

if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    ferror_log("------------------------------------------------");
    ferror_log("RUNNING insert_parts.php with id_part=". $_POST["catalog_number"] . ":" . $_POST["id_part_type"]);
    $output = '';
    $message = '';
    $timestamp = time();

    ferror_log(print_r($_POST, true));

    if (!empty($_POST['id_instrument'])) {
        ferror_log('POST id_instrument=*not_empty*');
    } else {
        ferror_log('POST id_instrument=*empty*');
    }

    // Get values from POST and handle empty values properly
    $catalog_number = !empty($_POST['catalog_number']) ? $_POST['catalog_number'] : null;
    $catalog_number_hold = !empty($_POST['catalog_number_hold']) ? $_POST['catalog_number_hold'] : null;
    $id_part_type = !empty($_POST['id_part_type']) ? (int)$_POST['id_part_type'] : null;
    $id_part_type_hold = !empty($_POST['id_part_type_hold']) ? (int)$_POST['id_part_type_hold'] : null;
    
    // Handle columns that can be NULL
    $name = !empty($_POST['name']) ? $_POST['name'] : null;
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $is_part_collection = is_numeric($_POST['is_part_collection']) ? (int)$_POST['is_part_collection'] : null;
    $paper_size = !empty($_POST['paper_size']) ? $_POST['paper_size'] : null;
    $page_count = is_numeric($_POST['page_count']) ? (int)$_POST['page_count'] : null;
    $originals_count = is_numeric($_POST['originals_count']) ? (int)$_POST['originals_count'] : null;
    $copies_count = is_numeric($_POST['copies_count']) ? (int)$_POST['copies_count'] : null;
    $image_path_display = !empty($_POST['image_path_display']) ? $_POST['image_path_display'] : null;
    $image_path = !empty($_POST['image_path']) ? $_POST['image_path'] : null;

    // Handle instrument array
    $id_instruments = array();
    if (isset($_POST['id_instrument'])) {
        if (is_array($_POST['id_instrument'])) {
            $id_instruments = array_map('intval', $_POST['id_instrument']);
        } else {
            $id_instruments = [intval($_POST['id_instrument'])];
        }
    }

    // Handle file upload
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_path']['tmp_name'];
        $fileName = $_FILES['image_path']['name'];
        $fileSize = $_FILES['image_path']['size'];
        $fileType = $_FILES['image_path']['type'];

        // Check if the file is a valid PDF or image file
        $allowedFileTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedFileTypes)) {
            ferror_log("Invalid file type: " . $fileType);
            die("Invalid file type. Only JPEG, PNG, and PDF files are allowed.");
        }

        $uploadDir = __DIR__ . "/" . ORGPRIVATE; // Directory to save uploaded files
        
        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true) && ferror_log("Uploads directory created: " . $uploadDir);
        } else {
            ferror_log("Uploads directory already exists: " . $uploadDir);  
        }

        // Check file size
        if ($fileSize > $maxFileSize) {
            die("File is too large. Max allowed size is 40MB.");
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileTmpPath);
        $allowedMimes = [
            'application/pdf' => 'pdf'
        ];
        if (!array_key_exists($mime, $allowedMimes)) {
            die("Only PDF files are allowed. Detected: $mime");
        }

        // Get the part type name from the database using prepared statement
        $part_type_stmt = mysqli_prepare($f_link, "SELECT name FROM part_types WHERE id_part_type = ?");
        mysqli_stmt_bind_param($part_type_stmt, "i", $id_part_type);
        mysqli_stmt_execute($part_type_stmt);
        $part_type_result = mysqli_stmt_get_result($part_type_stmt);
        
        if ($part_type_result && mysqli_num_rows($part_type_result) > 0) {
            $part_type_row = mysqli_fetch_assoc($part_type_result);
            $part_type_name = $part_type_row['name'];
            ferror_log("Part type name found: " . $part_type_name);
        } else {
            ferror_log("Part type name not found for ID: " . $id_part_type);
            $part_type_name = "Unknown part type";
        }
        mysqli_stmt_close($part_type_stmt);

        $composition_stmt = mysqli_prepare($f_link, "SELECT name, composer FROM compositions WHERE catalog_number = ?");
        mysqli_stmt_bind_param($composition_stmt, "s", $catalog_number);
        mysqli_stmt_execute($composition_stmt);
        $composition_result = mysqli_stmt_get_result($composition_stmt);

        if ($composition_result && mysqli_num_rows($composition_result) > 0) {
            $composition_row = mysqli_fetch_assoc($composition_result);
            $composition_name = $composition_row['name'];
            $composition_composer = $composition_row['composer'];
            ferror_log("Composition found: " . $composition_name . " by " . $composition_composer);
        } else {
            ferror_log("Composition not found for catalog number: " . $catalog_number);
            $composition_name = "unknown_composition";
            $composition_composer = "unknown_composer";
        }
        mysqli_stmt_close($composition_stmt);

        // Generate a semi-unique file name based on catalog_number and part_type_name
        $extension = $allowedMimes[$mime];
        
        // Create a consistent hash from catalog_number and part_type_name
        $clean_part_type = preg_replace('/[^a-zA-Z0-9]/', '', $part_type_name);
        $hashInput = $catalog_number . '_' . $clean_part_type;
        $hash = substr(md5($hashInput), 0, 8);
        
        // Create filename using hash only (not readable as requested)
        $safeName = $hash . '.' . $extension;
        $destination = $uploadDir . $safeName;

        // If file already exists, remove it before uploading the new one
        if (file_exists($destination)) {
            ferror_log("Existing file found, removing: " . $destination);
            unlink($destination);
        }

        // Use PHPdfer to change metadata if needed
        if ($extension === 'pdf') {
            try {
                ferror_log("Updating PDF metadata for file: " . $fileTmpPath);
                $newFileName = updatePartPDFMetadata($fileTmpPath, [
                    'name' => $composition_name . ' - ' . $part_type_name,
                    'composer' => $composition_composer,
                    'subject' => $part_type_name . ' part for ' . $composition_name,
                    'catalog_number' => $catalog_number,
                    'part_type' => $part_type_name
                ]);
                
                // PHPdfer creates a new file, so we need to copy it to the destination
                ferror_log("PHPdfer created file: " . $newFileName);
                ferror_log("Copying PHPdfer output to destination: " . $destination);
                
                if (!copy($newFileName, $destination)) {
                    ferror_log("Failed to copy PHPdfer output to destination: " . $destination);
                    die("Failed to save the processed PDF file.");
                }
                
                // Clean up the PHPdfer temporary file
                if (file_exists($newFileName)) {
                    unlink($newFileName);
                    ferror_log("Cleaned up PHPdfer temporary file: " . $newFileName);
                }
                
                ferror_log("PDF file processed and saved successfully: " . $destination);
                
                // Update the image_path variable to store the file name
                $image_path = $safeName;
                
            } catch (Exception $e) {
                ferror_log("Failed to update PDF metadata: " . $e->getMessage());
                die("Failed to update PDF metadata: " . $e->getMessage());
            }
        } else {
            // For non-PDF files, use the original upload process
            ferror_log("Attempting to move uploaded file from: " . $fileTmpPath . " to: " . $destination);
            if (!move_uploaded_file($fileTmpPath, $destination)) {
                ferror_log("Failed to move uploaded file to destination: " . $destination);
                die("Failed to save the uploaded file.");
            }
            ferror_log("File uploaded successfully: " . $destination);
            
            // Update the image_path variable to store the file name
            $image_path = $safeName;
        }
    } else {
        ferror_log("No file uploaded or upload error occurred.");
    }

    if($_POST["update"] == "update") {
        // Prepare UPDATE statement
        $update_sql = "UPDATE parts SET 
                       id_part_type = ?, 
                       catalog_number = ?, 
                       name = ?, 
                       description = ?, 
                       is_part_collection = ?, 
                       paper_size = ?, 
                       page_count = ?, 
                       image_path = ?,
                       originals_count = ?, 
                       copies_count = ?, 
                       last_update = CURRENT_TIMESTAMP() 
                       WHERE catalog_number = ? AND id_part_type = ?";
        
        ferror_log("Preparing UPDATE SQL: " . $update_sql);
        
        $update_stmt = mysqli_prepare($f_link, $update_sql);
        if (!$update_stmt) {
            die("Prepare failed: " . mysqli_error($f_link));
        }
        
        // If image_path is not set, but image_path_display is set, use that
        if (empty($image_path) && !empty($image_path_display)) {
            $image_path = $image_path_display;
        }

        mysqli_stmt_bind_param($update_stmt, "isssisisiisi", 
            $id_part_type, $catalog_number, $name, $description, 
            $is_part_collection, $paper_size, $page_count, $image_path, 
            $originals_count, $copies_count, $catalog_number_hold, $id_part_type_hold
        );
        
        if(mysqli_stmt_execute($update_stmt)) {
            $output = "Parts updated successfully.";
            ferror_log($output);
            mysqli_stmt_close($update_stmt);
            
            // Clean out instruments for this part type using prepared statement
            $delete_stmt = mysqli_prepare($f_link, "DELETE FROM part_collections WHERE catalog_number_key = ? AND id_part_type_key = ?");
            mysqli_stmt_bind_param($delete_stmt, "si", $catalog_number, $id_part_type);
            
            if(mysqli_stmt_execute($delete_stmt)) {
                ferror_log("Part collection removed for ".$catalog_number." and ".$id_part_type.".");
            } else {
                ferror_log("Part collection delete failed with error: ". mysqli_stmt_error($delete_stmt));
            }
            mysqli_stmt_close($delete_stmt);
            
            // Add to part_collections table for each instrument in the part
            if (!empty($id_instruments)) {
                ferror_log("Adding instruments to part_collections for catalog_number: " . $catalog_number . " and id_part_type: " . $id_part_type);
                
                $insert_collection_sql = "INSERT INTO part_collections(catalog_number_key, id_part_type_key, id_instrument_key, name, description, last_update) VALUES(?, ?, ?, ?, ?, CURRENT_TIMESTAMP())";
                $insert_collection_stmt = mysqli_prepare($f_link, $insert_collection_sql);
                
                foreach($id_instruments as $id_instrument_num) {
                    ferror_log("Adding instrument: ". $id_instrument_num . " to part_collections.");
                    
                    mysqli_stmt_bind_param($insert_collection_stmt, "siiss", 
                        $catalog_number, $id_part_type, $id_instrument_num, $name, $description
                    );
                    
                    if(mysqli_stmt_execute($insert_collection_stmt)) {
                        ferror_log("Part collection added for ".$catalog_number." and part type = ".$id_part_type." and instrument = ".$id_instrument_num.".");
                    } else {
                        ferror_log("Part collection add failed with error: ". mysqli_stmt_error($insert_collection_stmt));
                    }
                }
                mysqli_stmt_close($insert_collection_stmt);
            } else {
                ferror_log("No instruments to add to part_collections for catalog_number: " . $catalog_number . " and id_part_type: " . $id_part_type);
            }

        } else {
            $error_message = mysqli_stmt_error($update_stmt);
            $output = "Parts update failed with error = " . $error_message;
            ferror_log($output);
            mysqli_stmt_close($update_stmt);
        }
        
    } elseif($_POST["update"] == "add") {
        // Prepare INSERT statement
        $insert_sql = "INSERT INTO parts (
        catalog_number, 
        id_part_type,
        name, 
        description, 
        is_part_collection, 
        paper_size, 
        page_count, 
        image_path, 
        originals_count, 
        copies_count, 
        last_update) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())";
        
        ferror_log("Preparing INSERT SQL: " . $insert_sql);
        
        $insert_stmt = mysqli_prepare($f_link, $insert_sql);
        if (!$insert_stmt) {
            die("Prepare failed: " . mysqli_error($f_link));
        }

        // If image_path is not set, but image_path_display is set, use that
        if (empty($image_path) && !empty($image_path_display)) {
            $image_path = $image_path_display;
        }

        mysqli_stmt_bind_param($insert_stmt, "sissisisii", 
            $catalog_number, $id_part_type, $name, $description, 
            $is_part_collection, $paper_size, $page_count, $image_path, 
            $originals_count, $copies_count
        );
        
        if(mysqli_stmt_execute($insert_stmt)) {
            $output = "Parts inserted successfully.";
            ferror_log($output);
        } else {
            $error_message = mysqli_stmt_error($insert_stmt);
            $output = "Parts insert failed with error = " . $error_message;
            ferror_log($output);
        }
        mysqli_stmt_close($insert_stmt);
    }
    mysqli_close($f_link);
} else {
    require_once("header.php");
    echo '<body>
';
    require_once("navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Parts menu.</p></div>';
    require_once("footer.php");
    echo '</body>';
}
?>