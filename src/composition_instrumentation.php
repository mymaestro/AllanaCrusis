<?php
define('PAGE_TITLE', 'Manage instrumentation');
define('PAGE_NAME', 'Manage instrumentation');
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
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");

ferror_log("composition_instrumentation.php POST: " . print_r($_POST, TRUE));
// NEED TO HANDLE GETS

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['catalog_number'])) {
    $catalog_number = $_POST['catalog_number'];
    // Handle the POST logic (e.g., update instrumentation)
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['catalog_number'])) {
    $catalog_number = $_GET['catalog_number'];
    // Handle the GET logic (e.g., display instrumentation)
} else {
    $catalog_number = '';
    // Show a default page or error
}

ferror_log("Running composition_instrumentation.php with catalog_number: " . $catalog_number);

// Ways to get here:
// 1. Directly from the menu. Select Composition by name, enter default parts, click submit (need form validation)
// 2. From the compositions table. Same as 1, but with Catalog Number preselected
// 3. Edit mode... same as 2, but read the parts table and pre-populate the parts selection
// 4. User mode: Like 3 but view only

// Fill the parts table with information from the rows in this form

// It is expected that all the instrumentation (parts) for a composition will by default have the same
// catalog_number, paper_size, and page_count
// The code inserts a  
?>
<main role="main" class="container">
    <div class="container">
        <h1><?php echo ORGNAME . ' '. PAGE_NAME ?></h1>
        <?php if($u_librarian) : ?>
        <div id="instrumentation">
            <form action="index.php?action=insert_instrumentation" method="post" id="instrumentation_form">
                <div class="row mb-3">
                    <div class="col-sm-2 col-form-label">
                        <label for="catalog_number" class="form-label">Catalog number*</label>
                    </div>
                    <div class="col-md-4">
                        <!-- Choose from a composition in the database -->
                        <!-- Unless one is already provided in the _POST('catalog_number') -->
                        <!-- check if we got here by instr button in list_compositions -->
                    <?php
                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                        if(!empty($catalog_number)) $catalog_number = mysqli_real_escape_string($f_link, $catalog_number);
                        // Clicked to get here
                        // $_POST catalog_number=C123
                        // $_GET catalog_number=C123
                        // $_POST compositions=Instrumentation
                        if(!empty($catalog_number)) {
                            $sql = "SELECT `name` FROM compositions WHERE `catalog_number` = '".$catalog_number."' ORDER BY name;";
                            $opt = '<input type="hidden" name="catalog_number" id="catalog_number" value="'.$catalog_number.'" />';
                            $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                            while ($rowList = mysqli_fetch_array($res)) {
                                $comp_name = $rowList['name'];
                                $opt .= $catalog_number . " - " .$comp_name;
                            }
                            mysqli_close($f_link);
                        } else {
                            // User came here from the menu or by typing the URL
                            $sql = "SELECT `catalog_number`, `name`, `composer`,`arranger` FROM compositions ORDER BY name;";
                            //ferror_log("Running " . $sql);
                            $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                            $opt = "<select class='form-select form-control' aria-label='Select composition' id='catalog_number' name='catalog_number' aria-describedby='catalog_numberHelp'>";
                            while ($rowList = mysqli_fetch_array($res)) {
                                $comp_catno = $rowList['catalog_number'];
                                $comp_name = $rowList['name'];
                                $comp_composer = $rowList['composer'];
                                $comp_arranger = $rowList['arranger'];
                                $comp_display = $comp_name;
                                if (("$comp_composer" <> "" ) || ("$comp_arranger" <> "")) $comp_display .= ' (';
                                if (("$comp_composer" <> "" ) && ("$comp_arranger" <> "")) $comp_display .= $comp_composer . ", arr. " . $comp_arranger . ")";
                                if (("$comp_composer" == "" ) && ("$comp_arranger" <> "")) $comp_display .= "arr. " . $comp_arranger . ")";
                                if (("$comp_composer" <> "" ) && ("$comp_arranger" == "")) $comp_display .=  $comp_composer . ")";
                                $opt .= "<option value='" . $comp_catno . "'>" . $comp_display . "</option>".PHP_EOL;
                            }
                            $opt .= "</select>";
                            mysqli_close($f_link);
                        }
                        echo $opt;
                        //ferror_log("returned: " . $sql);
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <div id="catalog_numberHelp" class="form-text">The catalog number from the composition to set instrumentation.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-2 col-form-label">
                        <label for="paper_size" class="form-label">Paper size*</label>
                    </div>
                    <div class="col-sm-4">
                    <?php
                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                        $sql = "SELECT `id_paper_size`, `name` FROM paper_sizes WHERE `enabled` = 1 ORDER BY name;";
                        //ferror_log("Running " . $sql);
                        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                        $opt = "<select class='form-select form-control' aria-label='Select paper size' id='paper_size' name='paper_size' aria-describedby='paper_sizeHelp'>";
                        while ($rowList = mysqli_fetch_array($res)) {
                            $id_paper_size = $rowList['id_paper_size'];
                            $paper_size_name = $rowList['name'];
                            $opt .= "<option value='" . $id_paper_size . "'>" . $paper_size_name . "</option>";
                        }
                        $opt .= "</select>";
                        mysqli_close($f_link);
                        echo $opt;
                        //ferror_log("returned: " . $sql);
                    ?>
                    </div>
                    <div class="col-sm-6">
                        <div id="paper_sizeHelp" class="form-text">Default paper size for new parts only. Existing parts retain their current paper size.</div>                
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-2 col-form-label">
                        <label for="page_count" class="form-label">Page count*</label>
                    </div>
                    <div class="col-sm-4">
                        <input type="number" class="form-control" id="page_count" name="page_count" placeholder="0" aria-describedby="page_countHelp" required>
                    </div>
                    <div class="col-sm-6">
                        <div id="page_countHelp" class="form-text">Default page count for new parts only. Existing parts retain their current page count.</div>                
                    </div>
                </div>

                <div class="alert alert-info" role="alert" style="margin-bottom: 1rem;">
                    <strong>Reminder:</strong> Set the <b>page size</b> and <b>default number of pages</b> for parts every time you use this page. This ensures all new parts are created with the correct settings.
                </div>
                <hr />
                <div class="row mb-3">
                    <div class="col-form-label col-sm-2 pt-0">Instrument parts*</div>
                    <div class="col-sm-10 offset-sm-2">
                        <p>Select multiple parts by holding the Shift or Ctrl keys while clicking.</p>
                        <p><strong>Important:</strong> The parts list will be synchronized to match your selection. New parts will be added, unselected parts will be removed, and existing selected parts will be kept with their customizations preserved.</p>
                        <p>If a type of part does not appear on the list, check the <a href="/parttypes">Part types</a> page.</p>
                    <!-- Read part types from part_types table -->
                    <?php
                        $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                        $sql = "SELECT `id_part_type`, `name` FROM part_types WHERE `enabled` = 1 ORDER BY collation;";
                        $rowcount = 0;
                        ferror_log("Running " . $sql);
                        $res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
                        $opt = '<select class="form-select form-control" aria-label="Select part types" id="parttypes" name="parttypes[]" size="17" multiple>';
                        while ($rowList = mysqli_fetch_array($res)) {
                            $rowcount++;
                            $id_part_type = $rowList['id_part_type'];
                            $part_type_name = $rowList['name'];
                        //  $selected = (in_array($id_part_type, $parts_included)) ? ' selected' : '';
                            $opt .= "<option value='".$id_part_type . "'>" . $part_type_name . "</option>";
                        }
                        $opt .= "</select>";
                        mysqli_close($f_link);
                        echo $opt;
                        //ferror_log("returned: " . $sql);
                    ?>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <button class="btn btn-primary" type="submit" name="submit" value="add">Synchronize parts</button>
                        <button type="reset" class="btn btn-secondary" id="revertSelect">Cancel</button>
                        <a href="#" class="btn btn-link" role="button" onclick="goBack()">Back</a>
                        <a href="/compositions" class="btn btn-link" role="button">Compositions</a>
                        <a href="/parts" class="btn btn-link" role="button">Parts</a>
                    </div>
                </div>
            </form>
        </div><!-- instrumentation -->
    <?php else: ?>
    <div id="instrumentation_view">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    You do not have permission to view this page.
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    </div><!-- container -->
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
<?php if($u_librarian) : ?>
<!-- jquery function to add/update database records -->
<script>
function goBack() {
    if (document.referrer) {
        window.location = document.referrer;
    } else {
        window.history.back();
    }
}
$(document).ready(function(){
    var catalog_number = $("#catalog_number").val();
    $.ajax({
        url:"index.php?action=fetch_composition_parts",
        method:"POST",
        dataType: "json",
        data:{
            catalog_number: catalog_number, 
            user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
        },
        success:function(data){
            $.each(data, function(key, value) {
                $("select option[value='" + value + "']").attr("selected","selected");
            });
        }
    });
    $('#catalog_number').change(function() {
        var catalog_number = this.value;
        $.ajax({
            url:"index.php?action=fetch_composition_parts",
            method:"POST",
            dataType: "json",
            data:{
                catalog_number: catalog_number, 
                user_role: "<?php echo ($u_librarian) ? 'librarian' : 'nobody'; ?>"
            },
            success:function(data){
                $("#parttypes option:selected").prop("selected", false);
                $.each(data, function(key, value) {
                    $("#parttypes option[value='" + value + "']").attr("selected","selected");
                });
            }
        });
    });
    $('#add').click(function(){
        $('#insert').val("Insert");
        $('#update').val("add");
        $('#instrumentation_form')[0].reset();
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
            alert("Going for AJAX.");
            $.ajax({
                url:"index.php?action=insert_instrumentation",
                method:"POST",
                data:$('#insert_form').serialize(),
                beforeSend:function(){
                    $('#insert').val("Inserting");
                },
                success:function(data){
                    $('#insert_form')[0].reset();
                    $('#add_data_Modal').modal('hide');
                    $('#part_type_table').html(data);
                }
            });
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>
