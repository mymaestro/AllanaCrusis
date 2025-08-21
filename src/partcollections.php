<?php
  define('PAGE_TITLE', 'Part collections');
  define('PAGE_NAME', 'Part collections');
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
  ferror_log("RUNNING partcollections.php");
?>
<main role="main">
    <div class="container">
    <h1><?php echo ORGNAME . ' ' . PAGE_TITLE ?></h1>
    
    <!-- Search/Filter Controls -->
    <div class="row mb-4">
        <div class="col-md-4">
            <label for="searchInput" class="form-label">Search compositions or instruments:</label>
            <input type="text" class="form-control" id="searchInput" placeholder="Type to filter results...">
        </div>
        <div class="col-md-2">
            <label for="instrumentFilter" class="form-label">Filter by instrument:</label>
            <select class="form-select" id="instrumentFilter">
                <option value="">All instruments</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="showSingleInstrument" checked>
                <label class="form-check-label" for="showSingleInstrument">
                    Include single-instrument collections
                </label>
                <small class="form-text text-muted d-block">Uncheck to show only true shared parts (2+ instruments)</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="d-flex align-items-end h-100">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="expandAll">Expand All</button>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="collapseAll">Collapse All</button>
            </div>
        </div>
    </div>
<?php if($u_librarian) : ?>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-tools"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" name="add" id="add" data-bs-toggle="modal" data-bs-target="#add_data_Modal" class="btn btn-warning btn-sm w-100">
                                <i class="fas fa-plus"></i> Add New Collection
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info btn-sm w-100" id="showEmptyParts">
                                <i class="fas fa-search"></i> Find Parts Without Collections
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-secondary btn-sm w-100" id="exportData">
                                <i class="fas fa-download"></i> Export Collection Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-secondary">
        <i class="fas fa-info-circle"></i> <strong>Note:</strong> This page shows instrument groupings for shared parts. 
        Contact a librarian to modify collections or add new instrument groupings.
    </div>
<?php endif; ?>
        <?php
        if(!empty($_POST)) {
            echo '<h4>You selected</h4>';
            echo '<table>';
            // loop over checked checkboxes
            foreach($_POST as $key => $value) {
                echo '<tr><td>'. $key . '</td>';
                echo "    <td>". $value . "</td></tr>";
            }
            if(!empty($_POST['parttypes'])){
                echo "<p>Part types selected:</p>";
                echo "<ol>";
                foreach($_POST['parttypes'] as $selected) {
                    echo "<li>" . $selected . "</li>";
                }
                echo "</ol>";
            }
            echo '</table>';
         }?>
        <div id="part_collection_table">
        <?php
        echo '<div class="alert alert-info">
            <strong>Part Collections Overview:</strong> This page shows individual instruments grouped together on shared parts. 
            Each part (like "Percussion 1" or "Horn 1 & 2") contains multiple instruments printed on the same music sheet.
            <br><small><strong>Note:</strong> By default, only parts with multiple instruments are shown, as these represent true "collections" or shared parts.</small>
        </div>';
        
        // Add summary statistics
        echo '<div class="row mb-3">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-chart-bar"></i> Collection Statistics</h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-primary" id="stat-compositions">0</div>
                                    <small class="text-muted">Compositions with Collections</small>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-success" id="stat-parts">0</div>
                                    <small class="text-muted">Parts with Collections</small>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-warning" id="stat-instruments">0</div>
                                    <small class="text-muted">Total Instrument Assignments</small>
                                </div>
                                <div class="col-md-3">
                                    <div class="fs-4 fw-bold text-info" id="stat-unique-instruments">0</div>
                                    <small class="text-muted">Unique Instruments Used</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>';
        
        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Get data grouped by composition and part, only showing parts with multiple instruments
        $sql = "SELECT k.catalog_number_key catalog_number,
                   c.name composition_title,
                   k.id_part_type_key part_type_key,
                   y.name part_type_name,
                   k.id_instrument_key instrument_key,
                   i.name instrument_name,
                   k.name collection_name,
                   k.description description
        FROM       part_collections k
        LEFT JOIN  compositions c ON c.catalog_number = k.catalog_number_key
        LEFT JOIN  part_types y ON y.id_part_type = k.id_part_type_key
        LEFT JOIN  instruments i ON i.id_instrument = k.id_instrument_key
        WHERE      (k.catalog_number_key, k.id_part_type_key) IN (
                       SELECT catalog_number_key, id_part_type_key 
                       FROM part_collections 
                       GROUP BY catalog_number_key, id_part_type_key 
                       HAVING COUNT(*) > 1
                   )
        ORDER BY   composition_title, y.collation, i.collation;";
        
        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
        ferror_log("Running SQL: ". $sql);
        
        // Group data by composition and part
        $grouped_data = [];
        while ($rowList = mysqli_fetch_array($res)) {
            $comp_key = $rowList['catalog_number'] . '|' . $rowList['composition_title'];
            $part_key = $rowList['part_type_key'] . '|' . $rowList['part_type_name'];
            
            if (!isset($grouped_data[$comp_key])) {
                $grouped_data[$comp_key] = [
                    'catalog_number' => $rowList['catalog_number'],
                    'composition_title' => $rowList['composition_title'],
                    'parts' => []
                ];
            }
            
            if (!isset($grouped_data[$comp_key]['parts'][$part_key])) {
                $grouped_data[$comp_key]['parts'][$part_key] = [
                    'part_type_key' => $rowList['part_type_key'],
                    'part_type_name' => $rowList['part_type_name'],
                    'collection_name' => $rowList['collection_name'],
                    'description' => $rowList['description'],
                    'instruments' => []
                ];
            }
            
            $grouped_data[$comp_key]['parts'][$part_key]['instruments'][] = [
                'instrument_key' => $rowList['instrument_key'],
                'instrument_name' => $rowList['instrument_name'],
                'catalog_number' => $rowList['catalog_number'],
                'part_type_key' => $rowList['part_type_key']
            ];
        }
        
        // Display grouped data
        if (empty($grouped_data)) {
            echo '<div class="alert alert-warning">No part collections found. Use the "Add" button to create instrument groupings for parts.</div>';
        } else {
            echo '<div class="accordion" id="partCollectionsAccordion">';
            
            $comp_index = 0;
            foreach ($grouped_data as $comp_data) {
                $comp_index++;
                $comp_id = 'comp' . $comp_index;
                
                echo '<div class="accordion-item">
                        <h2 class="accordion-header" id="heading' . $comp_id . '">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse' . $comp_id . '" aria-expanded="false" 
                                    aria-controls="collapse' . $comp_id . '">
                                <strong>' . htmlspecialchars($comp_data['catalog_number']) . '</strong> &nbsp;&nbsp;
                                ' . htmlspecialchars($comp_data['composition_title']) . '
                                <span class="badge bg-secondary ms-2">' . count($comp_data['parts']) . ' parts with collections</span>
                            </button>
                        </h2>
                        <div id="collapse' . $comp_id . '" class="accordion-collapse collapse" 
                             aria-labelledby="heading' . $comp_id . '" data-bs-parent="#partCollectionsAccordion">
                            <div class="accordion-body">';
                
                foreach ($comp_data['parts'] as $part_data) {
                    $instrument_count = count($part_data['instruments']);
                    echo '<div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <strong>' . htmlspecialchars($part_data['part_type_name']) . '</strong>';
                    
                    if (!empty($part_data['collection_name'])) {
                        echo ' <small class="text-muted">(' . htmlspecialchars($part_data['collection_name']) . ')</small>';
                    }
                    
                    echo ' <span class="badge bg-primary ms-2">' . $instrument_count . ' instruments</span>
                                </h5>';
                    
                    if (!empty($part_data['description'])) {
                        echo '<p class="text-muted mb-0"><small>' . htmlspecialchars($part_data['description']) . '</small></p>';
                    }
                    
                    echo '</div>
                            <div class="card-body">
                                <div class="row">';
                    
                    foreach ($part_data['instruments'] as $instrument) {
                        echo '<div class="col-md-4 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                    <span>' . htmlspecialchars($instrument['instrument_name']) . '</span>
                                    <div class="btn-group btn-group-sm" role="group">';
                        
                        if ($u_librarian) {
                            echo '<button type="button" class="btn btn-outline-danger btn-sm delete_data" 
                                         id="' . $instrument['catalog_number'] . ':' . $instrument['part_type_key'] . ':' . $instrument['instrument_key'] . '"
                                         title="Delete this instrument from the part">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                  <button type="button" class="btn btn-outline-primary btn-sm edit_data" 
                                         id="' . $instrument['catalog_number'] . ':' . $instrument['part_type_key'] . ':' . $instrument['instrument_key'] . '"
                                         title="Edit this instrument assignment">
                                    <i class="fas fa-edit"></i>
                                  </button>';
                        }
                        
                        echo '<button type="button" class="btn btn-outline-secondary btn-sm view_data" 
                                     id="' . $instrument['catalog_number'] . ':' . $instrument['part_type_key'] . ':' . $instrument['instrument_key'] . '"
                                     title="View details">
                                <i class="fas fa-eye"></i>
                              </button>
                                    </div>
                                </div>
                              </div>';
                    }
                    
                    echo '</div>
                            </div>
                          </div>';
                }
                
                echo '</div>
                        </div>
                      </div>';
            }
            
            echo '</div>'; // Close accordion
        }
        
        mysqli_close($f_link);
        ?>
        </div><!-- part_collection_table -->
        <div id="dataModal" class="modal"><!-- view data -->
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Part type collection details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="part_collection_detail">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- dataModal view data -->
        <div id="deleteModal" class="modal" tabindex="-1" role="dialog"><!-- delete data -->
            <div class="modal-dialog" role="document">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-body p-4 text-center">
                        <h5 class="mb-0">Delete this part collection?</h5>
                        <p id="part_collection2delete">You can cancel now.</p>
                    </div>
                    <div class="modal-footer flex-nowrap p-0">
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0 border-right" id="confirm-delete" data-bs-dismiss="modal"><strong>Yes, delete</strong></button>
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0" data-bs-dismiss="modal">No thanks</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- deleteModal -->
        <div id="add_data_Modal" class="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Part type collection information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                        <form action="includes/insert_partcollections.php" method="post" id="insert_form">
                            <input type="hidden" name="is_part_collection" id="is_part_collection" value="0" />
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="name" class="col-form-label">Part type collection unique name</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Percussion 44"/>
                                </div>
                            </div>
                            <div class="row bg-light">
                                <div class="col-md-12">
                                    <label for="description" class="col-form-label">Description</label><br />
                                    <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="id_part" class="col-form-label">Part (from parts table)*</label>
                                </div>
                                <div class="col-md-9">
                                    <!-- Read compositions, parts, part types from each table -->
                                    <?php
                                    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                    $sql = "SELECT  p.catalog_number,
                                                    c.name title,
                                                    p.id_part_type,
                                                    y.name part_type
                                            FROM    parts p
                                            JOIN    compositions c
                                            ON      p.catalog_number = c.catalog_number
                                            JOIN    part_types y
                                            ON      y.id_part_type = p.id_part_type
                                            ORDER BY catalog_number, y.collation;";
                                    ferror_log("Running " . $sql);
                                    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                    $opt = "<select class='form-select form-control' aria-label='Select parts' id='id_part' name='id_part'>\n";
                                    while ($rowList = mysqli_fetch_array($res)) {
                                        $catalog_number_key = $rowList['catalog_number'];
                                        $title = $rowList['title'];
                                        $id_part_type = $rowList['id_part_type'];
                                        $part_type = $rowList['part_type'];
                                        $opt .= "                                   <option value='" . $catalog_number_key . ":". $id_part_type . "'>" . $catalog_number_key .': '. $title . " - " . $part_type . "</option>\n";
                                    }
                                    $opt .= "                           </select>\n";
                                    mysqli_close($f_link);
                                    echo $opt;
                                    //error_log("returned: " . $sql);
                                    ?>
                                </div>
                            </div>
                            <div class="row bg-light">
                                <div class="col-md-3">
                                    <label for="link" class="col-form-label">Instruments that are in this collection*</label>
                                </div>
                                <div class="col-md-9">
                                    <!-- Get all the instruments from instruments table -->
                                    <?php
                                    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                    $sql = "SELECT id_instrument, name FROM instruments WHERE enabled = 1 ORDER BY collation;";
                                    //error_log("Running " . $sql);
                                    $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                    $opt = "<select class='form-select form-control' aria-label='Select part type' id='id_instrument_key' name='id_instrument_key[]' size='17' multiple>";
                                    while ($rowList = mysqli_fetch_array($res)) {
                                        $id_instrument = $rowList['id_instrument'];
                                        $instrument = $rowList['name'];
                                        $opt .= "                                   <option value='" . $id_instrument . "'>" . $instrument . "</option>\n";
                                    }
                                    $opt .= "                          </select>\n";
                                    mysqli_close($f_link);
                                    echo $opt;
                                    //error_log("returned: " . $sql);
                                    ?>
                                </div>
                            </div>
                        </div><!-- modal-body -->
                    <div class="modal-footer">  
                            <input type="hidden" name="update" id="update" value="0" />
                            <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" />
                        </form>  
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- add_data_modal -->
        <div id="edit_data_Modal" class="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit part type collection</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                        <form method="post" id="edit_form">
                            <input type="hidden" name="catalog_number_key_hold2" id="catalog_number_key_hold2" value="0" />
                            <input type="hidden" name="id_part_type_key_hold2" id="id_part_type_key_hold2" value="0" />
                            <input type="hidden" name="id_instrument_key_hold2" id="id_instrument_key_hold2" value="0" />
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="name2" class="col-form-label">Part type collection name</label>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="name2" name="name2" placeholder="Percussion 44"/>
                                </div>
                            </div>
                            <div class="row bg-light">
                                <div class="col-md-12">
                                    <label for="description2" class="col-form-label">Description</label><br />
                                    <textarea class="form-control" id="description2" name="description2" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="catalog_number_key2" class="col-form-label">Catalog number*</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="catalog_number_key2" name="catalog_number_key2" placeholder="C123" required/>
                                </div>
                            </div>
                            <div class="row bg-light">
                                <div class="col-md-3">
                                    <label for="id_part_type_key2" class="col-form-label">Part type of the collection</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="id_part_type_key2" name="id_part_type_key2" placeholder="1" required/>
                                </div>
                            </div>
                            <div class="row bg-white">
                                <div class="col-md-3">
                                    <label for="id_instrument_key2" class="col-form-label">This instrument in the collection</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="id_instrument_key2" name="id_instrument_key2" placeholder="1" required/>
                                </div>
                            </div>
                        </div><!-- modal-body -->
                    <div class="modal-footer">  
                            <input type="hidden" name="update2" id="update2" value="0" />
                            <input type="submit" name="insert2" id="insert2" value="Update" class="btn btn-success" />
                        </form>  
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>  
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- edit_data_modal -->
    </div><!-- container -->
</main>
<?php require_once("includes/footer.php");?>
<!-- jquery function to add/update database records -->
<script>
// Load instruments data into a JSON array for frequent use
<?php
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT `id_instrument`, `name` FROM instruments WHERE `enabled` = 1 ORDER BY collation;";
ferror_log("Running " . $sql);
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
$jsondata = "var instrumentdata = {";
while($rowList = mysqli_fetch_array($res)) {
    $id_instrument = $rowList['id_instrument'];
    $instrument_name = $rowList['name'];
    $jsondata .= '"'.$id_instrument.'": "'.$instrument_name.'",';
}
$jsondata = rtrim($jsondata, ',');
$jsondata .= '}'.PHP_EOL;
mysqli_close($f_link);
echo $jsondata;
ferror_log("returned: " . $sql);
?>
$(document).ready(function(){
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });
    $(document).on('click', '.edit_data', function(){
        var part_collection_id = $(this).attr('id');
        var catalog_number_key = part_collection_id.split(':')[0];
        var id_part_type_key = part_collection_id.split(':')[1];
        var id_instrument_key = part_collection_id.split(':')[2];
        $.ajax({
            url:"includes/fetch_partcollections.php",
            method:"POST",
            data:{
                catalog_number_key: catalog_number_key,
                id_part_type_key: id_part_type_key,
                id_instrument_key: id_instrument_key
            },
            dataType:"json",
            success:function(data){
                $('#catalog_number_key2').val(data.catalog_number_key);
                $('#catalog_number_key_hold2').val(data.catalog_number_key);
                $('#id_part_type_key2').val(data.id_part_type_key);
                $('#id_part_type_key_hold2').val(data.id_part_type_key);
                $('#id_instrument_key2').val(data.id_instrument_key);
                $('#id_instrument_key_hold2').val(data.id_instrument_key);
                $('#name2').val(data.name);
                $('#description2').val(data.description);
                $('#insert2').val("Update");
                $('#update2').val("update");
                $('#edit_data_Modal').modal('show');
            }
        });
    });
    $(document).on('click', '.delete_data', function(){ // button that brings up modal
        var is_part_collection = $(this).attr("id");
        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', is_part_collection);
        $('#part_collection2delete').text(is_part_collection);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        var part_collection_id = $(this).data('id');
        var catalog_number_key = part_collection_id.split(':')[0];
        var id_part_type_key = part_collection_id.split(':')[1];
        var id_instrument_key = part_collection_id.split(':')[2];
        $.ajax({
            url:"includes/delete_partcollections.php",
            method:"POST",
            data:{
                catalog_number_key: catalog_number_key,
                id_part_type_key: id_part_type_key,
                id_instrument_key: id_instrument_key
            },
            success:function(data){
                $('#insert_form')[0].reset();
                $('#part_collection_table').html(data);
            }
        });
    });
    $('#edit_form').on("submit", function(event){
        event.preventDefault();
        var catalog_number_key =  $('#catalog_number_key2').val();
        var catalog_number_key_hold =  $('#catalog_number_key_hold2').val();
        
        var id_part_type_key = $('#id_part_type_key2').val();
        var id_part_type_key_hold = $('#id_part_type_key_hold2').val();

        var id_instrument_key = $('#id_instrument_key2').val();
        var id_instrument_key_hold = $('#id_instrument_key_hold2').val();

        var name = $('#name2').val();
        var description = $('#description2').val();
        var update = $('#update2').val();
        var insert = $('#insert2').val();
        $.ajax({
            url:"includes/insert_partcollections.php",
            method:"POST",
            data:{
                catalog_number_key: catalog_number_key,
                id_part_type_key: id_part_type_key,
                id_instrument_key: id_instrument_key,
                catalog_number_key_hold: catalog_number_key_hold,
                id_part_type_key_hold: id_part_type_key_hold,
                id_instrument_key_hold: id_instrument_key_hold,
                name: name,
                description: description,
                update: update
            },
            beforeSend:function(){
                $('#insert2').val("Updating");
            },
            success:function(data){
                $('#edit_form')[0].reset();
                $('#part_collection_table').html(data);
                $('#edit_data_Modal').modal('hide');
            }
        });
    });
    $('#insert_form').on("submit", function(event){
        event.preventDefault();
        var id_partcollection = $('#id_part').val();
        var catalog_number_key = id_partcollection.split(':')[0];
        var id_part_type_key = id_partcollection.split(':')[1];
        // id_part_type is multiple select - returns object
        var id_instrument_key = $('#id_instrument_key').val();
        var name = $('#name').val();
        var description = $('#description').val();
        var update = $('#update').val();
        $.ajax({
            url:"includes/insert_partcollections.php",
            method:"POST",
            data:{
                catalog_number_key: catalog_number_key,
                id_part_type_key: id_part_type_key,
                id_instrument_key: id_instrument_key,
                name: name,
                description: description,
                update: update
            },
            beforeSend:function(){
                $('#insert').val("Inserting");
            },
            success:function(data){
                $('#insert_form')[0].reset();
                $('#add_data_Modal').modal('hide');
                $('#part_collection_table').html(data);
            }
        });
    });
    $(document).on('click', '.view_data', function(){
        var part_collection_id = $(this).attr('id');
        var catalog_number_key = part_collection_id.split(':')[0];
        var id_part_type_key = part_collection_id.split(':')[1];
        var id_instrument_key = part_collection_id.split(':')[2];
        $.ajax({
            url:"includes/select_partcollections.php",
            method:"POST",
            data:{
                catalog_number_key: catalog_number_key,
                id_part_type_key: id_part_type_key,
                id_instrument_key: id_instrument_key
            },
            success:function(data){
                $('#part_collection_detail').html(data);
                $('#dataModal').modal('show');
            }
        });
    });
    
    // Enhanced search and filter functionality
    $(document).ready(function() {
        // Calculate and display statistics
        updateStatistics();
        
        function updateStatistics() {
            var compositions = $('.accordion-item:visible').length;
            var parts = $('.card:visible').length;
            var totalInstruments = $('.card:visible .card-body .col-md-4').length;
            
            var uniqueInstruments = new Set();
            $('.card:visible .card-body span').each(function() {
                var instrumentName = $(this).text().trim();
                if (instrumentName) {
                    uniqueInstruments.add(instrumentName);
                }
            });
            
            $('#stat-compositions').text(compositions);
            $('#stat-parts').text(parts);
            $('#stat-instruments').text(totalInstruments);
            $('#stat-unique-instruments').text(uniqueInstruments.size);
        }
        // Populate instrument filter dropdown
        var instruments = new Set();
        $('.card-body span').each(function() {
            var instrumentName = $(this).text().trim();
            if (instrumentName) {
                instruments.add(instrumentName);
            }
        });
        
        var sortedInstruments = Array.from(instruments).sort();
        sortedInstruments.forEach(function(instrument) {
            $('#instrumentFilter').append('<option value="' + instrument + '">' + instrument + '</option>');
        });
        
        // Search functionality
        $('#searchInput').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            filterResults();
        });
        
        // Single instrument filter
        $('#showSingleInstrument').on('change', function() {
            filterResults();
        });
        
        function filterResults() {
            var searchTerm = $('#searchInput').val().toLowerCase();
            var selectedInstrument = $('#instrumentFilter').val();
            var showSingleInstrument = $('#showSingleInstrument').is(':checked');
            
            $('.accordion-item').each(function() {
                var $accordionItem = $(this);
                var compositionText = $accordionItem.find('.accordion-button').text().toLowerCase();
                var hasMatchingComposition = compositionText.includes(searchTerm);
                var hasMatchingInstrument = false;
                var hasVisibleParts = false;
                
                // Check each part within this composition
                $accordionItem.find('.card').each(function() {
                    var $card = $(this);
                    var partText = $card.find('.card-header h5').text().toLowerCase();
                    var hasMatchingPart = partText.includes(searchTerm);
                    var hasVisibleInstruments = false;
                    
                    // Count instruments in this part
                    var instrumentCount = $card.find('.card-body .col-md-4').length;
                    var shouldShowPart = showSingleInstrument || instrumentCount > 1;
                    
                    if (!shouldShowPart) {
                        $card.hide();
                        return; // Skip this part entirely
                    }
                    
                    // Check each instrument within this part
                    $card.find('.card-body .col-md-4').each(function() {
                        var $instrumentDiv = $(this);
                        var instrumentText = $instrumentDiv.find('span').text().toLowerCase();
                        var instrumentName = $instrumentDiv.find('span').text().trim();
                        
                        var matchesSearch = instrumentText.includes(searchTerm) || hasMatchingComposition || hasMatchingPart;
                        var matchesInstrumentFilter = !selectedInstrument || instrumentName === selectedInstrument;
                        
                        if (matchesSearch && matchesInstrumentFilter) {
                            $instrumentDiv.show();
                            hasVisibleInstruments = true;
                            hasMatchingInstrument = true;
                        } else {
                            $instrumentDiv.hide();
                        }
                    });
                    
                    // Show/hide the part card based on whether it has visible instruments
                    if (hasVisibleInstruments && shouldShowPart) {
                        $card.show();
                        hasVisibleParts = true;
                    } else {
                        $card.hide();
                    }
                });
                
                // Show/hide the composition accordion based on whether it has visible parts
                if (hasVisibleParts) {
                    $accordionItem.show();
                } else {
                    $accordionItem.hide();
                }
            });
            
            // Update statistics after filtering
            updateStatistics();
        }
        
        // Expand/Collapse All functionality
        $('#expandAll').on('click', function() {
            $('.accordion-collapse').addClass('show');
            $('.accordion-button').removeClass('collapsed').attr('aria-expanded', 'true');
        });
        
        $('#collapseAll').on('click', function() {
            $('.accordion-collapse').removeClass('show');
            $('.accordion-button').addClass('collapsed').attr('aria-expanded', 'false');
        });
        
        // Auto-expand when searching
        $('#searchInput, #instrumentFilter').on('keyup change', function() {
            if ($(this).val()) {
                setTimeout(function() {
                    $('.accordion-item:visible .accordion-collapse').addClass('show');
                    $('.accordion-item:visible .accordion-button').removeClass('collapsed').attr('aria-expanded', 'true');
                }, 100);
            }
        });
    });
});
</script>

<!-- Add some custom CSS for better styling -->
<style>
.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    border-color: #b6d7ff;
}

.card-header {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
}

.border {
    border: 1px solid #dee2e6 !important;
}

.search-highlight {
    background-color: yellow;
    font-weight: bold;
}

#searchInput:focus, #instrumentFilter:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
</body>
</html>
