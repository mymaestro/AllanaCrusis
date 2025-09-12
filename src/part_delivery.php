<?php
define('PAGE_TITLE', 'Part Delivery for Concert Series');
define('PAGE_NAME', 'Part Delivery');
require_once(__DIR__. "/includes/header.php");
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");

$u_librarian = FALSE;
$u_user = FALSE;
$username = null;
$user_id = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);

    // Fetch user ID from users table
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql_user = "SELECT id_users FROM users WHERE username = '" . mysqli_real_escape_string($f_link, $username) . "' LIMIT 1";
    $res_user = mysqli_query($f_link, $sql_user);
    if ($row_user = mysqli_fetch_assoc($res_user)) {
        $user_id = $row_user['id_users'];
    }
    mysqli_free_result($res_user);
    mysqli_close($f_link);
}
// Check if user has permission
if (!$u_librarian && !$u_user) {
    echo '<main role="main" class="container"><div class="alert alert-danger">Access denied.</div></main>';
    require_once(__DIR__. "/includes/footer.php");
    exit;
}

ferror_log("RUNNING part_delivery.php");

// Get database connection
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get all enabled playgrams
$sql = "SELECT id_playgram, name, description FROM playgrams WHERE enabled = 1 ORDER BY name";
$playgrams_result = mysqli_query($f_link, $sql);
$playgrams = [];
while($row = mysqli_fetch_assoc($playgrams_result)) {
    $playgrams[] = $row;
}

// Build section_ids array to fetch sections for this user
$sections = [];
$section_ids = [];
if ($u_librarian) {
    // Librarian: all sections
    $sql = "SELECT id_section, name, description, section_leader FROM sections WHERE enabled = 1 ORDER BY name";
    ferror_log("Fetching ALL sections for librarian with SQL: " . $sql);
    $sections_result = mysqli_query($f_link, $sql);
    while($row = mysqli_fetch_assoc($sections_result)) {
        $sections[] = $row;
        $section_ids[] = $row['id_section'];
    }
} elseif ($user_id !== null) {
    // Section leader: only their sections
    $sql = "SELECT id_section, name, description, section_leader FROM sections WHERE enabled = 1 AND section_leader = " . intval($user_id) . " ORDER BY name";
    ferror_log("Fetching sections for section_leader user_id " . $user_id . " with SQL: " . $sql);
    $sections_result = mysqli_query($f_link, $sql);
    while($row = mysqli_fetch_assoc($sections_result)) {
        $sections[] = $row;
        $section_ids[] = $row['id_section'];
    }
}
// else: no sections
mysqli_close($f_link);
?>

<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><i class="fas fa-file-archive"></i> <?php echo ORGNAME; ?> part delivery for Concert Series</h1>
                <p class="lead">Generate ZIP files containing PDF parts organized by section for concert series</p>
            </div>
        </div>

        <!-- Help and Instructions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> How part delivery works</h6>
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#helpInstructionsCollapse" aria-expanded="false" aria-controls="helpInstructionsCollapse">
                            <span class="collapsed"><i class="fas fa-plus"></i></span>
                            <span class="expanded d-none"><i class="fas fa-minus"></i></span>
                        </button>
                    </div>
                    <div id="helpInstructionsCollapse" class="collapse">
                        <div class="card-body">
                            <ol>
                                <li><strong>Select a Playgram:</strong> Choose the concert program that contains the compositions you want to distribute from the Choose Playgram menu.</li>
                                <li><strong>Load program details:</strong> Click <em>Load program details</em> to view the compositions in the playgram and their available parts.</li>
                                <li><strong>Generate ZIP files:</strong> For each section (Woodwinds, Brass, Percussion, etc.), click <em>Generate ZIP file</em> to create a ZIP file that contains:</li>
                                <ul>
                                    <li>All PDF parts for that section across all compositions in the playgram</li>
                                    <li>The PDF files are named as: <code>[Order] - [Composition Name] - [Part Name].pdf</code></li>
                                    <li>Example: <code>01 - March Grandioso - Flute 1.pdf</code></li>
                                    <li><strong>Note:</strong> Only parts with PDF files are included. Missing PDFs are noted in the generation log.</li>
                                </ul>
                                <li><strong>Copy download link:</strong> Click <em>Copy link</em> to copy the download link for the ZIP file. You can send this link to a band member to download their parts.</li>
                            </ol>
                            <div class="alert alert-warning mt-3">
                                <strong>Note:</strong> The link you create contains a one-time use download token that is invalidated after use. The token must be used within 2 days, or it will expire.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Playgram Selection -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Select concert program (Playgram)</h5>
                    </div>
                    <div class="card-body">
                        <form id="playgram-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="playgram_select" class="form-label">Choose Playgram:</label>
                                    <select class="form-select" id="playgram_select" name="playgram_id" required>
                                        <option value="">-- Select a Concert Program --</option>
                                        <?php foreach($playgrams as $playgram): ?>
                                        <option value="<?php echo $playgram['id_playgram']; ?>">
                                            <?php echo htmlspecialchars($playgram['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" id="load_playgram" class="btn btn-primary me-2">
                                        <i class="fas fa-search"></i> Load program details
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Playgram Details -->
        <div class="row mt-4" id="playgram_details" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-music"></i> Program Compositions</h5>
                    </div>
                    <div class="card-body">
                        <div id="compositions_list">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections and Parts Distribution -->
        <div class="row mt-4" id="sections_distribution" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users"></i> Parts distribution by section</h5>
                        <small class="text-muted">Generate ZIP files for each section containing renamed PDF parts</small>
                    </div>
                    <div class="card-body">
                        <div class="row" id="sections_grid">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress and Results -->
        <div class="row mt-4" id="generation_progress" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 id="progress_header"><i class="fas fa-spinner fa-spin"></i> Generation progress</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progress_bar">0%</div>
                        </div>
                        <div id="progress_log">
                            <!-- Progress messages will appear here -->
                        </div>
                        <div id="download_links" style="display: none;">
                            <h6>Generated ZIP Files:</h6>
                            <ul id="zip_files_list">
                                <!-- Download links will appear here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once(__DIR__. "/includes/footer.php"); ?>

<script>
// Show/hide help instructions
$(function() {
    var $collapse = $('#helpInstructionsCollapse');
    var $button = $('[data-bs-target="#helpInstructionsCollapse"]');
    if ($collapse.length && $button.length) {
        $collapse.on('show.bs.collapse', function() {
            $button.find('.collapsed').addClass('d-none');
            $button.find('.expanded').removeClass('d-none');
        });
        $collapse.on('hide.bs.collapse', function() {
            $button.find('.collapsed').removeClass('d-none');
            $button.find('.expanded').addClass('d-none');
        });
    }
});
$(document).ready(function() {
    let currentPlaygramId = null;
    let playgramData = null;

    // Load playgram details when selected
    $('#load_playgram').on('click', function() {
        const playgramId = $('#playgram_select').val();
        if (!playgramId) {
            alert('Please select a playgram first.');
            return;
        }

        currentPlaygramId = playgramId;
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

        $.ajax({
            url: 'index.php?action=fetch_playgram_distribution',
            method: 'POST',
            data: {
                action: 'load_playgram',
                playgram_id: playgramId,
                section_ids: <?php echo json_encode($section_ids); ?>
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    playgramData = response.data;
                    displayPlaygramDetails(response.data);
                    displaySectionsDistribution(response.data);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('Error loading playgram data. Please try again.');
            },
            complete: function() {
                $('#load_playgram').prop('disabled', false).html('<i class="fas fa-search"></i> Load Program Details');
            }
        });
    });


    function displayPlaygramDetails(data) {
        let html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>Order</th><th>Composition</th><th>Composer</th><th>Available Parts</th><th>Missing PDFs</th></tr></thead><tbody>';
        
        data.compositions.forEach(function(comp) {
            html += '<tr>';
            html += '<td><span class="badge bg-primary">' + comp.comp_order + '</span></td>';
            html += '<td><strong>' + comp.composition_name + '</strong></td>';
            html += '<td>' + (comp.composer || 'Unknown') + '</td>';
            html += '<td><span class="badge bg-success">' + comp.parts_with_pdf + '</span></td>';
            html += '<td>' + (comp.parts_without_pdf > 0 ? '<span class="badge bg-warning">' + comp.parts_without_pdf + '</span>' : '-') + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        $('#compositions_list').html(html);
        $('#playgram_details').show();
    }

    function displaySectionsDistribution(data) {
        console.log("Displaying sections:", data.sections);
        let html = '';
        
        data.sections.forEach(function(section) {
            html += '<div class="col-md-6 col-lg-4 mb-3">';
            html += '<div class="card border-info">';
            html += '<div class="card-header bg-light">';
            html += '<h6 class="mb-0"><i class="fas fa-users"></i> ' + section.section_name + '</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<p class="card-text"><strong>' + section.total_parts + '</strong> parts across all compositions</p>';
            if (section.parts_with_pdf > 0) {
                html += '<p class="text-success small"><i class="fas fa-file-pdf"></i> ' + section.parts_with_pdf + ' parts have PDF files</p>';
            }
            if (section.parts_without_pdf > 0) {
                html += '<p class="text-warning small"><i class="fas fa-exclamation-triangle"></i> ' + section.parts_without_pdf + ' parts missing PDFs</p>';
            }
            html += '<button type="button" class="btn btn-outline-info btn-sm generate-section" data-section-id="' + section.id_section + '">';
            html += '<i class="fas fa-file-archive"></i> Generate ZIP</button>';
            html += '</div></div></div>';
        });
        
        $('#sections_grid').html(html);
        $('#sections_distribution').show();
    }

    // Handle individual section ZIP generation
    $(document).on('click', '.generate-section', function() {
        const sectionId = $(this).data('section-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
        
        $.ajax({
            url: 'index.php?action=fetch_playgram_distribution',
            method: 'POST',
            data: {
                action: 'generate_section_zip',
                playgram_id: currentPlaygramId,
                section_id: sectionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Create Copy Link button
                    const linkToCopy = response.data.download_link;
                    const copyBtn = $('<button>').attr({
                        type: 'button',
                        class: 'btn btn-primary btn-sm copy-link-btn',
                        'data-link': linkToCopy
                    }).html('<i class="fas fa-link"></i> Copy Link');
                    button.replaceWith(copyBtn);
                } else {
                    alert('Error: ' + response.message);
                    button.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Generate ZIP');
                }
            },
            error: function() {
                alert('Error generating ZIP file. Please try again.');
                button.prop('disabled', false).html('<i class="fas fa-file-archive"></i> Generate ZIP');
            }
        });
    });

    // Clipboard copy handler for all copy-link-btn buttons
    $(document).on('click', '.copy-link-btn', function() {
        const link = $(this).data('link');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.origin + link)
                .then(() => {
                    $(this).text('Copied!').removeClass('btn-primary').addClass('btn-success');
                    setTimeout(() => {
                        $(this).html('<i class="fas fa-link"></i> Copy Link');
                        $(this).removeClass('btn-success').addClass('btn-primary');
                    }, 1500);
                })
                .catch(() => {
                    alert('Failed to copy link.');
                });
        } else {
            // Fallback for older browsers
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(window.location.origin + link).select();
            document.execCommand('copy');
            tempInput.remove();
            $(this).text('Copied!').removeClass('btn-primary').addClass('btn-success');
            setTimeout(() => {
                $(this).html('<i class="fas fa-link"></i> Copy Link');
                $(this).removeClass('btn-success').addClass('btn-primary');
            }, 1500);
        }
    });
});
</script>

</body>
</html>
