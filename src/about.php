<?php
  define('PAGE_TITLE', 'About the music library');
  define('PAGE_NAME', 'about');
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
  require_once(__DIR__. "/includes/config.php");
  require_once(__DIR__. "/includes/navbar.php");
?>
<main role="main" class="container">
  <div class="container">
    <div class="row pb-3 pt-5 border-bottom"><h1><?php echo ORGNAME; ?> music library</h1></div>

    <div class="row pt-3">
      <div class="col-12">
          <h4>Welcome to the music library</h4>
          <p>The music library is a comprehensive application for managing sheet music collections for concert bands, brass bands, wind ensembles, orchestras, and other large music ensembles. This guide helps you navigate and use its features.</p>
      </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-12">
        <h2><i class="fas fa-compass"></i> Navigation</h2>
        <p>The system is organized into several main areas you can access from the top navigation bar:</p>
        
        <div class="row">
          <div class="col-md-6">
            <h4>Primary navigation</h4>
            <ul class="list-group">
              <li class="list-group-item"><strong><i class="fa fa-home"></i> Home</strong> - Statistics and quick access to key functions</li>
              <li class="list-group-item"><strong><i class="fas fa-id-card"></i> About</strong> - Information about the music library (the page you are viewing)</li>
              <li class="list-group-item"><strong><i class="fas fa-search"></i> Search</strong> - Browse and search the music library</li>
              <li class="list-group-item"><strong><i class="fas fa-folder"></i> Materials</strong> - Dropdown menu with data management pages</li>
              <li class="list-group-item"><strong><i class="fas fa-lock"></i> Login</strong> - User authentication (shows your username when logged in)</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h4>User access levels</h4>
            <div class="card">
              <div class="card-body">
                <h6 class="card-title text-success"><i class="fas fa-eye"></i> User (read-only)</h6>
                <p class="card-text small">Users can browse, search, and view all library materials but cannot make changes.</p>
                
                <h6 class="card-title text-warning"><i class="fas fa-edit"></i> Librarian</h6>
                <p class="card-text small">Librarians can add, edit, and manage compositions, parts, concerts, recordings, and most library data.</p>

                <h6 class="card-title text-danger"><i class="fas fa-cog"></i> Administrator</h6>
                <p class="card-text small">Administrators have full system access including user management and system configuration.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row pt-4">
      <div class="col-md-12">
        <h2><i class="fas fa-lightbulb"></i> Getting started</h2>
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-play"></i> For new users</h5>
              </div>
              <div class="card-body">
                <ol>
                  <li>Start with the <strong>Home page</strong> to get an overview</li>
                  <li>Use <strong>Search</strong> to explore existing content</li>
                  <li>Browse <strong>Compositions</strong> to see the main catalog</li>
                  <li>Check <strong>Recordings</strong> for audio examples</li>
                  <li>Contact a librarian for editing access</li>
                </ol>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-header bg-warning text-dark">
                <h5><i class="fas fa-edit"></i> For librarians</h5>
              </div>
              <div class="card-body">
                <ol>
                  <li>Set up <strong>Paper sizes</strong> and <strong>Genres</strong> first.</li>
                  <li>Add <strong>Ensembles</strong> as needed.</li>
                  <li>Create <strong>Instruments</strong> and set the score order.</li>
                  <li>Set up <strong>Part Types</strong> as needed. They typically appear on the first page of the score.</li>
                  <li>Define <strong>Sections</strong> for organizing parts.</li>
                  <li>Enter <strong>Compositions</strong> with complete metadata.</li>
                  <li>Add <strong>Parts</strong> for each composition.</li>
                  <li>Create <strong>Playgrams</strong> and schedule <strong>Concerts</strong>.</li>
                  <li>Upload <strong>Recordings</strong> after performances.</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row pt-4">
      <div class="col-md-12">
        <h2><i class="fas fa-map"></i> Pages guide</h2>
        
        <div class="accordion" id="pageGuideAccordion">
          
          <!-- Core Pages -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingCore">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCore" aria-expanded="true" aria-controls="collapseCore">
                <i class="fas fa-star"></i> &nbsp; Core pages - Start here
              </button>
            </h2>
            <div id="collapseCore" class="accordion-collapse collapse show" aria-labelledby="headingCore" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <dl>
                  <dt><a href="/index"><i class="fa fa-hands"></i> Welcome</a></dt>
                  <dd>You see the Welcome page first. You'll find an introduction to the library's features and a random selection of featured compositions and recordings.</dd>

                  <dt><a href="/home"><i class="fa fa-home"></i> Home</a></dt>
                  <dd>Use this page to get quick insights and statistics about your music library. You see composition counts, grade distribution breakdowns, ensemble summaries, and recent activity. Everyone can access the basic dashboard, but you see additional features when you're logged in.</dd>

                  <dt><a href="/search"><i class="fas fa-search"></i> Search</a></dt>
                  <dd>Browse and search through all library materials here. You can perform full-text searches, use filtering options to narrow results, and click on links to view detailed information about compositions, parts, and recordings. You don't need to log in to use this feature.</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Support Tables -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingSupport">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                <i class="fas fa-cogs"></i> &nbsp; Foundational data & configuration
              </button>
            </h2>
            <div id="collapseSupport" class="accordion-collapse collapse" aria-labelledby="headingSupport" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <p class="mb-3"><strong>These are the settings that support all your other data entry.</strong> You typically set up these configurations once when first organizing your library, and they rarely need regular updates afterward. However, it's important to configure these properly before adding compositions and parts, as they provide the structure and organization for everything else in your system.</p>

                <dl>
                  <dt><a href="/papersizes"><i class="fas fa-file"></i> Paper sizes</a></dt>
                  <dd>Track the physical dimensions of your sheet music using standard and custom paper size definitions from this page. You record measurements for proper inventory management and storage organization, ensuring you know exactly what size folders or storage systems you need for each piece, and potentially how much storage space your library requires.</dd>

                  <dt><a href="/ensembles"><i class="fas fa-users"></i> Ensembles</a></dt>
                  <dd>Define the different performing groups in your organization such as Concert Band, Wind Ensemble, Brass Quintet, or String Orchestra through this page. You use these ensemble categories to tag compositions, indicating which groups can perform specific pieces. This helps users find repertoire appropriate for their particular ensemble configuration.</dd>

                  <dt><a href="/instruments"><i class="fas fa-drum"></i> Instruments</a></dt>
                  <dd>Maintain the master list of all instruments available in your organization using this page. You can organize instruments by family groups and set their orchestral collation order to ensure consistent presentation throughout the system. This helps standardize how instruments appear in reports and part assignments.</dd>

                  <dt><a href="/parttypes"><i class="fas fa-tags"></i> Part types</a></dt>
                  <dd>Define the types of instrument parts used in your compositions through this page. You create entries like "Flute 1", "Trumpet 2", and "Percussion" while setting their orchestral order, assigning default instruments, and organizing them by family. This setup is crucial because you must have part types defined before you can add individual parts to any compositions in your library.</dd>

                  <dt><a href="/genres"><i class="fas fa-th-list"></i> Genres</a></dt>
                  <dd>Set up your music classification system using categories like March, Jazz, Transcription, Holiday, or Pop from this page. You assign genres to compositions to help organize and filter your collection by musical style, making it easier for users to find pieces that fit their programming needs.</dd>

                  <dt><a href="/sections"><i class="fas fa-layer-group"></i> Sections</a></dt>
                  <dd>Group your part types into logical sections such as Brass, Woodwinds, Percussion, and Strings through this page. You can assign section leaders and organize large ensembles more effectively by creating these groupings, which helps with rehearsal planning and music distribution.</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Music Materials -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingMusic">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMusic" aria-expanded="false" aria-controls="collapseMusic">
                <i class="fas fa-music"></i> &nbsp; Music materials
              </button>
            </h2>
            <div id="collapseMusic" class="accordion-collapse collapse" aria-labelledby="headingMusic" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <dl>
                  <dt><a href="/compositions"><i class="fas fa-music"></i> Compositions</a></dt>
                  <dd>Manage the main catalog of all musical works in your library here. You can add and edit compositions while tracking composer and arranger information, grade levels, performance notes, and storage locations. The system tracks catalog numbers, titles, composers, arrangers, publishers, genres, difficulty grades, and durations to help you organize your collection. Only librarians can edit this content.</dd>

                  <dt><a href="/parts"><i class="fas fa-puzzle-piece"></i> Parts</a></dt>
                  <dd>Use this page to manage individual instrument parts for each composition. You select a composition from the left panel, then manage its associated parts on the right side. The system tracks physical copies, page counts, and paper sizes to help with inventory management. If you are digitizing your library, you can upload a PDF file of each part here, and the system will automatically apply relevant metadata to the PDF file. Only librarians can edit parts information.</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Concert Management -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingConcerts">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConcerts" aria-expanded="false" aria-controls="collapseConcerts">
                <i class="fas fa-calendar-alt"></i> &nbsp; Concert & performance
              </button>
            </h2>
            <div id="collapseConcerts" class="accordion-collapse collapse" aria-labelledby="headingConcerts" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <dl>
                  <dt><a href="/playgrams"><i class="fas fa-list-ol"></i> Playgrams (concert programs)</a></dt>
                  <dd>Create and manage concert programs and playlists through this page. You build ordered lists of compositions for performances by first creating a playgram, then adding compositions in the sequence they will be performed. This helps you organize your concert repertoire and plan performance timing. Only librarians can edit playgrams.</dd>

                  <dt><a href="/part_distribution"><i class="fas fa-share-alt"></i> Part distribution</a></dt>
                  <dd>Generate lists of parts needed for concerts from this page. You can see which instrument parts are required for each playgram or concert, making it easy to prepare music folders and organize rehearsals efficiently. Parts are organized by section, so that you can download your section's parts as a ZIP file. Only librarians can access this feature.</dd>

                  <dt><a href="/concerts"><i class="fas fa-music"></i> Concerts</a></dt>
                  <dd>Schedule and track actual performance events using this page. You can link playgrams to specific performance dates and venues while tracking conductors and performance notes. The system records performance dates, venue information, conductor details, and connects everything to your prepared playgrams for complete concert documentation. Recordings reference the concert, so that you can easily find and manage them. Only librarians can manage concert information.</dd>

                  <dt><a href="/recordings"><i class="fas fa-play-circle"></i> Recordings</a></dt>
                  <dd>Catalog and store audio recordings of performances through this page. You can upload MP3 files up to 40MB, link recordings to specific concerts and compositions, and the system automatically handles metadata tagging. The built-in audio player lets you listen to recordings directly, and ID3 tags are written automatically for proper organization. Only librarians and administrators can manage recordings.</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Reports & Analysis -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingReports">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReports" aria-expanded="false" aria-controls="collapseReports">
                <i class="fas fa-chart-bar"></i> &nbsp; Reports & analysis
              </button>
            </h2>
            <div id="collapseReports" class="accordion-collapse collapse" aria-labelledby="headingReports" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <dl>
                  <dt><a href="/reports"><i class="fas fa-chart-line"></i> Reports</a></dt>
                  <dd>Generate comprehensive reports about your music library through this page. You can create composition lists, parts inventory reports, performance history summaries, and missing parts analysis to help manage your collection effectively. The system provides multiple export options, allowing you to save many reports in CSV or PDF formats for sharing with other staff members or for your own record-keeping purposes.</dd>
                </dl>
              </div>
            </div>
          </div>

          <!-- Admin Only -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingAdmin">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin" aria-expanded="false" aria-controls="collapseAdmin">
                <i class="fas fa-shield-alt"></i> &nbsp; Administrative functions
              </button>
            </h2>
            <div id="collapseAdmin" class="accordion-collapse collapse" aria-labelledby="headingAdmin" data-bs-parent="#pageGuideAccordion">
              <div class="accordion-body">
                <dl>                  
                  <dt><a href="/enable_disable_manager"><i class="fas fa-toggle-on"></i> Enable/Disable Manager</a> <span class="badge bg-warning">Librarian+</span></dt>
                  <dd>Perform bulk enable and disable operations across all database tables using this management tool. You can quickly hide outdated or inactive entries without permanently deleting them, which is helpful when you want to clean up your interface while preserving historical data for future reference.</dd>

                  <dt><a href="/partcollections"><i class="fas fa-layer-group"></i> Part Collections</a> <span class="badge bg-warning">Librarian+</span></dt>
                  <dd>Manage collections of parts that are shared among multiple instruments, such as "Percussion 1" or "Flutes". For example, "Percussion 1" might contain "Snare Drum" and "Bass Drum". This page allows you to create and edit which instruments are found on one part, so that you can track which instruments are needed to perform a specific composition.</dd>

                  <dt><a href="/users"><i class="fas fa-users-cog"></i> User Management</a> <span class="badge bg-danger">Admin Only</span></dt>
                  <dd>Manage all user accounts and permissions through this administrative interface. You can add new users, edit existing accounts, and assign roles such as Administrator, Librarian, or User to control access levels throughout the system. The page also provides security functions where you can change passwords and manage access levels to ensure proper system security.</dd>

                  <dt><a href="/admin_verifications"><i class="fas fa-key"></i> Password Reset & Email Verification</a> <span class="badge bg-danger">Admin Only</span></dt>
                  <dd>Handle password reset requests and email verification processes from this administrative page. You can view all pending requests and manually verify user accounts when automatic email verification isn't working or when users need immediate access to the system.</dd>

                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row pt-4">
      <div class="col-md-12">
        <h2><i class="fas fa-question-circle"></i> Music library concepts</h2>
        <div class="alert alert-info">
          <h5>Key terminology:</h5>
          <ul>
            <li><strong>Composition:</strong> A complete musical work (like "Stars and Stripes Forever")</li>
            <li><strong>Parts:</strong> Individual instrument sheets (Flute 1 part, Trumpet 2 part, etc.)</li>
            <li><strong>Playgram:</strong> A concert program/playlist - the order of pieces to be performed</li>
            <li><strong>Ensemble:</strong> A performing group (Full Band, Brass Quintet, Flute Choir)</li>
            <li><strong>Grade:</strong> Difficulty level from 1 (beginner) to 6 (professional)</li>
            <li><strong>Part collection:</strong> When multiple instruments share one physical part (like "Percussion 1")</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="row pt-4">
      <div class="col-md-12">
        <h2><i class="fas fa-desktop"></i> Technical requirements</h2>
        <div class="row">
          <div class="col-md-6">
            <h5>Supported browsers</h5>
            <ul>
              <li>Chrome 90+ (recommended)</li>
              <li>Firefox 88+</li>
              <li>Safari 14+</li>
              <li>Edge 90+</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h5>Security requirements</h5>
            <ul>
              <li>HTTPS connection recommended</li>
              <li>TLS v1.2 or higher</li>
              <li>JavaScript must be enabled</li>
              <li>Cookies enabled for login sessions</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row pt-4 pb-4">
      <div class="col-md-12">
        <div class="alert alert-secondary">
          <h5><i class="fas fa-external-link-alt"></i> Additional resources</h5>
          <p>For more information about wind ensemble repertoire, visit the <a href="https://www.windrep.org/" target="_blank">Wind Repertory Project</a>.</p>
          <p><small>This system uses responsive front-end designs built on <a href="https://getbootstrap.com/" target="_blank">Bootstrap</a> and is optimized for modern web browsers.</small></p>
        </div>
      </div>
    </div>
  </div>
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
</body>
</html>
