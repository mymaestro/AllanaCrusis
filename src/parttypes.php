<?php
  define('PAGE_TITLE', 'Part types');
  define('PAGE_NAME', 'PartTypes');
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
  ferror_log("RUNNING parttypes.php");
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
                        <!-- Part type to section assignment removed - sections now reference instruments directly -->
                <a href="/parttypesorderlist" class="btn btn-info" role="button" name="sort" id="sort">Set score order</a>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add"  class="btn btn-warning">Add</button>
<?php endif; ?>
            </div>
        </div><!-- right button -->
        <div id="part_type_table">
            <p class="text-center">Loading part types...</p>
        </div><!-- part_type_table -->
        <div id="dataModal" class="modal"><!-- view data -->
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Part type details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="part_type_detail">
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
                        <h5 class="mb-0">Delete part type <span id="part_type2delete">#</span>?</h5>
                        <div class="modal-body text-start" id="recording-delete_detail">
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
        <div id="editModal" class="modal"><!-- add/edit data -->
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Part type information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                    <div class="container-fluid">
                        <form method="post" id="insert_form">
                            <input type="hidden" name="id_part_type" id="id_part_type" />
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
                                    <label for="name" class="col-form-label">Part type name*</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Vuvuzela 4" required/>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="family" class="col-form-label">Part type family</label>
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
                                        <label class="form-check-label" for="Strings">Voice </label>
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
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-8">
                                    <label for="link" class="col-form-label">Default instrument</label>
                                    <select class="form-select form-control" aria-label="Select instrument" id="default_instrument" name="default_instrument">
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="is_part_collection" class="col-form-label">Additional instruments</label>
                                    <input type="number" class="form-control" id="is_part_collection" name="is_part_collection" placeholder="0"/>
                                </div>
                            </div>
                            <hr />
                            <div class="row bg-white">
                                <div class="col-md-12">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                        </form>  
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- editModal -->
        <!-- Assignment modal removed - sections now reference instruments directly -->
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
<script>
    // Load instruments data into a JSON array for frequent use
<?php
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT `id_instrument`, `collation`, `name` FROM instruments WHERE `enabled` = 1 ORDER BY collation;";
ferror_log("Running " . $sql);
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
$jsondata = "var instrumentdata = [";
while($rowList = mysqli_fetch_array($res)) {
    $id_instrument = $rowList['id_instrument'];
    $collation = $rowList['collation'];
    $instrument_name = $rowList['name'];
    $jsondata .= '{"collation":'.$collation.',"id":'.$id_instrument.',"name":"'.$instrument_name.'"},';
}
$jsondata = rtrim($jsondata, ',');
$jsondata .= ']'.PHP_EOL;
mysqli_close($f_link);
echo $jsondata;
ferror_log("returned: " . $sql);
?>
// jquery functions to add/update database records
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

    let id_part_type = null;

    $.ajax({
        url:"index.php?action=fetch_parttypes",
        method:"POST",
        data:{
            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
        },   
        success:function(data){
            $('#part_type_table').html(data);
            var selectitems = '';
            $.each(instrumentdata, function(key, value) {
                selectitems += '<option value=' + value.id + '>' + value.name + '</option>';
                $(".instrument_" + value.id).text(value.name);
            });
            $('#default_instrument').html(selectitems);
        }
    });
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });
    // Enable the edit and delete buttons, and get the playgram ID when a table row is clicked
    $(document).on('click', '#part_type_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#edit, #delete, #sort, #view').prop('disabled',false);
        id_part_type = $(this).data('id'); // data-id attribute
    });
    $(document).on('click', '.edit_data', function(){
        $.ajax({
            url:"index.php?action=fetch_parttypes",
            method:"POST",
            data:{id_part_type:id_part_type},
            dataType:"json",
            success:function(data){
                $('#id_part_type').val(data.id_part_type);
                $('#collation').val(data.collation);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#default_instrument').val(data.default_instrument);
                $('#' + data.family).prop('checked', true);
                $('#is_part_collection').val(data.is_part_collection);
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
        // input button name="delete" id="id_part_type" class="delete_data"
      //  var id_part_type = $(this).attr("id");
        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', id_part_type);
        $('#part_type2delete').text(id_part_type);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        var id_part_type = $(this).data('id');
        $.ajax({
            url:"index.php?action=delete_records",
            method:"POST",
            data:{
                table_name: "part_types",
                table_key_name: "id_part_type",
                table_key: id_part_type
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#part_type_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from part types</p>');
                } else {
                    $('#part_type_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');                
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
            alert("Part type name is required");
        }
        else if($('#collation').val() == '')
        {
            alert("Sort order is required");
        }
        else
        {
            $.ajax({
                url:"index.php?action=insert_parttypes",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $.ajax({
                        url:"index.php?action=fetch_parttypes",
                        method:"POST",
                        data:{
                            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
                        },
                        success:function(data){
                            $('#part_type_table').html(data);
                            $.each(instrumentdata, function(key, value) {
                                $(".instrument_" + value.id).text(value.name);
                            });
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
                id_part_type = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_part_type) {
            clicked_id = id_part_type;
        }
        
        if (clicked_id) {
        // Fetch part type details and show in modal
            $.ajax({
                url:"index.php?action=select_parttypes",
                method:"POST",
                data:{id_part_type:clicked_id},
                success:function(data){
                    $('#part_type_detail').html(data);
                    $('#dataModal').modal('show');
                    id_part_type = null; // Reset after viewing
                }
            });
        } else {
            alert("No part type selected. Please select a part type first.");
        }
    });

    // Section assignment functionality removed - sections now reference instruments directly
});
</script>
</body>
</html>
