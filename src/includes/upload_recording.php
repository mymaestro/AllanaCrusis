<?php
//insert_recordings.php
/*
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2022, 2025 All rights reserved.
#############################################################################
*/

define('PAGE_TITLE', 'Upload Recording');
define('PAGE_NAME', 'upload_recording');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

// Settings
$maxFileSize = 40 * 1024 * 1024; // 40 MB
// You might need to adjust these settings in your php.ini file as well

ini_set('upload_max_filesize', '40M');
ini_set('post_max_size', '40M');

$uploadDir = __DIR__ . "/" . ORGPUBLIC ; // Directory to save uploaded files

// Check if the getID3 library is available
if (!file_exists('../getID3/getid3/getid3.php')) {
    ferror_log("getID3 library not found at: ".__DIR__ . "../getID3/getid3/getid3.php");
    die("getID3 library not found. Please ensure it is installed in the correct path.");
} else {
    ferror_log("getID3 library found at: ../getID3/getid3/getid3.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['link'])) {

    require_once('../getID3/getid3/getid3.php');

    // Create the uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true) && ferror_log("Uploads directory created: " . $uploadDir);
    } else {
        ferror_log("Uploads directory already exists: " . $uploadDir);  
    }

    $file = $_FILES['link'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error code: " . $file['error']);
    }

    // Check file size
    if ($file['size'] > $maxFileSize) {
        die("File is too large. Max allowed size is 20MB.");
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
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
    $destination = $uploadDir . $safeName;

    // Move the uploaded file
    ferror_log("Attempting to move uploaded file from: " . $file['tmp_name'] . " to: " . $destination);
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to save the uploaded file.");
    }

    // Analyze and save metadata as JSON
    $audio = new getID3();
    $info = $audio->analyze($destination);
    if (isset($info['error'])) {
        die("Error analyzing audio file: " . implode(', ', $info['error']));
    }

    $metaFile = $uploadDir . 'meta_' . $safeName . '.json';
    //file_put_contents($metaFile, json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Audio file uploaded and metadata saved.<br>";
    echo "File: " . htmlspecialchars(basename($safeName)) . "<br>";
    echo "Metadata: " . htmlspecialchars(basename($metaFile));

    ferror_log("Audio file uploaded and metadata saved.");
    ferror_log("File: " . htmlspecialchars(basename($safeName)));
    ferror_log("Metadata: " . htmlspecialchars(basename($metaFile)));

} else {
    echo "No file uploaded.";
    ferror_log("You shall not pass. No file uploaded.");
}