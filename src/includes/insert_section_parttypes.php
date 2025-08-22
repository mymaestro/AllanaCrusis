<?php
// index.php?action=insert_section_parttypes
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("RUNNING insert_section_parttypes.php with POST data: " . print_r($_POST, true));
$section_id = intval($_POST['section_id']);
$assigned = isset($_POST['assigned_part_types']) ? $_POST['assigned_part_types'] : [];

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Remove all current assignments
mysqli_query($f_link, "DELETE FROM section_part_types WHERE id_section = $section_id");

// Add new assignments
if (!empty($assigned)) {
    foreach ($assigned as $id_part_type) {
        $id_part_type = intval($id_part_type);
        mysqli_query($f_link, "INSERT INTO section_part_types (id_section, id_part_type) VALUES ($section_id, $id_part_type)");
    }
}

mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
