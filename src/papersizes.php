<?php
  define('PAGE_TITLE', 'Music paper sizes');
  define('PAGE_NAME', 'PaperSizes');
  require_once("includes/header.php");
  $u_admin = FALSE;
  $u_librarian = FALSE;
  $u_user = FALSE;
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
  }
  require_once('includes/config.php');
  require_once("includes/navbar.php");
  require_once('includes/functions.php');
  ferror_log("Running papersizes.php");
?>
<main role="main">
    <div class="container">
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
        <div id="paper_size_table">
        <?php
        echo '
            <div class="panel panel-default">
               <div class="table-responsive scrolling-data">
                    <table class="table table-hover">
                    <caption class="title">Available paper sizes</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Vertical dimension</th>
                        <th>Horizontal dimension</th>
                        <th>Enabled</th>
                    </tr>
                    </thead>
                    <tbody>';
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sql = "SELECT * FROM paper_sizes ORDER BY name;";
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            $id_paper_size = $rowList['id_paper_size'];
            $name = $rowList['name'];
            $description = $rowList['description'];
            $vertical = $rowList['vertical'];
            $horizontal = $rowList['horizontal'];
            $enabled = $rowList['enabled'];
            echo '<tr data-id="'.$id_paper_size.'">
                        <td><input type="radio" name="record_select" value="'.$id_paper_size.'" class="form-check-input select-radio"></td>
                        <td>'.$id_paper_size.'</td>
                        <td><strong><a href="#" class="view_data" data-id="'.$id_paper_size.'">'.$name.'</a></strong></td>
                        <td>'.$description.'</td>
                        <td>'.$vertical.'</td>
                        <td>'.$horizontal.'</td>
                        <td>' . (($enabled == 1) ? "Yes" : "No") .'</td>
                        </tr>';
        }
        echo '
                    </tbody>
                    </table>
                </div><!-- table-responsive -->
            </div><!-- class panel -->
           ';
        mysqli_close($f_link);
        // ferror_log("returned: " . $sql);
        ?>
    </div><!-- container -->
    <div id="dataModal" class="modal"><!-- view data -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Paper Size Details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!-- modal-header -->
                <div class="modal-body" id="paper_size_detail">
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
                    <h5 class="mb-0">Delete this paper size <span id="papersize2delete">#</span>?</h5>
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
                    <h4 class="modal-title">Paper size information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div><!-- modal-header -->
                <div class="modal-body">
                  <div class="container-fluid">
                    <form method="post" id="insert_form">
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label for="id_paper_size" class="col-form-label">ID*</label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="id_paper_size" name="id_paper_size" placeholder="X" required minlength="1" maxlength="4" size="4"/>
                                <input type="hidden" id="id_paper_size_hold" name="id_paper_size_hold" value=""/>
                            </div>
                        </div><hr />
                        <div class="row">
                            <div class="col-md-3">
                                <label for="name" class="col-form-label">Paper size name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Big" required minlength="4" maxlength="255"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="horizontal" class="col-form-label">Horizontal size, inches*</label>
                            </div>
                            <div class="col-md-7"><!-- DECIMAL(7,2) -->
                                <input type="text" class="form-control" id="horizontal" name="horizontal" placeholder="77.7" required minlength="1" maxlength="9"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="vertical" class="col-form-label">Vertical size, inches*</label>
                            </div>
                            <div class="col-md-7"><!-- DECIMAL(7,2)) -->
                                <input type="text" class="form-control" id="vertical" name="vertical" placeholder="89.5" required minlength="1" maxlength="9"/>
                            </div>
                        </div>                        <hr />
                        <div class="row bg-white">
                            <div class="col-md-12">
                                <label for="description" class="col-form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" maxlength="255"></textarea>
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
</main>
<?php require_once("includes/footer.php");?>
<!-- jquery function to add/update database records -->
<script>
$(document).ready(function(){

    let id_paper_size = null;

    // When user clicks add button
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });

    // Enable the edit and delete buttons, and get the paper size ID when a table row is clicked
    $(document).on('click', '#paper_size_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_paper_size = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function(){
        $.ajax({
            url:"includes/fetch_papersizes.php",
            method:"POST",
            data:{id_paper_size:id_paper_size},
            dataType:"json",
            success:function(data){
                $('#id_paper_size').val(data.id_paper_size);
                $('#id_paper_size_hold').val(data.id_paper_size);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#vertical').val(data.vertical);
                $('#horizontal').val(data.horizontal);
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
        if(id_paper_size !== null) {
            $('#confirm-delete').data('id', id_paper_size);
            $('#papersize2delete').text(id_paper_size);
        }
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        $.ajax({
            url:"includes/delete_records.php",
            method:"POST",
            data:{
                table_name: "paper_sizes",
                table_key_name: "id_paper_size",
                table_key: id_paper_size
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#paper_size_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from paper sizes</p>');
                } else {
                    $('#paper_size_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
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
                url:"includes/insert_papersizes.php",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#paper_size_table').html(data);
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
                id_paper_size = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_paper_size) {
            clicked_id = id_paper_size;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"includes/select_papersizes.php",
                method:"POST",
                data:{id_paper_size:clicked_id},
                success:function(data){
                    $('#paper_size_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        } else {
            alert("No paper size selected. Please select a paper size first.");
        }
    });
});
</script>
</body>
</html>
