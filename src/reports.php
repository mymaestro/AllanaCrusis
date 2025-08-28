<?php
define('PAGE_TITLE', 'Administrative Reports - Data Integrity');
define('PAGE_NAME', 'reports');
require_once(__DIR__. "/includes/header.php");
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}
require_once(__DIR__. "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__. "/includes/functions.php");
ferror_log("RUNNING reports.php");

// Get database connection
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get counts for each report

$report_counts = array();
// X. Compositions with parts but no PDFs
$sql = "SELECT COUNT(DISTINCT c.catalog_number) as count
        FROM compositions c
        JOIN parts p ON c.catalog_number = p.catalog_number
        LEFT JOIN parts p2 ON c.catalog_number = p2.catalog_number AND (p2.image_path IS NOT NULL AND p2.image_path != '')
        WHERE c.enabled = 1
        GROUP BY c.catalog_number
        HAVING SUM(CASE WHEN p2.image_path IS NOT NULL AND p2.image_path != '' THEN 1 ELSE 0 END) = 0";
$res = mysqli_query($f_link, $sql);
$report_counts['compositions_parts_no_pdfs'] = 0;
if ($res && mysqli_num_rows($res) > 0) {
    $report_counts['compositions_parts_no_pdfs'] = mysqli_num_rows($res);
}

// 1. Parts with zero originals
$sql = "SELECT COUNT(*) as count FROM parts WHERE originals_count = 0";
$res = mysqli_query($f_link, $sql);
$report_counts['missing_originals'] = mysqli_fetch_assoc($res)['count'];

// 2. Instruments not in any sections
$sql = "SELECT COUNT(*) as count FROM instruments i
        LEFT JOIN section_instruments si ON i.id_instrument = si.id_instrument 
        WHERE si.id_instrument IS NULL AND i.enabled = 1";
$res = mysqli_query($f_link, $sql);
$report_counts['orphaned_instruments'] = mysqli_fetch_assoc($res)['count'];

// 3. Compositions in playgrams without all required parts
$sql = "SELECT COUNT(DISTINCT pi.catalog_number) as count 
        FROM playgram_items pi
        JOIN compositions c ON pi.catalog_number = c.catalog_number
        WHERE c.enabled = 1 AND pi.catalog_number IN (
            SELECT catalog_number FROM parts WHERE originals_count = 0
        )";
$res = mysqli_query($f_link, $sql);
$report_counts['playgram_missing_parts'] = mysqli_fetch_assoc($res)['count'];

// 4. Compositions without any parts
$sql = "SELECT COUNT(*) as count FROM compositions c 
        LEFT JOIN parts p ON c.catalog_number = p.catalog_number 
        WHERE p.catalog_number IS NULL AND c.enabled = 1";
$res = mysqli_query($f_link, $sql);
$report_counts['compositions_no_parts'] = mysqli_fetch_assoc($res)['count'];

// 5. Instruments not used in any part types
$sql = "SELECT COUNT(*) as count FROM instruments i
        LEFT JOIN part_types pt ON i.id_instrument = pt.default_instrument
        WHERE pt.default_instrument IS NULL AND i.enabled = 1";
$res = mysqli_query($f_link, $sql);
$report_counts['unused_instruments'] = mysqli_fetch_assoc($res)['count'];

// 6. Compositions with missing metadata
$sql = "SELECT COUNT(*) as count FROM compositions 
        WHERE enabled = 1 AND (genre IS NULL OR ensemble IS NULL OR grade IS NULL OR duration IS NULL)";
$res = mysqli_query($f_link, $sql);
$report_counts['incomplete_metadata'] = mysqli_fetch_assoc($res)['count'];

// Urgent: Compositions with parts but missing PDFs in future playgrams
$sql = "SELECT COUNT(DISTINCT c.catalog_number) as count
        FROM playgrams pg
        JOIN playgram_items pi ON pg.id_playgram = pi.id_playgram
        JOIN compositions c ON pi.catalog_number = c.catalog_number
        JOIN parts p ON c.catalog_number = p.catalog_number
        LEFT JOIN parts p2 ON c.catalog_number = p2.catalog_number AND (p2.image_path IS NOT NULL AND p2.image_path != '')
        WHERE pg.performance_date > CURDATE() AND pg.enabled = 1 AND c.enabled = 1
        GROUP BY pg.id_playgram, c.catalog_number
        HAVING SUM(CASE WHEN p2.image_path IS NOT NULL AND p2.image_path != '' THEN 1 ELSE 0 END) = 0";
$res = mysqli_query($f_link, $sql);
$report_counts['urgent_missing_pdfs_future_playgrams'] = 0;
if ($res && mysqli_num_rows($res) > 0) {
    $report_counts['urgent_missing_pdfs_future_playgrams'] = mysqli_num_rows($res);
}

mysqli_close($f_link);
?>

<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><i class="fas fa-exclamation-triangle text-warning"></i> <?php echo ORGNAME; ?> Library reports</h1>
                <p class="lead">Data integrity and missing pieces analysis</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Future playgrams missing PDFs
                                </h6>
                                <h3 class="text-danger"><?php echo number_format($report_counts['urgent_missing_pdfs_future_playgrams']); ?></h3>
                                <small class="text-muted">Upcoming concerts with missing PDFs</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-danger btn-sm report-btn" data-report="urgent_missing_pdfs_future_playgrams">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-danger">
                                    <i class="fas fa-file-times"></i> Missing originals
                                </h6>
                                <h3 class="text-danger"><?php echo number_format($report_counts['missing_originals']); ?></h3>
                                <small class="text-muted">Parts with zero originals</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-danger btn-sm report-btn" data-report="missing_originals">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-music"></i> Orphaned instruments
                                </h6>
                                <h3 class="text-warning"><?php echo number_format($report_counts['orphaned_instruments']); ?></h3>
                                <small class="text-muted">Instruments not in sections</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-warning btn-sm report-btn" data-report="orphaned_instruments">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-info">
                                    <i class="fas fa-calendar-times"></i> Playgram issues
                                </h6>
                                <h3 class="text-info"><?php echo number_format($report_counts['playgram_missing_parts']); ?></h3>
                                <small class="text-muted">Programmed works missing parts</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-info btn-sm report-btn" data-report="playgram_missing_parts">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-secondary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-secondary">
                                    <i class="fas fa-music"></i> No parts
                                </h6>
                                <h3 class="text-secondary"><?php echo number_format($report_counts['compositions_no_parts']); ?></h3>
                                <small class="text-muted">Compositions without parts</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-secondary btn-sm report-btn" data-report="compositions_no_parts">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-file-pdf"></i> Parts but no PDFs
                                </h6>
                                <h3 class="text-primary"><?php echo number_format($report_counts['compositions_parts_no_pdfs']); ?></h3>
                                <small class="text-muted">Compositions with parts but no PDFs</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-primary btn-sm report-btn" data-report="compositions_parts_no_pdfs">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-dark">
                                    <i class="fas fa-drum"></i> Unused instruments
                                </h6>
                                <h3 class="text-dark"><?php echo number_format($report_counts['unused_instruments']); ?></h3>
                                <small class="text-muted">Instruments not in part types</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-dark btn-sm report-btn" data-report="unused_instruments">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-tags"></i> Incomplete metadata
                                </h6>
                                <h3 class="text-primary"><?php echo number_format($report_counts['incomplete_metadata']); ?></h3>
                                <small class="text-muted">Missing genre/ensemble/grade/duration</small>
                            </div>
                            <div class="align-self-center">
                                <button class="btn btn-outline-primary btn-sm report-btn" data-report="incomplete_metadata">
                                    View Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($u_librarian): ?>
        <!-- Additional Tools -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Additional Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="/part_distribution" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-file-archive"></i> Part distribution
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/comps2csv" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-file-csv"></i> Export to CSV
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/composition_instrumentation" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-list-ul"></i> Manage instrumentations
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="/home" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-tachometer-alt"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal for Report Details -->
        <div class="modal fade" id="view_data_modal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Report details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="report_detail">
                        <!-- Report content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
<?php require_once(__DIR__. "/includes/footer.php"); ?>

<script>
$(document).ready(function() {
    // Handle report button clicks
    $('.report-btn').on('click', function() {
        var report_type = $(this).data('report');
        var button = $(this);
        
        // Show loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: "index.php?action=fetch_reports",
            type: "POST",
            data: {
                report_type: report_type
            },
            success: function(data) {
                $('#report_detail').html(data);
                $('#view_data_modal').modal('show');
                // Reset button
                button.prop('disabled', false).html('View Report');
            },
            error: function() {
                alert('Error loading report. Please try again.');
                // Reset button
                button.prop('disabled', false).html('View Report');
            }
        });
    });
});
</script>

</body>
</html>