<?php
  define('PAGE_TITLE', 'Music ensembles');
  define('PAGE_NAME', 'Ensembles');
  require_once(__DIR__. "/includes/header.php"); 
  $u_admin = FALSE;
  $u_librarian = FALSE;
  $u_user = FALSE;
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
  }
  require_once(__DIR__ . "/includes/config.php");
  require_once(__DIR__. "/includes/navbar.php");
  require_once(__DIR__ . "/includes/functions.php");
  ferror_log("Running ensembles.php");
?>
<main role="main">
  <div class="container">
        <button type="button" class="btn btn-warning btn-floating btn-lg" id="btn-back-to-top">
            <i class="fas fa-arrow-up"></i>
        </button>
        <div class="row pb-1 pt-5 border-bottom"><h1><?php echo ORGNAME . ' '. PAGE_TITLE ?></h1></div>
        <div class="row pt-3 justify-content-end">
            <div class="col-auto">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" id="view" class="btn btn-secondary view_data" disabled>Details</button>
<?php if($u_librarian) : ?>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add" class="btn btn-warning">Add</button>
<?php endif; ?>
            </div>
        </div><!-- right button -->
        <div id="ensemble_table">
            <p class="text-center">Loading ensembles...</p>
        </div><!-- ensemble_table -->
    <div id="dataModal" class="modal"><!-- view data -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Ensemble details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!-- modal-header -->
                <div class="modal-body" id="ensemble_detail">
                    <p class="text-center">Loading details...</p><!-- filled in by select_ensembles -->
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
                    <h5 class="mb-0">Delete this ensemble?</h5>
                    <p id="ensemble2delete">You can cancel now.</p>
                </div>
                <div class="modal-footer flex-nowrap p-0">
                    <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0 border-right" id="confirm-delete" data-bs-dismiss="modal"><strong>Yes, delete</strong></button>
                    <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0" data-bs-dismiss="modal">No thanks</button>
                </div><!-- modal-footer -->
            </div><!-- modal-content -->
        </div><!-- modal-dialog -->
    </div><!-- deleteModal -->
    <div id="editModal" class="modal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Ensemble information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div><!-- modal-header -->
                <div class="modal-body">
                  <div class="container-fluid">
                    <form method="post" id="insert_form">
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label for="id_ensemble" class="col-form-label">ID*</label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="id_ensemble" name="id_ensemble" placeholder="X" size="4" maxlength="4" required/>
                                <input type="hidden" id="id_ensemble_hold" name="id_ensemble_hold" value="" />
                            </div>
                        </div><hr />
                        <div class="row bg-white">
                            <div class="col-md-3">
                                <label for="name" class="col-form-label">Ensemble name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Vuvuzela choir" required/>
                            </div>
                        </div><hr />
                        <div class="row bg-white">
                            <div class="col-md-12">
                                <label for="description" class="col-form-label">Description (max. 512 characters)</label>
                                <textarea class="form-control" id="description" name="description" rows="3" maxlength="512"></textarea>
                                <br />
                                <label for="link" class="col-form-label">Link</label>
                                <input type="text" class="form-control" id="link" name="link">
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
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php"); ?>
<!-- jquery function to add/update database records -->
<script>
$(document).ready(function(){
    // Scroll-to-top button
    let $upButton = $("#btn-back-to-top");
    // When the user scrolls down 20px from the top of the document, show the button
    $(window).on("scroll", function () {
        if ($(document).scrollTop() > 20) {
            $upButton.show();
        } else {
            $upButton.hide();
        }
    });
    // When the user clicks the button, scroll to the top of the document
    $upButton.on("click", function () {
        $("html, body").animate({ scrollTop: 0 }, "fast");
    });

    $.ajax({
        url:"index.php?action=fetch_ensembles",
        method:"POST",
        data:{
            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
        },   
        success:function(data){
            $('#ensemble_table').html(data);
        }
    });

    let id_ensemble = null; // Variable to hold the current ensemble ID for editing

    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });

    // Enable the edit and delete buttons, and get the ensemble ID when a table row is clicked
    $(document).on('click', '#ensemble_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_ensemble = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function(){
        // var id_ensemble = $(this).attr("id");
        $.ajax({
            url:"index.php?action=fetch_ensembles",
            method:"POST",
            data:{id_ensemble:id_ensemble},
            dataType:"json",
            success:function(data){
                $('#id_ensemble').val(data.id_ensemble);
                $('#id_ensemble_hold').val(data.id_ensemble);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#link').val(data.link);
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
        $('#confirm-delete').data('id', id_ensemble);
        $('#ensemble2delete').text(id_ensemble);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        $.ajax({
            url:"index.php?action=delete_records",
            method:"POST",
            data:{
                table_name: "ensembles",
                table_key_name: "id_ensemble",
                table_key: id_ensemble
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#ensemble_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from ensembles</p>');
                } else {
                    $('#ensemble_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
                }
            },
            error:function(xhr, status, error){
                alert("Unexpected XHR error " + error);
            }
        });
    });
    $('#insert_form').on("submit", function(event){
        event.preventDefault();
        if($('#title').val() == "")
        {
            alert("Title is required");
        }
        else if($('#id_ensemble').val() == '')
        {
            alert("Ensemble ID is required");
        }
        else
        {
            $.ajax({
                url:"index.php?action=insert_ensembles",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#ensemble_table').html(data);
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
                id_ensemble = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_ensemble) {
            clicked_id = id_ensemble;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"index.php?action=select_ensembles",
                method:"POST",
                data:{id_ensemble:clicked_id},
                success:function(data){
                    $('#ensemble_detail').html(data);
                    $('#dataModal').modal('show');
                    id_ensemble = null; // Reset the ID after viewing
                }
            });
        }
    });
});
</script>
</body>
</html>