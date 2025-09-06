<?php
// Simple admin page to view pending requests (password reset and email verification)
// This should be protected and only accessible to administrators

define('PAGE_TITLE', 'Pending Requests');
define('PAGE_NAME', 'Admin - Password Reset & Email Verification');

$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
$username = $_SESSION['username'];
$u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
$u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
$u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
}

// Only allow admin to access this page
if (!$u_admin) {
    header("Location: index.php");
    exit();
}

require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . "/includes/navbar.php");

// Note: In a production environment, you should add proper authentication here
// to ensure only administrators can access this page

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$currentTime = date("U");

// Get all pending email verifications
$sql = "SELECT password_reset_id, password_reset_email, username, name, password_reset_expires, request_type
        FROM password_reset 
        WHERE password_reset_expires >= ? AND request_type='email_verification'
        ORDER BY password_reset_expires DESC";

$pending_verifications = [];

if ($stmt = mysqli_prepare($f_link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $currentTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_verifications[] = $row;
    }
    
    mysqli_stmt_close($stmt);
}

// Get all pending password resets
$sql = "SELECT password_reset_id, password_reset_email, password_reset_expires, request_type
        FROM password_reset 
        WHERE password_reset_expires >= ? AND request_type='password_reset'
        ORDER BY password_reset_expires DESC";

$pending_resets = [];

if ($stmt = mysqli_prepare($f_link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $currentTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_resets[] = $row;
    }
    
    mysqli_stmt_close($stmt);
}

// Get count of expired requests
$sql = "SELECT COUNT(*) as expired_count FROM password_reset WHERE password_reset_expires < ?";
$expired_count = 0;

if ($stmt = mysqli_prepare($f_link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $currentTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $expired_count = $row['expired_count'];
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($f_link);
?>

<div class="container">
<main>
    <div class="py-5 text-center">
        <h1><?php echo ORGNAME ?> - Pending Requests</h1>
    </div>
<?php if($u_admin) : ?>  
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Pending Email Verifications:</strong> <?php echo count($pending_verifications); ?><br>
                <strong>Pending Password Resets:</strong> <?php echo count($pending_resets); ?><br>
                <strong>Expired Requests:</strong> <?php echo $expired_count; ?>
                <?php if ($expired_count > 0): ?>
                    <br><small>Run cleanup_verification to remove expired entries.</small>
                <?php endif; ?>
            </div>
            
            <!-- Email Verifications Section -->
            <h3>Pending Email Verifications</h3>
            <?php if (count($pending_verifications) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Expires</th>
                            <th>Time Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_verifications as $verification): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($verification['username']); ?></td>
                                <td><?php echo htmlspecialchars($verification['name']); ?></td>
                                <td><?php echo htmlspecialchars($verification['password_reset_email']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $verification['password_reset_expires']); ?></td>
                                <td>
                                    <?php 
                                    $time_remaining = $verification['password_reset_expires'] - $currentTime;
                                    if ($time_remaining > 0) {
                                        echo gmdate('H:i:s', $time_remaining);
                                    } else {
                                        echo 'Expired';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-success">
                    No pending email verifications at this time.
                </div>
            <?php endif; ?>
            
            <!-- Password Resets Section -->
            <h3>Pending Password Resets</h3>
            <?php if (count($pending_resets) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Expires</th>
                            <th>Time Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_resets as $reset): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reset['password_reset_email']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $reset['password_reset_expires']); ?></td>
                                <td>
                                    <?php 
                                    $time_remaining = $reset['password_reset_expires'] - $currentTime;
                                    if ($time_remaining > 0) {
                                        echo gmdate('H:i:s', $time_remaining);
                                    } else {
                                        echo 'Expired';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-success">
                    No pending password resets at this time.
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                You do not have permission to view this page.
            </div>
        </div>
    </div>
<?php endif; ?>
</main>
</div>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>
