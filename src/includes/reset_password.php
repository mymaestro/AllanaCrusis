<?php
require_once('config.php');
require_once('functions.php');

// Called from login_newpassword.php, where the user entered their new password twice.
ferror_log("Running reset_password.php");

if (isset($_POST["selector"]) && isset($_POST["validator"])) {

    $selector = $_POST["selector"];
    $validator = $_POST["validator"];
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $password = mysqli_real_escape_string($f_link, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($f_link, $_POST['confirm_password']);

    if (empty($password) || empty($confirm_password)) {
        ferror_log("Requires password fields to not be empty");
        echo "empty";
        exit();
    } else {
        // Validate confirm password
        $number = preg_match('@[0-9]@', $password);
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        $passwordMatch = ($password === $confirm_password);
        if(strlen($password) < 8 || !$number || !$uppercase || !$lowercase || !$specialChars || !$passwordMatch) {
            echo "strength";
            ferror_log("Fails password strength requirements");
            exit();
        }
    }

    $currentDate = date("U");

    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql = "SELECT * FROM password_reset WHERE password_reset_selector = ? AND password_reset_expires >= $currentDate ;";

    if (!$stmt = mysqli_prepare($f_link, $sql)) {
        ferror_log("Database error preparing selector statement.");
        echo "dberror";
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, "s", $selector);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);
        if (!$row = mysqli_fetch_assoc($res)) {
            echo "expired";
            ferror_log("Invalid selector or request expired.");
            exit();
        } else {
            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row["password_reset_token"]);
            if($tokenCheck === false) {
                ferror_log("Request token mismatch.");
                exit();    
            } elseif ($tokenCheck === true) {
                $param_userEmail = $row["password_reset_email"];
                $sql = "SELECT * FROM users WHERE address=?;";

                if (!$stmt = mysqli_prepare($f_link, $sql)) {
                    echo "dberror";
                    ferror_log("Database error getting users table.");
                    exit();
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $param_userEmail);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if (!$row = mysqli_fetch_assoc($res)) {
                        echo "dberror";
                        ferror_log("Error reading users table.");
                        exit();
                    } else {
                        $sql = "UPDATE users SET password=? WHERE address=?;";
                        if (!$stmt = mysqli_prepare($f_link, $sql)) {
                            echo "dberror";
                            ferror_log("Database error preparing users table update.");
                            exit();
                        } else {
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            mysqli_stmt_bind_param($stmt, "ss", $passwordHash, $param_userEmail);
                            mysqli_stmt_execute($stmt);
                            ferror_log("Password updated.");
                            $sql = "DELETE FROM password_reset WHERE password_reset_email=?;";
                            if (!$stmt = mysqli_prepare($f_link, $sql)) {
                                ferror_log("Database error preparing reset token delete.");
                                exit();
                            } else {
                                mysqli_stmt_bind_param($stmt, "s", $param_userEmail);
                                mysqli_stmt_execute($stmt);
                                echo "success";
                                ferror_log("Token deleted. Success.");
                            }
                        }
                    }
                }

            }
        }
    }

} else {
    ferror_log("reset-password-submit NOT set");

    header("Location: ../index.php");
}