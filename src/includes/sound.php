<?php
// sound.php - Secure email handler for part delivery
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate input
// Inputs
$to = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$from = isset($_POST['from']) ? trim($_POST['from']) : ORGMAIL;
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Notification from Music Library';
$isHtml = isset($_POST['is_html']) ? ($_POST['is_html'] == '1' || $_POST['is_html'] === true) : false;

// Validate
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient email address.']);
    exit;
}
if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid sender email address.']);
    exit;
}
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit;
}
if (empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Subject cannot be empty.']);
    exit;
}

// Build headers
$headers = "From: $from\r\n";
$headers .= "Reply-To: $from\r\n";
if ($isHtml) {
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
} else {
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
}

// Send email
$mailSuccess = mail($to, $subject, $message, $headers);

if ($mailSuccess) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
}
?>
