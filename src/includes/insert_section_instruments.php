<?php
// index.php?action=insert_section_instruments
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("RUNNING insert_section_instruments.php with POST data: " . print_r($_POST, true));
$section_id = intval($_POST['section_id']);
$assigned = isset($_POST['assigned_instruments']) ? $_POST['assigned_instruments'] : [];

$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Remove all current assignments
mysqli_query($f_link, "DELETE FROM section_instruments WHERE id_section = $section_id");

// Add new assignments
if (!empty($assigned)) {
    foreach ($assigned as $id_instrument) {
        $id_instrument = intval($id_instrument);
        mysqli_query($f_link, "INSERT INTO section_instruments (id_section, id_instrument) VALUES ($section_id, $id_instrument)");
    }
}

mysqli_close($f_link);
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>