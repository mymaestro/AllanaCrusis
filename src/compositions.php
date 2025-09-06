<?php
define('PAGE_TITLE', 'List compositions');
define('PAGE_NAME', 'Compositions');
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
ferror_log("RUNNING compositions.php");
?>
<main role="main" class="container">
    <div class="container">
        <button type="button" class="btn btn-warning btn-floating btn-lg" id="btn-back-to-top">
            <i class="fas fa-arrow-up"></i>
        </button>
        <div class="row pb-1 pt-5 border-bottom">
            <h1><?php echo ORGNAME ?> Compositions</h1>
        </div>
        <div class="row pt-3">
            <div class="col">
                <div id="search_form">
                    <form action="" method="POST">
                        <div class="row align-items-center">
                            <div class="col-auto"><button type="submit" name="submitButton" class="btn btn-secondary">Search</button></div>
                            <div class="col-auto"><input type="text" size="40" id="search" name="search" class="form-control" aria-describedby="searchHelp" placeholder="Name, description, composer, or arranger"></div>
                        </div>
                    </form>
                </div><!-- search_form -->
            </div><!-- col -->
        
            <div class="col">
                <div class="float-end">
                    <button type="button" data-bs-toggle="modal" data-bs-target="#viewData" id="view" class="btn btn-secondary view_data" disabled>Details</button>
                <?php if($u_librarian) : ?>
                    <button type="button" id="instrumentation" class="btn btn-info instrumentation_btn" disabled>Instrumentation</button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#partsData" id="parts" class="btn btn-success parts_data" disabled>Parts</button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data" disabled>Edit</button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data" disabled>Delete</button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add"  class="btn btn-warning">Add</button>
                <?php endif; ?>
                </div><!-- right justify -->
            </div><!-- col-2 -->
        </div><!-- the heading row with buttons -->
        <div id="composition_table"><!-- filled in by fetch_compositions -->
        </div><!-- composition_table -->
        <div class="modal" id="viewData"><!-- view data -->
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Composition details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="composition_detail">
                        <p class="text-center">Loading details...</p>
                        <!-- filled in by select_compositions.php -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- viewData -->
        <div class="modal" id="partsData"><!-- view parts instrumentation -->
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content" id="instrumentation_detail">
                    <!-- filled in by select_composition_parts.php -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- partsData -->
        <div class="modal" id="deleteModal" tabindex="-1" role="dialog"><!-- delete data -->
            <div class="modal-dialog" role="document">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-body p-4 text-center">
                        <h5 class="mb-0">Delete this composition <span class="text-danger" id="composition2delete">#</span>?</h5>
                        <p><strong>This will delete the composition and all parts associated with it.</strong></p>
                        <p>You can safely cancel now.</p>
                    </div>
                    <div class="modal-footer flex-nowrap p-0">
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0 border-right" id="confirm-delete" data-bs-dismiss="modal"><strong>Yes, delete</strong></button>
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0" data-bs-dismiss="modal">No thanks</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- deleteModal -->
        <div class="modal" id="editModal"><!-- editModal -->
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Compositions information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                        <form class="align-items-center" method="post" id="insert_form">
                        <div class="row">
                            <div class="col-3">
                                <!-- catalog_number (5 characters) 'The catalog number is a letter and 3-digit number, for example M101' -->
                                <label for="catalog_number" class="col-form-label">Catalog number*</label>
                            </div>
                            <div class="col-3">
                                <input type="text" class="form-control" id="catalog_number" name="catalog_number" placeholder="X" required minlength="2" maxlength="5" size="5"/>
                                <input type="hidden" id="catalog_number_hold" name="catalog_number_hold" value="" />
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <!-- enabled (0 or 1) UNSIGNED  'Set greater than 0 if this composition can be played' -->
                                    <input class="form-check-input" id="enabled" name="enabled" type="checkbox" value="1">
                                    <label for="enabled" class="form-check-label">Enabled</label>
                                </div>
                            </div>
                        </div><!-- row -->
                        <div class="row">
                            <div class="col-3">
                                <!-- name (255 characters)  'The title of the composition' -->
                                <label for="name" class="col-form-label">Title*</label>
                            </div>
                            <div class="col-7">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Musical comedy with a plot" aria-describedby="titleHelp" required minlength="3" maxlength="255"/>
                                <small id="titleHelp" class="form-text text-muted">Enter the title of the composition</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <!-- description (512 characters)  'Description of the composition' -->
                                <label for="description" class="col-form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" maxlength="255" aria-describedby="descriptionHelp"></textarea>
                                <small id="descriptionHelp" class="form-text text-muted">Enter a description of the composition (publicly viewable)</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- composer (255 characters)  'The composer of the piece' -->
                                <label for="composer" class="col-form-label">Composer*</label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control" id="composer" name="composer" placeholder="Comb Poseur" required maxlength="255"/>
                                <small id="composerHelp" class="form-text text-muted">Enter the name of the composer of the composition</small>
                            </div>
                            <div class="col-2">
                                <!-- editor (255 characters)  'The editor or lyricist' -->
                                <label for="editor" class="col-form-label">Editor</label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control" id="editor" name="editor" placeholder="Ed Itor" maxlength="255"/>
                                <small id="editorHelp" class="form-text text-muted">Enter the name of the editor of the composition</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- arranger (255 characters)  'The arranger of the piece' -->
                                <label for="arranger" class="col-form-label">Arranger</label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control" id="arranger" name="arranger" placeholder="Art Ranger" maxlength="255"/>
                                <small id="arrangerHelp" class="form-text text-muted">Enter the name of the arranger of the composition</small>
                            </div>
                            <div class="col-2">
                                <!-- publisher (255 characters)  'The name of the publishing company' -->
                                <label for="publisher" class="col-form-label">Publisher</label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control" id="publisher" name="publisher" placeholder="P. Blisher" maxlength="255"/>
                                <small id="publisherHelp" class="form-text text-muted">Enter the name of the publisher of the composition</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- genre (4 characters)  'Which genre is the piece (from the genres table)' -->
                                <label for="genre" class="col-form-label">Genre</label>
                            </div>
                            <div class="col-4">
                            <?php
                                $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                $sql = "SELECT `id_genre`, `name` FROM genres WHERE `enabled` = 1 ORDER BY name;";
                                //error_log("Running " . $sql);
                                $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                $opt = "<select class='form-select form-control' aria-label='Select paper size' id='genre' name='genre'>
                                <option value=''>Select a genre</option>";
                                while($rowList = mysqli_fetch_array($res)) {
                                    $id_genre = $rowList['id_genre'];
                                    $genre_name = $rowList['name'];
                                    $opt .= "<option value='".$id_genre."'>".$genre_name."</option>";
                                }
                                $opt .= "</select>";
                                mysqli_close($f_link);
                                echo $opt;
                                //error_log("returned: " . $sql);
                                ?> 
                                <small id="genreHelp" class="form-text text-muted">This will be a selection from the Genres table</small>
                            </div>
                            <div class="col-2">
                                <!-- ensemble (4 characters)  'Which ensemble plays this piece ' -->
                                <label for="ensemble" class="col-form-label">Ensemble</label>
                            </div>
                            <div class="col-4">
                            <?php
                                $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                $sql = "SELECT `id_ensemble`, `name` FROM ensembles WHERE `enabled` = 1 ORDER BY name;";
                                //error_log("Running " . $sql);
                                $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                $opt = "<select class='form-select form-control' aria-label='Select ensemble' id='ensemble' name='ensemble'>
                                <option value=''>Select an ensemble</option>";
                                while($rowList = mysqli_fetch_array($res)) {
                                    $id_ensemble = $rowList['id_ensemble'];
                                    $ensemble_name = $rowList['name'];
                                    $opt .= "<option value='".$id_ensemble."'>".$ensemble_name."</option>";
                                }
                                $opt .= "</select>";
                                mysqli_close($f_link);
                                echo $opt;
                                ?>   
                                <small id="ensembleHelp" class="form-text text-muted">Choose from the Ensembles table</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- grade decimal(1,1) UNSIGNED  'Grade of difficulty' -->
                                <label for="grade" class="col-form-label">Grade level (0-7, 0="unknown")</label>
                            </div>
                            <div class="col-4">
                                <input type="number" name="grade" class="form-control" value="0" min="0" max="7" step="0.5" id="grade"/>
                                <small id="gradeHelp" class="form-text text-muted">Level of difficulty (1-7, 0 for "unknown")</small>
                            </div>
                            <div class="col-2">
                                <!-- paper_size (4 characters)  'Physical size, from the paper_sizes table' -->
                                <label for="paper_size" class="col-form-label">Paper size</label>
                            </div>
                            <div class="col-4">
                            <?php
                                $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                $sql = "SELECT `id_paper_size`, `name` FROM paper_sizes WHERE `enabled` = 1 ORDER BY name;";
                                //error_log("Running " . $sql);
                                $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                $opt = "<select class='form-select form-control' aria-label='Select paper size' id='paper_size' name='paper_size'>";
                                while($rowList = mysqli_fetch_array($res)) {
                                    $id_paper_size = $rowList['id_paper_size'];
                                    $paper_size_name = $rowList['name'];
                                    $opt .= "<option value='".$id_paper_size."'>".$paper_size_name."</option>";
                                }
                                $opt .= "</select>";
                                mysqli_close($f_link);
                                echo $opt;
                                //error_log("returned: " . $sql);
                                ?> 
                                <small id="paper_sizeHelp" class="form-text text-muted">What size of paper are the parts on? Choose one of the paper sizes.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- last_performance_date datetime  'When the composition was last performed' -->
                                <label for="last_performance_date" class="col-form-label">Last performed</label>
                            </div>
                            <div class="col-4">
                                <input type="date" class="form-control" id="last_performance_date" name="last_performance_date" placeholder="20180101T220000" />
                                <small id="last_performance_dateHelp" class="form-text text-muted">The date this piece was last performed</small>
                            </div>
                            <div class="col-2">
                                <!-- duration 'Performance duration in seconds' -->
                                <label for="duration" class="col-form-label">Duration (seconds)</label>
                            </div>
                            <div class="col-sm-1">
                                <input type="number" step="1" class="form-control" id="duration_hours" name="duration_hours" min="0" max="12"/>
                                <small id="durationHourHelp" class="form-text text-muted">Hr</small>
                            </div>
                            <div class="col-sm-1">
                                <input type="number" step="1" class="form-control" id="duration_minutes" name="duration_minutes" min="0" max="59"/>:
                                <small id="durationMinHelp" class="form-text text-muted">Min</small>
                            </div>
                            <div class="col-sm-1">
                                <input type="number" step="1" class="form-control" id="duration_seconds" name="duration_seconds" min="0" max="59"/>:
                                <small id="durationSecHelp" class="form-text text-muted">Sec</small>
                            </div>
                            <div class="col-sm-1">
                                <input type="number" step="1" class="form-control" id="duration" name="duration" min="0" max="99999"/>
                                <small id="durationHelp" class="form-text text-muted">Performance duration, in seconds.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <!-- comments (4096 characters)  'Comments about the piece, liner notes' -->
                                <label for="comments" class="col-form-label">Comments</label>
                                <textarea class="form-control" id="comments" name="comments" rows="3" maxlength="4096"></textarea>
                                <small id="commentsHelp" class="form-text text-muted">Library comments about this piece, not public.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <!-- performance_notes (4096 characters)  'Performance notes, or program notes' -->
                                <label for="performance_notes" class="col-form-label">Performance notes</label>
                                <textarea class="form-control" id="performance_notes" name="performance_notes" rows="3" maxlength="4096"></textarea>
                                <small id="performance_notesHelp" class="form-text text-muted">Performance notes, or "liner notes".</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                            <!-- storage_location (255 characters)  'Where it is kept (which drawer)' -->
                            <label for="storage_location" class="col-form-label">Storage location</label>
                            </div>
                            <div class="col-10">
                                <input type="text" class="form-control" id="storage_location" name="storage_location" placeholder="Third drawer from the left." maxlength="255"/>
                                <small id="storage_locationHelp" class="form-text text-muted">In which city, building, filing cabinet, or drawer might one find this piece of music?</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- provenance (1 character) 'How was this piece acquired?' -->
                                <label class="col-form-label">How acquired?</label>
                            </div>
                            <div class="col-10">
                                <small id="provenanceHelp" class="form-text text-muted">How was this piece acquired?</small>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="provenance" id="provenance_P" value="P" required>
                                    <label class="form-check-label" for="provenance_P">Purchased</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="provenance" id="provenance_R" value="R">
                                    <label class="form-check-label" for="provenance_R">Rented</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="provenance" id="provenance_B" value="B">
                                    <label class="form-check-label" for="provenance_B">Borrowed</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="provenance" id="provenance_D" value="D">
                                    <label class="form-check-label" for="provenance_D">Donated</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- date_acquired datetime  'When the piece was acquired' -->
                                <label for="date_acquired" class="col-form-label">Date acquired</label>
                            </div>
                            <div class="col-4">
                                <input type="date" class="form-control" id="date_acquired" name="date_acquired" placeholder="20180101T220000" />
                                <small id="date_acquiredHelp" class="form-text text-muted">When was the piece of music acquired?</small>
                            </div>
                            <div class="col-2">
                                <!-- cost decimal(4,2)  'How much did it cost, in dollars and cents' -->
                                <label for="cost" class="col-form-label">Cost</label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="form-control" id="cost" name="cost" placeholder="999.99" maxlength="6"/>
                                <small id="costHelp" class="form-text text-muted">How much did the piece of music cost (or, how much will it cost to replace)?</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- listening_example_link (255 characters)  'A link to a listening example, maybe on YouTube' -->
                                <label for="listening_example_link" class="col-form-label">Listening example</label>
                            </div>
                            <div class="col-10">
                                <input type="text" class="form-control" id="listening_example_link" name="listening_example_link" placeholder="https://acwe.org/recordings/piece.mp3" maxlength="255"/>
                                <small id="listening_example_linkHelp" class="form-text text-muted">Where can you find a recording of this arrangement of this piece?</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- image_path (255 characters)  'A link to a picture of the score' -->
                                <label for="image_path" class="col-form-label">Image path</label>
                            </div>
                            <div class="col-10">
                                <input type="text" class="form-control" id="image_path" name="image_path" placeholder="https://acwe.org/files/scores/piece.pdf" maxlength="255"/>
                                <small id="image_pathHelp" class="form-text text-muted">Where can you find an image of the first page of the score?</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- windrep (255 characters)  'A link to this piece on windrep.org' -->
                                <label for="windrep_link" class="col-form-label">Wind Repertory Project link</label><button id="windrep" class="btn btn-secondary btn-sm">Search</button>
                            </div>
                            <div class="col-10">
                                <input type="text" class="form-control" id="windrep_link" name="windrep_link" placeholder="https://www.windrep.org/Russian_Christmas_Music" maxlength="255"/>
                                <small id="windrep_linkHelp" class="form-text text-muted">Where can you this arrangement on the Wind Repertory site?</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- checked_out (255 characters)  'To whom was this piece lended' -->
                                <label for="checked_out" class="col-form-label">Source, or holder</label>
                            </div>
                            <div class="col-10">
                                <input type="text" class="form-control" id="checked_out" name="checked_out" placeholder="Not checked out" maxlength="255"/>
                                <small id="checked_outHelp" class="form-text text-muted">Source, if borrowed or rented; or holder, if loaned out.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <!-- last_inventory_date datetime  'When was the last time somebody touched this music' -->
                                <label for="last_inventory_date" class="col-form-label">Last inventory</label>
                            </div>
                            <div class="col-4">
                                <input type="date" class="form-control" id="last_inventory_date" name="last_inventory_date" placeholder="20180101T220000" />
                                <small id="last_inventory_dateHelp" class="form-text text-muted">When was the last time somebody touched this music</small>
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
        </div><!-- editModal -->
        <div class="modal" id="messageModal"><!-- message feedback -->
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
<?php require_once(__DIR__. "/includes/footer.php"); ?>
<script src="js/auto-tables.js"></script>
<!-- jquery function to add/update database records -->
<script>
$(document).ready(function() {
    // Declare global variable for selected catalog number
    let catalog_number = null;
    
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
    <?php
        $data = [];
        if (isset($_POST["submitButton"])) $data['submitButton'] = $_POST['submitButton'];
        if (isset($_POST["ensemble"]))     $data['ensemble'] = $_POST['ensemble'];
        if (isset($_POST["genre"]))        $data['genre'] = $_POST['genre'];
        if (isset($_POST["search"]))       $data['search'] = $_POST['search'];
        $data['user_role'] = ($u_librarian) ? 'librarian' : 'nobody';
    ?>
    $.ajax({
        url:"index.php?action=fetch_compositions",
        method:"POST",
        data: <?php echo json_encode($data); ?>,
        success:function(data){
            $('#composition_table').html(data);
        }
    });


    // Enable the edit and delete buttons, and get the composition ID when a table row is clicked
    $(document).on('click', '#composition_table tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#view, #edit, #delete, #parts, #instrumentation').prop('disabled',false);
        catalog_number = $(this).data('id'); // data-id attribute
        console.log("Selected catalog number: " + catalog_number);
    });


    $(document).on('click', '.view_data', function(e){
        e.preventDefault();
        // Get the clicked element's data attribute

        let clicked_id = $(this).data('id');

        // Try closest row if no data-id
        if (!clicked_id) {
            let $row = $(this).closest('tr');
            clicked_id = $row.data('id');

            // Also select the radio button
            if (clicked_id) {
                $row.find('input[type="radio"]').prop('checked', true);
                $('#view, #edit, #delete, #parts, #instrumentation').prop('disabled', false);
                catalog_number = clicked_id; // Update the global variable
            }
        }

        // Still no id but a selected row?
        if (!clicked_id && catalog_number) {
            clicked_id = catalog_number; // Use the global variable
        }

        //var catalog_number = $(this).attr("id");
        if(clicked_id)
        {
            $.ajax({
                url:"index.php?action=select_compositions",
                type:"POST",
                data:{catalog_number: clicked_id}, // Use clicked_id instead of catalog_number
                success:function(data){
                    $('#composition_detail').html(data);
                    $('#viewData').modal('show');
                }
            });
        } else { alert("No composition selected. Please select a composition first."); }
    });
    $(document).on('click', '.parts_data', function(){
        //var catalog_number = $(this).attr("id");
        if(catalog_number != '')
        {
            $.ajax({
                url:"index.php?action=select_composition_parts",
                type:"POST",
                data:{catalog_number:catalog_number},
                success:function(data){
                    $('#instrumentation_detail').html(data);
                    $('#partsData').modal('show');
                }
            });
        }
    });
    $(document).on('click', '.instrumentation_btn', function(){
        if(catalog_number != '')
        {
            // Create a form and submit it with POST to composition_instrumentation.php
            // FIX ME
            var form = $('<form></form>');
            form.attr('method', 'post');
            form.attr('action', '/composition_instrumentation');
            form.append('<input type="hidden" name="catalog_number" value="' + catalog_number + '" />');
            $('body').append(form);
            form.submit();
        }
    });
<?php if($u_librarian) : ?>
    $("#gradevalue").html($("#grade").val());;
    $('#windrep').click(function() {
        var searchURL = 'https://www.windrep.org/index.php?search=' + $('#name').val();
        window.open(searchURL);
        return false;
    });
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
    });
    $(document).on('click', '.edit_data', function(){
        //var catalog_number = $(this).attr("id");
        $.ajax({
            url:"index.php?action=fetch_compositions",
            type:"POST",
            data:{catalog_number:catalog_number},
            dataType:"json",
            success:function(data){
                $('#catalog_number').val(data.catalog_number);
                $('#catalog_number_hold').val(data.catalog_number);
                $('#name').val(data.name);
                $('#description').val(data.description);
                $('#composer').val(data.composer);
                $('#arranger').val(data.arranger);
                $('#editor').val(data.editor);
                $('#publisher').val(data.publisher);
                $('#genre').val(data.genre);
                $('#ensemble').val(data.ensemble);
                $('#grade').val(data.grade);
                $('#last_performance_date').val(data.last_performance_date);
                $('#duration').val(data.duration);

                // Set the hour, minutes, seconds fields
                var d_duration = data.duration;
                var d_hours = ~~(d_duration / 3600);
                var d_minutes = ~~((d_duration % 3600) / 60);
                var d_seconds = ~~d_duration % 60;
                $('#duration_hours').val(d_hours);
                $('#duration_minutes').val(d_minutes);
                $('#duration_seconds').val(d_seconds);

                $('#comments').val(data.comments);
                $('#performance_notes').val(data.performance_notes);
                $('#storage_location').val(data.storage_location);
                $('#provenance_' + data.provenance).prop('checked', true);

                $('#date_acquired').val(data.date_acquired);
                $('#cost').val(data.cost);
                $('#listening_example_link').val(data.listening_example_link);
                $('#checked_out').val(data.checked_out);
                $('#paper_size').val(data.paper_size);
                $('#image_path').val(data.image_path);
                $('#windrep_link').val(data.windrep_link);
                $('#last_inventory_date').val(data.last_inventory_date);
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
        // input button name="delete" id="catalog_number" class="delete_data"
        //var catalog_number = $(this).attr("id");
        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', catalog_number);
        $('#composition2delete').text(catalog_number);
    });
    $('#confirm-delete').click(function(){
        // The confirm delete button
        var catalog_number = $(this).data('id');
        $.ajax({
            url:"index.php?action=delete_compositions",
            type:"POST",
            data:{
                catalog_number: catalog_number
            },
            success:function(response){
                console.log("Response: " + response);
                response = JSON.parse(response);
                if (response.success) {
                    $('#message_detail').html('<p class="text-success">Record ' + response.message + ' deleted from compositions</p>');
                    $('#messageModal').modal('show');
                    <?php
                    $data = [];
                    if (isset($_POST["submitButton"])) $data['submitButton'] = $_POST['submitButton'];
                    if (isset($_POST["search"]))     $data['search'] = $_POST['search'];
                    $data['user_role'] = ($u_librarian) ? 'librarian' : 'nobody';
                    ?>
                    // Refresh the table
                    $.ajax({
                        url:"index.php?action=fetch_compositions",
                        method:"POST",
                        data: <?php echo json_encode($data); ?>,
                        success:function(data){
                            $('#composition_table').html(data);
                        }
                    });
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
            alert("Title is required");
        }
        else if($('#catalog_number').val() == '')
        {
            alert("Catalog number is required");
        }
        else if($('#composer').val() == '')
        {
            alert("Composer is required");
        }
        else
        {
            $.ajax({
                url:"index.php?action=insert_compositions",
                type:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#editModal').modal('hide');
                    $('#message_detail').html(data);
                    $('#messageModal').modal('show');
                    <?php 
                    $data = [];
                    if (isset($_POST["submitButton"])) $data['submitButton'] = $_POST['submitButton'];
                    if (isset($_POST["search"]))     $data['search'] = $_POST['search'];
                    $data['user_role'] = ($u_librarian) ? 'librarian' : 'nobody';
                    ?>
                    $.ajax({
                        url:"index.php?action=fetch_compositions",
                        method:"POST",
                        data: <?php echo json_encode($data); ?>,
                        success:function(data){
                            $('#composition_table').html(data);
                        }
                    });
                }
            });
        }
    });
<?php endif; ?>
    $('#duration_hours').on("input", function() {
        $('#duration').val(computeDurationSecs());
    });
    $('#duration_minutes').on("input", function() {
        $('#duration').val(computeDurationSecs());
    });
    $('#duration_seconds').on("input", function() {
        $('#duration').val(computeDurationSecs());
    });
});

function computeDurationSecs() {
    var durationSecs = 0;
    var hours = $('#duration_hours').val();
    var minutes = $('#duration_minutes').val();
    var seconds = $('#duration_seconds').val();
    if(!isNaN(hours) && hours.length !== 0)   durationSecs += parseInt(hours) * 3600;
    if(!isNaN(minutes) && minutes.length !== 0) durationSecs += parseInt(minutes) * 60;
    if(!isNaN(seconds) && seconds.length !== 0) durationSecs += parseInt(seconds);
    return durationSecs;
}

</script>
</body>
</html>