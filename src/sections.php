<?php
  define('PAGE_TITLE', 'Sections');
  define('PAGE_NAME', 'Sections');
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
  ferror_log("RUNNING sections.php");
?>
<main role="main">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom"><h1><?php echo ORGNAME . ' '. PAGE_TITLE ?></h1></div>
        <div class="row pt-3 justify-content-end">
            <div class="col-auto">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" id="view" class="btn btn-secondary view_data" disabled>Details</button>
<?php if($u_librarian) : ?>
                <button type="button" data-bs-toggle="modal" data-bs-target="#assignModal" id="assign" class="btn btn-info assign_sections">Assign part types to section</button>    
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add"  class="btn btn-warning">Add</button>
<?php endif; ?>
            </div>
        </div><!-- right button -->
<!-- visible if u_user or u_librarian or u_admin -->
<?php if($u_user || $u_librarian || $u_admin) : ?>
    <div id="section_table">
        <?php
        echo '
            <div class="panel panel-default">
               <div class="table-responsive scrolling-data">
                    <table class="table table-hover">
                    <caption class="title">Available sections</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Section leader</th>
                        <th>Part types</th>
                        <th>Enabled</th>
                    </tr>
                    </thead>
                    <tbody>';
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sql = "SELECT s.*, u.name AS leader_name, COUNT(spt.id_part_type) AS parttype_count
        FROM sections s
        LEFT JOIN users u ON s.section_leader = u.id_users
        LEFT JOIN section_part_types spt ON s.id_section = spt.id_section
        GROUP BY s.id_section
        ORDER BY s.name;";

        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            $id_section = $rowList['id_section'];
            $name = $rowList['name'];
            $description = $rowList['description'];
            $section_leader = $rowList['leader_name']; // Updated to use the joined user name
            $enabled = $rowList['enabled'];
            echo '<tr data-id="'.$id_section.'">
                        <td><input type="radio" name="record_select" value="'.$id_section.'" class="form-check-input select-radio"></td>
                        <td><strong><a href="#" class="view_data" data-id="'.$id_section.'">'.$name.'</a></strong></td>
                        <td>'.htmlspecialchars($description ?? '').'</td>
                        <td>'.htmlspecialchars($section_leader ?? '').'</td>
                        <td>'.intval($rowList['parttype_count']).'</td>
                        <td>' . (($enabled == 1) ? "Yes" : "No") .'</td>
                        </tr>';
        }
        echo '
                    </tbody>
                    </table>
                </div><!-- table-responsive -->
            </div><!-- class panel -->
           ';
        ferror_log("Returned ". mysqli_num_rows($res)." sections.");
        mysqli_close($f_link);
        // ferror_log("returned: " . $sql);
        ?>
    </div><!-- container -->
    <div id="dataModal" class="modal"><!-- view data -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Section details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!-- modal-header -->
                <div class="modal-body" id="section_detail">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div><!-- modal-footer -->
            </div><!-- modal-content -->
        </div><!-- modal-dialog -->
    </div><!-- dataModal -->
    <div id="deleteModal" class="modal" tabindex="-1" role="dialog"><!-- delete data -->
        <div class="modal-dialog" role="document">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-body p-4 text-center">
                    <h5 class="mb-0">Delete this section <span id="section2delete">#</span>?</h5>
                    <div class="modal-body text-start">
                        <p>You can cancel now.</p>
                    </div>
                </div>
                <div class="modal-footer flex-nowrap p-0">
                    <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0 border-right" id="confirm-delete" data-bs-dismiss="modal"><strong>Yes, delete</strong></button>
                    <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0" data-bs-dismiss="modal">No thanks</button>
                </div><!-- modal-footer -->
            </div><!-- modal-content -->
        </div><!-- modal-dialog -->
    </div><!-- deleteModal -->
    <div id="editModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true"><!-- edit data -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Section information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div><!-- modal-header -->
                <div class="modal-body">
                  <div class="container-fluid">
                    <form method="post" id="insert_form">
                        <input type="hidden" id="id_section" name="id_section"/>
                        <div class="row bg-light form-floating">
                            <div class="col-md-3">
                                <label for="name" class="col-form-label">Section name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="A cross section" required minlength="4" maxlength="255"/>
                            </div>
                        </div>
                        <div class="row form-floating">
                            <select class='form-select form-control' aria-label='Select section leader' id='section_leader' name='section_leader'>
                                <option value=''>Select section leader</option>
                            </select>
                            <label for="section_leader" class="col-form-label">Section Leader*</label>
                        </div><!-- row -->
                        <div class="row bg-white">
                            <div class="col-md-12">
                                <label for="description" class="col-form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" maxlength="1024"></textarea>
                                <br />
                                <div class="form-check">
                                    <label for="enabled" class="form-check-label">Enabled</label>
                                    <input class="form-check-input" id="enabled" name="enabled" type="checkbox" value="1"></>
                                </div>
                            </div>
                        </div>
                  </div><!-- container-fluid -->
                </div><!-- modal-body -->
                <div class="modal-footer">  
                        <input type="hidden" name="update" id="update" value="0" />
                        <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" />
                    </form>  
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                </div><!-- modal-footer -->
            </div><!-- modal-content -->
        </div><!-- modal-dialog -->
    </div><!-- editModal -->
    <!-- Assignment Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Assign part types to section</h5>
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
                                <label>Available Part Types</label>
                                <select multiple class="form-control" id="availablePartTypes" size="10"></select>
                            </div>
                            <div class="col-1 d-flex flex-column justify-content-center align-items-center">
                                <button type="button" id="addPartType" class="btn btn-outline-primary mb-2">&gt;&gt;</button>
                                <button type="button" id="removePartType" class="btn btn-outline-secondary">&lt;&lt;</button>
                            </div>
                            <div class="col">
                                <label>Assigned to Section</label>
                                <select multiple class="form-control" id="assignedPartTypes" name="assigned_part_types[]" size="10"></select>
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
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                You do not have permission to view this page.
            </div>
        </div>
    </div>
<?php endif; ?>
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>


<!-- Reference users data -->
<?php
// Build the reference users as a JSON array for JavaScript
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT `id_users`, `name`, `username` FROM users ORDER BY name ASC;";
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
$users = [];
while ($rowList = mysqli_fetch_assoc($res)) {
    $users[] = [
        'id_users' => $rowList['id_users'],
        'name' => $rowList['name'],
        'username' => $rowList['username']
    ];
}
mysqli_close($f_link);
?>
<!-- Use window.users in JavaScript to reference users data -->
<script>
    window.users = <?php echo json_encode($users, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<!-- jquery function to populate section leader dropdown -->
<script>
$(document).ready(function(){
    let sectionLeaderSelect = $('#section_leader');
    sectionLeaderSelect.empty();
    sectionLeaderSelect.append('<option value="">Select section leader</option>');
    $.each(window.users, function(index, user){
        sectionLeaderSelect.append('<option value="' + user.id_users + '">' + user.name + ' (' + user.username + ')</option>');
    });
});
</script>

<!-- jquery function to add/update database records -->
<script>
$(document).ready(function(){

    let id_section = null;

    // When user clicks add button
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });

    // Enable the edit and delete buttons, and get the paper size ID when a table row is clicked
    $(document).on('click', '#section_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_section = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function(){
        $.ajax({
            url:"includes/fetch_sections.php",
            method:"POST",
            data:{id_section:id_section},
            dataType:"json",
            success:function(data){
                $('#id_section').val(data.id_section);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#section_leader').val(data.section_leader);
                if ((data.enabled) == 1) {
                    $('#enabled').prop('checked',true);
                }
                $('#insert').val("Update");
                $('#update').val("update");
                $('#editModal').modal('show');
            }
        });
    });
    $(document).on('click', '.delete_data', function(){ // button that brings up modal
        // input button name="delete" id="id_section" class="delete_data"
        if(id_section !== null) {
            $('#confirm-delete').data('id', id_section);
            $('#section2delete').text(id_section);
        }
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        $.ajax({
            url:"includes/delete_records.php",
            method:"POST",
            data:{
                table_name: "sections",
                table_key_name: "id_section",
                table_key: id_section
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#section_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from sections</p>');
                } else {
                    $('#section_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
                }
            },
            error:function(xhr, status, error){
                alert("Unexpected XHR error " + error);
            }
        });
    });
    $('#insert_form').on("submit", function(event){
        event.preventDefault();
        if($('#name').val() == "")
        {
            alert("Name is required");
        }
        else
        {
            $.ajax({
                url:"includes/insert_sections.php",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#section_table').html(data);
                }
            });
        }
    });
    $(document).on('click', '.view_data', function(e){
        e.preventDefault(); // Prevent default link behavior
        
        // Get ID from the clicked element's data attribute first
        let clicked_id = $(this).data('id');
        
        // If no data-id on the clicked element, try to get from the closest row
        if (!clicked_id) {
            let $row = $(this).closest('tr');
            clicked_id = $row.data('id');
            
            // Also select the radio button in that row if found
            if (clicked_id) {
                $row.find('input[type="radio"]').prop('checked', true);
                $('#view, #edit, #delete, #sort').prop('disabled', false);
                id_section = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_section) {
            clicked_id = id_section;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"includes/select_sections.php",
                method:"POST",
                data:{id_section:clicked_id},
                success:function(data){
                    $('#section_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        }
    });

    let allPartTypes = [];

    // 1. Load sections and part types when modal opens
    $('#assignModal').on('show.bs.modal', function () {
        // Load sections
        $.getJSON('includes/fetch_sections_list.php', function(sections) {
            let $sectionSelect = $('#sectionSelect');
            $sectionSelect.empty().append('<option value="">Choose section...</option>');
            $.each(sections, function(i, section) {
                $sectionSelect.append('<option value="' + section.id_section + '">' + section.name + '</option>');
            });
            setTimeout(function() { $sectionSelect.val(id_section).trigger('change'); }, 200)
        });
        // Load all part types
        $.getJSON('includes/fetch_parttypes_list.php', function(parttypes) {
            allPartTypes = parttypes;
            $('#availablePartTypes').empty();
            $('#assignedPartTypes').empty();
        });
    });

    // 2. When a section is selected, load assigned part types
    $('#sectionSelect').on('change', function() {
        let sectionId = $(this).val();
        if (!sectionId) {
            $('#availablePartTypes').empty();
            $('#assignedPartTypes').empty();
            return;
        }
        // Get assigned part types for this section
        $.post('includes/fetch_section_parttypes.php', {section_id: sectionId}, function(assigned) {
            // assigned is an array of id_part_type
            let assignedSet = new Set(assigned);
            let $available = $('#availablePartTypes').empty();
            let $assigned = $('#assignedPartTypes').empty();
            $.each(allPartTypes, function(i, pt) {
                let option = $('<option>').val(pt.id_part_type).text(pt.name);
                if (assignedSet.has(pt.id_part_type)) {
                    $assigned.append(option);
                } else {
                    $available.append(option);
                }
            });
        }, 'json');
    });

    // 3. Move part types between lists
    $('#addPartType').on('click', function() {
        $('#availablePartTypes option:selected').each(function() {
            $('#assignedPartTypes').append($(this));
        });
    });
    $('#removePartType').on('click', function() {
        $('#assignedPartTypes option:selected').each(function() {
            $('#availablePartTypes').append($(this));
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
        $('#assignedPartTypes option').each(function() {
            assigned.push($(this).val());
        });
        $.post('includes/insert_section_parttypes.php', {
            section_id: sectionId,
            assigned_part_types: assigned
        }, function(response) {
            if (response.success) {
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
