<?php
define('PAGE_TITLE', 'Playgram builder');
define('PAGE_NAME', 'PlaygramBuilder');
require_once("includes/header.php");

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

// Only allow librarians to access the dashboard
if (!$u_librarian) {
    header("Location: index.php");
    exit();
}

require_once('includes/config.php');
require_once('includes/functions.php');
require_once("includes/navbar.php");
ferror_log("RUNNING playgram_builder.php");

// Check if we're editing an existing playgram
$editing_playgram = false;
$playgram_data = null;
$playgram_compositions = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $editing_playgram = true;
    $playgram_id = mysqli_real_escape_string(f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME), $_GET['id']);
    
    // Get playgram data
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $sql = "SELECT * FROM playgrams WHERE id_playgram = '$playgram_id'";
    $res = mysqli_query($f_link, $sql);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $playgram_data = mysqli_fetch_assoc($res);
        
        // Get playgram compositions
        $sql = "SELECT pi.catalog_number, c.name, c.composer, c.arranger, c.duration 
                FROM playgram_items pi 
                JOIN compositions c ON pi.catalog_number = c.catalog_number 
                WHERE pi.id_playgram = '$playgram_id' 
                ORDER BY pi.comp_order";
        $res = mysqli_query($f_link, $sql);
        
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $playgram_compositions[] = $row;
            }
        }
    }
    mysqli_close($f_link);
}
?>

<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><i class="fas fa-tachometer-alt"></i> <?php echo ORGNAME; ?> Playgram builder</h1>
                <p class="lead">
                    <?php if ($editing_playgram): ?>
                        Editing program: <strong><?php echo htmlspecialchars($playgram_data['name']); ?></strong>
                    <?php else: ?>
                        Intelligent program builder for music directors
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-auto">
                <a href="playgrams.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Playgrams
                </a>
                <?php if ($editing_playgram): ?>
                <a href="playgram_builder.php" class="btn btn-outline-primary">
                    <i class="fas fa-plus"></i> Create New
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Program settings -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5>Playgram settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="program_name" class="form-label">Playgram name*</label>
                                <input type="text" class="form-control" id="program_name" name="program_name" 
                                       placeholder="e.g., Spring Concert 2027" 
                                       value="<?php echo $editing_playgram ? htmlspecialchars($playgram_data['name']) : ''; ?>" required/>
                                <?php if ($editing_playgram): ?>
                                <input type="hidden" id="playgram_id" value="<?php echo $playgram_data['id_playgram']; ?>"/>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <label for="target_duration" class="form-label">Target duration (minutes)*</label>
                                <input type="number" class="form-control" id="target_duration" name="target_duration" 
                                       min="5" max="180" value="60" required/>
                            </div>
                            <div class="col-md-2">
                                <label for="performance_date" class="form-label">Performance Date</label>
                                <input type="date" class="form-control" id="performance_date" name="performance_date"
                                    value="<?php echo $editing_playgram && !empty($playgram_data['performance_date']) ? htmlspecialchars($playgram_data['performance_date']) : ''; ?>" />
                            </div>                           
                            <div class="col-md-4">
                                <label for="program_description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="program_description" name="program_description" 
                                       placeholder="Concert theme or notes..."
                                       value="<?php echo $editing_playgram ? htmlspecialchars($playgram_data['description']) : ''; ?>"/>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="program_enabled" name="program_enabled" 
                                           <?php echo ($editing_playgram && $playgram_data['enabled'] == 1) || !$editing_playgram ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="program_enabled">
                                        <strong>Enable this playgram</strong>
                                        <br><small class="text-muted">Enabled playgrams appear in searches and reports</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Tracker -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5>Playgram time tracker</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3 class="text-primary" id="target_time_display">60:00</h3>
                                <small class="text-muted">Target duration</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-success" id="used_time_display">0:00</h3>
                                <small class="text-muted">Time used</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-info" id="remaining_time_display">60:00</h3>
                                <small class="text-muted">Time remaining</small>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-secondary" id="composition_count">0</h3>
                                <small class="text-muted">Compositions</small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" id="time_progress" 
                                     style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    0%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Composition filters</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Search Bar -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <input type="text" class="form-control form-control-lg" id="composition_search" 
                                       placeholder="Search compositions by title, composer, or catalog number..."/>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-secondary btn-lg w-100" id="clear_filters">
                                    <i class="fas fa-times"></i> Clear all filters
                                </button>
                            </div>
                        </div>

                        <!-- Filter Categories in Four Columns -->
                        <div class="row">
                            <!-- Genre Filters -->
                            <div class="col-md-3">
                                <h6 class="text-success mb-3"><i class="fas fa-tags"></i> Genre</h6>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-success dropdown-toggle w-100" type="button" id="genreDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="genreSelectedText">Select genre</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="genreDropdown" id="genre_filters">
                                        <!-- Will be populated by JavaScript -->
                                    </ul>
                                </div>
                            </div>

                            <!-- Composer Filters -->
                            <div class="col-md-3">
                                <h6 class="text-info mb-3"><i class="fas fa-user"></i> Composer</h6>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-info dropdown-toggle w-100" type="button" id="composerDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="composerSelectedText">Select composer</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="composerDropdown" id="composer_filters" style="max-height: 300px; overflow-y: auto;">
                                        <!-- Will be populated by JavaScript -->
                                    </ul>
                                </div>
                            </div>

                            <!-- Grade Level Filters -->
                            <div class="col-md-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap"></i> Grade level</h6>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="gradeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="gradeSelectedText">Select grade</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="gradeDropdown" id="grade_filters">
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="1">Grade 1</a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="2">Grade 2</a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="3">Grade 3</a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="4">Grade 4</a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="5">Grade 5</a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="grade" data-value="6">Grade 6</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Instrumentation Complexity Filters -->
                            <div class="col-md-3">
                                <h6 class="text-dark mb-3"><i class="fas fa-layer-group"></i> Part complexity</h6>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-dark dropdown-toggle w-100" type="button" id="complexityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="complexitySelectedText">Select part complexity</span>
                                    </button>
                                    <ul class="dropdown-menu w-100" aria-labelledby="complexityDropdown" id="complexity_filters">
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="complexity" data-value="simple" title="Simple: 1-15 unique parts">
                                            <i class="fas fa-music text-success"></i> Simple (1-15 parts)
                                        </a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="complexity" data-value="moderate" title="Moderate: 16-25 unique parts">
                                            <i class="fas fa-drum text-warning"></i> Moderate (16-25 parts)
                                        </a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="complexity" data-value="complex" title="Complex: 26+ unique parts">
                                            <i class="fas fa-guitar text-danger"></i> Complex (26+ parts)
                                        </a></li>
                                        <li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="complexity" data-value="unknown" title="Unknown: No parts data available">
                                            <i class="fas fa-question text-secondary"></i> Unknown
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard -->
        <div class="row mt-4">
            <!-- Available Compositions -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-music"></i> Available compositions <i class="fas fa-music"></i></h5>
                        <span class="badge bg-secondary" id="available_count">0</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Title</th>
                                        <th>Composer</th>
                                        <th>Genre</th>
                                        <th>Grade</th>
                                        <th>Duration</th>
                                        <th>Parts</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="compositions_table">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Program -->
            <div class="col-md-4">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-dark">
                        <h5><i class="fas fa-list-ol"></i> Playgram lineup</h5>
                    </div>
                    <div class="card-body">
                        <div id="program_compositions" class="list-group">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-music fa-2x mb-2"></i>
                                <p>No compositions added yet.<br>Select compositions from the left to build your program.</p>
                            </div>
                        </div>
                        
                        <div class="mt-3 d-grid gap-2">
                            <button type="button" class="btn btn-success" id="save_program" disabled>
                                <i class="fas fa-save"></i> <?php echo $editing_playgram ? 'Update program' : 'Save program'; ?>
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="clear_program">
                                <i class="fas fa-trash"></i> Clear program
                            </button>
                            <?php if ($editing_playgram): ?>
                            <a href="playgramsorderlist.php?id=<?php echo $playgram_data['id_playgram']; ?>" class="btn btn-outline-info">
                                <i class="fas fa-sort"></i> Reorder program
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once("includes/footer.php"); ?>

<style>
.btn-group-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.btn-group-wrap-vertical {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.btn-group-wrap-vertical .filter-btn {
    width: 100%;
    text-align: left;
    justify-content: flex-start;
}

.filter-btn.active {
    background-color: var(--bs-primary);
    color: white;
}

.filter-dropdown-item.active {
    background-color: var(--bs-primary);
    color: white;
}

.dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
    z-index: 1050;
}

.dropdown {
    z-index: 1050;
}

.table-dark {
    z-index: 1;
}

.composition-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.composition-item:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.program-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
}

.time-warning {
    color: #dc3545 !important;
}

.time-over {
    background-color: #dc3545 !important;
}
</style>

<script>
// Load compositions data
<?php
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT c.catalog_number, c.name, c.composer, c.arranger, c.duration, c.grade, 
               g.name AS genre_name, c.genre,
               COUNT(DISTINCT p.id_part_type) AS part_count
        FROM compositions c 
        LEFT JOIN genres g ON c.genre = g.id_genre 
        LEFT JOIN parts p ON c.catalog_number = p.catalog_number
        WHERE c.enabled = 1 
        GROUP BY c.catalog_number, c.name, c.composer, c.arranger, c.duration, c.grade, g.name, c.genre
        ORDER BY c.name";
$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));

$compositionData = [];
$genres = [];
$composers = [];

while($row = mysqli_fetch_array($res)) {
    $duration_minutes = $row['duration'] ? round($row['duration'] / 60, 1) : 0;
    $part_count = intval($row['part_count']);
    
    // Determine complexity level
    $complexity = 'unknown';
    if ($part_count > 0) {
        if ($part_count <= 15) {
            $complexity = 'simple';
        } elseif ($part_count <= 25) {
            $complexity = 'moderate';
        } else {
            $complexity = 'complex';
        }
    }
    
    $composition = [
        'catalog_number' => $row['catalog_number'],
        'name' => $row['name'],
        'composer' => $row['composer'] ?: 'Unknown',
        'arranger' => $row['arranger'] ?: '',
        'duration' => intval($row['duration']),
        'duration_minutes' => $duration_minutes,
        'grade' => floatval($row['grade']),
        'genre' => $row['genre'],
        'genre_name' => $row['genre_name'] ?: 'Unknown',
        'part_count' => $part_count,
        'complexity' => $complexity,
        'searchable' => strtolower($row['name'] . ' ' . $row['composer'] . ' ' . $row['arranger'] . ' ' . $row['catalog_number'])
    ];
    
    $compositionData[] = $composition;
    
    // Collect unique genres and composers
    if ($row['genre_name'] && !in_array($row['genre_name'], $genres)) {
        $genres[] = $row['genre_name'];
    }
    if ($row['composer'] && !in_array($row['composer'], $composers)) {
        $composers[] = $row['composer'];
    }
}

echo "var compositionData = " . json_encode($compositionData) . ";\n";
echo "var genreList = " . json_encode($genres) . ";\n";
echo "var composerList = " . json_encode($composers) . ";\n";

// Add existing playgram data for editing
echo "var editingPlaygram = " . ($editing_playgram ? 'true' : 'false') . ";\n";
if ($editing_playgram) {
    echo "var existingPlaygramCompositions = " . json_encode($playgram_compositions) . ";\n";
    echo "var playgramId = " . $playgram_data['id_playgram'] . ";\n";
} else {
    echo "var existingPlaygramCompositions = [];\n";
    echo "var playgramId = null;\n";
}

mysqli_close($f_link);
?>

$(document).ready(function() {
    let selectedCompositions = [];
    let activeFilters = {
        genre: null,
        composer: null,
        grade: null,
        complexity: null,
        search: ''
    };
    
    // Initialize the dashboard
    initializeFilters();
    loadExistingPlaygram();
    updateCompositionsList();
    updateTimeDisplay();
    
    function initializeFilters() {
        // Create genre filter dropdown items
        let genreItems = '';
        genreList.forEach(function(genre) {
            genreItems += `<li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="genre" data-value="${genre}">${genre}</a></li>`;
        });
        $('#genre_filters').html(genreItems);
        
        // Create composer filter dropdown items (limit to top 20 most common)
        let composerItems = '';
        composerList.slice(0, 20).forEach(function(composer) {
            composerItems += `<li><a class="dropdown-item filter-dropdown-item" href="#" data-filter="composer" data-value="${composer}">${composer}</a></li>`;
        });
        $('#composer_filters').html(composerItems);
    }
    
    function loadExistingPlaygram() {
        if (editingPlaygram && existingPlaygramCompositions.length > 0) {
            // Load existing compositions into selectedCompositions
            existingPlaygramCompositions.forEach(function(existingComp) {
                const matchingComp = compositionData.find(comp => comp.catalog_number === existingComp.catalog_number);
                if (matchingComp) {
                    selectedCompositions.push(matchingComp);
                }
            });
            
            // Calculate estimated target duration from existing compositions
            const totalDuration = selectedCompositions.reduce((total, comp) => total + comp.duration, 0);
            const estimatedMinutes = Math.ceil(totalDuration / 60);
            if (estimatedMinutes > 0) {
                $('#target_duration').val(estimatedMinutes);
            }
            
            updateProgramDisplay();
        }
    }
    
    // Filter button handling (both old buttons and new dropdown items)
    $(document).on('click', '.filter-btn, .filter-dropdown-item', function(e) {
        e.preventDefault();
        const filterType = $(this).data('filter');
        const filterValue = $(this).data('value');
        
        // Handle dropdown items
        if ($(this).hasClass('filter-dropdown-item')) {
            // Remove active from other dropdown items of same type
            $(`.filter-dropdown-item[data-filter="${filterType}"]`).removeClass('active');
            
            // Toggle active state
            if (activeFilters[filterType] === filterValue) {
                // Clear filter
                activeFilters[filterType] = null;
                updateDropdownText(filterType, null);
            } else {
                // Set filter
                $(this).addClass('active');
                activeFilters[filterType] = filterValue;
                updateDropdownText(filterType, filterValue);
            }
        } else {
            // Handle regular buttons (legacy)
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                activeFilters[filterType] = null;
            } else {
                $(`.filter-btn[data-filter="${filterType}"]`).removeClass('active');
                $(this).addClass('active');
                activeFilters[filterType] = filterValue;
            }
        }
        
        updateCompositionsList();
    });
    
    function updateDropdownText(filterType, filterValue) {
        switch(filterType) {
            case 'genre':
                $('#genreSelectedText').text(filterValue || 'Select genre');
                break;
            case 'composer':
                $('#composerSelectedText').text(filterValue || 'Select composer');
                break;
            case 'grade':
                $('#gradeSelectedText').text(filterValue ? `Grade ${filterValue}` : 'Select grade');
                break;
            case 'complexity':
                let complexityText = 'Select part complexity';
                if (filterValue) {
                    switch(filterValue) {
                        case 'simple': complexityText = 'Simple (1-15)'; break;
                        case 'moderate': complexityText = 'Moderate (16-25)'; break;
                        case 'complex': complexityText = 'Complex (26+)'; break;
                        case 'unknown': complexityText = 'Unknown'; break;
                    }
                }
                $('#complexitySelectedText').text(complexityText);
                break;
        }
    }
    
    // Search handling
    $('#composition_search').on('input', function() {
        activeFilters.search = $(this).val().toLowerCase();
        updateCompositionsList();
    });
    
    // Clear filters
    $('#clear_filters').click(function() {
        $('.filter-btn').removeClass('active');
        $('.filter-dropdown-item').removeClass('active');
        $('#composition_search').val('');
        activeFilters = { genre: null, composer: null, grade: null, complexity: null, search: '' };
        
        // Reset dropdown texts
        $('#genreSelectedText').text('Select genre');
        $('#composerSelectedText').text('Select composer');
        $('#gradeSelectedText').text('Select grade');
        $('#complexitySelectedText').text('Select part complexity');
        
        updateCompositionsList();
    });
    
    function updateCompositionsList() {
        let filteredCompositions = compositionData.filter(function(comp) {
            // Apply filters
            if (activeFilters.genre && comp.genre_name !== activeFilters.genre) return false;
            if (activeFilters.composer && comp.composer !== activeFilters.composer) return false;
            if (activeFilters.grade && comp.grade !== activeFilters.grade) return false;
            if (activeFilters.complexity && comp.complexity !== activeFilters.complexity) return false;
            if (activeFilters.search && comp.searchable.indexOf(activeFilters.search) === -1) return false;
            
            // Don't show already selected compositions
            if (selectedCompositions.find(selected => selected.catalog_number === comp.catalog_number)) return false;
            
            return true;
        });
        
        let tableHtml = '';
        filteredCompositions.forEach(function(comp) {
            const durationDisplay = comp.duration_minutes > 0 ? `${comp.duration_minutes}m` : 'Unknown';
            const gradeDisplay = comp.grade > 0 ? comp.grade : 'N/A';
            
            // Format parts count with complexity indicator
            let partsDisplay = '';
            if (comp.part_count > 0) {
                let complexityClass = '';
                let complexityIcon = '';
                switch(comp.complexity) {
                    case 'simple':
                        complexityClass = 'bg-success';
                        complexityIcon = 'fas fa-music';
                        break;
                    case 'moderate':
                        complexityClass = 'bg-warning';
                        complexityIcon = 'fas fa-drum';
                        break;
                    case 'complex':
                        complexityClass = 'bg-danger text-white';
                        complexityIcon = 'fas fa-guitar';
                        break;
                }
                partsDisplay = `<span class="badge ${complexityClass}"><i class="${complexityIcon}"></i> ${comp.part_count} parts</span>`;
            } else {
                partsDisplay = '<span class="badge bg-secondary"><i class="fas fa-question"></i> Unknown</span>';
            }
            
            tableHtml += `
                <tr class="composition-item" data-catalog="${comp.catalog_number}">
                    <td><strong>${comp.name}</strong><br><small class="text-muted">${comp.catalog_number}</small></td>
                    <td>${comp.composer}${comp.arranger ? `<br><small class="text-muted">arr. ${comp.arranger}</small>` : ''}</td>
                    <td><span class="badge bg-light text-dark">${comp.genre_name}</span></td>
                    <td><span class="badge bg-primary">${gradeDisplay}</span></td>
                    <td>${durationDisplay}</td>
                    <td>${partsDisplay}</td>
                    <td><button class="btn btn-sm btn-success add-composition" data-catalog="${comp.catalog_number}">
                        <i class="fas fa-plus"></i> Add
                    </button></td>
                </tr>
            `;
        });
        
        $('#compositions_table').html(tableHtml);
        $('#available_count').text(filteredCompositions.length);
    }
    
    // Add composition to program
    $(document).on('click', '.add-composition', function() {
        const catalogNumber = $(this).data('catalog');
        const composition = compositionData.find(comp => comp.catalog_number === catalogNumber);
        
        if (composition) {
            selectedCompositions.push(composition);
            updateProgramDisplay();
            updateCompositionsList();
            updateTimeDisplay();
        }
    });
    
    // Remove composition from program
    $(document).on('click', '.remove-composition', function() {
        const catalogNumber = $(this).data('catalog');
        selectedCompositions = selectedCompositions.filter(comp => comp.catalog_number !== catalogNumber);
        updateProgramDisplay();
        updateCompositionsList();
        updateTimeDisplay();
    });
    
    function updateProgramDisplay() {
        if (selectedCompositions.length === 0) {
            $('#program_compositions').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-music fa-2x mb-2"></i>
                    <p>No compositions added yet.<br>Select compositions from the left to build your program.</p>
                </div>
            `);
            $('#save_program').prop('disabled', true);
        } else {
            let programHtml = '';
            selectedCompositions.forEach(function(comp, index) {
                const durationDisplay = comp.duration_minutes > 0 ? `${comp.duration_minutes}m` : 'Unknown';
                programHtml += `
                    <div class="program-item">
                        <div>
                            <strong>${comp.name}</strong><br>
                            <small class="text-muted">${comp.composer} • ${durationDisplay}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-composition" data-catalog="${comp.catalog_number}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            $('#program_compositions').html(programHtml);
            $('#save_program').prop('disabled', false);
        }
    }
    
    function updateTimeDisplay() {
        const targetMinutes = parseInt($('#target_duration').val()) || 60;
        const usedSeconds = selectedCompositions.reduce((total, comp) => total + comp.duration, 0);
        const usedMinutes = usedSeconds / 60;
        const remainingMinutes = targetMinutes - usedMinutes;
        const percentage = Math.min((usedMinutes / targetMinutes) * 100, 100);
        
        $('#target_time_display').text(formatDuration(targetMinutes * 60));
        $('#used_time_display').text(formatDuration(usedSeconds));
        $('#remaining_time_display').text(formatDuration(Math.max(remainingMinutes * 60, 0)));
        $('#composition_count').text(selectedCompositions.length);
        
        // Update progress bar
        $('#time_progress')
            .css('width', percentage + '%')
            .attr('aria-valuenow', percentage)
            .text(Math.round(percentage) + '%');
        
        // Color coding
        if (remainingMinutes < 0) {
            $('#remaining_time_display').addClass('time-warning');
            $('#time_progress').removeClass('bg-success bg-warning').addClass('time-over');
        } else if (remainingMinutes < 5) {
            $('#remaining_time_display').removeClass('time-warning');
            $('#time_progress').removeClass('bg-success time-over').addClass('bg-warning');
        } else {
            $('#remaining_time_display').removeClass('time-warning');
            $('#time_progress').removeClass('bg-warning time-over').addClass('bg-success');
        }
    }
    
    function formatDuration(seconds) {
        const minutes = Math.floor(Math.abs(seconds) / 60);
        const secs = Math.floor(Math.abs(seconds) % 60);
        const sign = seconds < 0 ? '-' : '';
        return `${sign}${minutes}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Target duration change
    $('#target_duration').on('input', function() {
        updateTimeDisplay();
    });
    
    // Clear program
    $('#clear_program').click(function() {
        if (confirm('Are you sure you want to clear the entire program?')) {
            selectedCompositions = [];
            updateProgramDisplay();
            updateCompositionsList();
            updateTimeDisplay();
        }
    });
    
    // Save program
    $('#save_program').click(function() {
        const programName = $('#program_name').val().trim();
        const programDescription = $('#program_description').val().trim();
        
        if (!programName) {
            alert('Please enter a program name.');
            return;
        }
        
        if (selectedCompositions.length === 0) {
            alert('Please add at least one composition to the program.');
            return;
        }
        
        const $btn = $(this);
        const isUpdating = editingPlaygram;
        const buttonText = isUpdating ? 'Update Program' : 'Save Program';
        const loadingText = isUpdating ? 'Updating...' : 'Saving...';
        
        $btn.prop('disabled', true).html(`<i class="fas fa-spinner fa-spin"></i> ${loadingText}`);
        
        // Prepare data for submission
        const formData = {
            name: programName,
            description: programDescription,
            performance_date: $('#performance_date').val(),
            enabled: $('#program_enabled').is(':checked') ? 1 : 0,
            'id_composition[]': selectedCompositions.map(comp => comp.catalog_number)
        };
        
        // Add update fields if editing
        if (isUpdating) {
            formData.update = 'update';
            formData.id_playgram = playgramId;
        } else {
            formData.id_playgram = null;
            formData.update = 'add';
        }
        $.ajax({
            url: 'includes/insert_playgrams.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                const successMessage = isUpdating ? 'Program updated successfully!' : 'Program saved successfully!';
                // alert(successMessage);
                
                if (!isUpdating) {
                    // For new programs, offer to create another or go to list
                    if (confirm('Create another playgram?')) {
                        // Reset for new program
                        selectedCompositions = [];
                        $('#program_name').val('');
                        $('#program_description').val('');
                        $('#target_duration').val('60');
                        updateProgramDisplay();
                        updateCompositionsList();
                        updateTimeDisplay();
                    } else {
                        window.location.href = 'playgrams.php';
                    }
                } else {
                    window.location.href = 'playgrams.php';
                }
            },
            error: function() {
                const errorMessage = isUpdating ? 'Error updating program. Please try again.' : 'Error saving program. Please try again.';
                alert(errorMessage);
            },
            complete: function() {
                $btn.prop('disabled', false).html(`<i class="fas fa-save"></i> ${buttonText}`);
            }
        });
    });
});
</script>

</body>
</html>
