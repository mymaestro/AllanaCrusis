<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/functions.php");

// Check user permissions
$u_librarian = FALSE;
if (isset($_SESSION['username'])) {
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
}

// Only allow librarians to update enabled status
if (!$u_librarian) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$table = $_POST['table'] ?? '';
$changes = json_decode($_POST['changes'] ?? '[]', true);

if (empty($table) || empty($changes)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Define allowed tables and their ID fields for security
$allowed_tables = [
    'compositions' => 'catalog_number',
    'ensembles' => 'id_ensemble',
    'genres' => 'id_genre',
    'instruments' => 'id_instrument',
    'paper_sizes' => 'id_paper_size',
    'part_types' => 'id_part_type',
    'playgrams' => 'id_playgram',
    'recordings' => 'id_recording',
    'sections' => 'id_section'
];

if (!isset($allowed_tables[$table])) {
    echo json_encode(['success' => false, 'message' => 'Invalid table specified']);
    exit();
}

$id_field = $allowed_tables[$table];
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$f_link) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Start transaction
mysqli_begin_transaction($f_link);

try {
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($changes as $change) {
        $id = $change['id'];
        $enabled = $change['enabled'] ? 1 : 0;
        
        // Prepare the update statement
        $sql = "UPDATE `{$table}` SET enabled = ? WHERE `{$id_field}` = ?";
        $stmt = mysqli_prepare($f_link, $sql);
        
        if (!$stmt) {
            $errors[] = "Failed to prepare statement for ID: {$id}";
            $error_count++;
            continue;
        }
        
        // Bind parameters based on ID field type
        if ($id_field === 'username') {
            mysqli_stmt_bind_param($stmt, "is", $enabled, $id);
        } else {
            mysqli_stmt_bind_param($stmt, "ii", $enabled, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success_count++;
            ferror_log("Updated {$table}.{$id_field} = {$id}, enabled = {$enabled}");
        } else {
            $errors[] = "Failed to update ID: {$id} - " . mysqli_stmt_error($stmt);
            $error_count++;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    if ($error_count === 0) {
        // Commit transaction if all updates succeeded
        mysqli_commit($f_link);
        echo json_encode([
            'success' => true, 
            'message' => "Successfully updated {$success_count} items",
            'updated_count' => $success_count
        ]);
        ferror_log("Successfully updated {$success_count} items in table {$table}");
    } else {
        // Rollback transaction if there were errors
        mysqli_rollback($f_link);
        echo json_encode([
            'success' => false, 
            'message' => "Errors occurred: {$error_count} failed, {$success_count} would have succeeded",
            'errors' => $errors
        ]);
        ferror_log("Failed to update items in table {$table}: " . implode(', ', $errors));
    }
    
} catch (Exception $e) {
    mysqli_rollback($f_link);
    echo json_encode([
        'success' => false, 
        'message' => 'Transaction failed: ' . $e->getMessage()
    ]);
    ferror_log("Transaction failed for table {$table}: " . $e->getMessage());
}

mysqli_close($f_link);
?>
