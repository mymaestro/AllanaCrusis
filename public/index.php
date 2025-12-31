<?php

error_log("Accessing URL: " . $_SERVER['REQUEST_URI']);
include(__DIR__ . '/../config/bootstrap.php');

$urlMap = [
   '/about' => 'about.php',
   '/admin_verifications' => 'admin_verifications.php',
   '/composition_instrumentation' => 'composition_instrumentation.php',
   '/compositions' => 'compositions.php',
   '/comps2csv' => 'comps2csv.php',
   '/concerts' => 'concerts.php',
   '/enable_disable_manager' => 'enable_disable_manager.php',
   '/ensembles' => 'ensembles.php',
   '/genres' => 'genres.php',
   '/HelloWorld' => 'HelloWorld.php',
   '/home' => 'home.php',
   '/instrumentsorderlist' => 'instrumentsorderlist.php',
   '/instruments' => 'instruments.php',
   '/login_newpassword' => 'login_newpassword.php',
   '/login' => 'login.php',
   '/login_reset' => 'login_reset.php',
   '/logout' => 'logout.php',
   '/papersizes' => 'papersizes.php',
   '/partcollections' => 'partcollections.php',
   '/part_delivery' => 'part_delivery.php',
   '/part_distribution' => 'part_distribution.php',
   '/partsections' => 'partsections.php',
   '/parts' => 'parts.php',
   '/parttypesorderlist' => 'parttypesorderlist.php',
   '/parttypes' => 'parttypes.php',
   '/playgram_builder' => 'playgram_builder.php',
   '/playgramsorderlist' => 'playgramsorderlist.php',
   '/playgrams' => 'playgrams.php',
   '/privacy-statement' => 'privacy-statement.php',
   '/recordings' => 'recordings.php',
   '/register' => 'register.php',
   '/reports' => 'reports.php',
   '/search' => 'search.php',
   '/sections' => 'sections.php',
   '/settings' => 'settings.php',
   '/terms-conditions' => 'terms-conditions.php',
   '/users' => 'users.php',
   '/verify_email' => 'verify_email.php',
   '/welcome' => 'welcome.php',
   '/' => 'index.php'
];

$actionRoleMap = [
    'admin_'   => 'admin',
    'delete_'  => 'librarian',
    'insert_'  => 'librarian',
    'update_'  => 'librarian',
    'upload_'  => 'librarian',
    'download_' => 'librarian'
    // Add more as needed
];

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'email_verification') {
        require_once __DIR__ . '/../src/includes/email_verification.php';
        exit;
    }
    if ($action === 'password_reset') {
        require_once __DIR__ . '/../src/includes/password_reset.php';
        exit;
    }
    if ($action === 'reset_password') {
        require_once __DIR__ . '/../src/includes/reset_password.php';
        exit;
    }
    if ($action === 'verify_email') {
        require_once __DIR__ . '/../src/verify_email.php';
        exit;
    }
    if ($action === 'sound') {
        require_once __DIR__ . '/../src/includes/sound.php';
        exit;
    }

    // Only allow certain prefixes for security
    // Function to get required role for an action
    function getRequiredRole($action, $map) {
        // fetch_ and select_ actions are always allowed
        if (strpos($action, 'fetch_') === 0 || strpos($action, 'select_') === 0) {
            return null;
        }
        foreach ($map as $prefix => $role) {
            if (strpos($action, $prefix) === 0) {
                return $role;
            }
        }
        return null; // No restriction
    }
    if (preg_match('/^(admin_|fetch_|insert_|delete_|download_|search_|select_|update_|upload_)[a-zA-Z0-9_]+$/', $action)) {
        $requiredRole = getRequiredRole($action, $actionRoleMap);
        if ($requiredRole) {
            if (!isset($_SESSION['roles']) || strpos($_SESSION['roles'], $requiredRole) === false) {
                http_response_code(403);
                echo "Access denied.";
                exit;
            }
        }
        $file = __DIR__ . '/../src/includes/' . $action . '.php';
        if (file_exists($file)) {
            require $file;
            exit;
        } else {
            http_response_code(404);
            error_log("Action file not found.");
            exit;
        }
    } else {
        http_response_code(400);
        error_log("Invalid action.");
        exit;
    }
}

// Extract the path from REQUEST_URI (ignore query string)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash except for root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Route /d/{token} to download_token.php
if (preg_match('#^/d/([a-f0-9]{32})$#', $requestUri, $matches)) {
    // Pass token as GET param for handler
    $_GET['token'] = $matches[1];
    include(__DIR__ . '/../src/download_token.php');
    exit;
}

if (isset($urlMap[$requestUri])) {
    include(__DIR__ . '/../src/' . $urlMap[$requestUri]);
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    include(__DIR__ . '/../src/error.php');
}
