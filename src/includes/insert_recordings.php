<?php
ob_start();
/* insert_recordings.php
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2022, 2025 All rights reserved.
#############################################################################
*/
define('PAGE_TITLE', 'Insert recordings');
define('PAGE_NAME', 'Insert recordings');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running ".PAGE_NAME." with POST data: " . print_r($_POST, true));

// Settings
$maxFileSize = 40 * 1024 * 1024; // 40 MB
// You might need to adjust these settings in your php.ini file as well
//ini_set('upload_max_filesize', '40M');
//ini_set('post_max_size', '40M');

$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');

// Check if the getID3 library is available
if (!file_exists(__DIR__ . '/../getID3/getid3/getid3.php') || !file_exists(__DIR__ . '/../getID3/getid3/write.php')) {
    ferror_log("getID3 library not found at: ".__DIR__ . "/../getID3/getid3/getid3.php");
    die("getID3 library not found. Please ensure it is installed in the correct path.");
} else {
    ferror_log("getID3 library found at: ../getID3/getid3/getid3.php");
} 
// NOTE TO SELF: Verify getID3 library functionality but also if it's not available we can still upload files.

if(!empty($_POST)) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $output = '';
    $message = '';
    $timestamp = time();
    // Validate input, ensure required fields are set
    // Columns in the recordings table
    $id_recording = mysqli_real_escape_string($f_link, $_POST['id_recording']);
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $id_concert = mysqli_real_escape_string($f_link, $_POST['concert']);
    $id_ensemble = mysqli_real_escape_string($f_link, $_POST['id_ensemble']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $ensemble = mysqli_real_escape_string($f_link, $_POST['ensemble']);
    $notes = mysqli_real_escape_string($f_link, $_POST['notes']);
    $composer = mysqli_real_escape_string($f_link, $_POST['composer']);
    $arranger = mysqli_real_escape_string($f_link, $_POST['arranger']);
    $venue = mysqli_real_escape_string($f_link, $_POST['venue']);
    $enabled = ((isset($_POST["enabled"])) ? 1 : 0);
    $enabled = mysqli_real_escape_string($f_link, $enabled);
    // Other important data
    $filedate = mysqli_real_escape_string($f_link, $_POST['filedate']);

    // Handle file upload
    $link = mysqli_real_escape_string($f_link, $_POST['linkDisplay']);
    $fileTmpPath = null;
    $fileName = null;
    $fileSize = null;
    $fileType = null;

    // Check if this is a chunked upload (file already assembled on server)
    if (isset($_POST['uploadedFilePath']) && isset($_POST['uploadedFileName'])) {
        // Chunked upload - file is already on server
        $fileTmpPath = $_POST['uploadedFilePath'];
        $fileName = $_POST['uploadedFileName'];
        $fileSize = file_exists($fileTmpPath) ? filesize($fileTmpPath) : 0;
        
        // Determine MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($fileTmpPath);
        
        ferror_log("Processing chunked upload: $fileName ($fileSize bytes)");
        
    } elseif (isset($_FILES['link']) && $_FILES['link']['error'] === UPLOAD_ERR_OK) {
        // Standard upload
        $fileTmpPath = $_FILES['link']['tmp_name'];
        $fileName = $_FILES['link']['name'];
        $fileSize = $_FILES['link']['size'];
        $fileType = $_FILES['link']['type'];
        
        ferror_log("Processing standard upload: $fileName ($fileSize bytes)");
    }

    if ($fileTmpPath && $fileName) { // There's a file to process
        require_once(__DIR__ . '/../getID3/getid3/getid3.php');
        require_once(__DIR__ . '/../getID3/getid3/write.php');
        
        $uploadDir = __DIR__ . "/" . ORGPUBLIC . $filedate . '/'; // Directory to save uploaded files
        // Example: www/public/recordings/2023-10-01/
        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true) && ferror_log("Uploads directory created: " . $uploadDir);
        } else {
            ferror_log("Uploads directory already exists: " . $uploadDir);  
        }

        // Check file size (only enforce for standard uploads, chunked uploads already validated)
        if (!isset($_POST['uploadedFilePath']) && $fileSize > $maxFileSize) {
            die("File is too large. Max allowed size is 40MB.");
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileTmpPath);
        $allowedMimes = [
            'audio/mpeg' => 'mp3',
            'audio/wav'  => 'wav',
            'audio/flac' => 'flac',
            'audio/x-flac' => 'flac', // Some systems use this
            'audio/ogg'  => 'ogg'
        ];
        if (!array_key_exists($mime, $allowedMimes)) {
            die("Only MP3, WAV, FLAC, and OGG audio files are allowed. Detected: $mime");
        }

        // Generate a safe file name with the correct extension
        $extension = $allowedMimes[$mime];
        $safeName = uniqid('audio_', true) . '.' . $extension;
        $destination = $uploadDir . $fileName; // Use original file name for simplicity

        // Move the uploaded file
        ferror_log("Attempting to move uploaded file from: " . $fileTmpPath . " to: " . $destination);
        
        // For chunked uploads, use copy + unlink instead of move_uploaded_file
        if (isset($_POST['uploadedFilePath'])) {
            if (!copy($fileTmpPath, $destination)) {
                die("Failed to save the uploaded file.");
            }
            // Clean up the temporary file
            @unlink($fileTmpPath);
            // Also clean up the temp directory
            $tempDir = dirname($fileTmpPath);
            @rmdir($tempDir);
        } else {
            // Standard upload
            if (!move_uploaded_file($fileTmpPath, $destination)) {
                die("Failed to save the uploaded file.");
            }
        }

        // We can tag these file formats
        $supportedTagFormats = [
            'audio/mpeg' => ['id3v2.3', 'id3v1'],
            'audio/ogg' => ['vorbiscomment'],
            'audio/flac' => ['vorbiscomment'],
            'audio/x-flac' => ['vorbiscomment'],
        ];

        $audio = new getID3();
        $didTag = false;

        if (isset($supportedTagFormats[$mime])) {
            $tagwriter = new getid3_writetags();
            $tagwriter->filename = $destination;
            $tagwriter->tagformats = $supportedTagFormats[$mime];

            $tagCommand = true;
            if (in_array('vorbiscomment', $tagwriter->tagformats)) {
                $vorbisPath = '/usr/bin/vorbiscomment';
                if (!file_exists($vorbisPath) || !is_executable($vorbisPath)) {
                    $tagCommand = false;
                    ferror_log('vorbiscomment tool not found or not executable at ' . $vorbisPath . ', skipping tagging for Ogg/FLAC.');
                }
            }

            if ($tagCommand) {
                $tagwriter->overwrite_tags = true;
                $tagwriter->remove_other_tags = false;
                $tagwriter->tag_encoding = 'UTF-8';
                $tagData = array(
                    'title'   => array($name),
                    'artist'  => array($composer),
                    'album'   => array($ensemble),
                    'year'    => array(substr($filedate, 0, 4)),
                    'comment' => array($notes),
                    'genre'   => array('Classical'),
                );
                $tagwriter->tag_data = $tagData;
                if ($tagwriter->WriteTags()) {
                    ferror_log('Successfully wrote tags');
                    $didTag = true;
                } else {
                    ferror_log('Failed to write tags: '.implode(', ', $tagwriter->errors));
                }
            }
        } else {
            ferror_log('Tagging skipped: format not supported for tagging (MIME: ' . $mime . ')');
        }

        // Always analyze and save metadata, even if tagging was skipped
        $info = $audio->analyze($destination);
        if (isset($info['error'])) {
            die("Error analyzing audio file: " . implode(', ', $info['error']));
        }

        $metaFile = $uploadDir . 'meta_' . $safeName . '.json';
        file_put_contents($metaFile, json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        ferror_log("Audio file uploaded and metadata saved.");
        ferror_log("File: " . htmlspecialchars(basename($safeName)));
        ferror_log("Metadata: " . htmlspecialchars(basename($metaFile)));
    } elseif (isset($_POST['linkDisplay']) && !empty($_POST['linkDisplay'])) {
        // If no file is uploaded, use the link provided
        $link = mysqli_real_escape_string($f_link, $_POST['linkDisplay']);
        ferror_log("No file uploaded, using link: " . $link);
    } else {
        die("No file uploaded or an error occurred.");
    }

    if($_POST["update"] == "update") {
        $sql = "
        UPDATE recordings 
        SET catalog_number = '$catalog_number',
        id_concert = '$id_concert',
        name ='$name',
        ensemble = '$ensemble',
        id_ensemble = '$id_ensemble',
        link = '$link',
        notes = '$notes',
        composer = '$composer',
        arranger = '$arranger',
        enabled = $enabled
        WHERE id_recording='".$_POST["id_recording"]."'";
        $message = 'Data Updated';
    } elseif($_POST["update"] == "add") {
        $sql = "
        INSERT INTO recordings(catalog_number, id_concert, name, ensemble, id_ensemble, link, notes, composer, arranger, enabled)
        VALUES('$catalog_number', $id_concert, '$name', '$ensemble', '$id_ensemble', '$link', '$notes', '$composer', '$arranger', $enabled);
        ";
        $message = 'Data Inserted';
    }
    ferror_log("Updating recordings with SQL: " . trim(preg_replace('/\s+/', ' ', $sql)));
    $referred = $_SERVER['HTTP_REFERER'];
    $referred .= "/#" . $id_recording;

    ob_clean();
    header('Content-Type: application/json');
    try {
        if(mysqli_query($f_link, $sql)) {
            ferror_log("SQL ran successfully.");
            echo json_encode([
                'success' => true,
                'message' => $message,
            ]);
        }
    } catch (mysqli_sql_exception $e) {
        $error_message = $e->getMessage();
        $mysql_errno = $e->getCode();
        
        ferror_log("SQL execution failed: " . $error_message . " (Error Code: " . $mysql_errno . ")");
        
        // Check for specific error types
        if ($mysql_errno == 1062) {
            echo json_encode([
                'success' => false,
                'message' => 'Duplicate Entry Error: A recording with this information already exists. Please check the data and try again.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed with error: Error Code ' . $mysql_errno . ' - ' . htmlspecialchars($error_message ?? ''),
            ]);
        }
    }
    mysqli_close($f_link);
 } else {
    require_once(__DIR__ . "/header.php");
    echo '<body>
';
    require_once(__DIR__ . "/navbar.php");
    echo '
    <div class="container">
    <h2 align="center">'. ORGNAME . ' ' . PAGE_NAME . '</h2>
    <div><p align="center" class="text-danger">You can get here only from the Recordings menu.</p></div>';
    require_once(__DIR__ . "/footer.php");
    echo '</body>';
 }
 ?>
