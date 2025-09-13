<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
("Running report_missing_parts.php");
if (isset($_POST["report"])) {
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql = 'SELECT `compositions`.`name` Composition,
                   `compositions`.`composer` Composer,
                   `part_types`.`name` Missing_part,
                   `compositions`.`enabled`
              FROM `compositions`
         LEFT JOIN `parts` ON `parts`.`catalog_number` = `compositions`.`catalog_number`
         LEFT JOIN `part_types` ON `parts`.`id_part_type` = `part_types`.`id_part_type`
             WHERE `parts`.`originals_count` = 0
          ORDER BY `compositions`.`name` ASC, `part_types`.`collation` ASC, `parts`.`id_part_type` ASC;';
    ferror_log("Running SQL: ". $sql);
    $res = mysqli_query($f_link, $sql);
    $output .= '
    <div class="table-responsive">
        <h4>Missing parts</h4>
        <table class="table">
        <thead>
        <tr>
            <th>Composition</th>
            <th>Composer</th>
            <th>Missing part</th>
            <th>Enabled</th>
        </tr>
        </thead>
        <tbody>';
    while($rowList = mysqli_fetch_array($res)) {
        $output .= '
            <tr>
                <td>'.$rowList["Composition"].'</td>
                <td>'.$rowList["Composer"].'</td>
                <td>'.$rowList["Missing_part"].'</td>
                <td>'. (($rowList["enabled"] == 1) ? "Yes" : "No") .'</td>
            </tr>
            ';
    }
    $output .= '
        </tbody>
        </table>
    </div>
    ';
    echo $output;
}
?>