<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

if (isset($_POST["reset-request-submit"])) {

    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $url = rtrim(ORGHOME, '/') . "/login_newpassword?selector=" . $selector . "&validator=" . bin2hex($token);
    $expires = date("U") + 1800;

    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check if the username/address exist
    $username = mysqli_real_escape_string($f_link, $_POST['username']);
    $address = mysqli_real_escape_string($f_link, $_POST['address']);
    $sql = "SELECT * FROM users WHERE username = ? AND address = ?;";

    if (!$stmt = mysqli_prepare($f_link, $sql)) {
        echo "Database error preparing $sql";
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $username, $address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            // username exists with that address
            ferror_log("user $username exists with address $address");
            $param_userEmail = $_POST["address"];

            $sql = "DELETE FROM password_reset WHERE password_reset_email=?;";

            if (!$stmt = mysqli_prepare($f_link, $sql)) {
                echo "Database error.";
                exit();
            } else {
                mysqli_stmt_bind_param($stmt, "s", $param_userEmail);
                mysqli_stmt_execute($stmt);
            }
        
            $sql = "INSERT INTO password_reset (password_reset_email, password_reset_selector, password_reset_token, password_reset_expires) VALUES (?, ?, ?, ?);";
        
            if (!$stmt = mysqli_prepare($f_link, $sql)) {
                echo "Database error preparing $sql.";
                exit();
            } else {
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "ssss", $param_userEmail, $selector, $hashedToken, $expires);
                mysqli_stmt_execute($stmt);
            }
        
            mysqli_stmt_close($stmt);
            mysqli_close($f_link);
        
            $to = $address;
            $subject = 'Your ' .ORGNAME . ' password reset request.';
            $message = '<p>We received a request to reset your ' . ORGNAME . ' music library password. If you did not make this request, you can ignore this email.</p>';
            $message .= "<p>Here is your password link: </p>";
            $message .= '<a href="' . $url . '">'. $url . '</a></p>';
            $headers = "From: ". ORGNAME . "<" .ORGMAIL . ">\r\n";
            $headers .= "Reply-To: ". ORGMAIL . "\r\n";
            $headers .= "Content-type: text/html\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            mail($to, $subject, $message, $headers);

            header("Location: /login_reset?reset=success");

        } else {
            header("Location: /login_reset?reset=fail");
        }
    }

} else {
    header("Location: /login");
}