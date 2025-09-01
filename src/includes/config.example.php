<?php
/*
#############################################################################
# Licensed Materials - Property of ACWE
# (C) Copyright Austin Civic Wind Ensemble 2020, 2022, 2025. All rights reserved.
#############################################################################
*/
define('ORGNAME', '4th Wind');
define('ORGDESC', 'Fourth Wind Wind Ensemble');
// with trailing slash
define('ORGHOME', 'http://library.local/');
define('ORGRECORDINGS', 'http://library.local/files/recordings/'); 
/* Define the path to the recordings directory.
 * This is used for file uploads and downloads.
 * Make sure this path is correct and accessible by the web server. */
define('ORGPUBLIC', '../../public/files/recordings/'); // Where to put recordings relative to src/includes
define('ORGPRIVATE', '/home/user/files/'); // Where to put parts and distributions - use a full path outside the web server public

define('ORGLOGO', 'images/logo.png');
define('ORGMAIL', 'librarian@musicLibraryDB.com');
define('DB_HOST', 'localhost');
define('DB_NAME', 'musicLibraryDB');
define('DB_USER', 'musicLibraryDB');
define('DB_PASS', 'superS3cretPa$$wo4d');
define('DB_CHARSET', 'utf8mb4');
define('REGION', 'HOME');
define('DEBUG', 1);
?>
