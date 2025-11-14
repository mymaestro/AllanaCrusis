<?php
define('PAGE_TITLE', 'List parts');
define('PAGE_NAME', 'Parts');
require_once(__DIR__. "/includes/header.php");
// DO NOT REMOVE the role checks - they are used by JavaScript to enable/disable buttons
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
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");
ferror_log("RUNNING parts.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['catalog_number'])) {
    $catalog_number = $_POST['catalog_number'];
    // Handle the POST logic (e.g., update instrumentation)
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['catalog_number'])) {
    $catalog_number = $_GET['catalog_number'];
    // Handle the GET logic (e.g., display instrumentation)
    echo "<script>window._autoload_catalog_number = '" . addslashes($catalog_number) . "';</script>";
} else {
    // Show a default page or error
}
ferror_log(print_r($_GET, true));
ferror_log("What is catalog_number? " . (isset($catalog_number) ? $catalog_number : 'Not set'));
?>

<!-- Main title above the panels -->
<div class="container pt-5 pb-1 px-4 py-3 border-bottom"><h1><?php echo ORGNAME ?> Instrument Parts</h1></div>

<!-- Toggle button for mobile/tablet -->
<button class="left-panel-toggle" id="toggleLeftPanel" type="button">
    <i class="fas fa-bars"></i> Compositions
</button>

<!-- Backdrop overlay for mobile -->
<div class="left-panel-backdrop" id="leftPanelBackdrop"></div>

<!-- main scrollable panels -->
<main role="main" class="container">
    <!-- left panel -->
    <aside class="left-panel">
        <div class="p-3 border-bottom">
            <input type="text" class="form-control" id="composition-search" placeholder="Search compositions..." autocomplete="off" />
        </div>
        <div class="left-menu-scroll" id="compositions_list">
            <!-- populated by JavaScript with compositions -->
        </div>
    </aside>
    <!-- right panel -->
    <section class="right-panel">
        <div id="parts_table">
            <p class="d-flex align-content-center flex-wrap lead">Choose a composition from the menu on the left.</p>
            <!-- gets replaced by JavaScript template -->
        </div>
    </section>
    <section>
        <div id="dataModal" class="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Part details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body" id="part_detail">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- dataModal -->
        <div id="deleteModal" class="modal" tabindex="-1" role="dialog">
            <!-- delete data -->
            <div class="modal-dialog" role="document">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-body p-4 text-center">
                        <h5 class="mb-0">Delete this part?</h5>
                        <p id="part2delete">You can cancel now.</p>
                    </div>
                    <div class="modal-footer flex-nowrap p-0">
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0 border-right" id="confirm-delete" data-bs-dismiss="modal"><strong>Yes, delete</strong></button>
                        <button type="button" class="btn btn-lg btn-link text-decoration-none rounded-0" data-bs-dismiss="modal">No thanks</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- deleteModal -->
        <div id="editModal" class="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Part information</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div><!-- modal-header -->
                    <div class="modal-body">
                        <div class="container-fluid">
                            <form action="index.php?action=insert_parts" method="post" id="insert_form">
                                <div class="row bg-light">
                                    <div class="col-md-2">
                                        <label for="id_part_type" class="col-form-label">Part type*</label>
                                    </div>
                                    <div class="col-md-3">
                                        <!-- Read part types from part_types table -->
                                        <?php
                                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                        $sql = "SELECT `id_part_type`, `name` FROM part_types WHERE `enabled` = 1 ORDER BY collation;";
                                        //error_log("Running " . $sql);
                                        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                        $opt = "<select class='form-select form-control' aria-label='Select part typee' id='id_part_type' name='id_part_type'>".PHP_EOL;
                                        $opt .= '                                            <option value="">Select a part type</option>' . PHP_EOL;
                                        while ($rowList = mysqli_fetch_array($res)) {
                                            $id_part_type = $rowList['id_part_type'];
                                            $part_type_name = $rowList['name'];
                                            $opt .= "                                           <option value='" . $id_part_type . "'>" . $part_type_name . "</option>" . PHP_EOL;
                                        }
                                        $opt .= "                                        </select>" . PHP_EOL;
                                        mysqli_close($f_link);
                                        echo $opt;
                                        //error_log("returned: " . $sql);
                                        ?>
                                        <input type="hidden" id="id_part_type_hold" name="id_part_type_hold" value="" />
                                        <input type="hidden" id="is_part_collection" name="is_part_collection" value="0" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="catalog_number" class="col-form-label">Catalog number*</label>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                        $sql = "SELECT `catalog_number`, `name` FROM compositions ORDER BY catalog_number;";
                                        //error_log("Running " . $sql);
                                        // Need to preselect
                                        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                        $opt = "<select class='form-select form-control' aria-label='Select composition' id='catalog_number' name='catalog_number'>".PHP_EOL;
                                        while ($rowList = mysqli_fetch_array($res)) {
                                            $comp_catno = $rowList['catalog_number'];
                                            $comp_name = $rowList['name'];
                                            $opt .= '                                            <option value="' . $comp_catno . '">' . $comp_catno . ': ' . $comp_name . '</option>' . PHP_EOL;
                                        }
                                        $opt .= "                                        </select>". PHP_EOL ;
                                        mysqli_close($f_link);
                                        echo $opt;
                                        //error_log("returned: " . $sql);
                                        ?>
                                        <input type="hidden" id="catalog_number_hold" name="catalog_number_hold" value="" />
                                    </div>
                                </div><!-- row -->
                                <div class="row bg-light">
                                    <div class="col-md-2">
                                        <label class="col-form-label">Pages*</label>
                                    </div>
                                    <div class="col-md-3">
                                        <!-- How many pages -->
                                        <input type="number" class="form-control" id="page_count" name="page_count" aria-label="Page count" min="1" max="99" required />
                                    </div>
                                    <div class="col-md-3">
                                        <!-- Request paper size -->
                                        <label for="paper_size" class="col-form-label">Paper size</label>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                        $sql = "SELECT `id_paper_size`, `name` FROM paper_sizes WHERE `enabled` = 1 ORDER BY name;";
                                        //error_log("Running " . $sql);
                                        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                                        $opt = "<select class='form-select form-control' aria-label='Select paper size' id='paper_size' name='paper_size'>".PHP_EOL;
                                        while ($rowList = mysqli_fetch_array($res)) {
                                            $id_paper_size = $rowList['id_paper_size'];
                                            $paper_size_name = $rowList['name'];
                                            $opt .= "                                            <option value='" . $id_paper_size . "'>" . $paper_size_name . "</option>" . PHP_EOL;
                                        }
                                        $opt .= "                                        </select>";
                                        mysqli_close($f_link);
                                        echo $opt;
                                        //error_log("returned: " . $sql);
                                        ?>
                                    </div>
                                </div><!-- row -->
                                <div class="row">
                                    <div class="col-auto">
                                        <hr />
                                    </div>
                                </div><!-- blank row -->
                                <div class="row bg-light">
                                    <div class="col-md-2">
                                        <label class="col-form-label">Originals count*</label>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" id="originals_count" name="originals_count" min="0" max="999" required />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="col-form-label">Copies count*</label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" id="copies_count" name="copies_count" min="0" max="999" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label class="col-form-label">Name</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Name (optional)" />
                                    </div>
                                </div>
                                <div class="row bg-light">
                                    <div class="col-md-2">
                                        <label class="col-form-label">Description</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="description" name="description" placeholder="Description (optional)" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="d-flex" id="part_instruments">
                                        <div class="col-md-2">
                                            <label for="id_instrument_list" class="col-form-label">Instrument(s) on the part.</br></br>* default</label>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select form-control text-muted d-flex" aria-label="Select instrument" id="id_instrument_list" name="id_instrument_list[]" multiple>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                        </br>
                                            <p class="text-center">
                                                <button type="button" class="btn btn-light" name="add_instrument" id="add_instrument"><i class="fa fa-angle-right"></i></button>
                                            </br>
                                                <button type="button" class="btn btn-light" id="remove_instrument"><i class="fa fa-angle-left"></i></button>
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <select class="form-select form-control d-flex" aria-label="Select instrument" id="id_instrument" name="id_instrument[]" multiple>
                                            </select>
                                        </div>
                                    </div><!-- part_instruments -->
                                </div>
                                <div class="row bg-light">
                                    <div class="col-md-2">
                                        <label for="image_path" class="col-form-label"><span id="image_path_display">Part PDF (optional)</span></label>
                                    </div>
                                    <!--
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" id="image_path" name="image_path" aria-label="Image path" placeholder="https://acwe.org/parts/flute1.pdf (optional)" />
                                    </div>
                                        -->
                                    <div class="input-group mb-3">
                                        <input type="file" class="form-control" id="image_path" name="image_path" aria-label="Image path" placeholder="https://acwe.org/parts/flute1.pdf (optional)" accept=".pdf" />
                                        <label class="input-group-text" for="image_path">Upload</label>
                                    </div>

                                </div>
                        </div><!-- container-fluid -->
                    </div><!-- modal-body -->
                    <div class="modal-footer">
                        <p id="parteditmessage" class="text-start"></p>
                        <input type="hidden" name="update" id="update" value="0" />
                        <button type="submit" name="insert" id="insert" class="btn btn-success">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span id="insert-text">Insert</span>
                        </button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div><!-- modal-footer -->
                </div><!-- modal-content -->
            </div><!-- modal-dialog -->
        </div><!-- editModal -->
    </section>
</main>
<?php require_once(__DIR__. "/includes/footer.php"); ?>
<!-- Load instruments data into JSON array and object -->
<script>
<?php
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT `id_instrument`, `collation`, `name` FROM instruments WHERE `enabled` = 1 ORDER BY collation;";
ferror_log("Running " . $sql);
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
$instrumentdataArray = [];
$instrumentdata = null;
while ($rowList = mysqli_fetch_array($res)) {
    $instrumentdataArray[] = [
        'collation' => $rowList['collation'],
        'id' => $rowList['id_instrument'],
        'name' => $rowList['name']
    ];
    $instrumentdata[$rowList['id_instrument']] = $rowList['name'];
}
mysqli_close($f_link);
?>
  window.instrumentdataArray = <?php echo json_encode($instrumentdataArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
  window.instrumentdata = <?php echo json_encode($instrumentdata, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
  window.u_librarian = <?php echo ($u_librarian ? 'true' : 'false'); ?>;
</script>
<!-- HTML Templates for dynamic content -->
<script type="text/html" id="composition-list-template">
<li class="list-group-item list-group-item-action" name="parts_data" id="catno_{{catalog_number}}">
    {{catalog_number}}: <strong>{{title}}</strong><br> 
    <span class="text-muted">{{composer}}{{#arranger}} arr. {{arranger}}{{/arranger}} ({{parts}} parts)</span>
</li>
</script>

<script type="text/html" id="parts-table-template">
    <div class="table-toolbar">
        <div class="row border-bottom g-2">
            <div class="col d-flex align-items-center" style="min-width: 0;">
                <h4 id="composition_header" class="mb-0" title="{{title}}">Composition parts</h4>
            </div>
            <div class="col-auto d-flex align-items-center">
                <button type="button" data-bs-toggle="modal" data-bs-target="#dataModal" value="View" id="view" class="btn btn-secondary view_data me-1" disabled>View</button>
            {{#u_librarian}}
                <button type="button" id="instrumentation" class="btn btn-info instrumentation_btn me-1">Instrumentation</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="edit" class="btn btn-primary edit_data me-1" disabled>Edit</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" id="delete" class="btn btn-danger delete_data me-1" disabled>Delete</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#editModal" id="add" class="btn btn-warning add_data">Add</button>
            {{/u_librarian}}
            </div>
        </div>
    </div><!-- table-toolbar -->
    <div class="table-wrapper table-responsive mt-2">
        <table class="table table-hover table-striped" id="partsdatatable">
        <caption class="title">Parts for {{catalog_number}}</caption>
            <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Part type</th>
                    <th>Name</th>
                    <th>PDF</th>
                    <th>Paper size</th>
                    <th>Pages</th>
                    <th>Originals</th>
                    <th>Copies</th>
                    <th>Instruments</th>
                </tr>
            </thead>
            <tbody>
                {{#parts}}
                <tr data-id="{{catalog_number}}-{{id_part_type}}">
                    <td>
                        <input type="radio" name="record_select" value="{{catalog_number}}-{{id_part_type}}" class="form-check-input select-radio" />
                    </td>
                    <td><a href="#" class="text-decoration-none view_part_link">{{part_type}}</a></td>
                    <td>{{name}}</td>
                    <td>{{image_path}}</td>
                    <td>{{paper}}</td>
                    <td>{{page_count}}</td>
                    <td>{{originals_count}}</td>
                    <td>{{copies_count}}</td>
                    <td>{{instruments}}</td>
                </tr>
                {{/parts}}
            </tbody>
        </table>
    </div><!-- table-responsive -->
</script>

<script type="text/html" id="compositions-table-template">
<div class="panel panel-default">
    <div class="table-responsive">
        <table class="table table-hover tablesort" id="cpdatatable">
            <caption class="title">Available compositions</caption>
            <thead>
                <tr>
                    <th data-tablesort-type="string">Catalog number <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Name <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Composer <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="string">Arranger <i class="fa fa-sort" aria-hidden="true"></i></th>
                    <th data-tablesort-type="number">Parts <i class="fa fa-sort" aria-hidden="true"></i></th>
                    {{#u_librarian}}
                    <th>Actions</th>
                    {{/u_librarian}}
                </tr>
            </thead>
            <tbody>
                {{#compositions}}
                <tr>
                    <td>{{catalog_number}}</td>
                    <td>{{title}}</td>
                    <td>{{composer}}</td>
                    <td>{{arranger}}</td>
                    <td>
                        {{#hasparts}}
                        <button type="button" name="parts_data" id="catno_{{catalog_number}}" class="btn btn-info btn-sm parts_data">{{parts}} parts</button>
                        {{/hasparts}}
                        {{^hasparts}}
                        <span class="text-muted">0 parts</span>
                        {{/hasparts}}
                    </td>
                    {{#u_librarian}}
                    <td>
                        <form method="post" id="instr_data_{{catalog_number}}" action="/composition_instrumentation">
                            <input type="hidden" name="catalog_number" value="{{catalog_number}}" />
                            <input type="submit" name="compositions" value="Instrumentation" id="{{catalog_number}}" class="btn btn-warning btn-sm instr_data" />
                        </form>
                    </td>
                    {{/u_librarian}}
                </tr>
                {{/compositions}}
            </tbody>
        </table>
    </div><!-- table-responsive -->
</div><!-- class panel -->
</script>

<!-- // Simple templating function to render HTML from templates -->
<script>
function renderTemplate(templateId, data) {
    var template = document.getElementById(templateId).innerHTML;
    
    // Handle {{#condition}} and {{/condition}} blocks first (positive conditions)
    template = template.replace(/\{\{#(\w+)\}\}([\s\S]*?)\{\{\/\1\}\}/g, function(match, conditionName, content) {
        var conditionValue = data[conditionName];
        
        // If it's an array, iterate through it
        if (Array.isArray(conditionValue)) {
            return conditionValue.map(function(item) {
                return renderSimpleTemplate(content, item);
            }).join('');
        }
        
        // For boolean/truthy conditions, check if we should render the content
        if (conditionValue) {
            return renderSimpleTemplate(content, data);
        }
        
        // Condition is false, return empty string
        return '';
    });
    
    // Handle {{^condition}} and {{/condition}} blocks (negative conditions)
    template = template.replace(/\{\{\^(\w+)\}\}([\s\S]*?)\{\{\/\1\}\}/g, function(match, condition, content) {
        return !data[condition] ? renderSimpleTemplate(content, data) : '';
    });
    
    return renderSimpleTemplate(template, data);
}

function renderSimpleTemplate(template, data) {
    return template.replace(/\{\{(\w+)\}\}/g, function(match, key) {
        return data[key] !== undefined && data[key] !== null ? data[key] : '';
    });
}

// Auto-load composition if catalog_number is present in URL
if (window._autoload_catalog_number) {
    $(function() {
        setTimeout(function() {
            var selector = '#catno_' + window._autoload_catalog_number;
            var $item = $(selector);
            if ($item.length) {
                $item.trigger('click');
                $item.addClass('active');
            }
        }, 400); // Wait for AJAX to populate the list
    });
};

// jquery functions to add/update database records
$(document).ready(function() {
    // Toggle left panel on mobile/tablet
    $('#toggleLeftPanel').on('click', function() {
        $('.left-panel').toggleClass('show');
        $('#leftPanelBackdrop').toggleClass('show');
        $(this).find('i').toggleClass('fa-bars fa-times');
    });
    
    // Close left panel when clicking backdrop
    $('#leftPanelBackdrop').on('click', function() {
        $('.left-panel').removeClass('show');
        $(this).removeClass('show');
        $('#toggleLeftPanel').find('i').removeClass('fa-times').addClass('fa-bars');
    });
    
    // Close left panel when clicking on a composition (mobile only)
    $(document).on('click', '.left-panel .list-group-item', function() {
        if ($(window).width() <= 991.98) {
            $('.left-panel').removeClass('show');
            $('#leftPanelBackdrop').removeClass('show');
            $('#toggleLeftPanel').find('i').removeClass('fa-times').addClass('fa-bars');
        }
    });
    
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

    // Reset button state when modal is hidden/dismissed
    $('#editModal').on('hidden.bs.modal', function () {
        $('#insert .spinner-border').addClass('d-none');
        $('#insert-text').text("Insert");
        $('#insert').prop('disabled', false);
        // Clear any error messages
        $('#parteditmessage').html('');
    });

    let catalog_number = null;
    let id_part_type = null;
    let allCompositions = []; // Store all compositions for filtering

    // Load compositions data into the left-hand list
    $.ajax({
    url:"index.php?action=fetch_parts_data",
    method:"POST",
    data:{},
        dataType:"text",
        success:function(response){
            try {
                var data = JSON.parse(response);
                data.u_librarian = window.u_librarian; // Ensure template gets correct role
                
                // Store compositions data for filtering
                allCompositions = data.compositions;
                
                // Render all compositions initially
                renderCompositionsList(allCompositions);
                
            } catch(e) {
                console.error('JSON parsing error:', e);
                console.log('Response was:', response);
            }
            <?php
            if(isset($_POST["catalog_number"])){
                $catalog_select = "catno_".$_POST["catalog_number"];
                echo '    $("#'.$catalog_select.'").trigger("click");'.PHP_EOL;
            }
            ?>
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            console.log('Response text:', xhr.responseText);
        }
    });

    // Function to render compositions list
    function renderCompositionsList(compositions) {
        var compositionsHtml = '';
        compositions.forEach(function(comp) {
            comp.hasparts = comp.parts > 0;
            comp.u_librarian = window.u_librarian;
            compositionsHtml += renderTemplate('composition-list-template', comp);
        });
        $('#compositions_list').html('<ul class="list-group list-group-flush">' + compositionsHtml + '</ul>');
    }

    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Filter compositions based on search input
    function filterCompositions() {
        const searchTerm = $('#composition-search').val().toLowerCase().trim();
        
        if (!searchTerm) {
            // Show all compositions if search is empty
            renderCompositionsList(allCompositions);
            return;
        }
        
        // Filter compositions based on multiple fields
        const filteredCompositions = allCompositions.filter(function(comp) {
            return (
                comp.catalog_number.toLowerCase().includes(searchTerm) ||
                comp.title.toLowerCase().includes(searchTerm) ||
                comp.composer.toLowerCase().includes(searchTerm) ||
                (comp.arranger && comp.arranger.toLowerCase().includes(searchTerm))
            );
        });
        
        renderCompositionsList(filteredCompositions);
    }

    // Add search event listener with debouncing
    $('#composition-search').on('input', debounce(filterCompositions, 300));
    // Handle click on composition list items, Load parts for the selected composition
    $(document).on('click','.list-group-item-action',function(e) {
        // Highlight the selected item
        $('.list-group-item-action').removeClass('active');
        $(this).addClass('active');
        // Reset the buttons
        $('#edit, #delete, #view').prop('disabled', true);
        // Get the catalog number from the clicked item
        var catno_select = $(this).attr("id");
        var catno_name = $(this).closest('li').text();
        const regex = /^catno_/i
        catalog_number = catno_select.replace(regex, '');
        e.preventDefault();
        $.ajax({
            url: "index.php?action=fetch_parts_data",
            method: "POST",
            data: {
                catalog_number: catalog_number
            },
            dataType: "text",
            success: function(response) {
                var data = JSON.parse(response);
                data.u_librarian = window.u_librarian; // Ensure template gets correct role
                if (data.parts && Array.isArray(data.parts)) {
                    data.parts.forEach(function(part) {
                        part.u_librarian = data.u_librarian;
                    });
                }
                var partsHtml = renderTemplate('parts-table-template', data);
                $('#parts_table').html(partsHtml);
                $('#composition_header').text(catno_name).attr('title', catno_name);
            }
        });
    });
    $(document).on('click', '#partsdatatable tbody tr', function(){
        $(this).find('input[type="radio"]').prop('checked',true);
        $('#edit, #delete, #view, #instrumentation').prop('disabled',false);
        let selectedRow = $(this).data('id'); // data-id attribute is on the row
        catalog_number = selectedRow.split('-')[0];
        id_part_type = selectedRow.split('-')[1];
    });
    $("#add_instrument").click(function() {
        $("#id_instrument_list :selected").each(function(){
            optionval = $(this).val();
            optiontext = instrumentdata[optionval];
            $('#id_instrument').append($('<option/>',{
               value: optionval,
               text: optiontext
            }));
        });
    });
    $('#remove_instrument').click(function() {
        $("#id_instrument :selected").each(function(){
            $(this).remove();
        });
    });
    $(document).on('click', '.add_data', function() {
        $('#insert-text').text("Insert");
        $('#update').val("add");
        $('#insert_form')[0].reset();
        // Clear any previous error messages
        $('#parteditmessage').html('');

        $('#catalog_number').val(catalog_number);
        $('#catalog_number_hold').val(catalog_number);
        var selectitems = '';
        $.each(instrumentdataArray, function(key, value) {
            selectitems += '<option value=' + value.id + '>' + value.name + '</option>';
            $(".instrument_" + value.id).text(value.name);
        });
        $('#id_instrument_list').html(selectitems);
        // Clear the selected instrument list
        $('#id_instrument').val('');
        selectitems = '';
    });
    $(document).on('click', '.instrumentation_btn', function(){
        if(catalog_number != '')
        {
            // Create a form and submit it with POST to composition_instrumentation.php
            var form = $('<form></form>');
            form.attr('method', 'post');
            form.attr('action', '/composition_instrumentation');
            form.append('<input type="hidden" name="catalog_number" value="' + catalog_number + '" />');
            $('body').append(form);
            form.submit();
        }
    });
    $(document).on('click', '.edit_data', function() {
        // These are set in the data-id attribute of the row
        // Clear the form
        $('#insert_form')[0].reset();
        $.ajax({
            url: "index.php?action=fetch_parts_data",
            method: "POST",
            data: {
                id_part_type: id_part_type,
                catalog_number: catalog_number
            },
            dataType: "json",
            success: function(result) {
                try {
                    const data = JSON.parse(result);
                    data.u_librarian = window.u_librarian; // Ensure template gets correct role

                    if (!data || !data.part ) {
                        console.error("Invalid part data structure");
                        alert("Error: Invalid part data structure");
                        return;
                    }

                    var part = data.part;
                    var inst_options = data.instruments;
                    var selectitems = '';

                    $.each(instrumentdataArray, function(key, value) {
                        selectitems += '<option value=' + value.id + '>' + value.name + '</option>';
                        $(".instrument_" + value.id).text(value.name);
                    });
                    $('#id_instrument_list').html(selectitems);

                    selectitems = '';
                    $.each(inst_options, function(key, value) {
                        selectitems += '<option value=' + value.id_instrument_key + '>' + instrumentdata[value.id_instrument_key] + '</option>';
                    });
                    if(selectitems == '' && part.default_instrument !== null ) {
                        selectitems += '<option value=' + part.default_instrument + '>' + instrumentdata[part.default_instrument] + '*</option>';
                    }
                    $('#id_instrument').html(selectitems);

                    // Set items in the id_instrument_list as selected if they are in the inst_options or part.default_instrument
                    if (part.default_instrument) {
                        $('#id_instrument_list option[value="' + part.default_instrument + '"]').prop('selected', true);
                    }
                    if (inst_options && inst_options.length > 0) {
                        inst_options.forEach(function(instrument) {
                            $('#id_instrument_list option[value="' + instrument.id_instrument_key + '"]').prop('selected', true);
                        });
                    }
                    $('#catalog_number').val(part.catalog_number);
                    $('#catalog_number_hold').val(part.catalog_number);
                    $('#id_part_type').val(part.id_part_type);
                    $('#id_part_type_hold').val(part.id_part_type);
                    $('#name').val(part.name);
                    $('#description').val(part.description);
                    $('#is_part_collection').val(part.is_part_collection);
                    $('#paper_size').val(part.paper_size);
                    $('#page_count').val(part.page_count);
                    $('#originals_count').val(part.originals_count);
                    $('#copies_count').val(part.copies_count);
                    $('#image_path_display').text(part.image_path);
                    $('#insert-text').text("Update");
                    $('#update').val("update");
                    // Clear any previous error messages
                    $('#parteditmessage').html('');
                    $('#editModal').modal('show');
                } catch(e) {
                    console.error("JSON parsing error:", e);
                    console.log("Response was:", result);
                    alert("Error parsing server response");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.log("Response text:", xhr.responseText);
                alert("Error fetching part data");
            }
        });
    });
    $(document).on('click', '.delete_data', function() { // button that brings up Delete this part? modal
        // Collected from data-id attributes
        var part_id = catalog_number + '-' + id_part_type;
        var part_name = instrumentdata[id_part_type];

        $('#deleteModal').modal('show');
        $('#confirm-delete').data('id', part_id);
        $('#part2delete').text(part_name);
    });
    $('#confirm-delete').click(function() {
        catno_name = $('#composition_header').text();
        // The confirm delete button
        var part_id = $(this).data('id');
        $.ajax({
            url: "index.php?action=delete_parts",
            method: "POST",
            data: {
                catalog_number: catalog_number,
                id_part_type: id_part_type
            },
            success: function(data) {
                $('#insert_form')[0].reset();
                $.ajax({
                    url: "index.php?action=fetch_parts_data",
                    method: "POST",
                    data: {
                        catalog_number: catalog_number
                    },
                    dataType: "text",
                        success: function(response) {
                            var data = JSON.parse(response);
                            data.u_librarian = window.u_librarian; // Ensure template gets correct role
                            if (data.parts && Array.isArray(data.parts)) {
                                data.parts.forEach(function(part) {
                                    part.u_librarian = data.u_librarian;
                                });
                            }
                            var partsHtml = renderTemplate('parts-table-template', data);
                            $('#parts_table').html(partsHtml);
                            $('#composition_header').text(catno_name).attr('title', catno_name);
                        }
                });
            }
        });
    });
    $(document).on('click', '.view_data', function() {
        //var part_id = $(this).attr("id");
        //var catalog_number = part_id.split('-')[0];
        //var id_part_type = part_id.split('-')[1];
        if (id_part_type != '') {
            $.ajax({
                url: "index.php?action=select_parts",
                method: "POST",
                data: {
                    id_part_type: id_part_type,
                    catalog_number: catalog_number
                },
                success: function(data) {
                    $('#part_detail').html(data);
                    $('#dataModal').modal('show');
                }
            });
        }
    });
    $(document).on('click', '.view_part_link', function(e) {
        e.preventDefault(); // Prevent default link behavior
        // Get the data from the row containing this link
        // Don't need clicked_id, we can get the id from the row
        var row = $(this).closest('tr');
        var selectedRow = row.data('id');
        var temp_catalog_number = selectedRow.split('-')[0];
        var temp_id_part_type = selectedRow.split('-')[1];
        
        // Select the radio button for this row
        row.find('input[type="radio"]').prop('checked', true);
        $('#edit, #delete, #view').prop('disabled', false);
        
        // Update global variables
        catalog_number = temp_catalog_number;
        id_part_type = temp_id_part_type;
        
        // Open the modal
        $.ajax({
            url: "index.php?action=select_parts",
            method: "POST",
            data: {
                id_part_type: temp_id_part_type,
                catalog_number: temp_catalog_number
            },
            success: function(data) {
                $('#part_detail').html(data);
                $('#dataModal').modal('show');
            }
        });
    });
    // Insert or update part data
    // This is the form submit handler for the insert_form
    $('#insert_form').on("submit", function(event) {
        catno_name = $('#composition_header').text();
        event.preventDefault();
        if ($('#id_part_type').val() == "") {
            alert("Part type ID is required");
        } else if ($('#catalog_number').val() == '') {
            alert("Catalog number is required");
        } else {
            catalog_number = $('#catalog_number').val();
            $('#id_instrument option').prop('selected',true);
            // If there is a file to upload in the image_path field, we need to handle it
            var formData = new FormData(this);

            if (!$('#image_path')[0].files.length)  {
                // If no file is selected, we can still submit the form
                // Use the value of image_path_display as a fallback
                $image_path_display = $('#image_path_display').text();
                formData.append('image_path_display', $image_path_display);
                console.log("No file selected for upload, using image_path_display value: " + $image_path_display);
                // Clear the file input to avoid sending an empty file path
                $('#image_path').val('');
            } else if ($('#image_path')[0].files.length > 0) {
                // If a file is selected, it will be handled by FormData
                $file_name = $('#image_path')[0].files[0].name;
                formData.append('image_path', $('#image_path')[0].files[0]);
            }

            $.ajax({
                url: "index.php?action=insert_parts",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function() {
                    // Clear any previous error messages
                    $('#parteditmessage').html('');
                    // Show spinner and disable button
                    $('#insert .spinner-border').removeClass('d-none');
                    $('#insert-text').text($('#update').val() === "update" ? "Updating..." : "Inserting...");
                    $('#insert').prop('disabled', true);
                },
                    success: function(data) {
                        // Reset button state
                        $('#insert .spinner-border').addClass('d-none');
                        $('#insert-text').text("Insert");
                        $('#insert').prop('disabled', false);
                        
                        if (data.success) {
                            // Show success message briefly
                            var action = $('#update').val() === "update" ? "updated" : "inserted";
                            $('#parteditmessage').html('<span class="text-success"><strong>Success:</strong> Part ' + action + ' successfully!</span>');
                            
                            // Hide the success message and close modal after 1.5 seconds
                            setTimeout(function() {
                                $('#parteditmessage').html('');
                                $('#editModal').modal('hide');
                            }, 1500);
                            
                            $('#insert_form')[0].reset();
                            $.ajax({
                                url: "index.php?action=fetch_parts_data",
                                method: "POST",
                                data: {
                                    catalog_number: catalog_number
                                },
                                dataType: "text",
                                success: function(response) {
                                    var data = JSON.parse(response);
                                    data.u_librarian = window.u_librarian; // Ensure template gets correct role
                                    var partsHtml = renderTemplate('parts-table-template', data);
                                    $('#parts_table').html(partsHtml);
                                    $('#composition_header').text(catno_name).attr('title', catno_name);
                                }
                            });
                        } else {
                            // Show error message (don't close modal)
                            $('#parteditmessage').html('<span class="text-danger"><strong>Error:</strong> ' + (data.error || 'Unknown error occurred') + '</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Reset button state on error
                        $('#insert .spinner-border').addClass('d-none');
                        $('#insert-text').text("Insert");
                        $('#insert').prop('disabled', false);
                        
                        // Try to parse JSON error response first
                        var errorMessage = "Error uploading part: " + error;
                        if (xhr.responseText) {
                            try {
                                var errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.error) {
                                    errorMessage = errorResponse.error;
                                } else {
                                    errorMessage = xhr.responseText;
                                }
                            } catch (e) {
                                errorMessage = xhr.responseText;
                            }
                        }
                        
                        // Display error message in the modal
                        $('#parteditmessage').html('<span class="text-danger"><strong>Error:</strong> ' + errorMessage + '</span>');
                        
                        // Scroll the modal to show the error message
                        $('#editModal .modal-body').scrollTop(0);
                    }
            });
        }
    });
});
</script>
</body>
</html>