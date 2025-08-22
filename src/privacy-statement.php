<?php
// Include config file
define('PAGE_TITLE', 'Privacy statement');
define('PAGE_NAME', 'privacy');
require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . "/includes/config.php");
require_once(__DIR__. "/includes/navbar.php");
ferror_log("RUNNING privacy-statement.php");
?>
<main>
  <div class="container">
    <h1 class="display-4">Privacy Statement</h1>
    <p>Effective Date: January 1, 2025</p>
    <p>At <b><?php echo ORGNAME ?></b>, we are committed to protecting your privacy. This Privacy Statement explains how we collect, use, and protect your information when you use our digital library of sheet music.</p>
    <h3>1. Information We Collect</h3>
    <p>We may collect the following types of information:
        <ul>
            <li>Personal Information (optional): If you choose to register, contact us, or contribute content, we may collect your name, email address, and any information you provide.</li>
            <li>Usage Data: We collect non-personal data such as your browser type, device, pages visited, and time spent on the site. This helps us improve functionality and user experience.</li>
            <li>Cookies: Our site uses cookies for analytics and to ensure proper functionality (e.g., staying logged in). You can disable cookies in your browser settings.</li>
        </ul>
    </p>
    <h3>2. How We Use Your Information</h3>
        <p>We use the information we collect to:
        <ul>
            <li>Provide and improve access to sheet music and related resources</li>
            <li>Respond to inquiries or user support requests</li>
            <li>Maintain and manage user accounts (if applicable)</li>
            <li>Monitor website usage and performance</li>
        </ul>
        </p>
    <h3>3. Sharing Your Information</h3>
        <p>We do not sell, rent, or trade your personal information. We may share limited information with trusted third-party service providers (e.g., website hosting or analytics services), only to the extent necessary to operate the website securely and effectively.</p>

    <h3>4. Data Security</h3>
        <p>We implement appropriate technical and organizational measures to safeguard your information. However, no internet transmission is 100% secure, and we cannot guarantee absolute security.</p>
    <h3>5. User Rights</h3>
        <p>You have the right to:
            <ul>
                <li>Request access to the personal information we hold about you</li>
                <li>Request correction or deletion of your data</li>
                <li>Withdraw consent for data processing at any time (if applicable)</li>
            </ul>
        To exercise any of these rights, contact us at ORGMAIL.
        </p>
    <h3>6. External Links</h3>
        <p>Our website may include links to external sites (e.g., publishers, performance videos). We are not responsible for the content or privacy practices of those sites.</p>
    <h3>7. Children's Privacy</h3>
        <p>Our website is not directed at children under the age of 13. We do not knowingly collect personal information from children.</p>
    <h3>8. Changes to This Policy</h3>
        <p>We may update this Privacy Statement from time to time. Changes will be posted on this page with an updated effective date.</p>
    <h3>9. Contact Us</h3>
    <p>If you have any questions or concerns about this Privacy Statement, please contact us at:
        <dl>
            <dt>Name:</dt>
            <dd><?php echo ORGDESC ?></dd>
            <dt>Website:</dt>
            <dd><?php echo ORGHOME ?></dd>
            <dt>e-mail:</dt>
            <dd><?php echo ORGMAIL ?></dd>
        </dl>
    </p>
  </div>
</main>
<?php require_once(__DIR__. "/includes/footer.php");?>
</body>
</html>
