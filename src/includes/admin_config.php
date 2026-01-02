<?php
// update_config.php
/*
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2026 All rights reserved.
#############################################################################
*/

define('PAGE_TITLE', 'Update Configuration');
define('PAGE_NAME', 'update_config');

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['roles']) || strpos($_SESSION['roles'], 'administrator') === false) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Administrator access required']);
    exit;
}

require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

// Get JSON data from request body
$inputData = json_decode(file_get_contents('php://input'), true);

if (!$inputData || !is_array($inputData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$username = mysqli_real_escape_string($f_link, $_SESSION['username']);
$timestamp = date('Y-m-d H:i:s');
$updatedCount = 0;
$errors = [];

// Get list of read-only keys
$readonlyQuery = "SELECT `config_key` FROM config WHERE is_readonly = 1";
$readonlyResult = mysqli_query($f_link, $readonlyQuery);
$readonlyKeys = [];
while ($row = mysqli_fetch_assoc($readonlyResult)) {
    $readonlyKeys[] = $row['config_key'];
}

// Process each setting
foreach ($inputData as $key => $data) {
    // Validate input
    if (!isset($data['value']) || !isset($data['type'])) {
        $errors[] = "$key: Missing value or type";
        continue;
    }
    
    // Prevent updating readonly settings
    if (in_array($key, $readonlyKeys)) {
        $errors[] = "$key: This setting is read-only";
        continue;
    }
    
    // Sanitize key (alphanumeric and underscore only)
    if (!preg_match('/^[A-Z0-9_]+$/', $key)) {
        $errors[] = "$key: Invalid key format";
        continue;
    }
    
    $value = $data['value'];
    $type = $data['type'];
    
    // Validate type
    $allowedTypes = ['string', 'integer', 'boolean', 'url', 'path', 'email'];
    if (!in_array($type, $allowedTypes)) {
        $errors[] = "$key: Invalid type '$type'";
        continue;
    }
    
    // Type-specific validation
    if ($type === 'integer') {
        if (!is_numeric($value)) {
            $errors[] = "$key: Must be a number";
            continue;
        }
        $value = (int)$value;
    } elseif ($type === 'boolean') {
        $value = (int)$value;
        if ($value !== 0 && $value !== 1) {
            $errors[] = "$key: Must be 0 or 1";
            continue;
        }
    } elseif ($type === 'email') {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$key: Invalid email format";
            continue;
        }
    } elseif ($type === 'url') {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = "$key: Invalid URL format";
            continue;
        }
    }
    
    // Escape for database
    $key = mysqli_real_escape_string($f_link, $key);
    $value = mysqli_real_escape_string($f_link, (string)$value);
    
    // Update the setting
    $updateSql = "UPDATE config SET 
                  `value` = '$value',
                  `updated_at` = '$timestamp',
                  `updated_by` = '$username'
                  WHERE `config_key` = '$key'";
    
    if (mysqli_query($f_link, $updateSql)) {
        if (mysqli_affected_rows($f_link) > 0) {
            $updatedCount++;
            ferror_log("Config updated: $key = $value (by $username)");
        }
    } else {
        $errors[] = "$key: Database error - " . mysqli_error($f_link);
    }
}

mysqli_close($f_link);

// Return response
if (count($errors) > 0 && $updatedCount === 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update settings',
        'errors' => $errors
    ]);
} else {
    $message = "Updated $updatedCount setting" . ($updatedCount !== 1 ? 's' : '');
    if (count($errors) > 0) {
        $message .= '. ' . count($errors) . ' error' . (count($errors) !== 1 ? 's' : '') . ' occurred.';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'updated' => $updatedCount,
        'errors' => $errors
    ]);
}

exit;
