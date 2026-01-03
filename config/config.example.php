<?php
/*
#############################################################################
# Licensed Materials - Property of ACWE
# (C) Copyright Austin Civic Wind Ensemble 2020, 2022, 2025, 2026. All rights reserved.
#############################################################################
*/

/**
 * INSTALLATION & SETUP INSTRUCTIONS
 * 
 * 1. Copy this file to config.php in the same directory
 * 2. Edit the DATABASE SETTINGS section below with your database credentials
 * 3. Customize the DEFAULT VALUES as needed for your organization
 * 4. Save the file and access the application
 * 5. Once running, log in as an admin and visit /settings to manage
 *    configuration values through the web interface (values are stored in the database)
 * 
 * How it works:
 * - This file first loads database credentials
 * - Then attempts to load configuration from the 'config' database table
 * - If the database is unavailable, it falls back to the defaults defined here
 * - This allows configuration to be managed both programmatically and via web UI
 */

// ============================================================================
// DATABASE CREDENTIALS (REQUIRED)
// ============================================================================
// Database credentials (required for all connections)
// Edit these to match your database setup

define('DB_HOST', 'localhost');
define('DB_NAME', 'musicLibraryDB');
define('DB_USER', 'musicLibraryDB');
define('DB_PASS', 'superS3cretPa$$wo4d');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// FILE UPLOAD PATHS
// ============================================================================
// Edit these to match your hosting filesystem setup
// Relative to the config.php file location for security

define('ORGPUBLIC', '../../public/files/recordings/'); // Publicly accessible files, recordings
define('ORGPRIVATE', __DIR__ . '/../files/'); // Private files, part PDFs, parts distributions

// ============================================================================
// CONFIGURATION LOADER
// ============================================================================
// Loads settings from database with fallback to defaults below

/**
 * Load configuration from database
 * Falls back to default values if database is unavailable
 */
function loadConfigFromDatabase() {
    $defaults = [
        'ORGNAME' => '4th Wind',
        'ORGDESC' => 'Fourth Wind Wind Ensemble',
        'ORGHOME' => 'http://musiclibrary.local/',
        'ORGLOGO' => 'images/logo.png',
        'ORGMAIL' => 'librarian@musicLibraryDB.com',
        'ORGRECORDINGS' => 'http://musiclibrary.local/files/recordings/',
        'DOWNLOAD_TOKEN_EXPIRY_DAYS' => 5,
        'REGION' => 'HOME',
        'DEBUG' => 0
    ];
    
    try {
        // Attempt to connect and load from database
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            throw new Exception("Database connection failed");
        }
        
        $connection->set_charset(DB_CHARSET);
        
        // Check if config table exists
        $tableCheck = $connection->query("SHOW TABLES LIKE 'config'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            throw new Exception("Config table does not exist");
        }
        
        // Load all config values including upload settings
        $uploadKeys = ['CHUNKED_UPLOAD_ENABLED', 'CHUNK_SIZE_MB', 'CHUNKED_UPLOAD_THRESHOLD_MB'];
        $allKeys = array_merge(array_keys($defaults), $uploadKeys);
        $result = $connection->query("SELECT `config_key`, `value`, `type` FROM config WHERE `config_key` IN ('" . implode("','", $allKeys) . "')");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['config_key'];
                $value = $row['value'];
                $type = $row['type'];
                
                // Type casting
                if ($type === 'integer') {
                    $value = (int)$value;
                } elseif ($type === 'boolean') {
                    $value = (int)$value;
                }
                
                // Validate upload settings to prevent DoS/misconfiguration
                if ($key === 'CHUNK_SIZE_MB') {
                    $value = max(1, min(100, $value)); // Clamp to 1-100 MB
                } elseif ($key === 'CHUNKED_UPLOAD_THRESHOLD_MB') {
                    $value = max(1, min(500, $value)); // Clamp to 1-500 MB
                }
                
                define($key, $value);
            }
        }
        
        $connection->close();
        
        // Define any missing settings from defaults
        foreach ($defaults as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
        
    } catch (Exception $e) {
        // Database unavailable or config table doesn't exist - use defaults
        error_log("Config: Using default values - " . $e->getMessage());
        foreach ($defaults as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Load configuration
loadConfigFromDatabase();

// ============================================================================
// DEFAULT VALUES FOR NEW INSTALLATIONS
// ============================================================================
// These are fallback values if database is not yet created
// Edit these before first run, then manage via /settings page in the admin panel
// NOTE: File storage paths (ORGPUBLIC, ORGPRIVATE) are NOT stored in database
//       for security reasons and must be edited here in the config file

// Organization branding
if (!defined('ORGNAME'))        define('ORGNAME', '4th Wind');
if (!defined('ORGDESC'))        define('ORGDESC', 'Fourth Wind Wind Ensemble');
if (!defined('ORGLOGO'))        define('ORGLOGO', 'images/logo.png');
if (!defined('ORGMAIL'))        define('ORGMAIL', 'librarian@musicLibraryDB.com');

// Web URLs (with trailing slashes)
if (!defined('ORGHOME'))        define('ORGHOME', 'http://musiclibrary.local/');
if (!defined('ORGRECORDINGS'))  define('ORGRECORDINGS', 'http://musiclibrary.local/files/recordings/');

// System settings
if (!defined('DOWNLOAD_TOKEN_EXPIRY_DAYS')) define('DOWNLOAD_TOKEN_EXPIRY_DAYS', 5);
if (!defined('REGION'))         define('REGION', 'HOME');
if (!defined('DEBUG'))          define('DEBUG', 0);

// ============================================================================
// CHUNKED UPLOAD CONFIGURATION
// ============================================================================
// These settings are now stored in the database and can be managed via the
// Settings page (/settings.php) in the admin panel.
//
// Fallback values below are used if database is unavailable.
//
// IMPORTANT: When adjusting via admin panel:
//   - CHUNK_SIZE_MB should be less than PHP post_max_size
//   - CHUNKED_UPLOAD_THRESHOLD_MB should be less than PHP post_max_size
//   - Values are automatically clamped to safe ranges (1-100 MB for chunks, 1-500 MB for threshold)
//
// Current PHP settings can be checked with: php -i | grep -E "upload_max|post_max"
//
// Recommended presets:
//   Development (post_max_size=8M):   CHUNK_SIZE_MB=2,  THRESHOLD=7
//   Production (post_max_size=100M):  CHUNK_SIZE_MB=10, THRESHOLD=80
//   Enterprise (post_max_size=500M):  CHUNK_SIZE_MB=20, THRESHOLD=300

if (!defined('CHUNKED_UPLOAD_ENABLED'))       define('CHUNKED_UPLOAD_ENABLED', true);
if (!defined('CHUNK_SIZE_MB'))                define('CHUNK_SIZE_MB', 2);      // Fallback: 2MB chunks
if (!defined('CHUNKED_UPLOAD_THRESHOLD_MB'))  define('CHUNKED_UPLOAD_THRESHOLD_MB', 7); // Fallback: 7MB threshold

?>
