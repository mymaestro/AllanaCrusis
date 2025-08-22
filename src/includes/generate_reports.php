<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

// Check user roles
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}

ferror_log("Running generate_reports.php");

if (isset($_POST["report_type"])) {
    $report_type = $_POST["report_type"];
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    switch($report_type) {
        case 'missing_originals':
            $sql = 'SELECT c.catalog_number, c.name as composition_name, c.composer, 
                           pt.name as part_name, p.originals_count, p.copies_count,
                           c.enabled as comp_enabled
                    FROM compositions c
                    JOIN parts p ON c.catalog_number = p.catalog_number
                    JOIN part_types pt ON p.id_part_type = pt.id_part_type
                    WHERE p.originals_count = 0
                    ORDER BY c.name ASC, pt.collation ASC';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-file-times text-danger"></i> Parts with zero originals</h4>
                <p class="text-muted">These parts exist in the database but have no original copies available.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Part Name</th>
                    <th>Copies Available</th>
                    <th>Enabled</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $status_class = $row['comp_enabled'] == 1 ? 'text-success' : 'text-muted';
                    $enabled_text = $row['comp_enabled'] == 1 ? 'Yes' : 'No';
                    $output .= '<tr>
                        <td><strong>' . htmlspecialchars($row['catalog_number']) . '</strong></td>
                        <td>' . htmlspecialchars($row['composition_name']) . '</td>
                        <td>' . htmlspecialchars($row['composer']) . '</td>
                        <td>' . htmlspecialchars($row['part_name']) . '</td>
                        <td><span class="badge bg-warning">' . $row['copies_count'] . '</span></td>
                        <td><span class="' . $status_class . '">' . $enabled_text . '</span></td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="6" class="text-center text-success">No missing originals found!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        case 'orphaned_part_types':
            $sql = 'SELECT pt.id_part_type, pt.name, pt.description, pt.family, pt.enabled
                    FROM part_types pt
                    LEFT JOIN section_part_types spt ON pt.id_part_type = spt.id_part_type
                    WHERE spt.id_part_type IS NULL AND pt.enabled = 1
                    ORDER BY pt.family, pt.name';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-puzzle-piece text-warning"></i> Part types not assigned to sections</h4>
                <p class="text-muted">These part types are enabled but not assigned to any section.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Part Type Name</th>
                    <th>Family</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $output .= '<tr>
                        <td>' . $row['id_part_type'] . '</td>
                        <td><strong>' . htmlspecialchars($row['name']) . '</strong></td>
                        <td><span class="badge bg-secondary">' . htmlspecialchars($row['family']) . '</span></td>
                        <td>' . htmlspecialchars($row['description']) . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="4" class="text-center text-success">All part types are properly assigned to sections!</td></tr>';
            }
            $output .= '</tbody></table>';
            $output .= $u_librarian ?
                '<div class="alert alert-info mt-3">
                    <strong>Action Required:</strong> Consider assigning these part types to appropriate sections in the <a href="/partsections">Part Sections</a> management page.
                </div>' : '';
            break;
            
        case 'playgram_missing_parts':
            $sql = 'SELECT DISTINCT c.catalog_number, c.name as composition_name, c.composer,
                           pg.name as playgram_name, pt.name as missing_part
                    FROM playgram_items pi
                    JOIN compositions c ON pi.catalog_number = c.catalog_number
                    JOIN playgrams pg ON pi.id_playgram = pg.id_playgram
                    JOIN parts p ON c.catalog_number = p.catalog_number
                    JOIN part_types pt ON p.id_part_type = pt.id_part_type
                    WHERE c.enabled = 1 AND p.originals_count = 0
                    ORDER BY pg.name, c.name, pt.collation';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-calendar-times text-info"></i> Programmed works with missing parts</h4>
                <p class="text-muted">These compositions are scheduled in playgrams but have parts with zero originals.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Playgram</th>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Missing Part</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $output .= '<tr>
                        <td><span class="badge bg-primary">' . htmlspecialchars($row['playgram_name']) . '</span></td>
                        <td><strong>' . htmlspecialchars($row['catalog_number']) . '</strong></td>
                        <td>' . htmlspecialchars($row['composition_name']) . '</td>
                        <td>' . htmlspecialchars($row['composer']) . '</td>
                        <td><span class="text-danger">' . htmlspecialchars($row['missing_part']) . '</span></td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" class="text-center text-success">No programmed works have missing parts!</td></tr>';
            }
            $output .= '</tbody></table>
                <div class="alert alert-warning mt-3">
                    <strong><i class="fas fa-exclamation-triangle"></i> Performance risk:</strong> These works are scheduled but missing essential parts.
                </div></div>';
            break;
            
        case 'compositions_no_parts':
            $sql = 'SELECT c.catalog_number, c.name, c.composer, c.ensemble, c.grade, c.enabled
                    FROM compositions c
                    LEFT JOIN parts p ON c.catalog_number = p.catalog_number
                    WHERE p.catalog_number IS NULL AND c.enabled = 1
                    ORDER BY c.name';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-music text-secondary"></i> Compositions without any parts</h4>
                <p class="text-muted">These compositions exist but have no parts defined in the system.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $grade_badge = $row['grade'] ? '<span class="badge bg-info">' . $row['grade'] . '</span>' : '<span class="text-muted">N/A</span>';
                    $action_cell = $u_librarian ? '<a href="/composition_instrumentation?catalog_number=' . urlencode($row['catalog_number']) . '" class="btn btn-sm btn-outline-primary">Add Parts</a>' : '<span class="text-muted">-</span>';
                    $output .= '<tr>
                        <td><strong>' . htmlspecialchars($row['catalog_number']) . '</strong></td>
                        <td>' . htmlspecialchars($row['name']) . '</td>
                        <td>' . htmlspecialchars($row['composer']) . '</td>
                        <td>' . $grade_badge . '</td>
                        <td>' . $action_cell . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" class="text-center text-success">All enabled compositions have parts defined!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        case 'unused_instruments':
            $sql = 'SELECT i.id_instrument, i.name, i.family, i.description
                    FROM instruments i
                    LEFT JOIN part_types pt ON i.id_instrument = pt.default_instrument
                    WHERE pt.default_instrument IS NULL AND i.enabled = 1
                    ORDER BY i.family, i.name';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-drum text-dark"></i> Instruments not used in part types</h4>
                <p class="text-muted">These instruments are enabled but not assigned as default instruments for any part type.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Instrument</th>
                    <th>Family</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $output .= '<tr>
                        <td>' . $row['id_instrument'] . '</td>
                        <td><strong>' . htmlspecialchars($row['name']) . '</strong></td>
                        <td><span class="badge bg-secondary">' . htmlspecialchars($row['family']) . '</span></td>
                        <td>' . htmlspecialchars($row['description']) . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="4" class="text-center text-success">All instruments are being used!</td></tr>';
            }
            $output .= '</tbody></table>
                <div class="alert alert-info mt-3">
                    <strong>Note:</strong> Consider whether these instruments should be assigned to part types or disabled if no longer needed.
                </div></div>';
            break;
            
        case 'incomplete_metadata':
            $sql = 'SELECT catalog_number, name, composer, 
                           CASE WHEN genre IS NULL THEN "Missing Genre" ELSE "" END as missing_genre,
                           CASE WHEN ensemble IS NULL THEN "Missing Ensemble" ELSE "" END as missing_ensemble,
                           CASE WHEN grade IS NULL THEN "Missing Grade" ELSE "" END as missing_grade,
                           CASE WHEN duration IS NULL THEN "Missing Duration" ELSE "" END as missing_duration,
                           genre, ensemble, grade, duration
                    FROM compositions 
                    WHERE enabled = 1 AND (genre IS NULL OR ensemble IS NULL OR grade IS NULL OR duration IS NULL)
                    ORDER BY name';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-tags text-primary"></i> Compositions with incomplete metadata</h4>
                <p class="text-muted">These compositions are missing essential metadata (genre, ensemble, grade, or duration).</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Missing Data</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $missing_items = array();
                    if ($row['missing_genre']) $missing_items[] = 'Genre';
                    if ($row['missing_ensemble']) $missing_items[] = 'Ensemble';
                    if ($row['missing_grade']) $missing_items[] = 'Grade';
                    if ($row['missing_duration']) $missing_items[] = 'Duration';
                    
                    $missing_text = implode(', ', $missing_items);
                    $action_cell = $u_librarian ? '<a href="/compositions?edit=' . urlencode($row['catalog_number']) . '" class="btn btn-sm btn-outline-primary">Edit</a>' : '<span class="text-muted">-</span>';
                    
                    $output .= '<tr>
                        <td><strong>' . htmlspecialchars($row['catalog_number']) . '</strong></td>
                        <td>' . htmlspecialchars($row['name']) . '</td>
                        <td>' . htmlspecialchars($row['composer']) . '</td>
                        <td><span class="text-warning">' . $missing_text . '</span></td>
                        <td>' . $action_cell . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" class="text-center text-success">All compositions have complete metadata!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        default:
            $output = '<div class="alert alert-danger">Unknown report type requested.</div>';
            break;
    }
    
    mysqli_close($f_link);
    echo $output;
} else {
    echo '<div class="alert alert-danger">No report type specified.</div>';
}
?>
