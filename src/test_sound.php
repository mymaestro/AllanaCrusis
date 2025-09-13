<?php
define('PAGE_TITLE', 'Test Sound Email Handler');
define('PAGE_NAME', 'Sounderings');
// Simple test page for sound.php email handler
require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . '/includes/navbar.php');
ferror_log("RUNNING sound.php");
?>
<main class="container mt-5">
    <h2>Test Sound Email Handler</h2>
    <form id="test_sound_form" method="post">
        <div class="mb-3">
            <label for="to_email" class="form-label">Recipient Email</label>
            <input type="email" class="form-control" id="to_email" name="to_email" required>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" value="Test Email from Sound Handler" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="4" required>This is a test message sent via the sound email handler.</textarea>
        </div>
        <div class="mb-3">
            <label for="from_email" class="form-label">From Email</label>
            <input type="email" class="form-control" id="from_email" name="from_email" value="<?php echo htmlspecialchars($_SESSION['address'] ?? ORGMAIL); ?>" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="is_html" name="is_html">
            <label class="form-check-label" for="is_html">Send as HTML</label>
        </div>
        <button type="submit" class="btn btn-primary">Send Test Email</button>
    </form>
    <div id="test_sound_result" class="mt-3"></div>
</main>
<?php require_once(__DIR__ . '/includes/footer.php'); ?>
<script>
$('#test_sound_form').on('submit', function(e) {
    e.preventDefault();
    $('#test_sound_result').html('<span class="text-info">Sending...</span>');
    console.log('Submitting test sound form');
    $.ajax({
        url: 'index.php?action=sound',
        method: 'POST',
        data: {
            email: $('#to_email').val(),
            subject: $('#subject').val(),
            message: $('#message').val(),
            from: $('#from_email').val(),
            is_html: $('#is_html').is(':checked') ? '1' : '0'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#test_sound_result').html('<span class="text-success">Email sent successfully!</span>');
            } else {
                $('#test_sound_result').html('<span class="text-danger">Error: ' + response.message + '</span>');
            }
        },
        error: function(xhr, status, error) {
            $('#test_sound_result').html('<span class="text-danger">AJAX error: ' + error + '</span>');
        }
    });
});
</script>
</body>
</html>