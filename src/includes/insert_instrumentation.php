<?php
define('PAGE_TITLE', 'Insert instrumentation');
define('PAGE_NAME', 'Insert instrumentation');
require_once(__DIR__ . "/header.php");
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/navbar.php");
require_once(__DIR__ . "/functions.php");
ferror_log("RUNNING insert_instrumentation.php with POST ". print_r($_POST, true));
?>
<main role="main">
    <div class="container">
        <h1><?php echo ORGNAME . ' ' . PAGE_NAME ?></h1>
        <?php if(!empty($_POST)) {
            $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (isset($_POST['catalog_number'])) $catalog_number = mysqli_real_escape_string($f_link, $_POST['catalog_number']);
            if (isset( $_POST['paper_size'])) $paper_size = mysqli_real_escape_string($f_link, $_POST['paper_size']);
            if (isset( $_POST['page_count'])) $page_count = mysqli_real_escape_string($f_link, $_POST['page_count']);
            // parttypes could/should be an array, that is handled below
            if (isset($_POST['parttypes'])) {
                if (!is_array($_POST['parttypes'])) {
                    $parttypes = mysqli_real_escape_string($f_link, $_POST['parttypes']);
                } else {
                    $parttypes = $_POST['parttypes'];
                }
            }
            echo '<h4>Parts synchronized to match your selection:</h4>';
            if($_POST["submit"] == "add"){
    
                // loop over part types selected in the dropdown <option>

                if(!empty($_POST['parttypes']) && isset($catalog_number) && isset($paper_size) && isset($page_count)) {
                    echo '<form action="/parts" method="POST"><input type="hidden" name="catalog_number" value="'.$catalog_number.'"><button class="btn btn-primary"/>Edit parts</button></form>';
                    
                    // Get current parts for this composition
                    $current_parts_sql = "SELECT id_part_type FROM parts WHERE catalog_number = ?";
                    $current_parts_stmt = mysqli_prepare($f_link, $current_parts_sql);
                    $current_parts = array();
                    
                    if ($current_parts_stmt) {
                        mysqli_stmt_bind_param($current_parts_stmt, "s", $catalog_number);
                        if (mysqli_stmt_execute($current_parts_stmt)) {
                            $result = mysqli_stmt_get_result($current_parts_stmt);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $current_parts[] = $row['id_part_type'];
                            }
                        }
                        mysqli_stmt_close($current_parts_stmt);
                    }
                    
                    // Convert form selection to array of integers for comparison
                    $selected_parts = array_map('intval', $_POST['parttypes']);
                    
                    // Find parts to delete (in current but not in selection)
                    $parts_to_delete = array_diff($current_parts, $selected_parts);
                    
                    // Find parts to add (in selection but not in current)
                    $parts_to_add = array_diff($selected_parts, $current_parts);
                    
                    ferror_log("Current parts: " . implode(", ", $current_parts));
                    ferror_log("Selected parts: " . implode(", ", $selected_parts));
                    ferror_log("Parts to delete: " . implode(", ", $parts_to_delete));
                    ferror_log("Parts to add: " . implode(", ", $parts_to_add));
                    
                    $output = '<table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Catalog number</th>
                            <th>Part type ID</th>
                            <th>Action</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';
                    
                    // Delete parts that are no longer selected
                    foreach ($parts_to_delete as $id_part_type) {
                        // First delete associated part_collections
                        $delete_collections_sql = "DELETE FROM part_collections WHERE catalog_number_key = ? AND id_part_type_key = ?";
                        $delete_collections_stmt = mysqli_prepare($f_link, $delete_collections_sql);
                        if ($delete_collections_stmt) {
                            mysqli_stmt_bind_param($delete_collections_stmt, "si", $catalog_number, $id_part_type);
                            mysqli_stmt_execute($delete_collections_stmt);
                            mysqli_stmt_close($delete_collections_stmt);
                        }
                        
                        // Then delete the part
                        $delete_part_sql = "DELETE FROM parts WHERE catalog_number = ? AND id_part_type = ?";
                        $delete_part_stmt = mysqli_prepare($f_link, $delete_part_sql);
                        if ($delete_part_stmt) {
                            mysqli_stmt_bind_param($delete_part_stmt, "si", $catalog_number, $id_part_type);
                            if (mysqli_stmt_execute($delete_part_stmt)) {
                                $output .= '<tr><td>' . $catalog_number . '</td><td>' . $id_part_type . '</td><td>Removed</td><td>';
                                $output .= '<span class="text-warning">Part ' . $catalog_number . '-' . $id_part_type . ' removed (no longer selected).</span></td></tr>';
                            }
                            mysqli_stmt_close($delete_part_stmt);
                        }
                    }
                    
                    $originals_count = 1;
                    $copies_count = 0;
                    
                    // Add new parts that were selected
                    foreach ($parts_to_add as $id_part_type) {
                        $name = "Part-".$catalog_number."-".$id_part_type;
                        $sql = "INSERT INTO parts(catalog_number, id_part_type, name, paper_size, page_count, originals_count, copies_count)
                        VALUES('$catalog_number', '$id_part_type', '$name', '$paper_size', $page_count, $originals_count, $copies_count);";
                        ferror_log("Running SQL ". $sql);
                        try {
                            if(mysqli_query($f_link, $sql)) {
                                $output .= '<tr><td>' . $catalog_number . '</td><td>' . $id_part_type . '</td><td>Added</td><td>';
                                $output .= '<span class="text-success">Part ' . $catalog_number . '-' . $id_part_type . ' added successfully.</span></td></tr>';
                            }
                        } catch (mysqli_sql_exception $e) {
                            $error_message = $e->getMessage();
                            $mysql_errno = $e->getCode();
                            
                            ferror_log("Error: " . $error_message . " (Error Code: " . $mysql_errno . ")");
                            
                            $output .= '<tr><td>' . $catalog_number . '</td><td>' . $id_part_type . '</td><td>Error</td><td>';
                            
                            // Check for specific error types
                            if ($mysql_errno == 1062) {
                                $output .= '<span class="text-danger">Duplicate Entry Error: Part ' . $catalog_number . '-' . $id_part_type . ' already exists.</span></td></tr>';
                            } else {
                                $output .= '<span class="text-danger">Insert failed. Error Code ' . $mysql_errno . ': ' . htmlspecialchars($error_message ?? '') . '</span></td></tr>';
                            }
                        }
                    }
                    
                    // Show kept parts (no action needed but inform user)
                    $parts_kept = array_intersect($current_parts, $selected_parts);
                    foreach ($parts_kept as $id_part_type) {
                        $output .= '<tr><td>' . $catalog_number . '</td><td>' . $id_part_type . '</td><td>Kept</td><td>';
                        $output .= '<span class="text-info">Part ' . $catalog_number . '-' . $id_part_type . ' kept (customizations preserved).</span></td></tr>';
                    }
                    $referred = $_SERVER['HTTP_REFERER'];
                    $query = parse_url($referred, PHP_URL_QUERY);
                    $referred = str_replace(array('?', $query), '', $referred);
                    echo $output;
                    echo '</tbody></table>';
                    echo '<form action="/parts" method="POST"><input type="hidden" name="catalog_number" value="'.$catalog_number.'"><button class="btn btn-outline-secondary" type="submit">Edit parts</button></form>';
                } // Part types array is not empty
            } // Submit function was "add"
            mysqli_close($f_link);
        }?>
</main>
</body>
<?php
require_once(__DIR__ . "/footer.php");
?>
