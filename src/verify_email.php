<?php
define('PAGE_TITLE', 'Verify Email');
define('PAGE_NAME', 'Email Verification');
require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . "/includes/navbar.php");
ferror_log("RUNNING verify_email.php");

$verification_result = "";

if (isset($_GET["selector"]) && isset($_GET["validator"])) {
    $selector = $_GET["selector"];
    $validator = $_GET["validator"];
    
    if (ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {
        
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check if verification request exists and is not expired
        $currentDate = date("U");
        $sql = "SELECT * FROM password_reset WHERE password_reset_selector=? AND password_reset_expires >= ? AND request_type='email_verification';";
        
        if (!$stmt = mysqli_prepare($f_link, $sql)) {
            $verification_result = "error";
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $selector, $currentDate);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $tokenBin = hex2bin($validator);
                $tokenCheck = password_verify($tokenBin, $row["password_reset_token"]);
                
                if ($tokenCheck === false) {
                    $verification_result = "invalid";
                } else {
                    // Verification successful - create the user account
                    $username = $row["username"];
                    $name = $row["name"];
                    $password_hash = $row["password_hash"];
                    $email = $row["password_reset_email"];
                    
                    // Check if username already exists (in case someone registered while verification was pending)
                    $sql = "SELECT id_users FROM users WHERE username = ?";
                    if ($stmt = mysqli_prepare($f_link, $sql)) {
                        mysqli_stmt_bind_param($stmt, "s", $username);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_store_result($stmt);
                        
                        if (mysqli_stmt_num_rows($stmt) > 0) {
                            $verification_result = "username_taken";
                        } else {
                            // Create the user account
                            $sql = "INSERT INTO users (username, name, password, address) VALUES (?, ?, ?, ?)";
                            if ($stmt = mysqli_prepare($f_link, $sql)) {
                                mysqli_stmt_bind_param($stmt, "ssss", $username, $name, $password_hash, $email);
                                
                                if (mysqli_stmt_execute($stmt)) {
                                    // Remove the verification request
                                    $sql = "DELETE FROM password_reset WHERE password_reset_selector=? AND request_type='email_verification'";
                                    if ($stmt = mysqli_prepare($f_link, $sql)) {
                                        mysqli_stmt_bind_param($stmt, "s", $selector);
                                        mysqli_stmt_execute($stmt);
                                    }
                                    $verification_result = "success";
                                } else {
                                    $verification_result = "error";
                                }
                            } else {
                                $verification_result = "error";
                            }
                        }
                    } else {
                        $verification_result = "error";
                    }
                }
            } else {
                $verification_result = "expired";
            }
        }
        
        mysqli_close($f_link);
    } else {
        $verification_result = "invalid";
    }
} else {
    $verification_result = "missing";
}
?>

<div class="container">
<main>
    <div class="py-5 text-center">
        <h1><?php echo ORGNAME ?> Email Verification</h1>
    </div>
    <div class="row g-5 justify-content-center">
        <div class="col-md-8">
            <?php if ($verification_result == "success"): ?>
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Email Verified Successfully!</h4>
                    <p>Your email has been verified and your account has been created. You can now log in to access the music library.</p>
                    <hr>
                    <a href="/login" class="btn btn-primary">Go to Login</a>
                </div>
            <?php elseif ($verification_result == "expired"): ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Verification Link Expired</h4>
                    <p>Your verification link has expired. Please register again to receive a new verification email.</p>
                    <hr>
                    <a href="/register" class="btn btn-primary">Register Again</a>
                </div>
            <?php elseif ($verification_result == "invalid"): ?>
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Invalid Verification Link</h4>
                    <p>The verification link is invalid. Please check your email for the correct link or register again.</p>
                    <hr>
                    <a href="/register" class="btn btn-primary">Register Again</a>
                </div>
            <?php elseif ($verification_result == "username_taken"): ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Username Already Taken</h4>
                    <p>The username you chose has been taken by another user while your verification was pending. Please register again with a different username.</p>
                    <hr>
                    <a href="/register" class="btn btn-primary">Register Again</a>
                </div>
            <?php elseif ($verification_result == "missing"): ?>
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">Missing Verification Information</h4>
                    <p>Please click the verification link in your email to complete your registration.</p>
                    <hr>
                    <a href="/register" class="btn btn-primary">Back to Registration</a>
                </div>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Verification Error</h4>
                    <p>An error occurred during verification. Please try again or contact support if the problem persists.</p>
                    <hr>
                    <a href="/register" class="btn btn-primary">Register Again</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>
