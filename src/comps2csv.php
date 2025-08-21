<?php
define('PAGE_TITLE', 'Compositions information');
define('PAGE_NAME', 'compositions');
require_once("includes/header.php");
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}
require_once("includes/config.php");
require_once("includes/navbar.php");
require_once('includes/functions.php');
ferror_log("Running comps2csv.php");
?>
<main role="main">
    <div class="container-xl">
        <h1><?php echo ORGDESC . ' ' ?>Compositions</h1>
<?php if($u_librarian) : ?>
        Jump to the <a href="#bottom">bottom</a>
        <div class="table-responsive">
            <table class="table table-sm" id="<?php echo ORGNAME ?>library.csv">
                <caption class="title">Available compositions</caption>
                <thead>
                    <tr>
                        <th>catalog_number</th>
                        <th>name</th>
                        <th>composer</th>
                        <th>arranger</th>
                        <th>editor</th>
                        <th>publisher</th>
                        <th>description</th>
                        <th>comments</th>
                        <th>grade</th>
                        <th>genre</th>
                        <th>ensemble</th>
                        <th>parts</th>
                        <th>last_inventory_date</th>
                        <th>enabled</th>
                        <th>last_updated</th>
                    </tr>
                </thead>
                <tbody>
<?php
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sql = "SELECT c.catalog_number,
                        c.name,
                        c.description,
                        c.comments,
                        c.composer,
                        c.arranger,
                        c.publisher,
                        c.editor,
                        c.last_inventory_date,
                        c.last_update,
                        g.name genre,
                        COUNT(p.id_part_type) as parts,
                        e.name ensemble,
                        c.grade,
                        c.enabled
                FROM   compositions c
                JOIN   genres g
                ON     c.genre = g.id_genre
                JOIN   ensembles e
                ON     c.ensemble = e.id_ensemble
                LEFT OUTER JOIN parts p
                ON     c.catalog_number = p.catalog_number
                GROUP  BY c.catalog_number
                ORDER BY c.catalog_number;";
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            echo '<tr>
                    <td>'.$rowList['catalog_number'].'</td>
                    <td>'.$rowList['name'].'</td>
                    <td>'.$rowList['composer'].'</td>
                    <td>'.$rowList['arranger'].'</td>
                    <td>'.$rowList['editor'].'</td>
                    <td>'.$rowList['publisher'].'</td>
                    <td>'.$rowList['description'].'</td>
                    <td>'.$rowList['comments'].'</td>
                    <td>'.$rowList['grade'].'</td>
                    <td>'.$rowList['genre'].'</td>
                    <td>'.$rowList['ensemble'].'</td>
                    <td>'.$rowList['parts'].'</td>
                    <td>'.$rowList['last_inventory_date'].'</td>
                    <td>'.(($rowList['enabled'] == 1) ? "enabled" : "disabled").'</td>
                    <td>'.$rowList['last_update'].'</td>
                  </tr>'. "\n";
        }
        mysqli_close($f_link);
        ?>
                </tbody>
            </table>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                You do not have permission to view this page.
            </div>
        </div>
    </div>
<?php endif; ?>
            <a id="bottom"></a>
        </div>
</main>
<?php require_once("includes/footer.php"); ?>
<script src="includes/table2CSV.js"></script>
<script>
    $(document).ready(function() {
        $('table').each(function() {
            var $table = $(this);
            var $tableid = document.getElementsByClassName("table")[0].id;
            var $button = $('<button type="button" class="btn btn-primary">');
            $button.text("Export to CSV");
            $button.insertAfter($table);
            $button.click(function() {
                var csv = $table.table2CSV({
                    delivery: 'download',
                    filename: $tableid
                });
            });
        });
    });
</script>
</body>
</html>