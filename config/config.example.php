<?php
/*
#############################################################################
# Licensed Materials - Property of ACWE
# (C) Copyright Austin Civic Wind Ensemble 2020, 2022, 2025. All rights reserved.
#############################################################################
*/
// Organization branding
define('ORGNAME', '4th Wind'); // Short name or acronym
define('ORGDESC', 'Fourth Wind Wind Ensemble'); // Full organization name
define('ORGLOGO', 'images/logo.png'); // Path to logo image
define('ORGMAIL', 'librarian@musicLibraryDB.com'); // Contact email

// Web root and public URLs (with trailing slash)
define('ORGHOME', 'http://library.local/'); // Main site URL
define('ORGRECORDINGS', 'http://library.local/files/recordings/'); // Public URL for recordings

// Secure file storage (recommended: outside web root)
define('ORGPUBLIC', '../../public/files/recordings/'); // Directory for recordings (relative to src/includes)
define('ORGPRIVATE', '/home/user/files/'); // Directory for parts/distributions (absolute path, outside web root)

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'musicLibraryDB');
define('DB_USER', 'musicLibraryDB');
define('DB_PASS', 'superS3cretPa$$wo4d');
define('DB_CHARSET', 'utf8mb4');

// Region/homepage
define('REGION', 'HOME');

// Debug mode (set to 1 for verbose error logging)
define('DEBUG', 1);
?>
