<?php
  define('PAGE_TITLE', 'Library Users');
  define('PAGE_NAME', 'Users');
  $u_admin = FALSE;
  $u_librarian = FALSE;
  $u_user = FALSE;
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
  }
  // Only allow admin to access this page
    if (!$u_admin) {
        header("Location: index.php");
        exit();
    }
  require_once(__DIR__. "/includes/header.php");
  require_once(__DIR__ . "/includes/config.php");
  require_once(__DIR__. "/includes/navbar.php");
  require_once(__DIR__ . "/includes/functions.php");
  ferror_log("RUNNING users.php");
?>
<main role="main">
   <div class="container">
        <div class="row pb-3 pt-5 border-bottom"><h1><?php echo ORGNAME . ' '. PAGE_TITLE ?></h1></div>
<?php if($u_admin) : ?>
        <div class="row pt-3 justify-content-end">
            <div class="col-auto">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" id="view" class="btn btn-secondary view_data" disabled>Details</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add"  class="btn btn-warning">Add</button>
            </div>
        </div><!-- right buttons -->
        <div id="user_table">
        <?php
        echo '            <div class="panel panel-default">
        <div class="table-responsive scrolling-data">
          <table class="table table-hover" id="users_table">
                    <caption class="title">Available users</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>ID</th>
                        <th>User name</th>
                        <th>Real name</th>
                        <th>e-mail address</th>
                        <th>User</th>
                        <th>Librarian</th>
                        <th>Admin</th>
                    </tr>
                    </thead>
                    <tbody>';
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sql = "SELECT * FROM users ORDER BY id_users;";
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            $id_user = $rowList['id_users'];
            $username = htmlspecialchars($rowList['username']);
            $name = htmlspecialchars($rowList['name']);
            $address = htmlspecialchars($rowList['address']);
            $roles = htmlspecialchars($rowList['roles']);
            $isAdmin = (strpos($roles, 'administrator') !== FALSE ? "Yes" : "No");
            $isLibrarian = (strpos($roles, 'librarian') !== FALSE ? "Yes" : "No");
            $isUser = (strpos($roles, 'user') !== FALSE ? "Yes" : "No");
            echo '<tr data-id="'. $id_user .'">
                        <td><input type="radio" name="user_select" value="'.$id_user.'" class="form-check-input select-radio"></td>
                        <td>'.$id_user.'<input type="hidden" name="id_user[]" value="'. $id_user .'"></td>
                        <td><strong><a href="#" class="view_data" name="view" data-id="'.$id_user.'">'.$username.'</a></strong></td>
                        <td>'.$name.'</td>
                        <td>'.$address.'</td>
                        <td>'.$isUser.'</td>
                        <td>'.$isLibrarian.'</td>
                        <td>'.$isAdmin.'</td>
                    </tr>
                    ';
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
                    <h3 class="modal-title">User details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!-- modal-header -->
                <div class="modal-body" id="user_detail">
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
                    <h5 class="mb-0">Delete user <span id="user2delete">#</span>?</h5>
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
                    <h4 class="modal-title">User information</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div><!-- modal-header -->
                <div class="modal-body">
                  <div class="container-fluid">
                    <form method="post" id="insert_form">
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label for="id_users" class="col-form-label">ID*</label>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" id="id_users" name="id_users" placeholder="999" min="1" max="999" required/>
                                <input type="hidden" id="id_users_hold" name="id_users_hold" value="" />
                            </div>
                        </div>
                        <div class="row bg-white">
                            <div class="col-md-3">
                                <label for="username" class="col-form-label">User name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="username" name="username" placeholder="bigdog" required/>
                            </div>
                        </div>
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label for="name" class="col-form-label">Real name*</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="First Last" required/>
                            </div>
                        </div>
                        <div class="row bg-white">
                            <div class="col-md-3">
                                <label for="address" class="col-form-label">e-mail address*</label>
                            </div>
                            <div class="col-md-2">
                                <input type="email" class="form-control" id="address" name="address" placeholder="musician@example.com" required/>
                            </div>
                        </div>
                        <div class="row bg-light">
                            <div class="col-md-3">
                                <label class="col-form-label">Roles</label>
                            </div>
                            <div class="col-md-7">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="role_user" name="role_user" value="user">
                                    <label class="form-check-label" for="role_user">User</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="role_librarian" name="role_librarian" value="librarian">
                                    <label class="form-check-label" for="role_librarian">Librarian</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="role_administrator" name="role_administrator" value="administrator">
                                    <label class="form-check-label" for="role_administrator">Administrator</label>
                                </div>
                                <!-- Hidden field to store the final roles string -->
                                <input type="hidden" id="roles" name="roles" value="">
                                <small class="form-text text-muted">Select one or more roles for this user</small>
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
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                You do not have permission to view this page.
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
<?php if($u_admin) : ?>
<!-- jquery function to add/update database records -->
<script>
$(document).ready(function(){
    let id_user = 0; // Initialize user ID variable

    // Function to convert roles string to checkboxes
    function setRoleCheckboxes(rolesString) {
        // Clear all checkboxes first
        $('#role_user, #role_librarian, #role_administrator').prop('checked', false);
        
        if (rolesString) {
            // Check boxes based on roles string
            if (rolesString.includes('user')) {
                $('#role_user').prop('checked', true);
            }
            if (rolesString.includes('librarian')) {
                $('#role_librarian').prop('checked', true);
            }
            if (rolesString.includes('administrator')) {
                $('#role_administrator').prop('checked', true);
            }
        }
    }

    // Function to convert checkboxes to roles string
    function getRolesString() {
        var roles = [];
        if ($('#role_user').is(':checked')) {
            roles.push('user');
        }
        if ($('#role_librarian').is(':checked')) {
            roles.push('librarian');
        }
        if ($('#role_administrator').is(':checked')) {
            roles.push('administrator');
        }
        return roles.join(' ');
    }

    // Update hidden roles field when checkboxes change
    $(document).on('change', '#role_user, #role_librarian, #role_administrator', function() {
        $('#roles').val(getRolesString());
    });

    // Enable the edit and delete buttons, and get the user ID when a table row is clicked
    $(document).on('click', '#users_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_user = $(this).data('id'); // data-id attribute
        console.log("Selected user ID: " + id_user); // Debugging log
    });

    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
        // Clear role checkboxes and hidden field
        setRoleCheckboxes('');
        $('#roles').val('');
    });
    $(document).on('click', '.edit_data', function(){
        // Not needed, id_user is already set from the row click
        // var id_users = $(this).attr("id");
        $.ajax({
            url:"index.php?action=fetch_users",
            method:"POST",
            data:{id_users:id_user},
            dataType:"json",
            success:function(data){
                $('#id_users').val(data.id_users);
                $('#id_users_hold').val(data.id_users);
                $('#username').val(data.username);
                $('#name').val(data.name);
                $('#address').val(data.address);
                
                // Set role checkboxes based on the roles string
                setRoleCheckboxes(data.roles || '');
                $('#roles').val(data.roles || '');
                
                $('#insert').val("Update");
                $('#update').val("update");
                $('#editModal').modal('show');
            }
        });
    });
    $(document).on('click', '.delete_data', function(){ // button that brings up modal
        // input button name="delete" id="id_users" class="delete_data"
        // Not needed, id_user is already set from the row click
        // var id_users = $(this).attr("id");
        console.log("Delete user ID: " + id_user);
        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', id_user);
        $('#user2delete').text(id_user);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        // Not needed, id_user is already set from the row click
        // var id_user = $(this).data('id');
        $.ajax({
            url:"index.php?action=delete_records", 
            method:"POST",
            data:{
                table_name: "users",
                table_key_name: "id_users",
                table_key: id_user
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#message_detail').html('<p class="text-success">User ' + response.message + ' deleted from recordings</p>');
                    $('#messageModal').modal('show');
                 } else {
                    $('#message_detail').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');
                    $('#messageModal').modal('show');
                }
            },
            error:function(xhr, status, error){
                alert("Unexpected XHR error " + error);
            }
        });
        $('#messageModal').on('hidden.bs.modal', function () {
            // Reload the page after the modal is closed
            location.reload(); // Reload the page to reflect changes
        });
    });
    $('#insert_form').on("submit", function(event){
        event.preventDefault();
        
        // Update the hidden roles field before submission
        $('#roles').val(getRolesString());
        
        if($('#title').val() == "")
        {
            alert("Title is required");
        }
        else if($('#id_users').val() == '')
        {
            alert("user ID is required");
        }
        else
        {
            $.ajax({
                url:"index.php?action=insert_users",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    setRoleCheckboxes(''); // Clear checkboxes
                    $('#editModal').modal('hide');
                    $('#user_table').html(data);
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
                $('#view, #edit, #delete').prop('disabled', false);
                id_user = clicked_id; // Update the global variable
            }
        }
        
        // If still no ID and we have a globally selected row, use that
        if (!clicked_id && id_user) {
            clicked_id = id_user;
        }
        
        if (clicked_id) {
            $.ajax({
                url:"index.php?action=select_users",
                method:"POST",
                data:{id_users:clicked_id},
                success:function(data){
                    $('#user_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        }
    });
});
</script>

<!-- Custom CSS for role checkboxes -->
<style>
.form-check-inline {
    margin-right: 1.5rem;
    margin-bottom: 0.5rem;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-label {
    font-weight: 500;
    cursor: pointer;
}

.form-check-input:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Add some visual hierarchy to role checkboxes */
#role_administrator:checked + label {
    color: #dc3545; /* Red for admin */
}

#role_librarian:checked + label {
    color: #fd7e14; /* Orange for librarian */
}

#role_user:checked + label {
    color: #198754; /* Green for user */
}

/* Styles for disabled checkboxes in view modal */
#view_role_administrator:checked + label {
    color: #dc3545; /* Red for admin */
    font-weight: 600;
}

#view_role_librarian:checked + label {
    color: #fd7e14; /* Orange for librarian */
    font-weight: 600;
}

#view_role_user:checked + label {
    color: #198754; /* Green for user */
    font-weight: 600;
}

.form-check-input:disabled {
    opacity: 0.7;
}

.form-check-input:disabled:checked {
    background-color: #6c757d;
    border-color: #6c757d;
}

/* Keep color coding even when disabled */
#view_role_administrator:disabled:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

#view_role_librarian:disabled:checked {
    background-color: #fd7e14;
    border-color: #fd7e14;
}

#view_role_user:disabled:checked {
    background-color: #198754;
    border-color: #198754;
}
</style>
<?php endif; ?>
</body>
</html>
