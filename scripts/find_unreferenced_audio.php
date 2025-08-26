<?php
// find_unreferenced_audio.php
// Utility to list audio and meta files not referenced in the recordings table


require_once __DIR__ . '/../src/includes/config.php';
require_once __DIR__ . '/../src/includes/functions.php';

function debug_log($msg) {
    if (defined('DEBUG') && DEBUG) {
        fwrite(STDERR, "[DEBUG] $msg\n");
    }
}

// Connect to DB
$link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get all referenced file names from the recordings table
$referenced = [];
$res = mysqli_query($link, "SELECT link FROM recordings WHERE link IS NOT NULL AND link != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $referenced[] = $row['link'];
}
$referencedSet = array_flip($referenced);

$baseDir = realpath(ORGPUBLIC);
if ($baseDir === false) {

    debug_log('ORGPUBLIC: ' . ORGPUBLIC);

    $baseDir = realpath(ORGPUBLIC);
    if ($baseDir === false) {
        $baseDir = realpath(__DIR__ . '/../public/' . ORGPUBLIC);
    }
    debug_log('Resolved recordings directory: ' . ($baseDir ?: 'NOT FOUND'));

    if ($baseDir === false) {
        fwrite(STDERR, "Error: Could not resolve recordings directory (ORGPUBLIC).\n");
        exit(1);
    }
}
$scanMsg = 'Scanning directory: ' . $baseDir;
debug_log($scanMsg);
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$audioFiles = [];
$jsonFiles = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $basename = $file->getFilename();
    $pathname = $file->getPathname();
    debug_log('Found file: ' . $basename);
    if (preg_match('/\.(mp3|ogg|flac)$/i', $basename)) {
        $audioFiles[$basename] = $pathname;
    } elseif (preg_match('/^meta_audio.*\.json$/i', $basename)) {
        $jsonFiles[$basename] = $pathname;
    }
}

// 1. List audio files not referenced in the database
$unrefAudio = [];
foreach ($audioFiles as $basename => $pathname) {
    if (!isset($referencedSet[$basename])) {
        $unrefAudio[] = $pathname;
    }
}

// 2. List JSON files that reference any audio file not in the database
$jsonReferencingUnref = [];
foreach ($jsonFiles as $jsonPath) {
    $json = @file_get_contents($jsonPath);
    if ($json) {
        $data = @json_decode($json, true);
        if (is_array($data)) {
            $referencedInJson = [];
            $possibleFields = ['filename', 'filepath', 'filenamepath', 'file', 'audiofile', 'name'];
            foreach ($possibleFields as $field) {
                if (!empty($data[$field]) && is_string($data[$field])) {
                    $referencedInJson[] = basename($data[$field]);
                }
            }
            $flat = new RecursiveIteratorIterator(new RecursiveArrayIterator($data));
            foreach ($flat as $k => $v) {
                if (is_string($v) && preg_match('/\.(mp3|ogg|flac)$/i', $v)) {
                    $referencedInJson[] = basename($v);
                }
            }
            foreach ($referencedInJson as $audioBase) {
                if ($audioBase && !isset($referencedSet[$audioBase])) {
                    $jsonReferencingUnref[] = $jsonPath;
                    break; // Only need to list each JSON file once
                }
            }
        }
    }
}

function shell_escape($filename) {
    // Escape single quotes and wrap in single quotes for shell safety
    return "'" . str_replace("'", "'\\''", $filename) . "'";
}

if (!empty($unrefAudio)) {
    foreach ($unrefAudio as $f) {
        echo shell_escape($f) . "\n";
    }
}
if (!empty($jsonReferencingUnref)) {
    foreach ($jsonReferencingUnref as $f) {
        echo shell_escape($f) . "\n";
    }
}
