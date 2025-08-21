<?php
require_once('config.php');
require_once('functions.php');

if (isset($_POST["registration-submit"])) {
    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $url = ORGHOME . "/verify_email.php?selector=" . $selector . "&validator=" . bin2hex($token);
    $expires = date("U") + 3600; // 1 hour expiration

    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Get the form data
    $username = mysqli_real_escape_string($f_link, $_POST['username']);
    $name = mysqli_real_escape_string($f_link, $_POST['name']);
    $password = mysqli_real_escape_string($f_link, $_POST['password']);
    $address = mysqli_real_escape_string($f_link, $_POST['address']);

    ferror_log("Running email verification for user: " . $username . " with email: " . $address);

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Delete any existing verification requests for this email
    $sql = "DELETE FROM password_reset WHERE password_reset_email=? AND request_type='email_verification';";
    if (!$stmt = mysqli_prepare($f_link, $sql)) {
        ferror_log("Database error.");
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "s", $address);
        mysqli_stmt_execute($stmt);
    }

    // Insert new verification request
    $sql = "INSERT INTO password_reset (password_reset_email, password_reset_selector, password_reset_token, password_reset_expires, username, name, password_hash, request_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'email_verification');";

    if (!$stmt = mysqli_prepare($f_link, $sql)) {
        ferror_log("Database error preparing verification insert.");
        exit();
    } else {
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, "sssssss", $address, $selector, $hashedToken, $expires, $username, $name, $password_hash);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($f_link);

            // Send verification email
            $to = $address;
            $subject = 'Verify your ' . ORGNAME . ' account registration.';
            $message = '<p>Welcome to ' . ORGNAME . ' music library!</p>';
            $message .= '<p>Please click the link below to verify your email address and complete your account registration:</p>';
            $message .= '<a href="' . $url . '">Verify Email Address</a>';
            $message .= '<p>This link will expire in 1 hour.</p>';
            $message .= '<p>If you did not request this account, you can ignore this email.</p>';
            
            $headers = "From: ". ORGNAME . "<" .ORGMAIL . ">\r\n";
            $headers .= "Reply-To: ". ORGMAIL . "\r\n";
            $headers .= "Content-type: text/html\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            if (mail($to, $subject, $message, $headers)) {
                header("Location: ../login_register.php?verification=sent");
            } else {
                header("Location: ../login_register.php?verification=email_error");
            }
        } else {
            ferror_log("Database error during verification insert.");
        }
    }
    mysqli_close($f_link);
} else {
    header("Location: ../login.php");
}
?>
