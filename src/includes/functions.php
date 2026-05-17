<?php
/*
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble, 2022, 2026 All rights reserved.
#############################################################################
*/

if (!defined('FERROR_LOG_DEBUG')) define('FERROR_LOG_DEBUG', 0);
if (!defined('FERROR_LOG_INFO')) define('FERROR_LOG_INFO', 1);
if (!defined('FERROR_LOG_WARN')) define('FERROR_LOG_WARN', 2);
if (!defined('FERROR_LOG_ERROR')) define('FERROR_LOG_ERROR', 3);

$ferror_log_level_names = [
    FERROR_LOG_DEBUG => 'DEBUG',
    FERROR_LOG_INFO => 'INFO',
    FERROR_LOG_WARN => 'WARN',
    FERROR_LOG_ERROR => 'ERROR'
];

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

/* Ensure the mysqli connection is still alive for long-running requests */
function f_sqlEnsureConnection($link, $dbhost, $user, $pass, $db) {
    if (!($link instanceof mysqli)) {
        ferror_log("Database link was invalid. Creating a new connection.", FERROR_LOG_WARN);
        return f_sqlConnect($dbhost, $user, $pass, $db);
    }

    try {
        if (mysqli_ping($link)) {
            return $link;
        }
    } catch (Throwable $e) {
        ferror_log("Database ping failed: " . $e->getMessage(), FERROR_LOG_WARN);
    }

    ferror_log("Database connection dropped during request. Reconnecting.", FERROR_LOG_WARN);
    try {
        mysqli_close($link);
    } catch (Throwable $e) {
        // Ignore close failures and proceed with reconnect.
    }

    return f_sqlConnect($dbhost, $user, $pass, $db);
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
function ferror_log($message, $level = FERROR_LOG_INFO) {
    global $ferror_log_level_names;

    if (is_string($level)) {
        $level_map = [
            'DEBUG' => FERROR_LOG_DEBUG,
            'INFO' => FERROR_LOG_INFO,
            'WARN' => FERROR_LOG_WARN,
            'WARNING' => FERROR_LOG_WARN,
            'ERROR' => FERROR_LOG_ERROR
        ];
        $level = $level_map[strtoupper($level)] ?? FERROR_LOG_INFO;
    }

    // DEBUG is treated as the minimum log level threshold (0=DEBUG ... 3=ERROR).
    $threshold = defined('DEBUG') ? (int) DEBUG : FERROR_LOG_INFO;
    if ($threshold < FERROR_LOG_DEBUG || $threshold > FERROR_LOG_ERROR) {
        return;
    }
    if ($level < $threshold) {
        return;
    }

    $username = $_SESSION['username'] ?? $_SESSION['user'] ?? 'anonymous';
    //$timestamp = date('Y-m-d H:i:s');
    $level_name = $ferror_log_level_names[$level] ?? 'UNKNOWN';
    $log_entry = "[$level_name] [$username] $message";

    error_log($log_entry);
}

/* Get chunked upload configuration as JSON for JavaScript */
function getChunkedUploadConfig() {
    return json_encode([
        'enabled' => defined('CHUNKED_UPLOAD_ENABLED') ? CHUNKED_UPLOAD_ENABLED : true,
        'chunkSizeMB' => defined('CHUNK_SIZE_MB') ? CHUNK_SIZE_MB : 2,
        'thresholdMB' => defined('CHUNKED_UPLOAD_THRESHOLD_MB') ? CHUNKED_UPLOAD_THRESHOLD_MB : 7,
        'chunkSizeBytes' => (defined('CHUNK_SIZE_MB') ? CHUNK_SIZE_MB : 2) * 1024 * 1024,
        'thresholdBytes' => (defined('CHUNKED_UPLOAD_THRESHOLD_MB') ? CHUNKED_UPLOAD_THRESHOLD_MB : 7) * 1024 * 1024
    ]);
}

function f_mailHeaderSafe($value) {
    return trim(str_replace(["\r", "\n"], '', (string) $value));
}

function f_mailDomainFromAddress($email) {
    if (!is_string($email) || strpos($email, '@') === false) {
        return '';
    }
    $parts = explode('@', $email);
    $domain = strtolower(end($parts));
    return preg_replace('/[^a-z0-9\.-]/i', '', $domain);
}

function f_mailMessageIdDomain() {
    $orgMailDomain = f_mailDomainFromAddress(defined('ORGMAIL') ? ORGMAIL : '');
    if ($orgMailDomain !== '') {
        return $orgMailDomain;
    }

    $host = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost.localdomain');
    $host = strtolower(f_mailHeaderSafe($host));
    $host = preg_replace('/:[0-9]+$/', '', $host);
    $host = preg_replace('/[^a-z0-9\.-]/i', '', $host);
    return $host !== '' ? $host : 'localhost.localdomain';
}

function f_sendEmail($to, $subject, $message, $options = []) {
    $to = trim((string) $to);
    $subject = f_mailHeaderSafe($subject);

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        ferror_log('Email send aborted: invalid recipient address', FERROR_LOG_WARN);
        return false;
    }
    if ($subject === '') {
        ferror_log('Email send aborted: empty subject', FERROR_LOG_WARN);
        return false;
    }

    $configuredFrom = defined('ORGMAIL') ? trim((string) ORGMAIL) : '';
    $requestedFrom = isset($options['from']) ? trim((string) $options['from']) : '';
    $replyTo = isset($options['replyTo']) ? trim((string) $options['replyTo']) : '';
    $isHtml = !empty($options['isHtml']);
    $context = isset($options['context']) ? f_mailHeaderSafe($options['context']) : 'app';
    $actor = isset($options['actor']) ? f_mailHeaderSafe($options['actor']) : 'system';

    $from = $configuredFrom;
    if ($requestedFrom !== '' && filter_var($requestedFrom, FILTER_VALIDATE_EMAIL)) {
        $requestedDomain = f_mailDomainFromAddress($requestedFrom);
        $configuredDomain = f_mailDomainFromAddress($configuredFrom);
        if ($requestedDomain !== '' && $configuredDomain !== '' && $requestedDomain === $configuredDomain) {
            $from = $requestedFrom;
        } else {
            if ($replyTo === '') {
                $replyTo = $requestedFrom;
            }
        }
    }

    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        ferror_log('Email send aborted: invalid configured sender address', FERROR_LOG_ERROR);
        return false;
    }

    if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $replyTo = '';
    }

    $fromName = defined('ORGNAME') ? f_mailHeaderSafe(ORGNAME) : 'Music Library';
    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $from . '>';
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Date: ' . date('r');

    try {
        $messageIdLeft = bin2hex(random_bytes(12));
    } catch (Throwable $e) {
        $messageIdLeft = uniqid('', true);
    }
    $headers[] = 'Message-ID: <' . $messageIdLeft . '@' . f_mailMessageIdDomain() . '>';
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    $headers[] = 'X-Auto-Response-Suppress: All';

    if ($isHtml) {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
    } else {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    }
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $additionalParams = '-f ' . escapeshellarg($from);
    $phpWarning = null;
    set_error_handler(function ($errno, $errstr) use (&$phpWarning) {
        $phpWarning = $errstr;
        return true;
    });

    try {
        $sent = mail($to, $subject, (string) $message, implode("\r\n", $headers), $additionalParams);
    } finally {
        restore_error_handler();
    }

    if (!$sent) {
        $warn = $phpWarning ? (' warning=' . $phpWarning) : '';
        ferror_log('Email send failed context=' . $context . ' actor=' . $actor . ' to=' . $to . ' subject=' . $subject . $warn, FERROR_LOG_WARN);
        return false;
    }

    ferror_log('Email sent context=' . $context . ' actor=' . $actor . ' to=' . $to . ' subject=' . $subject . ' from=' . $from, FERROR_LOG_INFO);
    return true;
}