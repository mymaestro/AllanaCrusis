<?php
// upload_chunk.php
/*
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2026 All rights reserved.
#############################################################################
*/

define('PAGE_TITLE', 'Upload Chunk Handler');
define('PAGE_NAME', 'upload_chunk');
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

// Disable output buffering for chunk uploads
if (ob_get_level()) ob_end_clean();

header('Content-Type: application/json');

// Verify this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Required parameters
$requiredParams = ['fileName', 'chunkIndex', 'totalChunks', 'uploadId'];
foreach ($requiredParams as $param) {
    if (!isset($_POST[$param])) {
        echo json_encode(['status' => 'error', 'message' => "Missing parameter: $param"]);
        exit;
    }
}

// Validate chunk file
if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = isset($_FILES['chunk']) ? $_FILES['chunk']['error'] : 'No file';
    echo json_encode(['status' => 'error', 'message' => 'Chunk upload error: ' . $errorCode]);
    exit;
}

// Get parameters
$fileName = basename($_POST['fileName']); // Sanitize filename
$chunkIndex = intval($_POST['chunkIndex']);
$totalChunks = intval($_POST['totalChunks']);
$uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['uploadId']); // Sanitize upload ID

// Create temporary directory for chunks
$tempDir = sys_get_temp_dir() . '/allanacrusis_uploads/';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Create upload-specific directory
$uploadDir = $tempDir . $uploadId . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Save chunk
$chunkFileName = $uploadDir . 'chunk_' . str_pad($chunkIndex, 4, '0', STR_PAD_LEFT);
if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkFileName)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save chunk']);
    exit;
}

ferror_log("Saved chunk $chunkIndex of $totalChunks for upload $uploadId");

// Check if all chunks are uploaded
$uploadedChunks = glob($uploadDir . 'chunk_*');
$uploadedCount = count($uploadedChunks);

if ($uploadedCount >= $totalChunks) {
    // All chunks received, reassemble the file
    ferror_log("All chunks received for upload $uploadId, assembling file...");
    
    $finalFile = $uploadDir . $fileName;
    $output = fopen($finalFile, 'wb');
    
    if (!$output) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create final file']);
        exit;
    }
    
    // Sort chunks by name to ensure correct order
    sort($uploadedChunks);
    
    // Concatenate all chunks
    foreach ($uploadedChunks as $chunkFile) {
        $chunk = fopen($chunkFile, 'rb');
        if ($chunk) {
            while (!feof($chunk)) {
                fwrite($output, fread($chunk, 8192));
            }
            fclose($chunk);
            unlink($chunkFile); // Delete chunk after processing
        }
    }
    
    fclose($output);
    
    // Verify file exists and has content
    if (!file_exists($finalFile) || filesize($finalFile) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to assemble file']);
        exit;
    }
    
    ferror_log("File assembled successfully: $finalFile (" . filesize($finalFile) . " bytes)");
    
    echo json_encode([
        'status' => 'complete',
        'message' => 'File uploaded successfully',
        'uploadedChunks' => $uploadedCount,
        'totalChunks' => $totalChunks,
        'filePath' => $finalFile,
        'fileName' => $fileName,
        'fileSize' => filesize($finalFile)
    ]);
} else {
    // More chunks expected
    echo json_encode([
        'status' => 'progress',
        'message' => "Chunk $chunkIndex uploaded",
        'uploadedChunks' => $uploadedCount,
        'totalChunks' => $totalChunks
    ]);
}

exit;
