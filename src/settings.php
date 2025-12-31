<?php
define('PAGE_TITLE', 'System Settings');
define('PAGE_NAME', 'Settings');
require_once(__DIR__. "/includes/header.php");

// Check admin role
$u_admin = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
}

if (!$u_admin) {
    echo "<div class='alert alert-danger'>Access Denied. Administrator role required.</div>";
    require_once(__DIR__. "/includes/footer.php");
    exit;
}

require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__. "/includes/navbar.php");
ferror_log("RUNNING settings.php");

// Get all config settings grouped by category
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT * FROM config ORDER BY category, `config_key`";
$result = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));

$configByCategory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $category = $row['category'] ?? 'Other';
    if (!isset($configByCategory[$category])) {
        $configByCategory[$category] = [];
    }
    $configByCategory[$category][] = $row;
}
mysqli_close($f_link);
?>

<main role="main">
    <div class="container pt-5">
        <div class="row pb-3 border-bottom">
            <div class="col">
                <h1><?php echo ORGNAME . ' - ' . PAGE_NAME; ?></h1>
                <p class="text-muted">Configure system settings and organization information</p>
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div id="settingsMessage" class="alert d-none" role="alert"></div>

                <ul class="nav nav-tabs" role="tablist">
                    <?php $isFirst = true; ?>
                    <?php foreach ($configByCategory as $category => $settings): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $isFirst ? 'active' : ''; ?>" 
                                    id="tab-<?php echo strtolower(str_replace(' ', '-', $category)); ?>" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#content-<?php echo strtolower(str_replace(' ', '-', $category)); ?>" 
                                    type="button" role="tab">
                                <?php echo htmlspecialchars($category); ?>
                            </button>
                        </li>
                        <?php $isFirst = false; ?>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content pt-4">
                    <?php $isFirst = true; ?>
                    <?php foreach ($configByCategory as $category => $settings): ?>
                        <div class="tab-pane fade <?php echo $isFirst ? 'show active' : ''; ?>" 
                             id="content-<?php echo strtolower(str_replace(' ', '-', $category)); ?>" 
                             role="tabpanel">
                            
                            <form class="settings-form" data-category="<?php echo htmlspecialchars($category); ?>">
                                <?php foreach ($settings as $setting): ?>
                                    <?php $isReadonly = (bool)$setting['is_readonly']; ?>
                                    <div class="mb-4 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label"><strong><?php echo htmlspecialchars($setting['config_key']); ?></strong></label>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($setting['description'] ?? ''); ?></small>
                                                <?php if (!empty($setting['usage'])): ?>
                                                    <small class="d-block mt-2 text-info"><strong>Usage:</strong> <?php echo htmlspecialchars($setting['usage']); ?></small>
                                                <?php endif; ?>
                                                <?php if ($isReadonly): ?>
                                                    <span class="badge bg-secondary mt-2">Read-only</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-8">
                                                <?php if ($setting['type'] === 'boolean'): ?>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input config-input" 
                                                               type="checkbox" 
                                                               id="config-<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                               data-key="<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                               data-type="boolean"
                                                               <?php echo ((int)$setting['value'] === 1) ? 'checked' : ''; ?>
                                                               <?php echo $isReadonly ? 'disabled' : ''; ?> />
                                                        <label class="form-check-label" for="config-<?php echo htmlspecialchars($setting['config_key']); ?>">
                                                            <?php echo $setting['value'] == 1 ? 'Enabled' : 'Disabled'; ?>
                                                        </label>
                                                    </div>
                                                <?php elseif ($setting['type'] === 'integer'): ?>
                                                    <input type="number" 
                                                           class="form-control config-input" 
                                                           id="config-<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                           data-key="<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                           data-type="integer"
                                                           value="<?php echo htmlspecialchars($setting['value']); ?>"
                                                           <?php echo $isReadonly ? 'disabled' : ''; ?> />
                                                <?php else: ?>
                                                    <textarea class="form-control config-input" 
                                                              id="config-<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                              data-key="<?php echo htmlspecialchars($setting['config_key']); ?>"
                                                              data-type="<?php echo htmlspecialchars($setting['type']); ?>"
                                                              rows="2"
                                                              <?php echo $isReadonly ? 'disabled' : ''; ?>><?php echo htmlspecialchars($setting['value']); ?></textarea>
                                                <?php endif; ?>
                                                <small class="text-muted d-block mt-2">Default: <?php echo htmlspecialchars($setting['default_value'] ?? 'N/A'); ?></small>
                                                <small class="text-muted d-block">Last updated: <?php echo htmlspecialchars($setting['updated_at']); ?> by <?php echo htmlspecialchars($setting['updated_by'] ?? 'unknown'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php $hasEditableSettings = array_filter($settings, fn($s) => !$s['is_readonly']); ?>
                                <?php if (!empty($hasEditableSettings)): ?>
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary save-category-settings">
                                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                                Save Settings
                                            </button>
                                            <button type="reset" class="btn btn-secondary ms-2">Reset</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                        <?php $isFirst = false; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once(__DIR__. "/includes/footer.php");?>

<script>
$(document).ready(function() {
    // Handle save button click
    $('.save-category-settings').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $form = $button.closest('form');
        var $message = $('#settingsMessage');
        var configData = {};
        
        // Collect all changed settings
        $form.find('.config-input:not(:disabled)').each(function() {
            var $input = $(this);
            var key = $input.data('key');
            var type = $input.data('type');
            var value = type === 'boolean' ? ($input.is(':checked') ? 1 : 0) : $input.val();
            
            configData[key] = {
                value: value,
                type: type
            };
        });
        
        if (Object.keys(configData).length === 0) {
            $message.removeClass('d-none alert-danger alert-success').addClass('alert-warning');
            $message.html('No changes to save.');
            return;
        }
        
        // Show spinner
        $button.find('.spinner-border').removeClass('d-none');
        $button.prop('disabled', true);
        
        // Send update request
        $.ajax({
            url: 'index.php?action=admin_config',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(configData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $message.removeClass('d-none alert-danger').addClass('alert-success');
                    $message.html('<strong>Success!</strong> Settings saved successfully.');
                    
                    // Hide message after 5 seconds
                    setTimeout(function() {
                        $message.addClass('d-none');
                    }, 5000);
                } else {
                    $message.removeClass('d-none alert-success').addClass('alert-danger');
                    $message.html('<strong>Error!</strong> ' + (response.message || 'Failed to save settings.'));
                }
            },
            error: function(xhr, status, error) {
                $message.removeClass('d-none alert-success').addClass('alert-danger');
                $message.html('<strong>Error!</strong> ' + error);
            },
            complete: function() {
                // Hide spinner
                $button.find('.spinner-border').addClass('d-none');
                $button.prop('disabled', false);
            }
        });
    });
    
    // Update label text when boolean checkbox changes
    $('.config-input[type="checkbox"]').on('change', function() {
        var $label = $(this).closest('.form-check').find('.form-check-label');
        $label.text($(this).is(':checked') ? 'Enabled' : 'Disabled');
    });
});
</script>

</body>
</html>
