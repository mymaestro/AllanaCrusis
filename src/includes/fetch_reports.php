<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/functions.php");

// Check user roles
// DON'T REMOVE THIS - we need to know user roles for some reports
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
}

ferror_log("Running fetch_reports.php");

if (isset($_POST["report_type"])) {
    $report_type = $_POST["report_type"];
    $output = "";
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    switch($report_type) {
        case 'urgent_missing_pdfs_future_playgrams':
            $today = date('Y-m-d');
            $sql = 'SELECT pg.name AS playgram_name, pg.performance_date, c.catalog_number, c.name AS composition_name, c.composer,
                           COUNT(p.id_part_type) AS total_parts,
                           SUM(CASE WHEN (p.image_path IS NULL OR p.image_path = "") THEN 1 ELSE 0 END) AS missing_pdfs
                    FROM playgrams pg
                    JOIN playgram_items pi ON pg.id_playgram = pi.id_playgram
                    JOIN compositions c ON pi.catalog_number = c.catalog_number
                    JOIN parts p ON c.catalog_number = p.catalog_number
                    WHERE pg.performance_date > "' . $today . '" AND pg.enabled = 1 AND c.enabled = 1
                    GROUP BY pg.id_playgram, c.catalog_number
                    HAVING missing_pdfs > 0
                    ORDER BY pg.performance_date ASC, pg.name, c.name';
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-exclamation-triangle text-danger"></i> URGENT: Future Playgrams with Parts Missing PDFs</h4>
                <p class="text-danger">These compositions are scheduled for future concerts and have parts but no PDFs uploaded. Please address these immediately!</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Playgram</th>
                    <th>Date</th>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Missing PDFs</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>';
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $action_cell = $u_librarian ? '<a href="/parts?catalog_number=' . urlencode($row['catalog_number']) . '" class="btn btn-sm btn-outline-primary">Go to Parts</a>' : '<span class="text-muted">-</span>';
                    $output .= '<tr>'
                        . '<td>' . htmlspecialchars($row['playgram_name'] ?? '') . '</td>'
                        . '<td>' . htmlspecialchars($row['performance_date'] ?? '') . '</td>'
                        . '<td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>'
                        . '<td>' . htmlspecialchars($row['composition_name'] ?? '') . '</td>'
                        . '<td>' . htmlspecialchars($row['composer'] ?? '') . '</td>'
                        . '<td><span class="badge bg-danger">' . ($row['missing_pdfs'] ?? 0) . '</span></td>'
                        . '<td>' . $action_cell . '</td>'
                        . '</tr>';
                }
            } else {
                $output .= '<tr><td colspan="7" class="text-center text-success">No urgent missing PDFs for future playgrams!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
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
                        <td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($row['composition_name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['composer'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['part_name'] ?? '') . '</td>
                        <td><span class="badge bg-warning">' . ($row['copies_count'] ?? 0) . '</span></td>
                        <td><span class="' . $status_class . '">' . $enabled_text . '</span></td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="6" class="text-center text-success">No missing originals found!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        case 'orphaned_instruments':
            $sql = 'SELECT i.id_instrument, i.name, i.description, i.family, i.enabled
                    FROM instruments i
                    LEFT JOIN section_instruments si ON i.id_instrument = si.id_instrument
                    WHERE si.id_instrument IS NULL AND i.enabled = 1
                    ORDER BY i.family, i.name';
            
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-music text-warning"></i> Instruments not assigned to sections</h4>
                <p class="text-muted">These instruments are enabled but not assigned to any section.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Instrument Name</th>
                    <th>Family</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>';
            
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $output .= '<tr>
                        <td>' . ($row['id_instrument'] ?? '') . '</td>
                        <td><strong>' . htmlspecialchars($row['name'] ?? '') . '</strong></td>
                        <td><span class="badge bg-secondary">' . htmlspecialchars($row['family'] ?? '') . '</span></td>
                        <td>' . htmlspecialchars($row['description'] ?? '') . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="4" class="text-center text-success">All instruments are properly assigned to sections!</td></tr>';
            }
            $output .= '</tbody></table>';
            $output .= $u_librarian ?
                '<div class="alert alert-info mt-3">
                    <strong>Action Required:</strong> Consider assigning these instruments to appropriate sections in the <a href="/sections">Sections</a> management page.
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
                        <td><span class="badge bg-primary">' . htmlspecialchars($row['playgram_name'] ?? '') . '</span></td>
                        <td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($row['composition_name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['composer'] ?? '') . '</td>
                        <td><span class="text-danger">' . htmlspecialchars($row['missing_part'] ?? '') . '</span></td>
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
                        <td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($row['name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['composer'] ?? '') . '</td>
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
                        <td>' . ($row['id_instrument'] ?? '') . '</td>
                        <td><strong>' . htmlspecialchars($row['name'] ?? '') . '</strong></td>
                        <td><span class="badge bg-secondary">' . htmlspecialchars($row['family'] ?? '') . '</span></td>
                        <td>' . htmlspecialchars($row['description'] ?? '') . '</td>
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
                        <td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($row['name'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['composer'] ?? '') . '</td>
                        <td><span class="text-warning">' . $missing_text . '</span></td>
                        <td>' . $action_cell . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" class="text-center text-success">All compositions have complete metadata!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        case 'compositions_parts_no_pdfs':
            $sql = 'SELECT c.catalog_number, c.name as composition_name, c.composer
                    FROM compositions c
                    JOIN parts p ON c.catalog_number = p.catalog_number
                    LEFT JOIN parts p2 ON c.catalog_number = p2.catalog_number AND (p2.image_path IS NOT NULL AND p2.image_path != "")
                    WHERE c.enabled = 1
                    GROUP BY c.catalog_number
                    HAVING SUM(CASE WHEN p2.image_path IS NOT NULL AND p2.image_path != "" THEN 1 ELSE 0 END) = 0
                    ORDER BY c.name';
            $output .= '<div class="table-responsive">
                <h4><i class="fas fa-file-pdf text-primary"></i> Compositions with parts but no PDFs</h4>
                <p class="text-muted">These compositions have at least one part, but none of their parts have a PDF file attached.</p>
                <table class="table table-striped table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Catalog #</th>
                    <th>Composition</th>
                    <th>Composer</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>';
            $res = mysqli_query($f_link, $sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $action_cell = $u_librarian ? '<a href="/composition_instrumentation?catalog_number=' . urlencode($row['catalog_number']) . '" class="btn btn-sm btn-outline-primary">Instrumentation</a>' : '<span class="text-muted">-</span>';
                    $output .= '<tr>'
                        . '<td><strong>' . htmlspecialchars($row['catalog_number'] ?? '') . '</strong></td>'
                        . '<td>' . htmlspecialchars($row['composition_name'] ?? '') . '</td>'
                        . '<td>' . htmlspecialchars($row['composer'] ?? '') . '</td>'
                        . '<td>' . $action_cell . '</td>'
                        . '</tr>';
                }
            } else {
                $output .= '<tr><td colspan="4" class="text-center text-success">All compositions with parts have at least one PDF!</td></tr>';
            }
            $output .= '</tbody></table></div>';
            break;
            
        case 'download_tokens_zips':
         $active_tokens_sql = 'SELECT dt.token, dt.zip_filename, dt.expires_at, dt.used, dt.created_at,
                          pg.name as playgram_name, s.name as section_name, u.username, dt.email
                      FROM download_tokens dt
                      LEFT JOIN playgrams pg ON dt.id_playgram = pg.id_playgram
                      LEFT JOIN sections s ON dt.id_section = s.id_section
                      LEFT JOIN users u ON dt.id_user = u.id_users
                      ORDER BY dt.created_at DESC
                      LIMIT 50';
            
            $all_zips_sql = 'SELECT DISTINCT dt.zip_filename,
                                    COUNT(*) as total_tokens,
                                    SUM(CASE WHEN dt.used = 0 AND dt.expires_at > NOW() THEN 1 ELSE 0 END) as active_tokens,
                                    MAX(dt.expires_at) as latest_expiration,
                                    MIN(dt.created_at) as first_created
                             FROM download_tokens dt
                             GROUP BY dt.zip_filename
                             ORDER BY dt.zip_filename';
            
            // ZIP files section (first, at top)
            $output .= '<div class="row mb-4">
                <div class="col-12">
                    <h4><i class="fas fa-file-archive text-success"></i> Available ZIP Files</h4>
                    <p class="text-muted">All ZIP files referenced in download tokens.</p>
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>ZIP Filename</th>
                        <th>Total Tokens</th>
                        <th>Active</th>
                        <th>Latest Expiry</th>
                        <th>First Created</th>
                    </tr>
                    </thead>
                    <tbody>';
            
            $res = mysqli_query($f_link, $all_zips_sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $latest_exp = $row['latest_expiration'] ? date('M j, Y', strtotime($row['latest_expiration'])) : 'N/A';
                    $first_created = date('M j, Y', strtotime($row['first_created']));
                    $active_badge = $row['active_tokens'] > 0 ? 
                        '<span class="badge bg-success">' . $row['active_tokens'] . '</span>' : 
                        '<span class="badge bg-secondary">0</span>';
                    
                    $output .= '<tr>
                        <td><strong>' . htmlspecialchars($row['zip_filename'] ?? '') . '</strong></td>
                        <td>' . ($row['total_tokens'] ?? 0) . '</td>
                        <td>' . $active_badge . '</td>
                        <td><small>' . $latest_exp . '</small></td>
                        <td><small>' . $first_created . '</small></td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" class="text-center text-muted">No ZIP files found</td></tr>';
            }
            $output .= '</tbody></table></div>
                </div>
            </div>';
            
            // Recent tokens section (second, below ZIP files)
            $output .= '<div class="row">
                <div class="col-12">
                    <h4><i class="fas fa-key text-success"></i> Recent Download Tokens</h4>
                    <p class="text-muted">Most recent 50 download tokens (showing status and expiration).</p>
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Token (Last 8)</th>
                        <th>Playgram</th>
                        <th>Section</th>
                        <th>ZIP File</th>
                        <th>Email Sent To</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Created By</th>
                    </tr>
                    </thead>
                    <tbody>';
            
            $res = mysqli_query($f_link, $active_tokens_sql);
            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $token_short = '...' . substr($row['token'], -8);
                    $expires_date = date('M j, Y H:i', strtotime($row['expires_at']));
                    $created_by = $row['username'] ? htmlspecialchars($row['username'] ?? '') : 'Unknown';
                    $status_badge = $row['used'] ? '<span class="badge bg-secondary">Used</span>' : '<span class="badge bg-success">Available</span>';
                    
                    $output .= '<tr>
                        <td><code>' . $token_short . '</code></td>
                        <td>' . htmlspecialchars($row['playgram_name'] ?? '') . '</td>
                        <td><span class="badge bg-info">' . htmlspecialchars($row['section_name'] ?? '') . '</span></td>
                        <td>' . htmlspecialchars($row['zip_filename'] ?? '') . '</td>
                        <td>' . htmlspecialchars($row['email'] ?? '') . '</td>
                        <td>' . $status_badge . '</td>
                        <td><small>' . $expires_date . '</small></td>
                        <td>' . $created_by . '</td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="7" class="text-center text-muted">No tokens found</td></tr>';
            }
            $output .= '</tbody></table></div>
                </div>
            </div>';
            
            $output .= '<div class="alert alert-info mt-3">
                <strong><i class="fas fa-info-circle"></i> About Download Tokens:</strong><br>
                • Tokens are valid for ' . DOWNLOAD_TOKEN_EXPIRY_DAYS . ' days after creation<br>
                • Each token can only be used once<br>
                • ZIP files are served from the distribution directory<br>
                • Tokens are automatically created when ZIP distributions are requested
            </div>';
            
            if ($u_librarian || $u_admin) {
                $output .= '<div class="mt-3">
                    <button id="cleanup-tokens-btn" class="btn btn-warning">
                        <i class="fas fa-broom"></i> Clean up
                    </button>
                    <small class="text-muted ms-2">Remove expired and used tokens and their associated ZIP files</small>
                    <div id="cleanup-result" class="mt-2"></div>
                </div>';
            }
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
