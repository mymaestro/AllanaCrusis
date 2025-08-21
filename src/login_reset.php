<?php
// Include config file
define('PAGE_TITLE', 'Reset your password');
define('PAGE_NAME', 'reset your password');

require_once('includes/header.php');
require_once('includes/config.php');
require_once('includes/navbar.php');
require_once('includes/password_hash.php');
require_once('includes/functions.php');

if(isset($_GET["reset"])) {
    $result = htmlspecialchars($_GET["reset"]);
} else {
    $result = "new";
}
ferror_log("Running login_reset.php");
// This page has a form to input username and email address
// That will POST to includes/password_reset.php
// -- which generates the token and sends e-mail
// Input submit button has "reset-request-submit" as its name/id

// And hopefully provide some feedback
// This page should expect $_GET["reset"] to be "success" as returned from password_reset.php
?>
<div class="container">
<main>
    <div class="py-5 text-center">
        <h1><?php echo ORGNAME ?> library <?php echo PAGE_TITLE ?></h1>
    </div>
    <div class="row g-5">
        <div class="col-md-7">
            <?php if($result == "new") : ?>
            <p class="lead">Please submit this form to reset your password.</p>
            <div class="col-12">
                <form action="includes/password_reset.php" method="post">
                <label for="username" class="col-form-label">Username*</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="username" required>
                <span class="help-block"></span>                            
            </div>
            <div class="col-12">
                <label for="address" class="col-form-label">e-mail address*</label>
                <input type="e-mail" name="address" class="form-control" placeholder="username@example.com" required>
                <span class="help-block"></span>
            </div>
            <hr class="my-4">
            <div class="col-12">
                <input type="submit" name="reset-request-submit" class="w-50 align-right btn btn-primary" value="Submit">
                </form>
            </div>
            <?php endif; ?>
            <?php if($result == "success") : ?>
            <p class="lead text-center">Please check your e-mail.</p>
            <?php endif; ?>
            <?php if($result == "fail") : ?>
            <p class="lead">Address or username not found. Please submit this form to reset your password.</p>
            <div class="col-12">
                <form action="includes/password_reset.php" method="post">
                <label for="username" class="col-form-label">Username*</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="username" required>
                <span class="help-block">Username on this system.</span>                            
            </div>
            <div class="col-12">
                <label for="address" class="col-form-label">e-mail address*</label>
                <input type="e-mail" name="address" class="form-control" placeholder="username@example.com" required>
                <span class="help-block">Address on this system.</span>
            </div>
            <hr class="my-4">
            <div class="col-12">
                <input type="submit" name="reset-request-submit" class="w-50 align-right btn btn-primary" value="Submit">
                </form>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Want to login?</h5>
                    <p class="card-text">Go to the login page.</p>
                    <a href="login.php" class="card-link">Login</a>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
<?php require_once('includes/footer.php'); ?>
</body>
</html>