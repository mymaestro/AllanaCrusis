<?php
  define('PAGE_TITLE', 'Login');
  define('PAGE_NAME', 'Login');
  require_once(__DIR__. "/includes/header.php");
  $u_admin = FALSE;
  $u_librarian = FALSE;
  $u_user = FALSE;
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
  }
  require_once(__DIR__ . "/includes/config.php");
  require_once(__DIR__. "/includes/navbar.php");
  require_once(__DIR__ . "/includes/functions.php");
  ferror_log("RUNNING login.php");

  // Eventually, update this to check $_GET["newpassword"] = "passwordupdated"
  //  from when the user requests to reset their password, and it's successful.
?>
<main role="form-signin" style="margin-bottom: -1px; border-bottom-right-radius: 0; border-bottom-left-radius: 0;">
    <div class="container">
        <h1><?php echo ORGNAME . ' ' . PAGE_NAME?></h1>
        <form id="signin_form" class="form-signin" method="post" style="width: 100%; max-width: 330px; padding: 15px; margin: auto;">
        <img class="d-block mx-auto mb-4" src="<?php echo ORGLOGO ?>" alt="<?php echo ORGDESC ?>" height="256">
        <h2 align="center">Please sign in</h2>
        <div class="form-floating">
            <input type="text" id="username" name="username" class="form-control" placeholder="username" required autofocus 
            style= "margin-bottom: -1px; border-bottom-right-radius: 0; border-bottom-left-radius: 0;">
            <label for="username">User name</label>
        </div>
        <div class="form-floating">
            <input type="password" name="password" placeholder="password" class="form-control"
            style="margin-bottom: 10px; border-top-left-radius: 0; border-top-right-radius: 0;">
            <label for="password">Password</label>
        </div>
        <button type="submit" id="form_button" name="form_button" value="signin" class="w-100 btn btn-primary btn-md">Sign in</button>
        <p id="login-message" align="center">Enter your user name and password.</p>

        <div class="text-center">
            <p><a href="/login_reset">Forgot your password?</a></p>
            <p>Not a member? <a href="/register">Register</a></p>
        </div>
    </form>
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
<!-- jquery functions to request authentication from the database -->
<script>
$(document).ready(function(){
    $('#signin_form').on("submit", function(event) {
        event.preventDefault();

        if($('#username').val() == "") {
            alert("login name is required");
        } else if($('#password').val() == '') {
            alert("Password is required");
        } else {
            $.ajax({
                url: "index.php?action=fetch_login",
                method: "POST",
                data: $('#signin_form').serialize(),
                beforeSend: function(){
                    $('#form_button').val("Authorizing");
                },
                success: function(data) {
                    $('#signin_form')[0].reset();
                    var text;
                    switch(data) {
                        case "success":
                            $("#login-message").addClass("text-success");
                            $("#login-message").html('You are logged in. <a href="/logout">Logout</a>.');
                            window.location.replace("/welcome");
                            break;
                        case "username":
                            $("#login-message").addClass("text-danger");
                            $("#login-message").html('No user by that name.<br>Need an account? <a href="/register">Sign up now</a>.');
                            break;
                        case "password":
                            $("#login-message").addClass("text-danger");
                            $("#login-message").html('Password does not match.<br>Need to reset? <a href="/login_reset">Reset your password</a>.');
                            break;
                        default:
                            $("#login-message").addClass("text-danger");
                            $("#login-message").html('Unknown error occurred. Try again later.');
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>
