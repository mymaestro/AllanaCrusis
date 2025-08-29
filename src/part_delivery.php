<?php
// part_delivery.php

define('PAGE_TITLE', 'Part Delivery for Concert Series');
define('PAGE_NAME', 'Part Delivery');
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
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");
ferror_log("Running part_delivery.php");

// Check if user has permission
if (!$u_librarian && !$u_admin) {
    echo '<main role="main" class="container"><div class="alert alert-danger">Access denied.</div></main>';
    require_once(__DIR__. "/includes/footer.php");
    exit;
}

?>
<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><i class="fas fa-envelope"></i> <?php echo ORGNAME; ?> part delivery for Concert Series</h1>
                <p class="lead">Send a secure download link for a ZIP of parts to a recipient by e-mail.</p>
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
                                <li><strong>Select a distribution:</strong> Choose the zip file that contains the section and concert program you want to deliver.</li>
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

        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-archive"></i> Select a Distribution ZIP</h6>
                    </div>
                    <div class="card-body">
                        <div id="delivery_zip_select">
                            <!-- ZIP list will be loaded here -->
                        </div>
                        <div id="delivery_email_template" style="display:none;">
                            <!-- Email template will be shown here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal for Email Template (uses popup-modal-email.html style) -->
<div class="modal fade" id="emailTemplateModal" tabindex="-1" aria-labelledby="emailTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailTemplateModalLabel"><i class="fas fa-envelope"></i> Prepare E-mail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="recipientName" class="form-label">Recipient Name</label>
            <input type="text" class="form-control" id="recipientName" placeholder="e.g. John Smith">
          </div>
          <div class="mb-3">
            <label for="recipientEmail" class="form-label">Recipient E-mail</label>
            <input type="email" class="form-control" id="recipientEmail" placeholder="e.g. john@example.com">
          </div>
          <div class="mb-3">
            <label for="emailBody" class="form-label">E-mail Message</label>
            <textarea class="form-control" id="emailBody" rows="8"></textarea>
          </div>
        </form>
        <button type="button" class="btn btn-outline-primary" id="copyEmailBody"><i class="fas fa-copy"></i> Copy E-mail</button>
      </div>
    </div>
  </div>
</div>

<?php require_once(__DIR__."/includes/footer.php"); ?>

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
$(function() {
    // Load ZIP list from fetch_distribution_zips.php
    $.getJSON('index.php?action=fetch_distribution_zips', function(zips) {
        if (!zips.length) {
            $('#delivery_zip_select').html('<div class="alert alert-warning">No distribution ZIPs found.</div>');
            return;
        }
        let html = '<label for="zipSelect" class="form-label">Choose a ZIP file to deliver:</label>';
        html += '<select class="form-select mb-3" id="zipSelect"><option value="">-- Select ZIP --</option>';
        zips.forEach(function(zip) {
            html += `<option value="${zip.zip_filename}">${zip.zip_filename} (expires: ${zip.latest_expiration})</option>`;
        });
        html += '</select>';
        html += '<button type="button" class="btn btn-primary" id="prepareEmailBtn" disabled>Prepare E-mail Template</button>';
        $('#delivery_zip_select').html(html);
    });

    // Enable button when a ZIP is selected
    $(document).on('change', '#zipSelect', function() {
        $('#prepareEmailBtn').prop('disabled', !$(this).val());
    });

    // Prepare e-mail template when button is clicked
    $(document).on('click', '#prepareEmailBtn', function() {
        const zipFilename = $('#zipSelect').val();
        if (!zipFilename) return;
    // AJAX to create a new token for this ZIP (now handled by fetch_distribution_zips.php)
    $.post('index.php?action=fetch_distribution_zips', { zip_filename: zipFilename }, function(data) {
            if (!data.success) {
                alert('Error creating download token: ' + (data.message || 'Unknown error'));
                return;
            }
            const downloadLink = data.download_link;
            // Show modal with e-mail template
            const defaultBody = `Hello,\n\nYou can download your music parts using the secure link below.\n\nDownload: ${downloadLink}\n\nThis link is unique and will expire after it is used or after 2 days.\n\nBest regards,\n${ORGNAME} Library`;
            $('#emailBody').val(defaultBody);
            $('#recipientName').val('');
            $('#recipientEmail').val('');
            $('#emailTemplateModal').modal('show');
        }, 'json');
    });

    // Copy e-mail body to clipboard
    $('#copyEmailBody').on('click', function() {
        const $body = $('#emailBody');
        $body.select();
        document.execCommand('copy');
        $(this).text('Copied!');
        setTimeout(() => $(this).html('<i class="fas fa-copy"></i> Copy E-mail'), 1500);
    });
});
</script>

</body>
</html>
