<?php
define('PAGE_TITLE', 'Enable/Disable Manager');
define('PAGE_NAME', 'Enable/Disable Manager');

// Check user permissions
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}

// Only allow librarians to access this page
if (!$u_librarian) {
    header("Location: index.php");
    exit();
}

require_once(__DIR__. "/includes/header.php");
require_once(__DIR__. "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__. "/includes/functions.php");

// Define tables with enabled columns
$enabled_tables = [
    'compositions' => [
        'name' => 'Compositions',
        'id_field' => 'catalog_number',
        'display_fields' => ['catalog_number', 'name', 'composer'],
        'display_names' => ['Catalog #', 'Name', 'Composer'],
        'icon' => 'fas fa-music'
    ],
    'ensembles' => [
        'name' => 'Ensembles',
        'id_field' => 'id_ensemble',
        'display_fields' => ['name', 'description'],
        'display_names' => ['Name', 'Description'],
        'icon' => 'fas fa-users'
    ],
    'genres' => [
        'name' => 'Genres',
        'id_field' => 'id_genre',
        'display_fields' => ['name', 'description'],
        'display_names' => ['Name', 'Description'],
        'icon' => 'fas fa-tags'
    ],
    'instruments' => [
        'name' => 'Instruments',
        'id_field' => 'id_instrument',
        'display_fields' => ['name', 'family', 'description'],
        'display_names' => ['Name', 'Family', 'Description'],
        'icon' => 'fas fa-drum'
    ],
    'paper_sizes' => [
        'name' => 'Paper Sizes',
        'id_field' => 'id_paper_size',
        'display_fields' => ['name', 'description'],
        'display_names' => ['Name', 'Description'],
        'icon' => 'fas fa-file'
    ],
    'part_types' => [
        'name' => 'Part Types',
        'id_field' => 'id_part_type',
        'display_fields' => ['name', 'family', 'description'],
        'display_names' => ['Name', 'Family', 'Description'],
        'icon' => 'fas fa-list'
    ],
    'playgrams' => [
        'name' => 'Playgrams',
        'id_field' => 'id_playgram',
        'display_fields' => ['name', 'description'],
        'display_names' => ['Name', 'Description'],
        'icon' => 'fas fa-calendar-alt'
    ],
    'recordings' => [
        'name' => 'Recordings',
        'id_field' => 'id_recording',
        'display_fields' => ['name', 'composer', 'ensemble'],
        'display_names' => ['Name', 'Composer', 'Ensemble'],
        'icon' => 'fas fa-microphone'
    ],
    'sections' => [
        'name' => 'Sections',
        'id_field' => 'id_section',
        'display_fields' => ['name', 'description'],
        'display_names' => ['Name', 'Description'],
        'icon' => 'fas fa-sitemap'
    ]
];

$selected_table = isset($_GET['table']) ? $_GET['table'] : '';
$table_data = [];

// Get database connection
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($selected_table && isset($enabled_tables[$selected_table])) {
    $table_config = $enabled_tables[$selected_table];
    $fields = implode(', ', $table_config['display_fields']);
    $sql = "SELECT {$table_config['id_field']}, {$fields}, enabled 
            FROM {$selected_table} 
            ORDER BY {$table_config['display_fields'][0]}";
    
    $res = mysqli_query($f_link, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $table_data[] = $row;
        }
    }
    ferror_log("Returned ". mysqli_num_rows($res)." rows for table " . $selected_table);
}

mysqli_close($f_link);
?>

<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><i class="fas fa-toggle-on"></i> <?php echo ORGNAME; ?> Enable/Disable Manager</h1>
                <p class="lead">Manage the enabled status of items across all database tables</p>
            </div>
        </div>

        <!-- Table Selection -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-table"></i> Choose a table to manage</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($enabled_tables as $table_key => $table_info): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="?table=<?php echo $table_key; ?>" 
                                   class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center <?php echo $selected_table === $table_key ? 'active' : ''; ?>">
                                    <i class="<?php echo $table_info['icon']; ?> fa-2x mb-2"></i>
                                    <span><?php echo $table_info['name']; ?></span>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($selected_table && !empty($table_data)): ?>
        <!-- Selected Table Data -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="<?php echo $enabled_tables[$selected_table]['icon']; ?>"></i> Manage
                            <?php echo $enabled_tables[$selected_table]['name']; ?></h5>
                        <div>
                            <button type="button" class="btn btn-success btn-sm" id="enableAll">
                                <i class="fas fa-check-circle"></i> Enable All
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="disableAll">
                                <i class="fas fa-times-circle"></i> Disable All
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="saveChanges">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="enableTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="80">Status</th>
                                        <?php foreach ($enabled_tables[$selected_table]['display_names'] as $name): ?>
                                        <th><?php echo $name; ?></th>
                                        <?php endforeach; ?>
                                        <th width="100">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table_data as $row): ?>
                                    <tr data-id="<?php echo htmlspecialchars($row[$enabled_tables[$selected_table]['id_field']]); ?>">
                                        <td class="text-center">
                                            <span class="status-badge">
                                                <?php if ($row['enabled'] == 1): ?>
                                                <span class="badge bg-success">Enabled</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Disabled</span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <?php foreach ($enabled_tables[$selected_table]['display_fields'] as $field): ?>
                                        <td><?php echo htmlspecialchars($row[$field] ?? ''); ?></td>
                                        <?php endforeach; ?>
                                        <td class="text-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input enable-toggle" type="checkbox" 
                                                       <?php echo $row['enabled'] == 1 ? 'checked' : ''; ?>
                                                       data-id="<?php echo htmlspecialchars($row[$enabled_tables[$selected_table]['id_field']]); ?>"
                                                       data-original="<?php echo $row['enabled']; ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> Disabling items will hide them from most searches and dropdowns, 
                                but they will remain in the database. This is useful for temporarily hiding items you 
                                don't frequently use without permanently deleting them.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($selected_table): ?>
        <!-- No Data Found -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No data found in the selected table.
                </div>
            </div>
        </div>
        
        <?php endif; ?>

        <!-- Summary Statistics -->
        <?php if ($selected_table && !empty($table_data)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-primary"><?php echo count($table_data); ?></h4>
                                <small class="text-muted">Total Items</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-success"><?php echo count(array_filter($table_data, function($item) { return $item['enabled'] == 1; })); ?></h4>
                                <small class="text-muted">Enabled</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-secondary"><?php echo count(array_filter($table_data, function($item) { return $item['enabled'] == 0; })); ?></h4>
                                <small class="text-muted">Disabled</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info" id="changedCount">0</h4>
                                <small class="text-muted">Pending Changes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once(__DIR__. "/includes/footer.php"); ?>

<script>
$(document).ready(function() {
    var selectedTable = '<?php echo $selected_table; ?>';
    var changedItems = new Set();
    
    // Track changes
    $('.enable-toggle').on('change', function() {
        var checkbox = $(this);
        var itemId = checkbox.data('id');
        var originalValue = checkbox.data('original');
        var currentValue = checkbox.is(':checked') ? 1 : 0;
        var row = checkbox.closest('tr');
        
        // Update visual status
        var statusBadge = row.find('.status-badge');
        if (currentValue == 1) {
            statusBadge.html('<span class="badge bg-success">Enabled</span>');
        } else {
            statusBadge.html('<span class="badge bg-secondary">Disabled</span>');
        }
        
        // Track changes
        if (currentValue != originalValue) {
            changedItems.add(itemId);
            row.addClass('table-warning');
        } else {
            changedItems.delete(itemId);
            row.removeClass('table-warning');
        }
        
        updateChangedCount();
    });
    
    // Enable all
    $('#enableAll').on('click', function() {
        $('.enable-toggle').prop('checked', true).trigger('change');
    });
    
    // Disable all
    $('#disableAll').on('click', function() {
        $('.enable-toggle').prop('checked', false).trigger('change');
    });
    
    // Save changes
    $('#saveChanges').on('click', function() {
        if (changedItems.size === 0) {
            alert('No changes to save.');
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        var changes = [];
        $('.enable-toggle').each(function() {
            var checkbox = $(this);
            var itemId = checkbox.data('id');
            if (changedItems.has(itemId)) {
                changes.push({
                    id: itemId,
                    enabled: checkbox.is(':checked') ? 1 : 0
                });
            }
        });
        
        $.ajax({
            url: 'index.php?action=update_enabled_status',
            type: 'POST',
            data: {
                table: selectedTable,
                changes: JSON.stringify(changes)
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    // Update original values and clear changes
                    $('.enable-toggle').each(function() {
                        var checkbox = $(this);
                        checkbox.data('original', checkbox.is(':checked') ? 1 : 0);
                        checkbox.closest('tr').removeClass('table-warning');
                    });
                    changedItems.clear();
                    updateChangedCount();
                    
                    // Show success message
                    showAlert('success', 'Changes saved successfully!');
                } else {
                    showAlert('danger', 'Error saving changes: ' + result.message);
                }
            },
            error: function() {
                showAlert('danger', 'Network error occurred while saving changes.');
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            }
        });
    });
    
    function updateChangedCount() {
        $('#changedCount').text(changedItems.size);
    }
    
    function showAlert(type, message) {
        var alertDiv = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>');
        
        $('.container .row:first').after('<div class="row"><div class="col-12 mt-3"></div></div>');
        $('.container .row:nth-child(2) .col-12').append(alertDiv);
        
        setTimeout(function() {
            alertDiv.alert('close');
        }, 5000);
    }
});
</script>

</body>
</html>
