<?php
// example: download.php
// Protect your actual music files outside web root if possible

// If form submitted and accepted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['agree'])) {
        // Redirect to the file or start download
        // Example: single file
        $file = 'files/mypart.pdf'; // path to music part
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="mypart.pdf"');
        readfile($file);
        exit;
    } else {
        $error = "You must agree to the Terms before downloading.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sheet Music Download</title>
  <style>
    body { font-family: sans-serif; margin: 2em; }
    .terms {
      border: 1px solid #aaa;
      padding: 1em;
      width: 400px;
      height: 200px;
      overflow-y: scroll;
      background: #f9f9f9;
    }
    .error { color: red; }
  </style>
</head>
<body>

<h2>Electronic Sheet Music Terms of Use</h2>

<div class="terms">
  <p><strong>1. Ownership</strong><br>
  All sheet music (electronic or printed) remains the exclusive property of [Organization Name]. Copyrights remain with the publisher or composer.</p>

  <p><strong>2. Authorized Use</strong><br>
  You are granted a limited license to download and use assigned sheet music solely for rehearsals and performances with the Organization. You may not share, copy, upload, or distribute the music.</p>

  <p><strong>3. Retention and Deletion</strong><br>
  After the final performance of a work (or if requested by the Organization), you must permanently delete all electronic copies and destroy or return any printed copies.</p>

  <p><strong>4. Confidentiality</strong><br>
  You may not post, email, or otherwise make the sheet music available to others.</p>
</div>

<form method="post">
  <label>
    <input type="checkbox" name="agree" value="yes"> I have read and agree to the Terms of Use
  </label><br><br>
  <button type="submit">Download Sheet Music</button>
</form>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

</body>
</html>
