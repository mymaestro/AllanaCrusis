<?php
// part_delivery.php

define('PAGE_TITLE', 'Part Delivery for Concert Series');
define('PAGE_NAME', 'Part Delivery');
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}

// Check if user has permission (librarian or admin)
if (!$u_librarian && !$u_admin) {
    header("Location: index.php");
    exit();
}

require_once(__DIR__. "/includes/header.php");
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");
ferror_log("Running part_delivery.php");

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

<!-- email templatey stuff here -->



<!-- Enhanced Modal with HTML support -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Email Template</h5>
                <div class="btn-group" role="group" aria-label="View toggle">
                    <input type="radio" class="btn-check" name="viewMode" id="htmlView" autocomplete="off" checked>
                    <label class="btn btn-outline-primary btn-sm" for="htmlView">HTML</label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="previewView" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="previewView">Preview</label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="plainView" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="plainView">Plain Text</label>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="emailSubject" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('emailSubject')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <!-- HTML Code View -->
                <div class="mb-3" id="htmlCodeSection">
                    <label class="form-label fw-bold">HTML Code:</label>
                    <div class="position-relative">
                        <textarea class="form-control font-monospace" id="emailBodyHtml" rows="12" readonly></textarea>
                        <button class="btn btn-outline-secondary position-absolute top-0 end-0 m-2" 
                                onclick="copyToClipboard('emailBodyHtml')" style="z-index: 10;">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Preview Section -->
                <div class="mb-3" id="previewSection" style="display: none;">
                    <label class="form-label fw-bold">Preview:</label>
                    <div class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                        <div id="emailPreview"></div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyRenderedHtml()">
                        <i class="fas fa-copy me-1"></i>Copy Rendered HTML
                    </button>
                </div>
                
                <!-- Plain Text Section -->
                <div class="mb-3" id="plainTextSection" style="display: none;">
                    <label class="form-label fw-bold">Plain Text Version:</label>
                    <div class="position-relative">
                        <textarea class="form-control" id="emailBodyPlain" rows="10" readonly></textarea>
                        <button class="btn btn-outline-secondary position-absolute top-0 end-0 m-2" 
                                onclick="copyToClipboard('emailBodyPlain')" style="z-index: 10;">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-3" id="recipientSection" style="display: none;">
                    <label class="form-label fw-bold">Recipients:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="emailRecipients" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('emailRecipients')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="copyCurrentView()">
                    <i class="fas fa-copy me-2"></i>Copy Current View
                </button>
                <button type="button" class="btn btn-primary" onclick="openEmailClient()">
                    <i class="fas fa-envelope me-2"></i>Open Email Client
                </button>
                <button type="button" class="btn btn-info" onclick="downloadAsFile()">
                    <i class="fas fa-download me-2"></i>Download HTML
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast for notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="copyToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Content copied to clipboard!
        </div>
    </div>
</div>



<?php require_once(__DIR__."/includes/footer.php"); ?>

<script>
    var ORGNAME = "<?php echo ORGNAME ?>";
    var ORGMAIL = "<?php echo ORGMAIL ?>";
    var ORGLOGO = "<?php echo ORGLOGO ?>";
    // Show/hide help instructions

// Enhanced email templates with HTML content
const emailTemplates = {
    musicParts: {
        subject: "[ORGNAME] - [Section] parts for [Song/Performance] Available",
        htmlBody: `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Parts Download</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            max-width: 600px; 
            margin: 0 auto; 
            background: #f5f5f5;
        }
        .email-container { 
            background: white; 
            margin: 20px auto; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
            position: relative;
        }
        .header::before {
            content: '🎵';
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 300;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .greeting {
            padding: 25px 30px 15px;
            font-size: 18px;
            color: #444;
        }
        .main-content { 
            padding: 0 30px 20px; 
        }
        .download-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);
            border: 2px solid #667eea;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .instrument-badge {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .download-message {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .download-button { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
            color: white; 
            padding: 16px 32px; 
            text-decoration: none; 
            border-radius: 8px; 
            display: inline-block; 
            margin: 15px 0; 
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }
        .download-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .security-icon {
            color: #f39c12;
            margin-right: 8px;
        }
        .details-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .details-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px 20px;
            margin-top: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #667eea;
        }
        .detail-value {
            color: #444;
        }
        .instructions {
            background: #e8f5e8;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .instructions h4 {
            color: #155724;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .instructions ol {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
            color: #155724;
        }
        .support-section {
            background: #f1f3f4;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .footer { 
            background: #2c3e50; 
            color: #ecf0f1; 
            padding: 25px; 
            text-align: center; 
            font-size: 14px;
        }
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .token-display {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            color: #666;
        }
        .warning-text {
            color: #dc3545;
            font-weight: bold;
        }
        .expires-badge {
            background: #ffc107;
            color: #856404;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        @media (max-width: 600px) { 
            .email-container { 
                margin: 10px; 
                border-radius: 8px;
            }
            .greeting, .main-content { 
                padding-left: 20px; 
                padding-right: 20px; 
            }
            .download-section {
                padding: 20px 15px;
            }
            .download-button { 
                display: block; 
                text-align: center;
                padding: 14px 20px;
                font-size: 16px;
            }
            .details-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .detail-label {
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>[ORGNAME]</h1>
            <p>Music parts distribution</p>
        </div>
        
        <div class="greeting">
            <p>Hello <strong>[Musician name]</strong>,</p>
        </div>
        
        <div class="main-content">
            <div class="download-section">
                <div class="instrument-badge">[Section] parts</div>
                <div class="download-message">
                    You can now download <strong>[Section]</strong> parts by using the secure link below.
                </div>
                <a href="[SECURE_DOWNLOAD_LINK_WITH_TOKEN]" class="download-button">
                    🎼 Download parts
                </a>
                <div class="expires-badge">⏰ Link expires in 2 days</div>
            </div>
            
            <div class="security-notice">
                <span class="security-icon">🔒</span>
                <strong>Security Notice:</strong> This is a personalized, secure download link that's unique to you. Please do not share this link with others.
            </div>
            
            <div class="instructions">
                <h4>📝 Download instructions</h4>
                <ol>
                    <li>Click the "Download parts" button above</li>
                    <li>You'll be redirected to a secure download page</li>
                    <li>Your parts will download as a ZIP file</li>
                    <li>Save the file to your device or cloud storage</li>
                    <li>Extract the ZIP file to access your parts</li>
                    <li>Print your parts if you prefer hard copies</li>
                </ol>
            </div>
            
            <div class="token-display">
                <strong>Your Access Token:</strong><br>
                [UNIQUE_ACCESS_TOKEN_HERE]
                <br><small>Keep this token secure - it's required for download verification</small>
            </div>
            
            <div class="support-section">
                <h4>🎯 Need Help?</h4>
                <p>If you experience any issues downloading your parts or have questions about the music:</p>
                <p>
                    📧 Email: <a href="mailto:ORGMAIL">ORGMAIL</a><br>
                </p>
            </div>
            
            <div class="security-notice">
                <span class="security-icon">⚠️</span>
                <strong class="warning-text">Important:</strong> This download link will expire on [Expiration Date] at [Time]. Please download your parts promptly. If you need a new link after expiration, contact [Contact Person].
            </div>
        </div>
        
        <div class="footer">
            <p><strong>[ORGNAME]</strong><br>
            Music Director: <a href="mailto:[DIRECTOR_EMAIL]">[Director Name]</a><br>
            <a href="[BAND_WEBSITE]">[Band Website]</a> | <a href="tel:[BAND_PHONE]">[Band Phone]</a></p>
            
            <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                This email was sent to [Musician Email] because you are a member of [Band Name].<br>
                Please keep your contact information up to date with our music librarian.
            </p>
        </div>
    </div>
</body>
</html>`,
        plainBody: `[BAND NAME] - MUSIC PARTS AVAILABLE
=====================================

Hello [Musician Name],

You can now download your [INSTRUMENT] parts by using the secure link below.

DOWNLOAD LINK: [SECURE_DOWNLOAD_LINK_WITH_TOKEN]

SECURITY NOTICE: This is a personalized, secure download link that's unique to you. Please do not share this link with others.

DOWNLOAD INSTRUCTIONS:
1. Click the download link above
2. You'll be redirected to a secure download page
3. Your parts will download as a ZIP file that contains all individual parts as PDF.
4. Save the file to your device or cloud storage
5. Print your parts if you prefer hard copies


YOUR ACCESS TOKEN: [UNIQUE_ACCESS_TOKEN_HERE]
Keep this token secure - it's required for download verification.

NEED HELP?
If you experience any issues downloading your parts or have questions about the music:
- Email: [ORGMAIL]

IMPORTANT: This download link will expire on [Expiration Date] at [Time]. Please download your parts promptly. If you need a new link after expiration, contact [Contact Person].

[ORGNAME]
Website: [ORGHOME]

This email was sent to [Musician Email] because you are a member of [ORGNAME].
Please keep your contact information up to date with our music librarian.`,
        recipients: ""
    }
};

let currentTemplate = null;

// Show email info modal with HTML content
function showEmailInfo(templateType) {
    currentTemplate = emailTemplates[templateType];
    
    if (!currentTemplate) {
        console.error('Template not found:', templateType);
        return;
    }

    // Populate modal content
    $('#emailSubject').val(currentTemplate.subject);
    $('#emailBodyHtml').val(currentTemplate.htmlBody);
    $('#emailBodyPlain').val(currentTemplate.plainBody);
    $('#emailPreview').html(currentTemplate.htmlBody);
    
    // Show/hide recipients section
    if (currentTemplate.recipients) {
        $('#emailRecipients').val(currentTemplate.recipients);
        $('#recipientSection').show();
    } else {
        $('#recipientSection').hide();
    }
    
    // Update modal title
    $('#emailModalLabel').text(`${templateType.charAt(0).toUpperCase() + templateType.slice(1)} Email Template`);
    
    // Reset to HTML view
    $('#htmlView').prop('checked', true);
    showSection('html');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('emailModal'));
    modal.show();
}

// Handle view mode changes
$(document).on('change', 'input[name="viewMode"]', function() {
    const viewMode = $(this).attr('id').replace('View', '');
    showSection(viewMode);
});

// Show different sections based on view mode
function showSection(mode) {
    // Hide all sections
    $('#htmlCodeSection, #previewSection, #plainTextSection').hide();
    
    // Show selected section
    switch(mode) {
        case 'html':
            $('#htmlCodeSection').show();
            break;
        case 'preview':
            $('#previewSection').show();
            break;
        case 'plain':
            $('#plainTextSection').show();
            break;
    }
}

// Copy specific field to clipboard
async function copyToClipboard(elementId) {
    try {
        const element = document.getElementById(elementId);
        const text = element.value;
        
        await navigator.clipboard.writeText(text);
        showToast('Content copied to clipboard!');
    } catch (err) {
        // Fallback for older browsers
        const element = document.getElementById(elementId);
        element.select();
        element.setSelectionRange(0, 99999);
        document.execCommand('copy');
        showToast('Content copied to clipboard!');
    }
}

// Copy rendered HTML from preview
async function copyRenderedHtml() {
    try {
        const htmlContent = $('#emailPreview').html();
        await navigator.clipboard.writeText(htmlContent);
        showToast('Rendered HTML copied to clipboard!');
    } catch (err) {
        showToast('Failed to copy content');
    }
}

// Copy current view content
async function copyCurrentView() {
    const checkedView = $('input[name="viewMode"]:checked').attr('id');
    let content = '';
    let label = '';
    
    switch(checkedView) {
        case 'htmlView':
            content = $('#emailBodyHtml').val();
            label = 'HTML code';
            break;
        case 'previewView':
            content = $('#emailPreview').html();
            label = 'Rendered HTML';
            break;
        case 'plainView':
            content = $('#emailBodyPlain').val();
            label = 'Plain text';
            break;
    }
    
    try {
        await navigator.clipboard.writeText(content);
        showToast(`${label} copied to clipboard!`);
    } catch (err) {
        showToast('Failed to copy content');
    }
}

// Open email client (works better with plain text for compatibility)
function openEmailClient() {
    const subject = encodeURIComponent($('#emailSubject').val());
    const body = encodeURIComponent($('#emailBodyPlain').val()); // Use plain text for better compatibility
    const recipients = $('#emailRecipients').val();
    
    const mailtoLink = `mailto:${recipients}?subject=${subject}&body=${body}`;
    window.location.href = mailtoLink;
}

// Download HTML as file
function downloadAsFile() {
    if (!currentTemplate) return;
    
    const htmlContent = currentTemplate.htmlBody;
    const blob = new Blob([htmlContent], { type: 'text/html' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `email-template-${Date.now()}.html`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('HTML file downloaded!');
}

// Show toast notification
function showToast(message) {
    $('.toast-body').text(message);
    const toast = new bootstrap.Toast(document.getElementById('copyToast'));
    toast.show();
}

// Convert HTML to plain text (utility function)
function htmlToPlainText(html) {
    const temp = document.createElement('div');
    temp.innerHTML = html;
    return temp.textContent || temp.innerText || '';
}

// Initialize
$(document).ready(function() {
    // Show HTML section by default
    showSection('html');
});


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
            html += `<option value="${zip.zip_filename}" data-id_playgram="${zip.id_playgram || ''}" data-id_section="${zip.id_section || ''}">${zip.zip_filename} (expires: ${zip.latest_expiration})</option>`;
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
        const $selected = $('#zipSelect option:selected');
        const zipFilename = $selected.val();
        const idPlaygram = $selected.data('id_playgram');
        const idSection = $selected.data('id_section');
        if (!zipFilename) return;
        // AJAX to create a new token for this ZIP (now handled by fetch_distribution_zips.php)
        $.post('index.php?action=fetch_distribution_zips', {
            zip_filename: zipFilename,
            id_playgram: idPlaygram,
            id_section: idSection
        }, function(data) {
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
            $('#emailModal').modal('show');
            showEmailInfo('musicParts')
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
