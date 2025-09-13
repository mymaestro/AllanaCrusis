<?php
// Include config file
define('PAGE_TITLE', 'New password');
define('PAGE_NAME', 'new password');

require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/includes/navbar.php");

// Use this page to put in your new password
// User gets here only by clicking the link sent to their e-mail address
// which was sent with a selector and a validator

// The form on this page requests new password and matching new password.
// And calls includes/reset_password.php

// If the selector and validator match the request the new password gets stored in the database

?>
<div class="container">
<main>
    <div class="py-5 text-center">
        <h1><?php echo ORGNAME ?> library <?php echo PAGE_TITLE ?></h1>
    </div>
    <div class="row g-5">
        <div class="col-md-7">
<?php
if(isset($_GET["selector"])) {
    $selector = $_GET["selector"];
} else {
    $selector = '';
}

if(isset($_GET["validator"])) {
    $validator = $_GET["validator"];
} else {
    $validator = "";
}

if (empty($selector) || empty($validator)) {
    echo '     <p class="lead text-center">You must provide the code sent to you by e-mail to change your password.</p>';
} else {
    if (ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false) {
        ?>
            <p class="lead">Please enter your new password.</p>
            <div class="row">
                <div class="col-6">
                    <form action="" method="post" id="password_form">
                    <input type="hidden" name="selector" value="<?php echo $selector ?>">
                    <input type="hidden" name="validator" value="<?php echo $validator ?>">
                    <label for="password" class="col-form-label">Password*</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="password" data-bs-content="Enter a password" required>
                    <span class="help-block"></span>                            
                </div>
                <div class="col-6">
                    <p>Password must be greater than 8 characters, and must have 1 number, 1 uppercase letter, 1 lowercase letter, and one special character.<p>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label for="confirm_password" class="col-form-label">Repeat password*</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" data-bs-content="Repeat the password" required>
                    <span class="help-block"></span>                            
                </div>
                <div class="col-6">
                    <p id="status-info"></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="col-12">
                <input type="submit" name="reset-password-submit" class="w-50 align-right btn btn-primary" value="Submit">
                </form>
            </div>
    <?php
    }
}
?>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Want to login?</h5>
                    <p class="card-text">Go to the login page.</p>
                    <a href="/login" class="card-link">Login</a>
                </div>
            </div>
        </div>
    </div>

</main>
</div>
<?php require_once(__DIR__ . "/includes/footer.php"); ?>
<script>
    $(document).ready(function(){
        $('#password_form').on("submit", function(event){
            event.preventDefault();
            if($('#password').val() != $('#confirm_password').val())
            {
                alert("Password and confirm password must match");
            }
            else
            {
                $.ajax({
                    url:"index.php?action=reset_password",
                    method:"POST",
                    data:$('#password_form').serialize(),
                    beforeSend:function(){
                        $('#insert').val("Inserting");
                    },
                    success:function(data){
                        $('#password_form')[0].reset();
                        var text;
                        switch(data) {
                            case "success":
                                $("#status-info").addClass("text-success");
                                $("#status-info").html('Password updated.');
                                break;
                            case "empty":
                                $("#status-info").addClass("text-danger");
                                $("#status-info").html('Password fields must not be empty.');
                                break;
                            case "expired":
                                $("#status-info").addClass("text-danger");
                                $("#status-info").html('Invalid token selector or token expired.');
                                break;
                            case "strength":
                                $("#status-info").addClass("text-danger");
                                $("#status-info").html('Password does not meet strength requirements.');
                                break;
                            case "dberror":
                                $("#status-info").addClass("text-danger");
                                $("#status-info").html('Database error!');
                                break;
                            default:
                                $("#status-info").addClass("text-danger");
                                $("#status-info").html('Unknown error occurred. Try again later.');
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>