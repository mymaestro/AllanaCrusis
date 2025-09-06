<?php
  define('PAGE_TITLE','Music Library');
  define('PAGE_NAME', 'home');
  require_once(__DIR__. "/includes/header.php");
  $u_admin = FALSE;
  $u_librarian = FALSE;
  $u_user = FALSE;
  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $u_admin = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'administrator') !== FALSE ? TRUE : FALSE);
	$u_librarian = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'librarian') !== FALSE ? TRUE : FALSE);
    $u_user = (strpos(htmlspecialchars($_SESSION['roles'] ?? ''), 'user') !== FALSE ? TRUE : FALSE);
  }
  $configFile = __DIR__ . '/includes/config.php';
  if (!file_exists($configFile)) {
	echo "<div class='alert alert-danger'>Error: failed to read required config.php. Did you create it yet?</div>
	</body>
</html>";
	exit; // we are done here
  } else {
	require_once($configFile);
  };

  require_once(__DIR__. "/includes/navbar.php");
  require_once(__DIR__ . "/includes/functions.php");
  ferror_log("RUNNING index.php");
?>
<main role="main" class="container">
	<div class="px-4 py-5 my-5 text-center align-items-center rounded-3 border shadow-lg"> <img class="d-block mx-auto mb-4" src="<?php echo ORGLOGO ?>" alt="<?php echo ORGDESC ?>" height="256">
		<h1 class="display-5 fw-bold text-body-emphasis"><?php echo ORGDESC ?></h1>
		<div class="col-lg-6 mx-auto">
			<p class="lead mb-4">The <strong>music library</strong> is an application you can use to keep track of the sheet music in your library. While intended for full-size concert bands, brass bands, wind ensembles, and orchestras, the music database can be tailored to fit the complexity of any size library. </p>
			<div class="d-grid gap-2 d-sm-flex justify-content-sm-center"> <a href="/home" class="btn btn-primary btn-lg px-4 gap-3">Home</a> <a
					href="/about" class="btn btn-outline-secondary btn-lg px-4">About</a> </div>
		</div>
	</div>
	
	<div class="row g-4 py-4">
		<div class="col-md-6">
			<div class="card h-100">
				<div class="card-body text-center">
					<h5 class="card-title">Today's composition</h5>
					<?php
					require_once(__DIR__ . "/includes/functions.php");
					$f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
					$sql = "SELECT name, composer FROM compositions ORDER BY RAND() LIMIT 1;";
					$res = mysqli_query($f_link, $sql) or die('Error: ' . mysqli_error($f_link));
					while ($rowList = mysqli_fetch_array($res)) {
						$name = $rowList['name'];
						$composer = $rowList['composer'];
						echo "<p class='card-text'><strong><em>".$name.'</em></strong><br>by '.$composer.'</p>';
					}?>
				</div>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="card h-100">
				<div class="card-body text-center">
					<h5 class="card-title">Today's recording</h5>
					<?php
					$sql_recording = "SELECT 
						r.name AS recording_name,
						r.composer,
						c.name AS composition_name,
						con.venue,
						con.performance_date,
						r.link AS link,
						DATE_FORMAT(con.performance_date, '%M %d, %Y') AS formatted_date
					FROM recordings r
					LEFT JOIN compositions c ON r.catalog_number = c.catalog_number  
					LEFT JOIN concerts con ON r.id_concert = con.id_concert
					WHERE r.enabled = 1
					ORDER BY RAND() LIMIT 1;";
					$res_recording = mysqli_query($f_link, $sql_recording);
					if ($res_recording && mysqli_num_rows($res_recording) > 0) {
						while ($rowList = mysqli_fetch_array($res_recording)) {
							$recording_name = $rowList['recording_name'];
							$composition_name = $rowList['composition_name'];
							$composer = $rowList['composer'];
							$venue = $rowList['venue'];
							$date = $rowList['formatted_date'];
							$performance_date = $rowList['performance_date'];
							$link = $rowList['link'] ?? '';
							$playlink = !empty($link) ? ORGRECORDINGS . $performance_date . '/' . $link : '';
							
							$display_title = !empty($recording_name) ? $recording_name : $composition_name;
							echo "<p class='card-text'><strong><em><a href='".$playlink."' target='_blank'>".$display_title.'</a></em></strong><br>by '.$composer;
							if (!empty($venue) && !empty($date)) {
								echo '<br><small class="text-muted">Recorded at '.$venue.' on '.$date.'</small>';
							}
							echo '</p>';
						}
					} else {
						echo "<p class='card-text text-muted'>No recordings available</p>";
					}
					mysqli_close($f_link);
					?>
				</div>
			</div>
		</div>
	</div>
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
</body>
</html>