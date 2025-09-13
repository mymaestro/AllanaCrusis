<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running admin_select_users.php with id_users=". $_POST["id_users"]);
if (isset($_POST["id_users"])) {
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_user = intval($_POST["id_users"]);
    $sql = "SELECT * FROM users WHERE id_users = $id_user";
    ferror_log("Running SQL: ". $sql);
    $res = mysqli_query($f_link, $sql);
    $output .= '
    <div class="table-responsive">
        <table class="table">';
    while($rowList = mysqli_fetch_array($res)) {
        $output .= '
            <tr>
                <td><h4 class="text-primary">'.$rowList["id_users"].'</h4></td>
                <td><h4 class="text-info">'.$rowList["username"].'</h4></td>
            </tr>
            <tr>
                <td><label>Name</label></td>
                <td>'.$rowList["name"].'</td>
            </tr>
            <tr>
                <td><label>Address</label></td>
                <td>'.$rowList["address"].'</td>
            </tr>
            <tr>
                <td><label>Roles</label></td>
                <td>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="view_role_user" value="user" '.
                        (strpos($rowList["roles"], 'user') !== FALSE ? 'checked' : '') .' disabled>
                        <label class="form-check-label" for="view_role_user">User</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="view_role_librarian" value="librarian" '.
                        (strpos($rowList["roles"], 'librarian') !== FALSE ? 'checked' : '') .' disabled>
                        <label class="form-check-label" for="view_role_librarian">Librarian</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="view_role_administrator" value="administrator" '.
                        (strpos($rowList["roles"], 'administrator') !== FALSE ? 'checked' : '') .' disabled>
                        <label class="form-check-label" for="view_role_administrator">Administrator</label>
                    </div>
                    <br><small class="text-muted">('.$rowList["roles"].')</small>
                </td>
            </tr>';
    }
    $output .= '
        </table>
    </div>
    ';
    echo $output;
    mysqli_close($f_link);
}
?>
