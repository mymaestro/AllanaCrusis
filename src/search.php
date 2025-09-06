<?php
define('PAGE_TITLE', 'Search');
define('PAGE_NAME', 'search');
require_once(__DIR__. "/includes/header.php");

// User permissions
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
}
require_once(__DIR__. "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__. "/includes/functions.php");
ferror_log("RUNNING search.php");
?>

<style>
    .sidebar {
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        border-radius: 0.375rem 0 0 0.375rem;
        padding: 0;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    .sidebar-header {
        background-color: #e9ecef;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .sidebar-content {
        padding: 1rem;
    }

    .filter-section {
        margin-bottom: 2rem;
    }

    .filter-section h6 {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
    }

    .filter-group {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }

    .filter-group::-webkit-scrollbar {
        width: 6px;
    }

    .filter-group::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .filter-group::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .filter-group::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .main-content {
        padding: 1rem;
        background-color: #ffffff;
        border-radius: 0 0.375rem 0.375rem 0;
        border: 1px solid #dee2e6;
        border-left: none;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    .composition-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
        border: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }

    .composition-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-catalog {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .card-duration {
        background-color: #e9ecef;
        color: #495057;
        border-radius: 0.25rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .results-header {
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }

    .no-results {
        text-align: center;
        color: #6c757d;
        padding: 4rem 2rem;
    }

    .filter-group .form-check {
        margin-bottom: 0.5rem;
    }

    .filter-group .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .collapse-header {
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 0.5rem 0;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0.75rem;
    }

    .collapse-header:hover {
        color: #0d6efd !important;
    }

    .collapse-header .fa-chevron-down {
        transition: transform 0.2s ease;
    }

    .collapse-header[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
    }

    @media (max-width: 768px) {
        .sidebar {
            min-height: auto;
        }
    }
</style>

<main role="main" class="container my-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar">
            <div class="sidebar-header">
                <h5 class="mb-0"><i class="fas fa-search"></i> Find Music</h5>
                <p class="text-muted mb-0 small">Discover compositions for your ensemble</p>
            </div>

            <div class="sidebar-content">
                <!-- Search Field (Always visible) -->
                <div class="filter-section">
                    <h6><i class="fas fa-keyboard"></i> Search</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_text"
                            placeholder="Title, composer, catalog...">
                        <button class="btn btn-outline-secondary" type="button" id="clear_search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Composer Filter -->
                <div class="filter-section">
                    <h6>
                        <a class="text-decoration-none text-dark d-flex justify-content-between align-items-center collapse-header"
                            data-bs-toggle="collapse" href="#composerCollapse" role="button"
                            aria-expanded="false" aria-controls="composerCollapse">
                            <span><i class="fas fa-user-tie me-2"></i> Composer</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h6>
                    <div class="collapse" id="composerCollapse">
                        <div class="filter-group" id="composer_filters">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="composer_filter" id="composer_all" value="" checked>
                                <label class="form-check-label" for="composer_all">All Composers</label>
                            </div>
                            <?php
                            $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $sql = "SELECT DISTINCT composer FROM compositions WHERE composer IS NOT NULL AND composer != '' AND enabled = 1 ORDER BY composer";
                            $res = mysqli_query($f_link, $sql);
                            while ($row = mysqli_fetch_array($res)) {
                                $composer = htmlspecialchars($row['composer'] ?? '');
                                $composer_id = preg_replace('/[^a-zA-Z0-9]/', '_', $composer);
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="radio" name="composer_filter" id="composer_' . $composer_id . '" value="' . $composer . '">';
                                echo '<label class="form-check-label" for="composer_' . $composer_id . '">' . $composer . '</label>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Genre Filter -->
                <div class="filter-section">
                    <h6>
                        <a class="text-decoration-none text-dark d-flex justify-content-between align-items-center collapse-header"
                            data-bs-toggle="collapse" href="#genreCollapse" role="button"
                            aria-expanded="false" aria-controls="genreCollapse">
                            <span><i class="fas fa-tags me-2"></i> Genre</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h6>
                    <div class="collapse" id="genreCollapse">
                        <div class="filter-group" id="genre_filters">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genre_filter" id="genre_all" value="" checked>
                                <label class="form-check-label" for="genre_all">All Genres</label>
                            </div>
                            <?php
                            $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $sql = "SELECT id_genre, name FROM genres WHERE enabled = 1 ORDER BY name";
                            $res = mysqli_query($f_link, $sql);
                            while ($row = mysqli_fetch_array($res)) {
                                $genre_id = $row['id_genre'];
                                $genre_name = htmlspecialchars($row['name'] ?? '');
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="radio" name="genre_filter" id="genre_' . $genre_id . '" value="' . $genre_id . '">';
                                echo '<label class="form-check-label" for="genre_' . $genre_id . '">' . $genre_name . '</label>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Ensemble Filter -->
                <div class="filter-section">
                    <h6>
                        <a class="text-decoration-none text-dark d-flex justify-content-between align-items-center collapse-header"
                            data-bs-toggle="collapse" href="#ensembleCollapse" role="button"
                            aria-expanded="false" aria-controls="ensembleCollapse">
                            <span><i class="fas fa-users me-2"></i> Ensemble</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h6>
                    <div class="collapse" id="ensembleCollapse">
                        <div class="filter-group" id="ensemble_filters">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ensemble_filter" id="ensemble_all" value="" checked>
                                <label class="form-check-label" for="ensemble_all">All Ensembles</label>
                            </div>
                            <?php
                            $sql = "SELECT id_ensemble, name FROM ensembles WHERE enabled = 1 ORDER BY name";
                            $res = mysqli_query($f_link, $sql);
                            while ($row = mysqli_fetch_array($res)) {
                                $ensemble_id = $row['id_ensemble'];
                                $ensemble_name = htmlspecialchars($row['name'] ?? '');
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="radio" name="ensemble_filter" id="ensemble_' . $ensemble_id . '" value="' . $ensemble_id . '">';
                                echo '<label class="form-check-label" for="ensemble_' . $ensemble_id . '">' . $ensemble_name . '</label>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Grade Filter -->
                <div class="filter-section">
                    <h6>
                        <a class="text-decoration-none text-dark d-flex justify-content-between align-items-center collapse-header"
                            data-bs-toggle="collapse" href="#gradeCollapse" role="button"
                            aria-expanded="false" aria-controls="gradeCollapse">
                            <span><i class="fas fa-graduation-cap me-2"></i> Grade Level</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h6>
                    <div class="collapse" id="gradeCollapse">
                        <div class="filter-group" id="grade_filters">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_all" value="" checked>
                                <label class="form-check-label" for="grade_all">All Grades</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_1" value="1">
                                <label class="form-check-label" for="grade_1">Grade 1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_2" value="2">
                                <label class="form-check-label" for="grade_2">Grade 2</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_3" value="3">
                                <label class="form-check-label" for="grade_3">Grade 3</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_4" value="4">
                                <label class="form-check-label" for="grade_4">Grade 4</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_5" value="5">
                                <label class="form-check-label" for="grade_5">Grade 5</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="grade_filter" id="grade_6" value="6">
                                <label class="form-check-label" for="grade_6">Grade 6</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Duration Filter -->
                <div class="filter-section">
                    <h6>
                        <a class="text-decoration-none text-dark d-flex justify-content-between align-items-center collapse-header"
                            data-bs-toggle="collapse" href="#durationCollapse" role="button"
                            aria-expanded="false" aria-controls="durationCollapse">
                            <span><i class="fas fa-clock me-2"></i> Duration</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h6>
                    <div class="collapse" id="durationCollapse">
                        <div class="filter-group" id="duration_filters">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_filter" id="duration_all" value="" checked>
                                <label class="form-check-label" for="duration_all">Any Duration</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_filter" id="duration_short" value="short">
                                <label class="form-check-label" for="duration_short">Short (0-3 min)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_filter" id="duration_medium" value="medium">
                                <label class="form-check-label" for="duration_medium">Medium (3-8 min)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_filter" id="duration_long" value="long">
                                <label class="form-check-label" for="duration_long">Long (8+ min)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div class="d-grid mt-3">
                    <button type="button" class="btn btn-outline-secondary" id="clear_all_filters">
                        <i class="fas fa-eraser"></i> Clear All Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 main-content">
            <div class="results-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><?php echo ORGNAME; ?> Music Library</h4>
                        <p class="text-muted mb-0" id="results_subtitle">Browse or search to discover compositions</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6" id="results_count">0 compositions</span>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loading_indicator" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Searching compositions...</p>
            </div>

            <!-- Results Container -->
            <div id="results_container">
                <div class="no-results">
                    <i class="fas fa-music fa-4x mb-4 text-muted"></i>
                    <h5 class="text-muted">Welcome to the Music Library</h5>
                    <p class="text-muted">Use the search filters on the left to discover compositions for your ensemble</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Composition Details Modal -->
    <div class="modal" id="viewData">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Composition Details</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="composition_detail">
                    <p class="text-center">Loading details...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Parts Modal -->
    <div class="modal" id="partsData">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="instrumentation_detail">
                <!-- filled in by select_composition_parts.php -->
            </div>
        </div>
    </div>
</main>

<?php
mysqli_close($f_link);
require_once(__DIR__. "/includes/footer.php");
?>

<script>
    $(document).ready(function() {
        // Auto-search on page load with all compositions
        performSearch();

        // Search input with debounce
        let searchTimeout;
        $('#search_text').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 300);
        });

        // Filter change handlers
        $('input[type="radio"]').on('change', function() {
            performSearch();
        });

        // Clear search button
        $('#clear_search').click(function() {
            $('#search_text').val('');
            performSearch();
        });

        // Clear all filters
        $('#clear_all_filters').click(function() {
            $('#search_text').val('');
            $('input[type="radio"][value=""]').prop('checked', true);
            performSearch();
        });

        // Enter key in search box
        $('#search_text').keypress(function(e) {
            if (e.which == 13) {
                clearTimeout(searchTimeout);
                performSearch();
            }
        });

        function performSearch() {
            const searchData = {
                search: $('#search_text').val(),
                genre: $('input[name="genre_filter"]:checked').val(),
                ensemble: $('input[name="ensemble_filter"]:checked').val(),
                grade: $('input[name="grade_filter"]:checked').val(),
                duration: $('input[name="duration_filter"]:checked').val(),
                composer: $('input[name="composer_filter"]:checked').val()
            };

            // Check if no filters are applied
            const hasFilters = searchData.search ||
                searchData.genre ||
                searchData.ensemble ||
                searchData.grade ||
                searchData.duration ||
                searchData.composer;

            // Show loading
            $('#loading_indicator').show();
            $('#results_container').hide();

            // If no filters, show random composition
            if (!hasFilters) {
                showRandomComposition();
                return;
            }

            // Perform AJAX search
            $.ajax({
                url: 'index.php?action=search_compositions',
                method: 'POST',
                data: searchData,
                dataType: 'json',
                success: function(response) {
                    displayResults(response);
                },
                error: function() {
                    $('#results_container').html(`
                    <div class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h5>Error Loading Compositions</h5>
                        <p>Please try again or contact support if the problem persists.</p>
                    </div>
                `);
                },
                complete: function() {
                    $('#loading_indicator').hide();
                    $('#results_container').show();
                }
            });
        }

        function showRandomComposition() {
            // Load a random composition
            $.ajax({
                url: 'index.php?action=search_compositions',
                method: 'POST',
                data: {
                    random: 1
                }, // Special parameter for random selection
                dataType: 'json',
                success: function(response) {
                    displayRandomComposition(response);
                },
                error: function() {
                    $('#results_container').html(`
                    <div class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h5>Error Loading Composition</h5>
                        <p>Please try again or contact support if the problem persists.</p>
                    </div>
                `);
                },
                complete: function() {
                    $('#loading_indicator').hide();
                    $('#results_container').show();
                }
            });
        }

        function displayRandomComposition(data) {
            if (data.length === 0) {
                $('#results_container').html(`
                <div class="no-results">
                    <i class="fas fa-music fa-4x mb-4 text-muted"></i>
                    <h5 class="text-muted">No Compositions Available</h5>
                    <p class="text-muted">Please check back later</p>
                </div>
            `);
                $('#results_count').text('0 compositions');
                $('#results_subtitle').text('No compositions found');
                return;
            }

            const comp = data[0]; // Get the first (and only) random composition
            const duration = comp.duration ? Math.round(comp.duration / 60) + ' min' : '0:00';
            const grade = comp.grade || 'N/A';
            const composer = comp.composer || 'Unknown';
            const arranger = comp.arranger ? ` • arr. ${comp.arranger}` : '';
            const genre = comp.genre_name || 'Unknown';
            const ensemble = comp.ensemble_name || 'Unknown';
            const partsCount = comp.parts_count || 0;
            const partsText = partsCount === 1 ? '1 Part' : `${partsCount} Parts`;

            const html = `
            <div class="mb-4 row">
                <div class="col-sm-6 text-center">
                    <div class="card border-primary shadow-sm mx-auto">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Featured composition</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-light text-dark">${ensemble} • ${genre}</span>
                                <span class="card-duration">${duration}</span>
                            </div>
                            <h4 class="card-title text-primary mb-2">${comp.name}</h4>
                            <p class="card-text text-muted mb-3">
                                <strong>${composer}${arranger}</strong>
                            </p>
                        
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="badge bg-primary">Grade ${grade}</span>
                                </div>
                                <small class="card-catalog">${comp.catalog_number}</small>
                            </div>
                        
                            <div class="d-grid gap-2">
                                <button type="button" data-catalog="${comp.catalog_number}"
                                    class="btn btn-primary view-composition-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button type="button" data-catalog="${comp.catalog_number}"
                                class="btn btn-outline-success view-parts-btn">
                                    <i class="fas fa-puzzle-piece"></i> ${partsText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card mx-auto">
                        <h6 class="card-header"><i class="fas fa-lightbulb"></i> Discover more music</h6>
                        <div class="card-body">
                            <p class="mb-2">Use the search field or expand the filter sections to explore the full music library.</p>
                            <hr class="my-2">
                            <p class="mb-0 small">You can search by title, composer, or catalog number, or filter by genre, ensemble, grade level, and more!</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

            $('#results_container').html(html);
            $('#results_count').text('1 featured composition');
            $('#results_subtitle').text('Discover more by searching or using filters');
        }

        function displayResults(data) {
            if (data.length === 0) {
                $('#results_container').html(`
                <div class="no-results">
                    <i class="fas fa-search fa-4x mb-4 text-muted"></i>
                    <h5 class="text-muted">No Compositions Found</h5>
                    <p class="text-muted">Try adjusting your search criteria or clearing some filters</p>
                </div>
            `);
                $('#results_count').text('0 compositions');
                $('#results_subtitle').text('No results match your search criteria');
                return;
            }

            let html = '<div class="row">';

            data.forEach(function(comp) {
                const duration = comp.duration ? Math.round(comp.duration / 60) + ' min' : '0:00';
                const grade = comp.grade || 'N/A';
                const composer = comp.composer || 'Unknown';
                const arranger = comp.arranger ? ` • arr. ${comp.arranger}` : '';
                const genre = comp.genre_name || 'Unknown';
                const ensemble = comp.ensemble_name || 'Unknown';
                const partsCount = comp.parts_count || 0;
                const partsText = partsCount === 1 ? '1 Part' : `${partsCount} Parts`;

                // Create ensemble/genre text with truncation
                const ensembleGenreText = `${ensemble} • ${genre}`;
                const displayText = ensembleGenreText.length > 35 ?
                    ensembleGenreText.substring(0, 32) + '...' :
                    ensembleGenreText;

                html += `
                <div class="col-lg-6 col-xl-4">
                    <div class="card composition-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark" title="${ensembleGenreText}">${displayText}</span>
                                <span class="card-duration">${duration}</span>
                            </div>
                            
                            <h6 class="card-title mb-2">${comp.name}</h6>
                            <p class="card-text text-muted mb-2">
                                <small>${composer}${arranger}</small>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary">Grade ${grade}</span>
                                </div>
                                <small class="card-catalog">${comp.catalog_number}</small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" data-catalog="${comp.catalog_number}"
                                   class="btn btn-outline-primary btn-sm flex-md-fill view-composition-btn">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button type="button" data-catalog="${comp.catalog_number}"
                                   class="btn btn-outline-success btn-sm flex-md-fill view-parts-btn">
                                    <i class="fas fa-puzzle-piece"></i> ${partsText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });

            html += '</div>';

            $('#results_container').html(html);
            $('#results_count').text(`${data.length} composition${data.length !== 1 ? 's' : ''}`);

            // Update subtitle based on search criteria
            const hasFilters = $('#search_text').val() ||
                $('input[name="genre_filter"]:checked').val() ||
                $('input[name="ensemble_filter"]:checked').val() ||
                $('input[name="grade_filter"]:checked').val() ||
                $('input[name="duration_filter"]:checked').val() ||
                $('input[name="composer_filter"]:checked').val();

            $('#results_subtitle').text(hasFilters ?
                'Showing filtered results' :
                'Showing all available compositions'
            );
        }

        // View composition button handler
        $(document).on('click', '.view-composition-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const catalogNumber = $(this).data('catalog');

            // Show loading in modal
            $('#composition_detail').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading composition details...</p></div>');

            // Show modal immediately
            $('#viewData').modal('show');

            // Load composition details
            $.ajax({
                url: 'index.php?action=select_compositions',
                type: 'POST',
                data: {
                    catalog_number: catalogNumber
                },
                success: function(data) {
                    $('#composition_detail').html(data);
                },
                error: function() {
                    $('#composition_detail').html('<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h5>Error Loading Details</h5><p>Unable to load composition details. Please try again.</p></div>');
                }
            });
        });

        // View parts button handler
        $(document).on('click', '.view-parts-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const catalogNumber = $(this).data('catalog');

            // Show loading in modal
            $('#instrumentation_detail').html('<div class="modal-header"><h3 class="modal-title">Loading Parts...</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading parts information...</p></div></div>');

            // Show modal immediately
            $('#partsData').modal('show');

            // Load parts details
            $.ajax({
                url: 'index.php?action=select_composition_parts',
                type: 'POST',
                data: {
                    catalog_number: catalogNumber
                },
                success: function(data) {
                    $('#instrumentation_detail').html(data);
                },
                error: function() {
                    $('#instrumentation_detail').html('<div class="modal-header"><h3 class="modal-title">Error</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h5>Error loading parts</h5><p>Unable to load parts information. Please try again.</p></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>');
                }
            });
        });
    });
</script>

</body>

</html>