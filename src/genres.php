<?php
  define('PAGE_TITLE', 'Musical genres');
  define('PAGE_NAME', 'Genres');
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
  ferror_log("RUNNING genres.php");
?>
<main role="main" class="container">
    <div class="container">
        <div class="row pb-1 pt-5 border-bottom"><h1><?php echo ORGNAME . ' ' . PAGE_TITLE ?></h1></div>
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
        <div id="genre_table">
        <?php
        echo '
            <div class="panel panel-default">
               <div class="table-responsive scrolling-data">
                    <table class="table table-hover">
                    <caption class="title">Available genres</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Enabled</th>
                    </tr>
                    </thead>
                    <tbody>';
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sql = "SELECT * FROM genres ORDER BY name;";
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            $id_genre = $rowList['id_genre'];
            $name = $rowList['name'];
            $description = $rowList['description'];
            $enabled = $rowList['enabled'];
            echo '<tr data-id="'.$id_genre.'">
                        <td><input type="radio" name="record_select" value="'.$id_genre.'" class="form-check-input select-radio"></td>
                        <td>'.$id_genre.'</td>
                        <td><strong><a href="#" class="view_data" data-id="'.$id_genre.'">'.$name.'</a></strong></td>
                        <td>'.$description.'</td>
                        <td>'. (($enabled == 1) ? "Yes" : "No") .'</td>
                    </tr>
                    ';
        }
        echo '
                    </tbody>
                    </table>
                </div><!-- table-responsive -->
            </div><!-- class panel -->
           ';
        ferror_log("Returned ". mysqli_num_rows($res)." genres.");
        mysqli_close($f_link);
        // ferror_log("returned: " . $sql);
        ?>
    </div><!-- container -->

    <div id="dataModal" class="modal"><!-- view data -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Genre details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!-- modal-header -->
                <div class="modal-body" id="genre_detail">
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
                    <h5 class="mb-0">Delete genre <span id="genre2delete">#</span>?</h5>
                    <p>You can cancel now.</p>
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
                    <h4 class="modal-title">Genre information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div><!-- modal-header -->
                <div class="modal-body">
                  <div class="container-fluid">
                    <form method="post" id="insert_form">
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label for="id_genre" class="col-form-label">ID*</label>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="id_genre" name="id_genre" placeholder="X" minlength="1" maxlength="4" size="4" required/>
                                <input type="hidden" id="id_genre_hold" name="id_genre_hold" value=""/>
                            </div>
                        </div><hr />
                        <div class="row bg-white">
                            <div class="col-md-3">
                                <label for="name" class="col-form-label">Genre name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Musical comedy" required minlength="3" maxlength="255"/>
                            </div>
                        </div><hr />
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
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });


    // Enable the edit and delete buttons, and get the genre ID when a table row is clicked
    $(document).on('click', '#genre_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_genre = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function(){
        $.ajax({
            url:"includes/fetch_genres.php",
            method:"POST",
            data:{id_genre:id_genre},
            dataType:"json",
            success:function(data){
                $('#id_genre').val(data.id_genre);
                $('#id_genre_hold').val(data.id_genre);
                $('#name').val(data.name);
                $('#description').val(data.description);
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
        if (id_genre !== null && id_genre !== undefined) {
            $('#confirm-delete').data('id', id_genre);
            $('#genre2delete').text(id_genre);
        }
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        $.ajax({
            url:"includes/delete_records.php",
            method:"POST",
            data:{
                table_name: "genres",
                table_key_name: "id_genre",
                table_key: id_genre
            },
            success:function(response){
                if (response.success) {
                    $('#insert_form')[0].reset();
                    $('#genre_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from genres</p>');
                } else {
                    $('#genre_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
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
            alert("Genre name is required");
        }
        else if($('#id_genre').val() == '')
        {
            alert("Genre ID is required");
        }
        else
        {
            $.ajax({
                url:"includes/insert_genres.php",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#genre_table').html(data);
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
                id_genre = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_genre) {
            clicked_id = id_genre;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"includes/select_genres.php",
                method:"POST",
                data:{id_genre:clicked_id},
                success:function(data){
                    $('#genre_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        } else {
            alert("No genre selected. Please select a genre first.");
        }
    });
});
</script>
</body>
</html>
