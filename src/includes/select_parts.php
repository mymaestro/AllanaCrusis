<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");
// Detect librarian role
$u_librarian = FALSE;
if (isset($_SESSION['roles'])) {
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE);
}
/* called by parts.php when user selects "View" */
ferror_log("RUNNING select_parts.php with POST data: ". print_r($_POST, true));
if (isset($_POST["id_part_type"]) && isset($_POST["catalog_number"])) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
    $id_part_type = mysqli_real_escape_string($f_link, $_POST['id_part_type']);

    $output = "";
    $sql = "SELECT t.name  'Part',
    p.id_part_type         'Part type ID',
    p.name                 'Part name',
    p.description          'Part description',
    p.catalog_number       'Catalog number',
    c.name                 'Composition name',
    c.composer             'Composer',
    c.arranger             'Arranger',
    p.is_part_collection   'Instruments in collection',
    z.name                 'Paper size',
    p.page_count           'Pages',
    p.originals_count      'Originals',
    p.copies_count         'Copies',
    p.image_path           'PDF path',
    CASE 
        WHEN p.image_path IS NULL OR p.image_path = '' THEN 'No'
        ELSE 'Yes'
    END AS 'PDF available',  -- Indicates if a PDF is available
    p.last_update          'Last updated'
    FROM   parts p
    LEFT JOIN compositions c ON c.catalog_number = p.catalog_number
    LEFT JOIN part_types t ON t.id_part_type = p.id_part_type
    LEFT JOIN paper_sizes z ON z.id_paper_size = p.paper_size
    WHERE  p.catalog_number = '" . $catalog_number . "'
    AND    p.id_part_type = " . $id_part_type . ";";

    $output .= '
    <div class="table-responsive">
        <table class="table table-striped table-condensed">';

    if ($res = mysqli_query($f_link, $sql)) {
        $col = 0;
        while ($fieldinfo = mysqli_fetch_field($res)) {
            $fields[$col] =  $fieldinfo -> name;
            $col++;
        }
        while ($rowList = mysqli_fetch_array($res, MYSQLI_NUM)) {
            $pdf_path = null;
            for ($row = 0; $row < $col; $row++) {
                $field_name = $fields[$row];
                $field_value = $rowList[$row];
                // Save PDF path for later
                if ($field_name === 'PDF path') {
                    $pdf_path = $field_value;
                    continue; // Don't output PDF path directly
                }
                if ($field_name === 'PDF available' && $u_librarian && $pdf_path && $field_value === 'Yes') {
                    // Show secure download link for librarians
                    $safe_file = htmlspecialchars($pdf_path);
                    $output .= '<tr><td><strong>PDF available</strong></td>';
                    $output .= '<td><a href="index.php?action=download_part&file=' . urlencode($safe_file) . '" target="_blank">' . basename($safe_file) . '</a></td></tr>';
                } else {
                    $output .= '<tr><td><strong>'. $field_name . '</strong></td>';
                    $output .= '<td>'. $field_value . '</td></tr>';
                }
            }
        }
    }
    $output .= '
        </table>
    </div>
    ';
    echo $output;
    mysqli_close($f_link);
}
?>
