<?php
// Include config file
define('PAGE_TITLE', 'Privacy statement');
define('PAGE_NAME', 'privacy');
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__. "/includes/navbar.php");
ferror_log("RUNNING terms-conditions.php");
?>
<main>
  <div class="container">
  <h1>Terms and Conditions of Use</h1>
  <p><strong>Effective Date:</strong> January 1, 2025</p>

  <p>Welcome to <b><?php echo ORGDESC ?></b>! By accessing or using this site, you agree to the following terms and conditions. Please read them carefully.</p>

  <h2>1. Purpose of the Library</h2>
  <p>The <b><?php echo ORGNAME ?></b> sheet music library is maintained by and for the use of the <strong><?php echo ORGDESC ?></strong> and its members. Its purpose is to provide a centralized digital collection of sheet music for use in rehearsals and performances by the group and its affiliated musicians.</p>

  <h2>2. Use of Materials</h2>
  <ul>
    <li><strong>Internal Use Only:</strong> The materials found on this site are intended for use <strong>only by the members of <?php echo ORGNAME ?></strong>. This includes current players, conductors, librarians, and other supporting personnel.</li>
    <li><strong>Public Domain Works:</strong> Where applicable, public domain scores may be freely used within the group for performance and rehearsal purposes.</li>
    <li><strong>Contributed Works:</strong> Some materials have been contributed by arrangers or composers who retain copyright or have made their works available under specific terms (e.g., Creative Commons licenses). Please respect any usage restrictions noted on individual pieces.</li>
    <li><strong>No Redistribution:</strong> Users may <strong>not share, redistribute, or post</strong> materials from this library to external websites, repositories, or third parties without written permission from the site administrator or rights holder.</li>
    <li><strong>No Commercial Use:</strong> Content from this site may not be used for commercial purposes or resale under any circumstances.</li>
  </ul>

  <h2>3. User Contributions</h2>
  <ul>
    <li>Members may contribute music or arrangements to the library if they own the rights or have permission to do so.</li>
    <li>By submitting content, contributors grant <?php echo ORGNAME ?> and its members a <strong>non-exclusive, royalty-free license</strong> to use the material internally for performance and rehearsal.</li>
    <li>Submitted materials will not be redistributed outside the group without express permission from the contributor.</li>
  </ul>

  <h2>4. Membership and Access</h2>
  <ul>
    <li>Access to the site and its contents may be limited to verified members of <?php echo ORGDESC ?>.</li>
    <li>Members are responsible for maintaining the security of their login credentials.</li>
    <li>Sharing login access with non-members is prohibited.</li>
  </ul>

  <h2>5. Disclaimer of Warranties</h2>
  <p>All materials are provided “as-is” without warranties of any kind. We make no guarantees regarding the accuracy, completeness, or usability of content. The website and its administrators are not liable for any issues resulting from the use or interpretation of the sheet music or files provided.</p>

  <h2>6. Copyright Concerns</h2>
  <p>If you believe any content hosted on the site infringes your copyright, please notify us at <em><?php echo ORGMAIL ?></em>. We will investigate and remove any infringing content where appropriate.</p>

  <h2>7. External Links</h2>
  <p>This site may include links to external websites. We are not responsible for the content or privacy practices of those third-party sites.</p>

  <h2>8. Modifications to Terms</h2>
  <p>These terms may be updated periodically. Continued use of the site implies acceptance of the most current version.</p>

  <h2>9. Contact Us</h2>
  <p>For questions about these Terms and Conditions, or to report a concern, please contact:</p>
  <p>
    <strong><?php echo ORGDESC ?></strong><br>
    Email: <?php echo ORGMAIL ?><br>
    Website: <a href="<?php echo ORGHOME ?>"><?php echo ORGHOME ?></a>
  </p>
  </div>
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
</body>
</html>
