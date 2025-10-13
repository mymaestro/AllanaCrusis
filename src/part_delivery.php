<?php
define('PAGE_TITLE', 'Part Delivery for Concert Series');
define('PAGE_NAME', 'Part Delivery');
require_once(__DIR__. "/includes/header.php");
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");

$u_librarian = FALSE;
$u_user = FALSE;
$username = null;
$user_id = null;
$user_real_name = '';
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);

    // Fetch user ID, real name, and email from users table
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql_user = "SELECT id_users, name, address FROM users WHERE username = '" . mysqli_real_escape_string($f_link, $username) . "' LIMIT 1";
    $res_user = mysqli_query($f_link, $sql_user);
    if ($row_user = mysqli_fetch_assoc($res_user)) {
        $user_id = $row_user['id_users'];
        $user_real_name = $row_user['name'];
        $user_email = $row_user['address'];
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
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Instructions: How part delivery works</h6>
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#helpInstructionsCollapse" aria-expanded="false" aria-controls="helpInstructionsCollapse">
                            <span class="collapsed"><i class="fas fa-plus"></i></span>
                            <span class="expanded d-none"><i class="fas fa-minus"></i></span>
                        </button>
                    </div>
                    <div id="helpInstructionsCollapse" class="collapse">
                        <div class="card-body">
                            <ol>
                                <li><strong>Select a playgram:</strong> Choose the concert program that contains the compositions you want to distribute from the Choose playgram menu.</li>
                                <li><strong>Load playgram details:</strong> Click <em>Load playgram details</em> to view the compositions in the playgram and their available parts.</li>
                                <li><strong>Generate ZIP files:</strong> For each section (Woodwinds, Brass, Percussion, etc.), click <em>Generate ZIP</em> to create a ZIP file that contains:</li>
                                <ul>
                                    <li>All PDF parts for that section across all compositions in the playgram</li>
                                    <li>The PDF files insdide the ZIP are named as: <code>[Order] - [Composition Name] - [Part Name].pdf</code></li>
                                    <li>Example: <code>01 - March Grandioso - Flute 1.pdf</code></li>
                                    <li><strong>Note:</strong> Only parts with PDF files are included. Missing PDFs are noted in the generation log.</li>
                                    <li>The button changes to <em>Send ZIP</em> after generation, so that you can email the download instructions.</li>
                                </ul>
                                <li><strong>Send ZIP:</strong> Click <em>Send ZIP</em> to open the email form to send the link to the ZIP file. A dialog opens so you can enter the recipient's email address and message. The message contains a secure one-time download link to the ZIP file, instructions for use, and a reminder not to share the link.</li>
                                <li><strong>Send email:</strong> In the email form, enter the recipient's email address. You can also edit the message before sending, and you can choose whether to send HTML or plain text. Choose the <strong>Send</strong> button to send the email.</li>
                                <li><strong>Confirmation:</strong> After sending, you will see a confirmation message indicating that the email has been sent successfully. You can then choose <strong>Send ZIP</strong> again to generate a new token and send the email to a different recipient.</li>
                            </ol>
                            <div class="alert alert-warning mt-3">
                                <strong>Note:</strong> The link you create contains a one-time use download token that is invalidated after use. The token must be used within <?php echo DOWNLOAD_TOKEN_EXPIRY_DAYS; ?> days, or it will expire.
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
                                    <label for="playgram_select" class="form-label">Choose playgram:</label>
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
                                        <i class="fas fa-search"></i> Load playgram details
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
            <!-- Download Tokens & ZIP Files Report (collapsible) -->
        <?php if (!empty($sections)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-download"></i> Download tokens & ZIP files report</h6>
                        <button class="btn btn-sm btn-light" type="button" id="showTokensReportBtn">
                            <span id="showTokensReportIcon"><i class="fas fa-plus"></i></span> Show Report
                        </button>
                    </div>
                    <div id="tokensReportCollapse" class="collapse">
                        <div class="card-body" id="tokensReportContent">
                            <div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading report...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- eMail Modal for future use -->
        <div id="emailModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="emailModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Send parts</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <form method="post" id="send_email_form">
                                <div class="mb-3">
                                    <label for="emailFormControlInput1" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="emailFormControlInput1" placeholder="name@example.com">
                                </div>
                                <ul class="nav nav-tabs" id="emailTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="html-tab" data-bs-toggle="tab" data-bs-target="#htmlTabPane" type="button" role="tab" aria-controls="htmlTabPane" aria-selected="true">HTML</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="text-tab" data-bs-toggle="tab" data-bs-target="#textTabPane" type="button" role="tab" aria-controls="textTabPane" aria-selected="false">Plain Text</button>
                                    </li>
                                </ul>
                                <div class="tab-content mt-3" id="emailTabContent">
                                    <div class="tab-pane fade show active" id="htmlTabPane" role="tabpanel" aria-labelledby="html-tab">
                                        <label for="emailFormControlTextareaHtml" class="form-label">HTML Message</label>
                                        <textarea class="form-control" id="emailFormControlTextareaHtml" rows="15"></textarea>
                                    </div>
                                    <div class="tab-pane fade" id="textTabPane" role="tabpanel" aria-labelledby="text-tab">
                                        <label for="emailFormControlTextareaText" class="form-label">Plain Text Message</label>
                                        <textarea class="form-control" id="emailFormControlTextareaText" rows="15"></textarea>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="modal-footer">  
                        <input type="submit" name="send_email" id="send_email" value="Send" class="btn btn-success" />
                        </form>  
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
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
        // Download tokens & ZIP files report loader
    $('#showTokensReportBtn').on('click', function() {
        var $collapse = $('#tokensReportCollapse');
        var $icon = $('#showTokensReportIcon i');
        if ($collapse.hasClass('show')) {
            $collapse.collapse('hide');
            $icon.removeClass('fa-minus').addClass('fa-plus');
        } else {
            $collapse.collapse('show');
            $icon.removeClass('fa-plus').addClass('fa-minus');
            // Load report via AJAX only if not loaded yet or on every open
            $('#tokensReportContent').html('<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading report...</div>');
            $.ajax({
                url: 'index.php?action=fetch_reports',
                type: 'POST',
                data: { report_type: 'download_tokens_zips' },
                success: function(data) {
                    $('#tokensReportContent').html(data);
                },
                error: function() {
                    $('#tokensReportContent').html('<div class="alert alert-danger">Error loading report. Please try again.</div>');
                }
            });
        }
    });

    let currentPlaygramId = null;
    let playgramData = null;
    let zipData = null;

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
                $('#load_playgram').prop('disabled', false).html('<i class="fas fa-search"></i> Load playgram details');
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
        let html = '';
        
        data.sections.forEach(function(section) {
            html += '<div class="col-md-6 col-lg-4 mb-3">';
            html += '<div class="card border-info">';
            html += '<div class="card-header bg-light">';
            html += '<h6 class="mb-0">' + section.section_name + '</h6>';
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
                action: 'create_section_zip',
                playgram_id: currentPlaygramId,
                section_id: sectionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    zipData = response.data;
                    // Before creating new Send ZIP button, revert all other Send ZIP buttons to Generate ZIP
                    $('#sections_grid .copy-link-btn').each(function() {
                        const otherSectionId = $(this).data('section-id');
                        // Replace with Generate ZIP button for that section
                        const genBtn = $('<button>').attr({
                            type: 'button',
                            class: 'btn btn-outline-info btn-sm generate-section',
                            'data-section-id': otherSectionId
                        }).html('<i class="fas fa-file-archive"></i> Generate ZIP');
                        $(this).replaceWith(genBtn);
                    });
                    // Create Copy Link button for this section
                    const zipToSend = zipData.filename;
                    const copyBtn = $('<button>').attr({
                        type: 'button',
                        class: 'btn btn-primary btn-sm copy-link-btn',
                        'data-link': zipToSend,
                        'data-section-id': sectionId,
                    }).html('<i class="fas fa-link"></i> Send ZIP');
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

    // Email modal handler for all copy-link-btn buttons
    $(document).on('click', '.copy-link-btn', function() {
        // Get section and playgram info
        const sectionId = $(this).data('section-id');
        const sectionName = $(this).closest('.card').find('h6').text().trim();
        const bandName = <?php echo json_encode(defined('ORGDESC') ? ORGDESC : 'Your Band'); ?>;
        <?php
        $logoPath = rtrim(ORGHOME ?? '', '/') . '/' . ltrim(defined('ORGLOGO') ? ORGLOGO : '', '/');
        $logoUrl = filter_var($logoPath, FILTER_VALIDATE_URL) ? $logoPath : '';
        ?>
        const logoUrl = <?php echo json_encode($logoUrl); ?>;
        let playgramName = '';
        if (playgramData && playgramData.playgram_name) {
            playgramName = playgramData.playgram_name;
        } else {
            playgramName = $('#playgram_select option:selected').text().trim();
        }
        const contactName = <?php echo json_encode(isset($user_real_name) ? $user_real_name : 'the Librarian'); ?>;
        const fromEmail = <?php echo json_encode(isset($user_email) ? $user_email : ''); ?>;
        // Get ZIP info from zipData
        if (!zipData || !zipData.filename) {
            alert('ZIP info missing. Please generate ZIP first.');
            return;
        }
        // Request a new token/link for this ZIP
        $.ajax({
            url: 'index.php?action=fetch_playgram_distribution',
            method: 'POST',
            data: {
                action: 'generate_download_token',
                playgram_id: currentPlaygramId,
                section_id: sectionId,
                zip_filename: zipData.filename
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success || !response.data) {
                    alert('Error generating new link: ' + (response.message || 'Unknown error'));
                    return;
                }
                const link = response.data.download_link;
                const token = response.data.token;
                const expiresAt = response.data.expires_at;
                zipData.token = token; // Update zipData with new token
                zipData.download_link = link; // Update link
                console.log('Generated new download link that expires ', expiresAt);

                <?php
                $templatePath = __DIR__ . '/../config/download-contract.html';
                $templateHtml = file_exists($templatePath) ? file_get_contents($templatePath) : '';
                $jsTemplate = json_encode($templateHtml);
                ?>
                let templateHtml = <?php echo $jsTemplate; ?>;
                templateHtml = templateHtml
                    .replace(/\{\{sectionName\}\}/g, sectionName)
                    .replace(/\{\{bandName\}\}/g, bandName)
                    .replace(/\{\{playgramName\}\}/g, playgramName)
                    .replace(/\{\{download_link\}\}/g, window.location.origin + link)
                    .replace(/\{\{contactName\}\}/g, contactName)
                    .replace(/\{\{logoUrl\}\}/g, logoUrl)
                    .replace(/\{\{expiryDays\}\}/g, <?php echo DOWNLOAD_TOKEN_EXPIRY_DAYS; ?>);

                // Generate plain text version by stripping tags and formatting
                function htmlToPlainText(html) {
                    let text = html;
                     // Remove style blocks
                    text = text.replace(/<style[\s\S]*?<\/style>/gi, '');
                    // Add newlines before and after <h1>-<h6> tags
                    text = text.replace(/<(h[1-6])[^>]*>/gi, '\n');
                    text = text.replace(/<\/h[1-6]>/gi, '\n');
                    // Add newlines after block elements
                    text = text.replace(/<\/?(p|div|ol|ul|br|footer)>/gi, '\n');
                    // Convert list items to lines with dashes
                    let liIndex = 1;
                    text = text.replace(/<li[^>]*>([\s\S]*?)<\/li>/gi, function(match, content) {
                        // Use numbered list if inside <ol>, dash if <ul>
                        return '\n' + (liIndex++) + '. ' + content.trim() + '\n';
                    });
                    // Remove all remaining tags
                    text = text.replace(/<[^>]+>/g, '');
                    // Decode HTML entities
                    text = text.replace(/&nbsp;/g, ' ')
                        .replace(/&amp;/g, '&')
                        .replace(/&lt;/g, '<')
                        .replace(/&gt;/g, '>')
                        .replace(/&quot;/g, '"')
                        .replace(/&#39;/g, "'");
                    // Collapse multiple spaces and newlines
                    text = text.replace(/ +/g, ' ').trim();
                    // Trim leading/trailing whitespace and newlines
                    text = text.replace(/^\s+|\s+$/g, '');
                    // Replace multiple newlines with a single newline
                    text = text.replace(/\n{2,}/g, '\n');
                    return text;
                }

                $('#emailModal').modal('show');
                $('#emailFormControlTextareaHtml').val(templateHtml);
                $('#emailFormControlTextareaText').val(htmlToPlainText(templateHtml));
                const subject = `Your ${sectionName} parts for ${bandName} (${playgramName})`;
                $('#emailModal .modal-title').text('Send parts');
                $('#emailFormControlInput1').val('');
                $('#send_email_form').data('from-email', fromEmail);
                $('#send_email_form').data('subject', subject);
            },
            error: function(xhr, status, error) {
                alert('Error requesting new link: ' + error);
            }
        });
    });

    // Enable/disable Send button based on required fields
    function updateSendButtonState() {
        const email = $('#emailFormControlInput1').val();
        let message = '';
        if ($('#htmlTabPane').hasClass('show')) {
            message = $('#emailFormControlTextareaHtml').val();
        } else {
            message = $('#emailFormControlTextareaText').val();
        }
        const isValid = email.length > 0 && message.length > 0;
        $('#send_email').prop('disabled', !isValid);
    }

    $('#emailFormControlInput1, #emailFormControlTextareaHtml, #emailFormControlTextareaText').on('input', updateSendButtonState);

    $('#emailModal').on('shown.bs.modal', function() {
        updateSendButtonState();
    });

    // Email form submission handler
    $('#send_email_form').on('submit', function(e) {
        e.preventDefault();
        const email = $('#emailFormControlInput1').val();
        let message = '';
        let isHtml = 1;
        if ($('#htmlTabPane').hasClass('show')) {
            message = $('#emailFormControlTextareaHtml').val();
            isHtml = 1;
        } else {
            message = $('#emailFormControlTextareaText').val();
            isHtml = 0;
        }
        const from = $(this).data('from-email') || '';
        const subject = $(this).data('subject') || 'Parts Delivery';
        if (!email || !message) {
            alert('Please enter both email and message.');
            return;
        }
        $('#send_email').prop('disabled', true).val('Sending...');
        $.ajax({
            url: 'index.php?action=sound',
            method: 'POST',
            data: {
                email: email,
                message: message,
                from: from,
                subject: subject,
                is_html: isHtml
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Get zip token from zipData if available
                    let token = '';
                    if (zipData && zipData.token) {
                        token = zipData.token;
                    }
                    if (!token) {
                        alert('Error: No download token available to update.');
                        return;
                    }
                    console.log('Email sent. Updating token email for token: ...', token.substring(0, 6) );
                    // Update token email in database
                    $.ajax({
                        url: 'index.php?action=fetch_playgram_distribution',
                        method: 'POST',
                        data: {
                            action: 'update_token_email',
                            token: token,
                            email: email
                        },
                        dataType: 'json',
                        success: function(resp) {
                            if (!resp.success) {
                                alert('Warning: Could not update token email: ' + resp.message);
                            }
                        }
                    });
                    alert('Email sent successfully!');
                    $('#emailModal').modal('hide');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error sending email: ' + error);
            },
            complete: function() {
                $('#send_email').prop('disabled', false).val('Send');
            }
        });
    });
});
</script>

</body>
</html>
