<!DOCTYPE html>
<html lang="en">
<!-- Warren Gill ACWE
#############################################################################
# Licensed Materials - Property of ACWE*
# (C) Copyright Austin Civic Wind Ensemble 2022, 2026 All rights reserved.
#############################################################################
-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Music library system">
    <meta name="author" content="Warren Gill">
    <link rel="icon" href="favicon.ico">
    <!-- Favorite icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title><?php echo PAGE_TITLE ?></title>

    <!-- Bootswatch Yeti https://bootswatch.com/yeti/ -->
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.7/dist/yeti/bootstrap.min.css" rel="stylesheet">

    <!-- Uncomment to use Bootstrap core CSS instead of Bootswatch theme
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    -->

    <!-- Font Awesome for icons -->
    <!-- https://use.fontawesome.com/releases/v5.0.8/css/all.css" -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

    <!-- Prevent navbar overlay -->
    <style>
    html, body {
        padding-top: 30px;
        height: 100%;
        margin: 0;
    }
    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
    }
    #btn-back-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: none;
    }
    body {
      display: flex;
      flex-direction: column;
    }

    header .navbar {
      height: 56px;
    }

    /* Fix dropdown menu background in collapsed/mobile view */
    @media (max-width: 991.98px) {
        /* Main collapsed navbar background */
        .navbar-collapse {
            background-color: #f8f9fa !important;
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0,0,0,.15);
        }
        
        .navbar-collapse .nav-item {
            background-color: transparent;
        }
        
        .navbar-collapse .nav-link {
            color: #212529;
            padding: 0.5rem 1rem;
        }
        
        .navbar-collapse .nav-link:hover,
        .navbar-collapse .nav-link:focus {
            background-color: #e9ecef;
            border-radius: 0.25rem;
        }
        
        /* Dropdown menu styling */
        .navbar-collapse .dropdown-menu {
            background-color: #ffffff !important;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            margin-left: 1rem;
        }
        
        .navbar-collapse .dropdown-menu.show {
            background-color: #ffffff !important;
        }
        
        .navbar-collapse .dropdown-item {
            padding: 0.5rem 1.5rem;
            background-color: transparent;
        }
        
        .navbar-collapse .dropdown-item:hover,
        .navbar-collapse .dropdown-item:focus {
            background-color: #e9ecef !important;
        }
    }


    footer {
      flex-shrink: 0;
    }
    </style>

<?php if (PAGE_NAME == 'Parts') : ?>
   <!-- this is the PARTS page 
        eventually you will be able to upload PDF parts here -->
    <style>
    main {
      flex: 1;
      display: flex;
      overflow: hidden;
    }

    aside.left-panel {
      width: 280px;
      border-right: 1px solid #ccc;
      background: #f8f9fa;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease-in-out;
    }
    
    /* Mobile/Tablet: Collapsible left panel */
    @media (max-width: 991.98px) {
      aside.left-panel {
        position: fixed;
        left: 0;
        top: 86px; /* Below navbar */
        bottom: 0; /* Go all the way to bottom */
        height: calc(100vh - 86px); /* Full height minus navbar */
        z-index: 1050;
        transform: translateX(-100%);
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      }
      
      aside.left-panel.show {
        transform: translateX(0);
      }
      
      .left-panel-toggle {
        display: block !important;
        position: fixed;
        left: 10px;
        top: 100px;
        z-index: 1049;
        background: #0d6efd;
        color: white;
        border: none;
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      }
      
      .left-panel-toggle:hover {
        background: #0b5ed7;
      }
      
      /* Backdrop overlay */
      .left-panel-backdrop {
        display: none;
        position: fixed;
        top: 86px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
      }
      
      .left-panel-backdrop.show {
        display: block;
      }
      
      section.right-panel {
        padding: 0.5rem;
      }
    }
    
    /* Desktop: Hide toggle button */
    .left-panel-toggle {
      display: none;
    }

    #composition_header {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        display: block;
        line-height: 1.5;
        padding-bottom: 2px;
    }
    
    .table-toolbar .row {
        align-items: center;
    }
    
    .table-toolbar .col.flex-shrink-0 {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
    }
    
    .left-menu-scroll,
    .table-wrapper {
      height: calc(100vh - 310px); /* Adjusted for single-line header: navbar + title + toolbar + footer + padding */
      overflow-y: auto;
      flex-grow: 1;
    }
    
    /* Mobile: Adjust left-menu-scroll height and add bottom padding */
    @media (max-width: 991.98px) {
      .left-menu-scroll {
        height: calc(100vh - 140px); /* Account for navbar and search box */
        padding-bottom: 2rem; /* Extra space at bottom for easier scrolling */
      }
    }

    section.right-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      padding: 1rem;
    }

    .table-toolbar {
      position: sticky;
      top: 0;
      background: #fff;
      z-index: 2;
      padding-bottom: 0.5rem;
      width: 100%;
      display: block;
    }
    
    .table-toolbar .row {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
    }

    .table-wrapper {
      flex: 1;
      overflow-y: auto;
      width: 100%;
    }

    .table thead th {
      position: sticky;
      top: 0;
      background-color: #f8f9fa;
      z-index: 1;
    }
  </style>
<?php elseif (PAGE_NAME == 'Compositions' || PAGE_NAME == 'Concerts' || PAGE_NAME == 'Genres' || PAGE_NAME == 'Instruments' || PAGE_NAME == 'PaperSizes' || PAGE_NAME == 'PartTypes' || PAGE_NAME == 'Playgrams' || PAGE_NAME == 'Recordings' || PAGE_NAME == 'Sections' || PAGE_NAME == 'Users') : ?>
    <style>
    main {
      flex: 1;
      display: flex;
      overflow: hidden;
    }
    .scrolling-data {
      height: calc(100vh - 296px); /* each row is 33px */
      /* Adjust this value based on your layout
        navbar = 56px
        title = 49px
        table toolbar = 47.33px
        footer = 64px
        body padding-top = 30px
        Total = 56 + 30 + 49 + 47.33 + 64 = 246.33px
       */
      overflow-y: auto;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    </style>
<?php endif; ?>

</head>
<body class="d-flex flex-column h-100">
