<?php
/*
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2022, 2025 All rights reserved.
#############################################################################
*/

function f_getIP() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                // trim for safety measures
                $ip = trim($ip);
                // attempt to validate IP
                ferror_log("Detected IP address: " . $ip);
                if (f_validateIP($ip)) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}
/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function f_validateIP($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

/* Connect to the database */
function f_sqlConnect($dbhost, $user, $pass, $db) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $link = mysqli_connect($dbhost, $user, $pass);
    if (mysqli_connect_errno()) {
        printf("Database connection failed: %s\n", mysqli_connect_error());
        exit();
    }
    /* Allow UTF characters to display properly */
    mysqli_set_charset($link, DB_CHARSET );
    $db_selected = mysqli_select_db($link, $db);
    if (!$db_selected) {
        die('Can\'t use ' . $db . ": " . mysqli_error($link));
    }

    return $link;
}

function f_mysqlEscape($text) {
    global $link;
    return mysqli_real_escape_string($link, $text);
}

/* Protect against injection attacks */
function f_clean($link, $array) {
    return array_map('f_mysqlEscape', $array);
}

/* Check if the table exists */
function f_tableExists($link, $tablename, $database = false) {
    if (!$database) {
        $res = mysqli_query($link, "SELECT DATABASE()");
        $database = mysqli_fetch_array($res, 0);
    }
    $res = mysqli_query($link, "SHOW TABLES LIKE '$tablename'");
    return mysqli_num_rows($res) > 0;
}

/* Check if the field exists */
/* This function doesn't work with mysqli :( */
function f_fieldExists($link, $table, $column, $column_attr = "VARCHAR( 255 ) NULL") {
    $exists = false;
    $columns = mysqli_query($link, "SHOW COLUMNS FROM $table LIKE '".$column."'");
    //ferror_log("SQL: $sql ". "returns ". $num_rows . " rows.");
    $exists = ( mysqli_num_rows($columns) )?TRUE:FALSE;
    if (!$exists) {
        ferror_log("ALTER TABLE `$table` ADD `$column` $column_attr");
        if (mysqli_query($link, "ALTER TABLE `$table` ADD `$column` $column_attr")) {
            return TRUE;
        }
    } else {
        return TRUE;
    }
    return FALSE;
}

/* Custom error logging */
function ferror_log($error){
    if (DEBUG == 1) {
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        } else {
            $username = 'anonymous';
        }
        error_log($username."> ".$error);
    }
}
?>
