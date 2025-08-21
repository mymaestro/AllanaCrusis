<header>
    <nav class="navbar navbar-expand-lg fixed-top bg-body-tertiary" aria-label="Main navigation">
        <div class="container-xl">
            <a class="navbar-brand" href="/"><img src="<?php echo ORGLOGO ?>" alt="<?php echo ORGNAME ?>" height="32"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTop">
                <ul class="navbar-nav nav-underline me-auto mb-2 mb-lg-0">
                    <li class="nav-item<?php echo ( PAGE_NAME === 'home' ? ' active">' : '">') ?><a class="nav-link text-uppercase" href="/home"><i class="fa fa-home"></i> Home</a></li>
                    <li class="nav-item<?php echo ( PAGE_NAME === 'about' ? ' active">' : '">') ?><a class="nav-link text-uppercase" href="/about"><i class="fas fa-info-circle"></i> About</a></li>
                    <li class="nav-item<?php echo ( PAGE_NAME === 'search' ? ' active">' : '">') ?><a class="nav-link text-uppercase" href="/search"><i class="fas fa-search"></i> Search</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="nav_menu_dropdown" data-bs-toggle="dropdown" aria-expanded="false">MATERIALS</a>
                        <ul class="dropdown-menu" aria-labelledby="nav_menu_dropdown">
                        <li><a class="dropdown-item" href="/compositions"><i class="fas fa-music"></i> Compositions</a></li>
                        <li><a class="dropdown-item" href="/concerts"><i class="fas fa-calendar"></i> Concerts</a></li>
                        <li><a class="dropdown-item" href="/parts"><i class="fas fa-file-music"></i> Parts</a></li>
                        <li><a class="dropdown-item" href="/recordings"><i class="fas fa-microphone"></i> Recordings</a></li>
                        <li><a class="dropdown-item" href="/reports"><i class="fas fa-chart-line"></i> Reports</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/ensembles"><i class="fas fa-users"></i> Ensembles</a></li>
                        <li><a class="dropdown-item" href="/instruments"><i class="fas fa-guitar"></i> Instruments</a></li>
                        <li><a class="dropdown-item" href="/genres"><i class="fas fa-music"></i> Genres</a></li>
                        <li><a class="dropdown-item" href="/papersizes"><i class="fas fa-file-pdf"></i> Paper sizes</a></li>
                        <li><a class="dropdown-item" href="/parttypes"><i class="fas fa-file-alt"></i> Part types</a></li><?php if (isset($_SESSION['username'])) if (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ) echo '
                        <li><a class="dropdown-item" href="/playgrams"><i class="fas fa-play-circle"></i> Playgrams</a></li>
                        <li><a class="dropdown-item" href="/sections"><i class="fas fa-th-list"></i> Sections</a></li>
'; ?>
<?php if (isset($_SESSION['username'])) if (strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE ) echo '
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/enable_disable_manager"><i class="fas fa-toggle-on"></i> Enable/Disable Manager</a></li>
'; ?><?php if (isset($_SESSION['username'])) if (strpos(htmlspecialchars($_SESSION['roles']), 'administrator') !== FALSE ) echo '
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin_verifications"><i class="fas fa-user-check"></i> Password reset & email verification</a></li>
                        <li><a class="dropdown-item" href="/users"><i class="fas fa-users"></i> Users</a></li>
'; ?>
                        </ul>

                </li>
            </ul>

            <p class="nav navbar-right">
                <?php if (isset($_SESSION['username'])) {
            echo '<a href="/logout"><i class="fas fa-unlock"></i>' . $_SESSION['username'];
        } else {
            echo '<a href="/login"><i class="fas fa-lock"></i>';
        } ?></a></p>
            </div>
        </div>
    </nav>
</header>
