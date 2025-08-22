<?php
define('PAGE_TITLE', 'Home');
define('PAGE_NAME', 'home');
require_once(__DIR__. "/includes/header.php");
$u_admin = FALSE;
$u_librarian = FALSE;
$u_user = FALSE;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ? TRUE : FALSE);
    $u_librarian = (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles']), 'user') !== FALSE ? TRUE : FALSE);
}
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
require_once(__DIR__ . "/includes/functions.php");

ferror_log("RUNNING home.php");

// Get database connection
$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Get summary statistics
$stats = array();

// Compositions statistics
$sql = "SELECT 
    COUNT(*) as total_compositions,
    COUNT(CASE WHEN enabled = 1 THEN 1 END) as enabled_compositions,
    AVG(grade) as avg_grade,
    COUNT(CASE WHEN last_performance_date IS NOT NULL THEN 1 END) as performed_compositions
FROM compositions";
$res = mysqli_query($f_link, $sql);
$stats['compositions'] = mysqli_fetch_assoc($res);
ferror_log("Returned ". mysqli_num_rows($res)." counts for compositions");
// Parts statistics
$sql = "SELECT 
    COUNT(*) as total_parts,
    COUNT(DISTINCT catalog_number) as compositions_with_parts,
    AVG(page_count) as avg_page_count,
    SUM(originals_count) as total_originals,
    SUM(copies_count) as total_copies
FROM parts";
$res = mysqli_query($f_link, $sql);
$stats['parts'] = mysqli_fetch_assoc($res);
ferror_log("Returned ". mysqli_num_rows($res)." counts for parts");
// Recordings statistics
$sql = "SELECT 
    COUNT(*) as total_recordings,
    COUNT(DISTINCT catalog_number) as recorded_compositions,
    COUNT(DISTINCT id_concert) as recording_sessions
FROM recordings WHERE 1=1";
$res = mysqli_query($f_link, $sql);
if ($res) {
    $stats['recordings'] = mysqli_fetch_assoc($res);
} else {
    $stats['recordings'] = array('total_recordings' => 0, 'recorded_compositions' => 0, 'recording_sessions' => 0);
}
ferror_log("Returned ". mysqli_num_rows($res)." counts for recordings");
// Concerts statistics  
$sql = "SELECT 
    COUNT(*) as total_concerts,
    COUNT(DISTINCT DATE(performance_date)) as concert_dates
FROM concerts WHERE 1=1";
$res = mysqli_query($f_link, $sql);
if ($res) {
    $stats['concerts'] = mysqli_fetch_assoc($res);
} else {
    $stats['concerts'] = array('total_concerts' => 0, 'concert_dates' => 0);
}
ferror_log("Returned ". mysqli_num_rows($res)." counts for concerts");
// Ensembles, Genres, Part Types statistics
$sql = "SELECT COUNT(*) as total FROM ensembles WHERE enabled = 1";
$res = mysqli_query($f_link, $sql);
$stats['ensembles'] = mysqli_fetch_assoc($res)['total'];

$sql = "SELECT COUNT(*) as total FROM genres WHERE enabled = 1";
$res = mysqli_query($f_link, $sql);
$stats['genres'] = mysqli_fetch_assoc($res)['total'];

$sql = "SELECT COUNT(*) as total FROM part_types WHERE enabled = 1";
$res = mysqli_query($f_link, $sql);
$stats['part_types'] = mysqli_fetch_assoc($res)['total'];

$sql = "SELECT COUNT(*) as total FROM instruments WHERE enabled = 1";
$res = mysqli_query($f_link, $sql);
$stats['instruments'] = mysqli_fetch_assoc($res)['total'];

// Playgrams statistics
$sql = "SELECT COUNT(*) as total_playgrams FROM playgrams WHERE enabled = 1";
$res = mysqli_query($f_link, $sql);
if ($res) {
    $stats['playgrams'] = mysqli_fetch_assoc($res);
} else {
    $stats['playgrams'] = array('total_playgrams' => 0);
}

// Get recent activity
$sql = "SELECT 
    c.catalog_number,
    c.name,
    c.composer,
    c.last_update,
    'composition' as type
FROM compositions c
ORDER BY c.last_update DESC
LIMIT 5";
$recent_activity = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $recent_activity[] = $row;
}

// Get recent parts activity
$sql = "SELECT 
    p.catalog_number,
    c.name as composition_name,
    c.composer,
    DATE(MAX(p.last_update)) as last_update_date,
    COUNT(p.id_part_type) as parts_updated,
    GROUP_CONCAT(DISTINCT pt.name ORDER BY pt.name SEPARATOR ', ') as part_types_updated,
    'part' as type
FROM parts p
LEFT JOIN compositions c ON p.catalog_number = c.catalog_number
LEFT JOIN part_types pt ON p.id_part_type = pt.id_part_type
GROUP BY p.catalog_number, c.name, c.composer, DATE(p.last_update)
ORDER BY last_update_date DESC, MAX(p.last_update) DESC
LIMIT 5";
$recent_parts_activity = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $recent_parts_activity[] = $row;
}

// Get compositions by genre
$sql = "SELECT 
    g.name as genre_name,
    COUNT(c.catalog_number) as count
FROM genres g
LEFT JOIN compositions c ON g.id_genre = c.genre AND c.enabled = 1
WHERE g.enabled = 1
GROUP BY g.id_genre, g.name
ORDER BY count DESC
LIMIT 10";
$genre_stats = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $genre_stats[] = $row;
}

// Get compositions by ensemble
$sql = "SELECT 
    e.name as ensemble_name,
    COUNT(c.catalog_number) as count
FROM ensembles e
LEFT JOIN compositions c ON e.id_ensemble = c.ensemble AND c.enabled = 1
WHERE e.enabled = 1
GROUP BY e.id_ensemble, e.name
ORDER BY count DESC
LIMIT 10";
$ensemble_stats = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $ensemble_stats[] = $row;
}

// Get performance statistics by genre
$sql = "SELECT 
    g.name as genre_name,
    COUNT(DISTINCT r.id_recording) as recordings_count,
    COUNT(DISTINCT CONCAT(ci.id_concert, '-', pi.catalog_number)) as concert_performances,
    (COUNT(DISTINCT r.id_recording) + COUNT(DISTINCT CONCAT(ci.id_concert, '-', pi.catalog_number))) as total_performances
FROM genres g
LEFT JOIN compositions c ON g.id_genre = c.genre AND c.enabled = 1
LEFT JOIN recordings r ON c.catalog_number = r.catalog_number
LEFT JOIN playgram_items pi ON c.catalog_number = pi.catalog_number
LEFT JOIN playgrams p ON pi.id_playgram = p.id_playgram
LEFT JOIN concerts ci ON p.id_playgram = ci.id_playgram
WHERE g.enabled = 1
GROUP BY g.id_genre, g.name
HAVING total_performances > 0
ORDER BY total_performances DESC
LIMIT 10";
$performance_stats = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $performance_stats[] = $row;
}

// Get concerts by venue
$sql = "SELECT 
    venue,
    COUNT(*) as concert_count
FROM concerts 
WHERE venue IS NOT NULL AND venue != ''
GROUP BY venue
ORDER BY concert_count DESC
LIMIT 15";
$venue_stats = array();
$res = mysqli_query($f_link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $venue_stats[] = $row;
}

mysqli_close($f_link);
?>

<main role="main" class="container-fluid">
    <div class="container">
        <div class="row pb-3 pt-5 border-bottom">
            <div class="col">
                <h1><?php echo ORGDESC ?> Music Library</h1>
                <p class="lead">Welcome to the <?php echo ORGNAME ?> music library database</p>
            </div>
        </div>

        <!-- Quick Statistics Panel -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="card bg-light border-0">
                    <div class="card-body py-3">
                        <div class="row text-center g-0">
                            <div class="col-3">
                                <div class="d-flex flex-column align-items-center">
                                    <h3 class="display-3 text-secondary mb-0"><?php echo number_format($stats['compositions']['total_compositions']); ?></h3>
                                    <small class="text-muted">Compositions</small>
                                </div>
                            </div>
                            <div class="col-3 border-start">
                                <div class="d-flex flex-column align-items-center">
                                    <h3 class="display-3 text-secondary mb-0"><?php echo number_format($stats['parts']['total_parts']); ?></h3>
                                    <small class="text-muted">Parts</small>
                                </div>
                            </div>
                            <div class="col-3 border-start">
                                <div class="d-flex flex-column align-items-center">
                                    <h3 class="display-3 text-secondary mb-0"><?php echo number_format($stats['recordings']['total_recordings']); ?></h3>
                                    <small class="text-muted">Recordings</small>
                                </div>
                            </div>
                            <div class="col-3 border-start">
                                <div class="d-flex flex-column align-items-center">
                                    <h3 class="display-3 text-secondary mb-0"><?php echo number_format($stats['concerts']['total_concerts']); ?></h3>
                                    <small class="text-muted">Concerts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Feature Cards - Library Materials -->
        <div class="row border-bottom">
            <div class="col-12">
                <h2>Materials</h2>
            </div>
        </div>
        
        <div class="row g-4 py-4 row-cols-1 row-cols-lg-3">
            <!-- Compositions -->
            <div class="feature col">
                <div class="card h-100 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
					    <div class="icon-square text-body-emphasis bg-body-primary d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3"> <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-palette" viewBox="0 0 16 16">
							<path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
							<path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7" />
						</svg> </div>
                            <div>
                                <h3 class="fs-2 text-body-emphasis mb-0">Compositions</h3>
                                <div class="mt-2">
                                    <span class="badge bg-secondary"><?php echo number_format($stats['compositions']['total_compositions']); ?> total</span>
                                    <span class="badge bg-success"><?php echo number_format($stats['compositions']['enabled_compositions']); ?> enabled</span>
                                </div>
                            </div>
                        </div>
                        <p>Work with compositions in the music library. This table holds the data about the music in the library including title, composer, arranger, and publish date.</p>
                        <a href="/compositions" class="btn btn-secondary">
                            <i class="fas fa-music"></i> Manage compositions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Parts -->
            <div class="feature col">
                <div class="card h-100 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
					        <div class="icon-square text-body-emphasis bg-body-primary d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3"> <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-puzzle" viewBox="0 0 16 16">
							    <path d="M3.112 3.645A1.5 1.5 0 0 1 4.605 2H7a.5.5 0 0 1 .5.5v.382c0 .696-.497 1.182-.872 1.469a.5.5 0 0 0-.115.118l-.012.025L6.5 4.5v.003l.003.01q.005.015.036.053a.9.9 0 0 0 .27.194C7.09 4.9 7.51 5 8 5c.492 0 .912-.1 1.19-.24a.9.9 0 0 0 .271-.194.2.2 0 0 0 .039-.063v-.009l-.012-.025a.5.5 0 0 0-.115-.118c-.375-.287-.872-.773-.872-1.469V2.5A.5.5 0 0 1 9 2h2.395a1.5 1.5 0 0 1 1.493 1.645L12.645 6.5h.237c.195 0 .42-.147.675-.48.21-.274.528-.52.943-.52.568 0 .947.447 1.154.862C15.877 6.807 16 7.387 16 8s-.123 1.193-.346 1.638c-.207.415-.586.862-1.154.862-.415 0-.733-.246-.943-.52-.255-.333-.48-.48-.675-.48h-.237l.243 2.855A1.5 1.5 0 0 1 11.395 14H9a.5.5 0 0 1-.5-.5v-.382c0-.696.497-1.182.872-1.469a.5.5 0 0 0 .115-.118l.012-.025.001-.006v-.003a.2.2 0 0 0-.039-.064.9.9 0 0 0-.27-.193C8.91 11.1 8.49 11 8 11s-.912.1-1.19.24a.9.9 0 0 0-.271.194.2.2 0 0 0-.039.063v.003l.001.006.012.025c.016.027.05.068.115.118.375.287.872.773.872 1.469v.382a.5.5 0 0 1-.5.5H4.605a1.5 1.5 0 0 1-1.493-1.645L3.356 9.5h-.238c-.195 0-.42.147-.675.48-.21.274-.528.52-.943.52-.568 0-.947-.447-1.154-.862C.123 9.193 0 8.613 0 8s.123-1.193.346-1.638C.553 5.947.932 5.5 1.5 5.5c.415 0 .733.246.943.52.255.333.48.48.675.48h.238zM4.605 3a.5.5 0 0 0-.498.55l.001.007.29 3.4A.5.5 0 0 1 3.9 7.5h-.782c-.696 0-1.182-.497-1.469-.872a.5.5 0 0 0-.118-.115l-.025-.012L1.5 6.5h-.003a.2.2 0 0 0-.064.039.9.9 0 0 0-.193.27C1.1 7.09 1 7.51 1 8s.1.912.24 1.19c.07.14.14.225.194.271a.2.2 0 0 0 .063.039H1.5l.006-.001.025-.012a.5.5 0 0 0 .118-.115c.287-.375.773-.872 1.469-.872H3.9a.5.5 0 0 1 .498.542l-.29 3.408a.5.5 0 0 0 .497.55h1.878c-.048-.166-.195-.352-.463-.557-.274-.21-.52-.528-.52-.943 0-.568.447-.947.862-1.154C6.807 10.123 7.387 10 8 10s1.193.123 1.638.346c.415.207.862.586.862 1.154 0 .415-.246.733-.52.943-.268.205-.415.39-.463.557h1.878a.5.5 0 0 0 .498-.55l-.001-.007-.29-3.4A.5.5 0 0 1 12.1 8.5h.782c.696 0 1.182.497 1.469.872.05.065.091.099.118.115l.025.012.006.001h.003a.2.2 0 0 0 .064-.039.9.9 0 0 0 .193-.27c.14-.28.24-.7.24-1.191s-.1-.912-.24-1.19a.9.9 0 0 0-.194-.271.2.2 0 0 0-.063-.039H14.5l-.006.001-.025.012a.5.5 0 0 0-.118.115c-.287.375-.773.872-1.469.872H12.1a.5.5 0 0 1-.498-.543l.29-3.407a.5.5 0 0 0-.497-.55H9.517c.048.166.195.352.463.557.274.21.52.528.52.943 0 .568-.447.947-.862 1.154C9.193 5.877 8.613 6 8 6s-1.193-.123-1.638-.346C5.947 5.447 5.5 5.068 5.5 4.5c0-.415.246-.733.52-.943.268-.205.415-.39.463-.557z" />
						    </svg> </div>
                            <div>
                                <h3 class="fs-2 text-body-emphasis mb-0">Parts</h3>
                                <div class="mt-2">
                                    <span class="badge bg-secondary"><?php echo number_format($stats['parts']['total_parts']); ?> total</span>
                                    <span class="badge bg-info"><?php echo number_format($stats['parts']['compositions_with_parts']); ?> compositions</span>
                                </div>
                            </div>
                        </div>
                        <p>Work with instrument parts for each composition. You find which parts are in a composition, and where they are located.</p>
                        <a href="/parts" class="btn btn-secondary">
                            <i class="fas fa-puzzle-piece"></i> Manage parts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Playgrams -->
            <div class="feature col">
                <div class="card h-100 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
      				        <div class="icon-square text-body-emphasis bg-body-primary d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3"> <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
							    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z" />
					    		<path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0" />
					    	</svg></div>
                            <div>
                                <h3 class="fs-2 text-body-emphasis mb-0">Playgrams</h3>
                                <div class="mt-2">
                                    <span class="badge bg-secondary"><?php echo number_format($stats['playgrams']['total_playgrams']); ?> total</span>
                                </div>
                            </div>
                        </div>
                        <p>Work with "playgrams". Playgrams are program playlists, or lists of compositions to be performed, in order, at a concert.</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/playgrams" class="btn btn-secondary">
                                <i class="fas fa-list-ol"></i> Manage playgrams
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Concerts and Recordings Section -->
        <div class="row g-4 py-4 row-cols-1 row-cols-lg-2">
            <!-- Concerts -->
            <div class="feature col">
                <div class="card h-100 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-square text-body-emphasis bg-body-primary d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3"> <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-music-note-list" viewBox="0 0 16 16">
                                <path d="M12 13c0 1.105-1.12 2-2.5 2S7 14.105 7 13s1.12-2 2.5-2 2.5.895 2.5 2" />
                                <path fill-rule="evenodd" d="M12 3v10h-1V3z" />
                                <path d="M11 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 16 2.22V4l-5 1z" />
                                <path fill-rule="evenodd" d="M0 11.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5m0-4A.5.5 0 0 1 .5 7H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5m0-4A.5.5 0 0 1 .5 3H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5" />
		    				</svg></div>
                            <div>
                                <h3 class="fs-2 text-body-emphasis mb-0">Concerts</h3>
                                <div class="mt-2">
                                    <span class="badge bg-secondary"><?php echo number_format($stats['concerts']['concert_dates']); ?> unique dates</span>
                                </div>
                            </div>
                        </div>
                        <p>Work with concerts. Concerts are performances of playlists at a venue on a particular date.</p>
                        <a href="/concerts" class="btn btn-secondary">
                            <i class="fas fa-calendar-alt"></i> Manage concerts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recordings -->
            <div class="feature col">
                <div class="card h-100 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
					        <div class="icon-square text-body-emphasis bg-body-primary d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3"> <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-cassette" viewBox="0 0 16 16">
							    <path d="M4 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2m9-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 6a1 1 0 0 0 0 2h2a1 1 0 1 0 0-2z" />
							    <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zM1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-.691l-1.362-2.724A.5.5 0 0 0 12 10H4a.5.5 0 0 0-.447.276L2.19 13H1.5a.5.5 0 0 1-.5-.5zM11.691 11l1 2H3.309l1-2z" />
						    </svg> </div>
                            <div>
                                <h3 class="fs-2 text-body-emphasis mb-0">Recordings</h3>
                                <div class="d-flex gap-3 mt-2">
                                    <span class="badge bg-secondary"><?php echo number_format($stats['recordings']['recorded_compositions']); ?> compositions</span>
                                    <span class="badge bg-info"><?php echo number_format($stats['recordings']['recording_sessions']); ?> sessions</span>
                                </div>
                            </div>
                        </div>
                        <p>Work with recordings. Recordings are digital audio files that captured a concert performance of one composition at a concert.</p>
                        <a href="/recordings" class="btn btn-secondary">
                            <i class="fas fa-microphone"></i> Manage recordings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supporting Data & Configuration -->
        <div class="row mt-5 border-bottom">
            <div class="col-12">
                <h2>Supporting data</h2>
            </div>
        </div>
        
        <div class="row g-3 py-4">
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-shadows text-secondary" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-8 7a.5.5 0 0 1 0-1h3.5q.048 0 .093.009A7 7 0 0 0 12.9 13H8a.5.5 0 0 1 0-1h5.745q.331-.474.581-1H8a.5.5 0 0 1 0-1h6.71a7 7 0 0 0 .22-1H8a.5.5 0 0 1 0-1h7q0-.51-.07-1H8a.5.5 0 0 1 0-1h6.71a7 7 0 0 0-.384-1H8a.5.5 0 0 1 0-1h5.745a7 7 0 0 0-.846-1H8a.5.5 0 0 1 0-1h3.608A7 7 0 1 0 8 15" />
                            </svg>
                        </div>
                        <h6 class="card-title text-secondary">Instruments</h6>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo number_format($stats['instruments']); ?> items</small>
                        </div>
                        <a href="/instruments" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-list text-secondary" style="font-size: 32px;"></i>
                        </div>
                        <h6 class="card-title text-secondary">Part types</h6>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo number_format($stats['part_types']); ?> items</small>
                        </div>
                        <a href="/parttypes" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users text-secondary" style="font-size: 32px;"></i>
                        </div>
                        <h6 class="card-title text-secondary">Ensembles</h6>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo number_format($stats['ensembles']); ?> items</small>
                        </div>
                        <a href="/ensembles" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-tags text-secondary" style="font-size: 32px;"></i>
                        </div>
                        <h6 class="card-title text-secondary">Genres</h6>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo number_format($stats['genres']); ?> items</small>
                        </div>
                        <a href="/genres" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-layer-group text-secondary" style="font-size: 32px;"></i>
                        </div>
                        <h6 class="card-title text-secondary">Part collections</h6>
                        <div class="mb-2">
                            <small class="text-muted">Collections</small>
                        </div>
                        <a href="/partcollections" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="card border-secondary h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-file-alt text-secondary" style="font-size: 32px;"></i>
                        </div>
                        <h6 class="card-title text-secondary">Paper sizes</h6>
                        <div class="mb-2">
                            <small class="text-muted">Page formats</small>
                        </div>
                        <a href="/papersizes" class="btn btn-outline-secondary btn-sm">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analysis -->
        <div class="row mt-5 mb-4 border-bottom">
            <div class="col-12">
                <h2>Charts and analysis</h2>
            </div>
        </div>
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Recent composition activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activity)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_activity as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['name']); ?></h6>
                                        <small><?php echo date('M j, Y', strtotime($activity['last_update'])); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <strong><?php echo htmlspecialchars($activity['catalog_number']); ?></strong>
                                        <?php if ($activity['composer']): ?>
                                        - <?php echo htmlspecialchars($activity['composer']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <small class="text-muted">Updated <?php echo ucfirst($activity['type']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No recent activity</p>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="/compositions" class="btn btn-outline-primary btn-sm">View All Compositions</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-puzzle-piece"></i> Recent parts activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_parts_activity)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_parts_activity as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['composition_name']); ?></h6>
                                        <small><?php echo date('M j, Y', strtotime($activity['last_update_date'])); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <strong><?php echo htmlspecialchars($activity['catalog_number']); ?></strong>
                                        <?php if ($activity['composer']): ?>
                                        - <?php echo htmlspecialchars($activity['composer']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small class="text-muted"><?php echo $activity['parts_updated']; ?> part<?php echo $activity['parts_updated'] > 1 ? 's' : ''; ?> updated</small>
                                        <?php if ($activity['part_types_updated']): ?>
                                        <div class="flex-shrink-0">
                                            <?php 
                                            $part_types = explode(', ', $activity['part_types_updated']);
                                            $display_types = array_slice($part_types, 0, 3); // Show max 3 types
                                            foreach ($display_types as $type): ?>
                                                <span class="badge bg-info me-1"><?php echo htmlspecialchars($type); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($part_types) > 3): ?>
                                                <span class="badge bg-secondary">+<?php echo count($part_types) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No recent parts activity</p>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="/parts" class="btn btn-outline-primary btn-sm">View All Parts</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Compositions by genre</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($genre_stats)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Genre</th>
                                            <th class="text-end">Count</th>
                                            <th>Distribution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $max_count = max(array_column($genre_stats, 'count'));
                                        foreach ($genre_stats as $genre): 
                                            $percentage = $max_count > 0 ? ($genre['count'] / $max_count) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($genre['genre_name']); ?></td>
                                            <td class="text-end"><?php echo number_format($genre['count']); ?></td>
                                            <td>
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                         style="width: <?php echo $percentage; ?>%" 
                                                         aria-valuenow="<?php echo $genre['count']; ?>" 
                                                         aria-valuemin="0" aria-valuemax="<?php echo $max_count; ?>"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No genre data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Compositions by ensemble</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ensemble_stats)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ensemble</th>
                                            <th class="text-end">Count</th>
                                            <th>Distribution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $max_count = max(array_column($ensemble_stats, 'count'));
                                        foreach ($ensemble_stats as $ensemble): 
                                            $percentage = $max_count > 0 ? ($ensemble['count'] / $max_count) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ensemble['ensemble_name']); ?></td>
                                            <td class="text-end"><?php echo number_format($ensemble['count']); ?></td>
                                            <td>
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?php echo $percentage; ?>%" 
                                                         aria-valuenow="<?php echo $ensemble['count']; ?>" 
                                                         aria-valuemin="0" aria-valuemax="<?php echo $max_count; ?>"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No ensemble data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analysis -->
        <div class="row mt-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Performance activity by genre</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($performance_stats)): ?>
                            <div class="row">
                                <div class="col-lg-8">
                                    <!-- Bar Chart -->
                                    <div class="chart-container" style="position: relative; height: 400px;">
                                        <canvas id="performanceChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <!-- Performance Statistics Table -->
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Genre</th>
                                                    <th class="text-center">Recordings</th>
                                                    <th class="text-center">Concerts</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($performance_stats as $stat): ?>
                                                <tr>
                                                    <td><small><?php echo htmlspecialchars($stat['genre_name']); ?></small></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info"><?php echo $stat['recordings_count']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning"><?php echo $stat['concert_performances']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success"><?php echo $stat['total_performances']; ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Shows recordings and concert performances by genre. 
                                            This helps identify which genres are most actively performed.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No performance data available yet.</p>
                                <small>Performance data will appear when recordings or concerts are added to the system.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue Analysis -->
        <div class="row mt-4">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Concerts by venue</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($venue_stats)): ?>
                            <div class="row">
                                <div class="col-lg-8">
                                    <!-- Pie Chart -->
                                    <div class="chart-container" style="position: relative; height: 300px;">
                                        <canvas id="venueChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <!-- Venue Statistics Table -->
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Venue</th>
                                                    <th class="text-center">Concerts</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($venue_stats as $venue): ?>
                                                <tr>
                                                    <td><small><?php echo htmlspecialchars($venue['venue']); ?></small></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary"><?php echo $venue['concert_count']; ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> Shows the distribution of concerts across different venues. 
                                            This helps identify the most frequently used performance locations.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No venue data available yet.</p>
                                <small>Venue data will appear when concerts are added to the system.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    </div>
</main>

<?php require_once(__DIR__. "/includes/footer.php");?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function(){
    // Home page loaded
    console.log("Home page loaded");

    <?php if (!empty($performance_stats)): ?>
    // Performance Chart by Genre
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php foreach ($performance_stats as $stat): ?>
                '<?php echo addslashes($stat['genre_name']); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Recordings',
                data: [
                    <?php foreach ($performance_stats as $stat): ?>
                    <?php echo $stat['recordings_count']; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: 'rgba(13, 202, 240, 0.7)',
                borderColor: 'rgba(13, 202, 240, 1)',
                borderWidth: 1
            }, {
                label: 'Concert Performances',
                data: [
                    <?php foreach ($performance_stats as $stat): ?>
                    <?php echo $stat['concert_performances']; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Performance activity by genre',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of performances'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Genre'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($venue_stats)): ?>
    // Venue Pie Chart
    const venueCtx = document.getElementById('venueChart').getContext('2d');
    const venueChart = new Chart(venueCtx, {
        type: 'pie',
        data: {
            labels: [
                <?php foreach ($venue_stats as $venue): ?>
                '<?php echo addslashes($venue['venue']); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($venue_stats as $venue): ?>
                    <?php echo $venue['concert_count']; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',   // Red
                    'rgba(54, 162, 235, 0.8)',   // Blue
                    'rgba(255, 205, 86, 0.8)',   // Yellow
                    'rgba(75, 192, 192, 0.8)',   // Green
                    'rgba(153, 102, 255, 0.8)',  // Purple
                    'rgba(255, 159, 64, 0.8)',   // Orange
                    'rgba(199, 199, 199, 0.8)',  // Grey
                    'rgba(83, 102, 255, 0.8)',   // Indigo
                    'rgba(255, 99, 255, 0.8)',   // Pink
                    'rgba(99, 255, 132, 0.8)'    // Light Green
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(255, 99, 255, 1)',
                    'rgba(99, 255, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Concert distribution by venue',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' concerts (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

</body>
</html>
