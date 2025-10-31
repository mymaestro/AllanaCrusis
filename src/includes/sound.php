<?php
// sound.php - Secure email handler for part delivery
// Require user to be logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in to send emails.']);
    exit;
}

// Rate limiting - configurable daily limits based on user role
$rateLimitKey = 'email_count_' . $_SESSION['username'];
$currentDay = date('Y-m-d');
$sessionKey = $rateLimitKey . '_' . $currentDay;

// Set rate limits based on user role (daily limits)
$maxEmailsPerDay = 20; // Default for regular users
if (isset($_SESSION['roles'])) {
    $roles = $_SESSION['roles'];
    if (strpos($roles, 'librarian') !== false) {
        $maxEmailsPerDay = 100; // Librarians get highest limits for mass distribution
    } elseif (strpos($roles, 'admin') !== false) {
        $maxEmailsPerDay = 50; // Admins get moderate limits for system management
    }
}

if (!isset($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = 0;
}

if ($_SESSION[$sessionKey] >= $maxEmailsPerDay) {
    // Return error as JSON with 200 status so AJAX success handler can process it
    echo json_encode([
        'success' => false, 
        'message' => "Daily email limit exceeded. You can send a maximum of {$maxEmailsPerDay} emails per day. Please try again tomorrow.",
        'retry_after' => 'You can send more emails after midnight (' . date('Y-m-d', strtotime('+1 day')) . ')'
    ]);
    exit;
}
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/functions.php');
ferror_log("sound.php accessed by user: " . $_SESSION['username']);

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST requests are allowed.']);
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

// Anti-spam content validation (music-context aware)
$suspiciousPatterns = [
    '/\b(viagra|cialis|casino|lottery|winner|congratulations)\b/i',
    '/\$\d{3,}/i', // Large dollar amounts (but allow small amounts like $10 deposits)
    '/\b(click here now|urgent.*act|limited.*time.*offer)\b/i',
    '/\b(free money|guaranteed income|no cost.*money)\b/i',
    '/\b(nigerian prince|inheritance|beneficiary)\b/i'
];

// Music-specific exceptions - don't flag these legitimate terms
$musicExceptions = [
    '/\b(concert|recital|performance|rehearsal|parts|score|music)\b/i',
    '/\b(download|sheet music|pdf|conductor|musician)\b/i',
    '/\b(playgram|section|instrument|band|orchestra|ensemble)\b/i'
];

$isMusicContent = false;
foreach ($musicExceptions as $exception) {
    if (preg_match($exception, $message) || preg_match($exception, $subject)) {
        $isMusicContent = true;
        break;
    }
}

// Only apply strict spam filtering to non-music content
if (!$isMusicContent) {
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $message) || preg_match($pattern, $subject)) {
            ferror_log("sound.php: Suspicious content detected from user: " . $_SESSION['username']);
            echo json_encode(['success' => false, 'message' => 'Message contains content that may be flagged as spam by email providers. Please review and modify your message.']);
            exit;
        }
    }
}

// Improve subject line to avoid spam triggers (but avoid redundancy)
$orgNamePattern = '/\b' . preg_quote(ORGNAME, '/') . '\b/i';
if (!preg_match('/^\[' . preg_quote(ORGNAME, '/') . '\]/', $subject) && !preg_match($orgNamePattern, $subject)) {
    $subject = '[' . ORGNAME . '] ' . $subject;
}

// Format message with conditional footer
$formattedMessage = $message;

// Check if message already contains a structured footer (from HTML template)
$hasStructuredFooter = (
    stripos($message, 'best regards') !== false || 
    stripos($message, '</body>') !== false ||
    stripos($message, 'terms of use') !== false ||
    stripos($message, ORGNAME . '</div>') !== false
);

// Only add minimal footer if message doesn't already have one
if (!$hasStructuredFooter) {
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
} else {
    // For HTML messages with existing structure, just add minimal tracking
    if ($isHtml && stripos($message, '</body>') !== false) {
        // Insert tracking info before closing body tag
        $trackingInfo = "<!-- Email sent via " . ORGNAME . " Library System by " . $_SESSION['username'] . " at " . date('Y-m-d H:i:s T') . " -->\n";
        $formattedMessage = str_replace('</body>', $trackingInfo . '</body>', $message);
    } else {
        // Keep message as-is for structured content
        $formattedMessage = $message;
    }
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
