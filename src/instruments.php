<?php
  define('PAGE_TITLE', 'Instruments');
  define('PAGE_NAME', 'Instruments');
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
  require_once(__DIR__ . "/includes/functions.php");
  require_once(__DIR__. "/includes/navbar.php");
  ferror_log("RUNNING instruments.php");
?>
<main role="main" class="container">
    <div class="container">
        <button type="button" class="btn btn-warning btn-floating btn-lg" id="btn-back-to-top">
            <i class="fas fa-arrow-up"></i>
        </button>
        <div class="row pb-1 pt-5 border-bottom"><h1><?php echo ORGNAME . ' ' . PAGE_TITLE ?></h1></div>
        <div class="row pt-3 justify-content-end">
            <div class="col-auto">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" id="view" class="btn btn-secondary view_data" disabled>Details</button>
<?php if($u_librarian) : ?>
                <a href="instrumentsorderlist.php" class="btn btn-info" role="button" name="sort" id="sort">Set score order</a>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add" class="btn btn-warning">Add</button>
<?php endif; ?>
            </div>
        </div><!-- right button -->
        <div id="instrument_table">
            Loading instruments...
        </div><!-- instrument_table -->
        <div id="dataModal" class="modal"><!-- view data -->
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Instrument details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="instrument_detail">
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
                        <h5 class="mb-0">Delete this instrument?</h5>
                        <p id="instrument2delete">You can cancel now.</p>
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
                        <h4 class="modal-title">Instrument information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                    <div class="container-fluid">
                        <form method="post" id="insert_form">
                            <input type="hidden" name="id_instrument" id="id_instrument" />
                            <div class="row bg-light">
                                <div class="col-md-3">
                                    <label for="collation" class="col-form-label">Sorting order*</label>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="collation" name="collation" placeholder="999" required/>
                                </div>
                            </div><hr />
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="name" class="col-form-label">Instrument name*</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Vuvuzela 4" required/>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="family" class="col-form-label">Instrument family</label>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Woodwind">Woodwind </label>
                                        <input type="radio" class="form-check-input" id="Woodwind" name="family" value="Woodwind">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Brass">Brass </label>
                                        <input type="radio" class="form-check-input" id="Brass" name="family" value="Brass">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Percussion">Percussion </label>
                                        <input type="radio" class="form-check-input" id="Percussion" name="family" value="Percussion">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Strings">Strings </label>
                                        <input type="radio" class="form-check-input" id="Strings" name="family" value="Strings">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Voice">Voice </label>
                                        <input type="radio" class="form-check-input" id="Voice" name="family" value="Voice">
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label" for="Other">Other </label>
                                        <input type="radio" class="form-check-input" id="Other" name="family" value="Other">
                                    </div>
                                </div>
                            </div><hr />
                            <div class="row bg-white">
                                <div class="col-md-12">
                                    <label for="description" class="col-form-label">Description (up to 2048 characters)</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" maxlength="2048"></textarea>
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
                            <input type="hidden" name="result-message" id="result-message" value="Nothing to report" class="btn" />
                            <input type="hidden" name="update" id="update" value="0" />
                            <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" />
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                        </form>  
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- editModal -->
        <div id="messageModal" class="modal"><!-- message feedback -->
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Message</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="message_detail">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- messageModal -->
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
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
        url:"includes/fetch_instruments.php",
        method:"POST",
        data:{
            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
        },   
        success:function(data){
            $('#instrument_table').html(data);
        }
    });

    let id_instrument = null; // Variable to hold the ID of the instrument being edited or deleted

    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });

    // Enable the edit and delete buttons, and get the instrument ID when a table row is clicked
    $(document).on('click', '#instrument_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_instrument = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function(){
        // var id_instrument = $(this).attr("id");
        // Load instruments table
        $.ajax({
            url:"includes/fetch_instruments.php",
            method:"POST",
            data:{id_instrument:id_instrument},
            dataType:"json",
            success:function(data){
                $('#id_instrument').val(data.id_instrument);
                $('#collation').val(data.collation);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#' + data.family).prop('checked', true);
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
        // input button name="delete" id="id_instrument" class="delete_data"
        // var id_instrument = $(this).attr("id");
        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', id_instrument);
        $('#instrument2delete').text(id_instrument);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        // var id_instrument = $(this).data('id');
        $.ajax({
            url:"includes/delete_records.php",
            method:"POST",
            data:{
                table_name: "instruments",
                table_key_name: "id_instrument",
                table_key: id_instrument
            },
            success:function(response){
                if (response.success) {
                    $('#message_detail').html('<p class="text-success">Record ' + response.message + ' deleted from instruments</p>');
                    $('#messageModal').modal('show');
                    $.ajax({
                        url:"includes/fetch_instruments.php",
                        method:"POST",
                        data:{
                            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
                        },   
                        success:function(data){
                            $('#instrument_table').html(data);
                        }
                    })
                } else {
                    $('#message_detail').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
                    $('#messageModal').modal('show');
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
            alert("Instrument name is required");
        }
        else if($('#collation').val() == '')
        {
            alert("Sort order is required");
        }
        else
        {
            $.ajax({
                url:"includes/insert_instruments.php",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $.ajax({
                        url:"includes/fetch_instruments.php",
                        method:"POST",
                        data:{
                            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
                        },   
                        success:function(data){
                            $('#instrument_table').html(data);
                        }
                    });
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
                id_instrument = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_instrument) {
            clicked_id = id_instrument;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"includes/select_instruments.php",
                method:"POST",
                data:{id_instrument:clicked_id},
                success:function(data){
                    $('#instrument_detail').html(data);
                    $('#dataModal').modal('show');
                    id_instrument = null; // Reset the ID after viewing
                }
            });
        } else {
            alert("No instrument selected. Please select an instrument first.");
        }   
    });
});
</script>
</body>
</html>
