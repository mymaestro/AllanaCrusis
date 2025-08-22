<?php  
 //delete_playgrams.php
 // Deletes a playgram and its items
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

ferror_log("Running delete_playgrams.php with id=". $_POST["id_playgram"]);
header('Content-Type: application/json');
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

function deleteItemsWithSTMT($link, $sql, $id, $label) {
    try {
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);  // i=integer, d=double, s=string, b=BLOB
        mysqli_stmt_execute($stmt);
        ferror_log("Deleted " . $label . " with id: " . $id);
        return ['success' => true, 'message' => $label . ' deleted successfully.'];
    } catch (mysqli_sql_exception $e) {
        $error_code = $e->getCode();
        $error_message = $e->getMessage();
        ferror_log("Error deleting " . $label . " with id: " . $id . ". Error code: " . $error_code . ". Message: " . $error_message);
        return ['success' => false, 'error' => 'Failed to delete ' . $label . ': ' . $error_message];
    }
}

if (isset($_POST["id_playgram"])) {
    $id_playgram = mysqli_real_escape_string($f_link, $_POST["id_playgram"]);
    $results = [];
    ferror_log("Deleting playgram items with playgram id: " . $id_playgram);
    // First delete all items associated with this playgram
    $results[] = deleteItemsWithSTMT($f_link, "DELETE FROM playgram_items WHERE id_playgram = ?", $id_playgram, "playgram_items");
    // Then delete the playgram itself
    ferror_log("Deleting playgram with id: " . $id_playgram);
    // Note: This assumes that the playgram table has a foreign key constraint that prevents deletion
    $results[] = deleteItemsWithSTMT($f_link, "DELETE FROM playgrams WHERE id_playgram = ?", $id_playgram, "playgrams");

    // If any failed, return the first error, else success
    foreach ($results as $res) {
        if (!$res['success']) {
            echo json_encode($res);
            exit;
        }
    }
    echo json_encode(['success' => true, 'message' => "Playgram and items deleted"]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'No id_playgram provided']);
?>
