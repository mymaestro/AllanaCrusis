<?php
define('PAGE_TITLE', 'Concerts');
define('PAGE_NAME', 'Concerts');
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
  require_once(__DIR__ . "/../config/config.php");
  require_once(__DIR__ . "/includes/functions.php");
  require_once(__DIR__. "/includes/navbar.php");
  ferror_log("RUNNING concerts.php");
?>
<main role="main">
    <div class="container">
        <button type="button" class="btn btn-warning btn-floating btn-lg" id="btn-back-to-top">
            <i class="fas fa-arrow-up"></i>
        </button>
        <div class="row pb-1 pt-5 border-bottom"><h1><?php echo ORGNAME . ' '. PAGE_TITLE ?></h1></div>
        <div class="row pt-3 justify-content-end">
            <div class="col-auto">
                <input type="text" class="tablesearch-input" data-tablesearch-table="#concert_table" placeholder="Search">
            </div>
            <div class="col-auto">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" id="view" class="btn btn-secondary view_data" disabled>Details</button>
<?php if($u_librarian) : ?>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add"  class="btn btn-warning">Add</button>
<?php endif; ?>
            </div>
        </div><!-- right button -->
        <div id="concert_table">
        <?php
        echo '
            <div class="panel panel-default">
               <div class="table-responsive scrolling-data">
                    <table class="table table-hover tablesort tablesearch-table" id="concert_table">
                    <caption class="title">Concerts</caption>
                    <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th data-tablesort-type="date">Performance date <i class="fa fa-sort" aria-hidden="true"></i></th>
                        <th data-tablesort-type="string">Venue <i class="fa fa-sort" aria-hidden="true"></i></th>
                        <th data-tablesort-type="string">Playgram <i class="fa fa-sort" aria-hidden="true"></i></th>
                        <th data-tablesort-type="string">Conductor <i class="fa fa-sort" aria-hidden="true"></i></th>
                        <th data-tablesort-type="string">Notes <i class="fa fa-sort" aria-hidden="true"></i></th>
                    </tr>
                    </thead>
                    <tbody>';
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        //$sql = "SELECT * FROM concerts ORDER BY performance_date DESC;";
            $sql = 'SELECT 
                COUNT(pi.id_playgram_item) AS playgram_item_count,
                c.*
            FROM concerts c
            LEFT JOIN playgram_items pi ON c.id_playgram = pi.id_playgram
            GROUP BY c.id_concert
            ORDER BY c.performance_date DESC;';
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        while ($rowList = mysqli_fetch_array($res)) {
            $id_concert = $rowList['id_concert'];
            $id_playgram = $rowList['id_playgram'];
            $playgram_count = $rowList['playgram_item_count'];
            $performance_date = $rowList['performance_date'];
            $venue = $rowList['venue'];
            $conductor = $rowList['conductor'];
            $notes = $rowList['notes'];
            echo '<tr data-id="'.$id_concert.'" >
                        <td><input type="radio" name="record_select" value="'.$id_concert.'" class="form-check-input select-radio"></td>
                        <td class="tablesearch-source">'.$performance_date.'</td>
                        <td class="tablesearch-source">'.$venue.'</td>
                        <td class="tablesearch-source"><a href="#" class="view_playgram_data" name="view" id="'.$id_playgram.'">'.$playgram_count.' items</a></td>
                        <td class="tablesearch-source">'.$conductor.'</td>
                        <td class="tablesearch-source">'.$notes.'</td>
                  </tr>';
        }
        echo '
                    </tbody>
                    </table>
                </div><!-- table-responsive -->
            </div><!-- class panel -->
           ';
        ferror_log("Returned ". mysqli_num_rows($res)." concerts.");
        mysqli_close($f_link);
        // ferror_log("returned: " . $sql);
        ?>
        </div><!-- concert_table -->
        <div id="dataModal" class="modal"><!-- view data -->
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Concert playgram details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="playgram_detail">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- dataModal -->
        <div id="deleteModal" class="modal" tabindex="-1" role="dialog"><!-- delete data -->
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-body p-4 text-center">
                        <h5 class="mb-0">Delete concert <span id="concert2delete">#</span>?</h5>
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
        <div id="editModal" class="modal" tabindex="-1" aria-hidden="true"><!-- add/edit data -->
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Concert information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                    <div class="container-fluid">
                        <form method="post" id="insert_form">
                            <input type="hidden" name="id_concert" id="id_concert" />
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="conductor" class="col-form-label">Conductor*</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="conductor" name="conductor" placeholder="Baton Rough" required/>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="venue" class="col-form-label">Concert venue*</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="venue" name="venue" placeholder="Big Recital Hall" required/>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="performance_date" class="col-form-label">Performance date*</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="date" class="form-control" id="performance_date" name="performance_date" placeholder="2030-12-25" required />
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-12">
                                    <label for="notes" class="col-form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-4">
                                    <label for="id_playgram" class="col-form-label">Playgram (program playlist)</label>
                                </div>
                                <div class="col-md-8">
                                    <label for="link" class="col-form-label">Playgram, program playlist</label>
                                    <select class="form-select form-control" aria-label="Select playgram" id="id_playgram" name="id_playgram">
                                    </select>
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
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php"); ?>
<script src="js/auto-tables.js"></script>
<script>
    // Load playgrams into a JSON array for frequent use
<?php
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT `id_playgram`, `name`, `description` FROM playgrams WHERE `enabled` = 1 ORDER BY name;";
ferror_log("Running " . $sql);
echo "// -----" . PHP_EOL;
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
$playgramArray = array();
while($rowList = mysqli_fetch_array($res)) {
    $id_playgram = $rowList['id_playgram'];
    $playgram_name = $rowList['name'];
    $playgram_description = $rowList['description'];
    $playgram_display = $playgram_name . " - " . $playgram_description;
    $playgramArray[] = array(
        'id_playgram' => $id_playgram,
        'name' => $playgram_display
    );
}
mysqli_close($f_link);
echo "var playgramData = " . json_encode($playgramArray) . ";" . PHP_EOL;
?>
// jQuery functions
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

    let id_concert = null; // Which concert the user clicks
    loadAllTableText();

    // When user clicks add button
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
        $.each(playgramData, function(key, value) {
            $('#id_playgram').append($('<option>', {
                value: value.id_playgram,
                text: value.name
            }));
        });
    });

    // Enable the edit and delete buttons, and get the playgram ID when a table row is clicked
    $(document).on('click', '#concert_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete').prop('disabled',false);
        id_concert = $(this).data('id'); // data-id attribute
    });

    $(document).on('click', '.edit_data', function() {
        $.ajax({
            url: "index.php?action=fetch_concerts",
            method: "POST",
            data:{ id_concert : id_concert },
            dataType: "text",
            success:function(result) {
                const concert = JSON.parse(result);          
                $('#id_concert').val(concert.id_concert);
                $('#performance_date').val(concert.performance_date);
                $('#venue').val(concert.venue);
                $('#conductor').val(concert.conductor);
                $('#notes').val(concert.notes);
                // Populate the select options
                var selectedPlaygram = concert.id_playgram;
                var $select = $("#id_playgram").empty();
                playgramData.forEach(function(opt) {
                    var $option = $('<option></option>')
                    .val(opt.id_playgram)
                    .text(opt.name);
                    
                    if(opt.id_playgram === selectedPlaygram) {
                        $option.prop('selected',true);
                    }
                    $select.append($option);
                });

                $('#insert').val("Update"); // Set button name to Update
                $('#update').val("update"); // Set control to 'update'
            }
        });
    });
    $('#insert_form').on("submit", function(event){
        event.preventDefault();
        if ($('#id_concert') === undefined || $('#id_concert').length === 0) {
            alert("No concert!");
        }
        // $('#id_playgram option').prop('selected',true);
        if($('#id_playgram').val() == "")
        {
            alert("Program playlist name is required");
        } else {
            $.ajax({
                url:"index.php?action=insert_concerts",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#concert_table').html(data);
                }
            });
        }
    });
    $(document).on('click', '.delete_data', function() { // button that brings up delete modal
        if(id_concert !== null) {
        $('#confirm-delete').data('id', id_concert); // Set the id for delete function
        $('#concert2delete').text(id_concert); // Update ID in the modal
        }
    });
    $('#confirm-delete').click(function(){ // The confirm delete button
        $.ajax({
            url:"index.php?action=delete_records",
            method:"POST",
            data:{
                table_name: "concerts",
                table_key_name: "id_concert",
                table_key: id_concert
            },
            success:function(response){
                $('#insert_form')[0].reset();
                if (response.success) {
                    $('#concert_table').html('<p><a href="#" onclick="window.location.reload(true)">Return</a></p><p class="text-success">Record ' + response.message + ' deleted from concerts</p>');
                } else {
                    $('#concert_table').html('<p class="text-danger">Error: <emp>' + response.error + '</emp></p>');                
                }
            },
            error:function(xhr, status, error){
                alert("Unexpected XHR error " + error);
            }
        });
    });
    $(document).on('click', '.view_playgram_data', function(){
        var id_playgram = $(this).attr("id");
        if(id_playgram !== null) {
            $.ajax({
                url:"index.php?action=select_playgrams",
                method:"POST",
                data:{id_playgram:id_playgram},
                success:function(data){
                    $('#playgram_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        }
    });
    $(document).on('click', '.view_data', function(){
        // clicked_id not needed here, we already have id_concert
        // and the playgram ID from the table row
        if(id_concert !== null) {
            $.ajax({
                url:"index.php?action=select_concerts",
                method:"POST",
                data:{id_concert:id_concert},
                success:function(data){
                    $('#playgram_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        }
    });
});

</script>
</body>

</html>