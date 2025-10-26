<?php
// sound.php - Secure email handler for part delivery
// Require user to be logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// Rate limiting - max 5 emails per user per hour
$rateLimitKey = 'email_count_' . $_SESSION['username'];
$currentHour = date('Y-m-d-H');
$sessionKey = $rateLimitKey . '_' . $currentHour;

if (!isset($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = 0;
}

if ($_SESSION[$sessionKey] >= 5) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded. Maximum 5 emails per hour.']);
    exit;
}
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/functions.php');
ferror_log("sound.php accessed by user: " . $_SESSION['username']);

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate input
$to = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$from = isset($_POST['from']) ? trim($_POST['from']) : ORGMAIL;
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Notification from Music Library';
$isHtml = isset($_POST['is_html']) ? ($_POST['is_html'] == '1' || $_POST['is_html'] === true) : false;

// Validate email addresses first
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient email address.']);
    exit;
}
if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid sender email address.']);
    exit;
}

// Validate and sanitize message content
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit;
}
if (empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Subject cannot be empty.']);
    exit;
}

// Anti-spam content validation
$suspiciousPatterns = [
    '/\b(viagra|cialis|casino|lottery|winner|congratulations)\b/i',
    '/\$\d+/i', // Dollar amounts
    '/\b(click here|urgent|act now)\b/i',
    '/\b(free money|guaranteed|no cost)\b/i'
];

foreach ($suspiciousPatterns as $pattern) {
    if (preg_match($pattern, $message) || preg_match($pattern, $subject)) {
        ferror_log("sound.php: Suspicious content detected from user: " . $_SESSION['username']);
        echo json_encode(['success' => false, 'message' => 'Message contains content that may be flagged as spam.']);
        exit;
    }
}

// Improve subject line to avoid spam triggers
if (!preg_match('/^\[' . ORGNAME . '\]/', $subject)) {
    $subject = '[' . ORGNAME . '] ' . $subject;
}

// Format message with professional footer
$formattedMessage = $message;

// Add professional footer
$footer = "\n\n---\n";
$footer .= "This message was sent from " . ORGNAME . " Music Library\n";
$footer .= "Sender: " . $_SESSION['username'] . "\n";
$footer .= "Time: " . date('Y-m-d H:i:s T') . "\n";
if (defined('ORGHOME') && constant('ORGHOME')) {
    $footer .= "Website: " . constant('ORGHOME') . "\n";
}

if ($isHtml) {
    $footer = str_replace("\n", "<br>\n", $footer);
    $footer = str_replace("---", "<hr>", $footer);
    $formattedMessage = $message . $footer;
} else {
    $formattedMessage = $message . $footer;
}

// Build headers with anti-spam improvements
$headers = "From: $from\r\n";
$headers .= "Reply-To: $from\r\n";
$headers .= "Return-Path: $from\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "X-Priority: 3\r\n";
$headers .= "MIME-Version: 1.0\r\n";

// Add Message-ID to improve deliverability
$messageId = '<' . uniqid() . '@' . $_SERVER['HTTP_HOST'] . '>';
$headers .= "Message-ID: $messageId\r\n";

// Add Date header
$headers .= "Date: " . date('r') . "\r\n";

// Content-Type with proper MIME structure
if ($isHtml) {
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
} else {
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
}

// Send email with formatted message
$mailSuccess = mail($to, $subject, $formattedMessage, $headers);

// Log the attempt
$logUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';
$logMsg = sprintf(
    'sound.php: user=%s, to=%s, subject=%s, result=%s',
    $logUser,
    $to,
    $subject,
    $mailSuccess ? 'success' : 'fail'
);
if (function_exists('ferror_log')) {
    ferror_log($logMsg);
} else {
    error_log($logMsg);
}

if ($mailSuccess) {
    // Increment rate limit counter
    $_SESSION[$sessionKey]++;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
}
?>
