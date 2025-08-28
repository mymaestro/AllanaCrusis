<?php
define('PAGE_TITLE', 'Assign instruments to sections');
define('PAGE_NAME', 'PartSections');
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
ferror_log("RUNNING partsections.php");
?>
<main role="main">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <h1><?php echo ORGNAME . ' ' . PAGE_TITLE ?></h1>
        </div>
<?php if($u_librarian) : ?>
        <!-- Button to open the assignment modal -->
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#assignModal">
            Assign instruments to sections
        </button>
<?php else: ?>
    <div id="instrumentation_view">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    You do not have permission to view this page.
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
        <!-- Assignment Modal -->
        <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalLabel">Assign Instruments to Section</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="assignForm">
                            <div class="mb-3">
                                <label for="sectionSelect" class="form-label">Select Section</label>
                                <select class="form-select" id="sectionSelect" name="section_id">
                                    <!-- Populate with PHP or JS -->
                                    <option value="">Choose section...</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label>Available Instruments</label>
                                    <select multiple class="form-control" id="availableInstruments" size="10"></select>
                                </div>
                                <div class="col-1 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" id="addInstrument" class="btn btn-outline-primary mb-2">&gt;&gt;</button>
                                    <button type="button" id="removeInstrument" class="btn btn-outline-secondary">&lt;&lt;</button>
                                </div>
                                <div class="col">
                                    <label>Assigned to Section</label>
                                    <select multiple class="form-control" id="assignedInstruments" name="assigned_instruments[]" size="10"></select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" id="saveAssignments">Save Assignments</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once(__DIR__. "/includes/footer.php"); ?>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js" integrity="sha256-eTyxS0rkjpLEo16uXTS0uVCS4815lc40K2iVpWDvdSY=" crossorigin="anonymous"></script>

<!-- jquery function to add/update database records -->
<script>
$(document).ready(function() {

    let allInstruments = [];

    // 1. Load sections and instruments when modal opens
    $('#assignModal').on('show.bs.modal', function () {
        // Load sections
        $.getJSON('index.php?action=fetch_sections_list', function(sections) {
            let $sectionSelect = $('#sectionSelect');
            $sectionSelect.empty().append('<option value="">Choose section...</option>');
            $.each(sections, function(i, section) {
                $sectionSelect.append('<option value="' + section.id_section + '">' + section.name + '</option>');
            });
        });
        // Load all instruments
        $.getJSON('index.php?action=fetch_instruments_list', function(instruments) {
            allInstruments = instruments;
            $('#availableInstruments').empty();
            $('#assignedInstruments').empty();
        });
    });

    // 2. When a section is selected, load assigned instruments
    $('#sectionSelect').on('change', function() {
        let sectionId = $(this).val();
        if (!sectionId) {
            $('#availableInstruments').empty();
            $('#assignedInstruments').empty();
            return;
        }
        // Get assigned instruments for this section
        $.post('index.php?action=fetch_section_instruments', {section_id: sectionId}, function(assigned) {
            // assigned is an array of id_instrument
            let assignedSet = new Set(assigned);
            let $available = $('#availableInstruments').empty();
            let $assigned = $('#assignedInstruments').empty();
            $.each(allInstruments, function(i, inst) {
                let option = $('<option>').val(inst.id_instrument).text(inst.name);
                if (assignedSet.has(inst.id_instrument)) {
                    $assigned.append(option);
                } else {
                    $available.append(option);
                }
            });
        }, 'json');
    });

    // 3. Move instruments between lists
    $('#addInstrument').on('click', function() {
        $('#availableInstruments option:selected').each(function() {
            $('#assignedInstruments').append($(this));
        });
    });
    $('#removeInstrument').on('click', function() {
        $('#assignedInstruments option:selected').each(function() {
            $('#availableInstruments').append($(this));
        });
    });

    // 4. Save assignments
    $('#saveAssignments').on('click', function() {
        let sectionId = $('#sectionSelect').val();
        if (!sectionId) {
            alert('Please select a section.');
            return;
        }
        let assigned = [];
        $('#assignedInstruments option').each(function() {
            assigned.push($(this).val());
        });
        $.post('index.php?action=insert_section_instruments', {
            section_id: sectionId,
            assigned_instruments: assigned
        }, function(response) {
            if (response.success) {
                alert('Assignments saved!');
                $('#assignModal').modal('hide');
            } else {
                alert('Error saving assignments.');
            }
        }, 'json');
    });
});
</script>
</body>

</html>