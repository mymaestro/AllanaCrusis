<?php
// Aggressive error display: show error at the very top of the page, always visible
if (isset($errorMessage) && $errorMessage) {
  echo '<div style="position:fixed;top:0;left:0;width:100%;z-index:99999;background:#dc3545;color:#fff;padding:2em 1em;text-align:center;font-size:2em;font-weight:bold;">'
    . 'ERROR: ' . htmlspecialchars($errorMessage ?? '') .
    '</div>';
  flush();
}
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Error');
if (!defined('PAGE_NAME')) define('PAGE_NAME', 'Error');
require_once(__DIR__ . "/includes/header.php");
$u_admin = FALSE; // User admin flag
$u_librarian = FALSE; // User librarian flag
$u_user = FALSE; // User general flag
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];
  $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
  $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
  $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
}
$configFile = __DIR__ . "/../config/config.php";
if (!file_exists($configFile)) {
  echo "<div class='alert alert-danger'>Error: failed to read required config.php. Did you create it yet?</div>
	</body>
</html>";
  exit; // we are done here
} else {
  require_once($configFile);
};

require_once __DIR__ . "/includes/navbar.php";
require_once __DIR__ . "/includes/functions.php";
ferror_log("RUNNING error.php");
?>
<main role="main" class="container">
  <div class="px-4 py-5 my-5 text-center align-items-center rounded-3 border shadow-lg">
    <h1 class="display-5 fw-bold text-body-emphasis">ERROR</h1>
    <div class="col-lg-6 mx-auto">
      <p class="lead mb-4">An error occurred while processing your request.</p>
      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center"> <a href="/home" class="btn btn-primary btn-lg px-4 gap-3">Home</a> <a
          href="/about" class="btn btn-outline-secondary btn-lg px-4">About</a> </div>
    </div>
  </div>
</main>
<?php
if (!isset($errorMessage) || !$errorMessage) {
  echo '<div class="alert alert-danger"><strong>An unexpected error occurred.</strong></div>';
}
?>
<?php require_once(__DIR__ . "/includes/footer.php"); ?>
</body>

</html>