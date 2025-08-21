<?php
// Settings
$uploadDir = __DIR__ . '/uploads/';
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Create the uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function generatePartFilename($title, $composer, $instrument, $ext = 'pdf') {
    $input = trim("$title - $composer - $instrument");
    $hash = substr(md5($input), 0, 12); // or sha1() for longer hash
    return $hash . '.' . $ext;
}

$hash = generatePartHash($title, $composer, $instrument);
$targetPath = "uploads/$hash.pdf";

move_uploaded_file($_FILES['pdfpart']['tmp_name'], $targetPath);


// Include the FPDI library
// Make sure you have installed the FPDI library via Composer or manually
// composer require setasign/fpdi
// or download from https://www.setasign.com/products/fpdi/download/
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Please run 'composer install' to install dependencies.");
}



require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;

// Settings
$uploadDir = __DIR__ . '/uploads/';
$maxFileSize = 5 * 1024 * 1024; // 5 MB

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {
    $file = $_FILES['pdfFile'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error code: " . $file['error']);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        die("Only PDF files are allowed.");
    }

    if ($file['size'] > $maxFileSize) {
        die("File is too large. Max allowed size is 5MB.");
    }

    $safeName = uniqid('doc_', true) . '.pdf';
    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to save the uploaded file.");
    }

    // Inject metadata
    $newFile = $uploadDir . 'meta_' . $safeName;

    $pdf = new FPDI();
    $pageCount = $pdf->setSourceFile($destination);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tpl = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tpl);
    }

    // Set metadata
    $pdf->SetTitle("User Uploaded Document");
    $pdf->SetAuthor("Uploaded by Web User");

    $pdf->Output('F', $newFile);

    // Optionally delete original file
    unlink($destination);

    echo "PDF uploaded and metadata added. Saved as: " . basename($newFile);
} else {
    echo "No file uploaded.";
}

embedPdfMetadata($targetPath, $title, $composer, $instrument);
// Add this .htaccess file to the uploads
//
// <FilesMatch "\.pdf$">
//   Order allow,deny
//   Deny from all
// </FilesMatch>
// 

// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {
    $file = $_FILES['pdfFile'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error code: " . $file['error']);
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        die("Only PDF files are allowed.");
    }

    // Check file size
    if ($file['size'] > $maxFileSize) {
        die("File is too large. Max allowed size is 5MB.");
    }

    // Generate safe, unique file name
    $safeName = uniqid('doc_', true) . '.pdf';

    // Move file to upload directory
    $destination = $uploadDir . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to save the uploaded file.");
    }

    echo "File uploaded successfully as: $safeName";
} else {
    echo "No file uploaded.";
}
?>
