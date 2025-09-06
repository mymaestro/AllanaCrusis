<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");
ferror_log("Running select_compositions.php with POST ". print_r($_POST, true));
if (isset($_POST["catalog_number"])) {
    $output = '
    <div class="table-responsive">
        <table class="table table-striped table-condensed">';
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $catalog_number = mysqli_real_escape_string($f_link, $_POST["catalog_number"]);

    $sql = "SELECT c.catalog_number         'Catalog',
                   c.name                   'Composition',
                   IF(c.enabled = 1, 'Yes', 'No') 'Enabled',
                   c.description            'Description',
                   c.composer               'Composer',
                   c.arranger               'Arranger',
                   c.editor                 'Editor',
                   c.publisher              'Publisher',
                   g.name                   'Genre',
                   e.name                   'Ensemble',
                   c.grade                  'Grade',
                   c.last_performance_date  'Last performed',
                   c.duration               'Duration (secs)',
                   c.performance_notes      'Performance notes',
                   c.listening_example_link 'Listening link',
                   p.name                   'Paper size',
                   c.image_path             'Picture',
                   c.windrep_link           'windrep.org link',
                   c.last_update            'Record updated'
            FROM   compositions c
            LEFT JOIN genres g ON c.genre = g.id_genre
            LEFT JOIN ensembles e ON c.ensemble = e.id_ensemble
            LEFT JOIN paper_sizes p ON c.paper_size = p.id_paper_size
            WHERE  c.catalog_number = '".$catalog_number."'";

    ferror_log("Getting details for composition with catalog number: ".$catalog_number);
    if ($res = mysqli_query($f_link, $sql)) {
        $col = 0;
        while ($fieldinfo = mysqli_fetch_field($res)) {
            $fields[$col] =  $fieldinfo -> name;
            $col++;
        }
        while ($rowList = mysqli_fetch_array($res, MYSQLI_NUM)) {
            for ($row = 0; $row < $col; $row++) {
                $output .= '<tr><td><strong>'. $fields[$row] . '</strong></td>';
                $output .= '<td id="'.$fields[$row].'-data">'. $rowList[$row] . '</td></tr>';
            }
        }
    }
        $output .= '
        </table>
    </div>
    ';
    echo $output;
    ferror_log("select compositions returned ".mysqli_num_rows($res). " rows.");
    mysqli_close($f_link);
}
?>