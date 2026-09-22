<?php

define('PAGE_TITLE', 'Add concert recordings');
define('PAGE_NAME', 'Concert recordings');
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/includes/functions.php");

$is_librarian = isset($_SESSION['roles']) && strpos(htmlspecialchars($_SESSION['roles']), 'librarian') !== FALSE;
$id_concert = filter_input(INPUT_GET, 'id_concert', FILTER_VALIDATE_INT);
if (!$id_concert && isset($_GET['id_concert']) && ctype_digit((string)$_GET['id_concert'])) {
    $id_concert = (int)$_GET['id_concert'];
}
$concert = null;
$playgram_items = [];
$ensembles = [];
$existing_recordings = [];
$saved_ensemble_id = '';
$saved_ensemble = '';
$saved_notes = '';

if ($is_librarian && $id_concert) {
    $f_link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $stmt = mysqli_prepare($f_link, 'SELECT c.id_concert, c.performance_date, c.venue, c.conductor, c.notes, p.name AS playgram_name FROM concerts c JOIN playgrams p ON p.id_playgram = c.id_playgram WHERE c.id_concert = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id_concert);
    mysqli_stmt_execute($stmt);
    $concert_result = mysqli_stmt_get_result($stmt);
    $concert = mysqli_fetch_assoc($concert_result);
    mysqli_stmt_close($stmt);

    if ($concert) {
        $stmt = mysqli_prepare($f_link, 'SELECT pi.catalog_number, c.name, c.composer, c.arranger FROM playgram_items pi JOIN concerts co ON co.id_playgram = pi.id_playgram JOIN compositions c ON c.catalog_number = pi.catalog_number WHERE co.id_concert = ? ORDER BY pi.comp_order');
        mysqli_stmt_bind_param($stmt, 'i', $id_concert);
        mysqli_stmt_execute($stmt);
        $items_result = mysqli_stmt_get_result($stmt);
        while ($item = mysqli_fetch_assoc($items_result)) {
            $playgram_items[] = $item;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($f_link, 'SELECT id_recording, catalog_number, name, ensemble, id_ensemble, notes, link FROM recordings WHERE id_concert = ? ORDER BY id_recording');
        mysqli_stmt_bind_param($stmt, 'i', $id_concert);
        mysqli_stmt_execute($stmt);
        $recordings_result = mysqli_stmt_get_result($stmt);
        while ($recording = mysqli_fetch_assoc($recordings_result)) {
            $recording_key = $recording['catalog_number'] ?? '__OTHER__';
            if (!isset($existing_recordings[$recording_key])) {
                $existing_recordings[$recording_key] = $recording;
            }
            if ($saved_ensemble_id === '') {
                $saved_ensemble_id = $recording['id_ensemble'] ?? '';
                $saved_ensemble = $recording['ensemble'] ?? '';
                $saved_notes = $recording['notes'] ?? '';
            }
        }
        mysqli_stmt_close($stmt);
    }

    $ensembles_result = mysqli_query($f_link, 'SELECT id_ensemble, name FROM ensembles WHERE enabled = 1 ORDER BY name');
    while ($ensemble = mysqli_fetch_assoc($ensembles_result)) {
        $ensembles[] = $ensemble;
    }
    mysqli_close($f_link);
}

require_once(__DIR__ . "/includes/header.php");
require_once(__DIR__ . "/includes/navbar.php");
?>
<main role="main">
    <div class="container pb-5">
        <div class="row pb-1 pt-5 border-bottom">
            <div class="col">
                <h1><?php echo htmlspecialchars(ORGNAME . ' ' . PAGE_TITLE); ?></h1>
            </div>
            <div class="col-auto align-self-center">
                <a class="btn btn-outline-secondary" href="/concerts">Back to concerts</a>
            </div>
        </div>
<?php if (!$is_librarian) : ?>
        <div class="alert alert-warning mt-4">Librarian access is required to add recordings.</div>
<?php elseif (!$id_concert || !$concert) : ?>
        <div class="alert alert-danger mt-4">The selected concert could not be found.</div>
<?php else : ?>
        <section class="mt-4" aria-labelledby="concert-heading">
            <h2 id="concert-heading"><?php echo htmlspecialchars($concert['playgram_name']); ?></h2>
            <p class="mb-1">
                <strong><?php echo htmlspecialchars($concert['performance_date']); ?></strong>
                at <?php echo htmlspecialchars($concert['venue']); ?>
                <?php if ($concert['conductor']) : ?>
                    &middot; conducted by <?php echo htmlspecialchars($concert['conductor']); ?>
                <?php endif; ?>
            </p>
            <p class="text-muted">Choose a file for each piece below. Concert details and composition credits are filled in automatically.</p>
        </section>

        <form id="concert-recordings-form" class="mt-4">
            <input type="hidden" id="id_concert" value="<?php echo (int)$concert['id_concert']; ?>">
            <input type="hidden" id="filedate" value="<?php echo htmlspecialchars($concert['performance_date']); ?>">
            <input type="hidden" id="venue" value="<?php echo htmlspecialchars($concert['venue']); ?>">
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label" for="id_ensemble">Ensemble*</label>
                    <select class="form-select" id="id_ensemble" required>
                        <option value="">Select ensemble</option>
<?php foreach ($ensembles as $ensemble) : ?>
                        <option value="<?php echo htmlspecialchars($ensemble['id_ensemble']); ?>"<?php echo ((string)$ensemble['id_ensemble'] === (string)$saved_ensemble_id) ? ' selected' : ''; ?>><?php echo htmlspecialchars($ensemble['name']); ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label" for="ensemble">Ensemble description</label>
                    <input class="form-control" type="text" id="ensemble" value="<?php echo htmlspecialchars($saved_ensemble); ?>" maxlength="2048" placeholder="Community Concert Band">
                </div>
                <div class="col-12">
                    <label class="form-label" for="notes">Recording notes</label>
                    <textarea class="form-control" id="notes" rows="2" placeholder="Notes shared by the recordings from this concert"><?php echo htmlspecialchars($saved_notes); ?></textarea>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="concert-recordings-table">
                    <caption class="title">Recordings for this concert</caption>
                    <thead>
                        <tr>
                            <th>Piece</th>
                            <th style="min-width: 220px;">Recording name</th>
                            <th style="min-width: 260px;">MP3 file</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($playgram_items as $item) : ?>
<?php $existing = $existing_recordings[$item['catalog_number']] ?? null; ?>
                        <tr class="recording-row" data-catalog-number="<?php echo htmlspecialchars($item['catalog_number']); ?>" data-composer="<?php echo htmlspecialchars($item['composer'] ?? ''); ?>" data-arranger="<?php echo htmlspecialchars($item['arranger'] ?? ''); ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                <div class="small text-muted"><?php echo htmlspecialchars($item['catalog_number']); ?></div>
                            </td>
                            <td><input class="form-control recording-name" type="text" value="<?php echo htmlspecialchars($existing['name'] ?? $item['name']); ?>" maxlength="255"></td>
                            <td><input class="form-control recording-file" type="file" accept=".mp3,audio/mpeg"<?php echo $existing ? ' disabled' : ''; ?> /></td>
                            <td class="recording-status <?php echo $existing ? 'text-success' : 'text-muted'; ?>"><?php echo $existing ? 'Uploaded: ' . htmlspecialchars($existing['link']) : 'Waiting for file'; ?></td>
                            <td><button type="button" class="btn btn-primary upload-recording"<?php echo $existing ? ' disabled' : ''; ?>><?php echo $existing ? 'Uploaded' : 'Upload'; ?></button></td>
                        </tr>
<?php endforeach; ?>
<?php $existing_other = $existing_recordings['__OTHER__'] ?? null; ?>
<?php if ($existing_other) : ?>
                        <tr class="recording-row" data-catalog-number="" data-composer="" data-arranger="">
                            <td>
                                <strong>Other</strong>
                                <div class="small text-muted">Non-music recording</div>
                            </td>
                            <td><input class="form-control recording-name" type="text" value="<?php echo htmlspecialchars($existing_other['name']); ?>" maxlength="255" disabled></td>
                            <td><input class="form-control recording-file" type="file" accept=".mp3,audio/mpeg" disabled /></td>
                            <td class="recording-status text-success">Uploaded: <?php echo htmlspecialchars($existing_other['link']); ?></td>
                            <td><button type="button" class="btn btn-primary upload-recording" disabled>Uploaded</button></td>
                        </tr>
<?php endif; ?>
                        <tr class="recording-row" data-catalog-number="" data-composer="" data-arranger="">
                            <td>
                                <strong>Other</strong>
                                <div class="small text-muted">Non-music recording</div>
                            </td>
                            <td><input class="form-control recording-name" type="text" value="Other" maxlength="255"></td>
                            <td><input class="form-control recording-file" type="file" accept=".mp3,audio/mpeg" /></td>
                            <td class="recording-status text-muted">Waiting for file</td>
                            <td><button type="button" class="btn btn-primary upload-recording">Upload</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
<?php if (!$playgram_items) : ?>
            <div class="alert alert-info">This concert has no compositions in its playgram.</div>
<?php endif; ?>
            <div id="upload-summary" class="mt-3" role="status" aria-live="polite"></div>
        </form>
<?php endif; ?>
    </div>
</main>
<?php require_once(__DIR__ . "/includes/footer.php"); ?>
<script src="js/chunked-upload.js"></script>
<?php if ($is_librarian && $concert) : ?>
<script>
$(function () {
    let activeUpload = false;
    const uploadConfig = <?php echo getChunkedUploadConfig(); ?>;

    function setStatus(row, message, className) {
        row.find('.recording-status').removeClass('text-muted text-success text-danger text-info').addClass(className).text(message);
    }

    function finishRow(row, button, success, message) {
        activeUpload = false;
        button.prop('disabled', false);
        if (success) {
            button.prop('disabled', true).text('Uploaded');
            row.find('.recording-file').prop('disabled', true);
            setStatus(row, message, 'text-success');
        } else {
            setStatus(row, message, 'text-danger');
        }
    }

    function submitRecording(row, button, file, uploadedFile) {
        const formData = new FormData();
        formData.append('id_recording', '');
        formData.append('catalog_number', row.data('catalog-number'));
        formData.append('concert', $('#id_concert').val());
        formData.append('id_ensemble', $('#id_ensemble').val());
        formData.append('name', row.find('.recording-name').val().trim());
        formData.append('ensemble', $('#ensemble').val().trim());
        formData.append('notes', $('#notes').val().trim());
        formData.append('composer', row.data('composer') || '');
        formData.append('arranger', row.data('arranger') || '');
        formData.append('filedate', $('#filedate').val());
        formData.append('venue', $('#venue').val());
        formData.append('update', 'add');
        formData.append('linkDisplay', file.name);
        if (uploadedFile) {
            formData.append('uploadedFilePath', uploadedFile.filePath);
            formData.append('uploadedFileName', uploadedFile.fileName);
        } else {
            formData.append('link', file);
        }

        $.ajax({
            url: '/index.php?action=insert_recordings',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                finishRow(row, button, response.success, response.message || 'Upload failed.');
            },
            error: function (xhr, status, error) {
                finishRow(row, button, false, 'Upload error: ' + error);
            }
        });
    }

    function startUpload(row, button, file) {
        const threshold = uploadConfig.thresholdBytes || 7 * 1024 * 1024;
        setStatus(row, 'Preparing upload...', 'text-info');
        button.prop('disabled', true).text('Uploading...');

        if (!shouldUseChunkedUpload(file, threshold)) {
            submitRecording(row, button, file, null);
            return;
        }

        const uploader = new ChunkedUploader(file, {
            uploadUrl: '/index.php?action=upload_chunk',
            chunkSize: uploadConfig.chunkSizeBytes || 2 * 1024 * 1024,
            onProgress: function (percent, message) {
                setStatus(row, percent + '% - ' + message, 'text-info');
            },
            onComplete: function (response) {
                submitRecording(row, button, file, response);
            },
            onError: function (message) {
                finishRow(row, button, false, message);
            }
        });
        uploader.start();
    }

    $('.upload-recording').on('click', function () {
        if (activeUpload) {
            return;
        }
        const button = $(this);
        const row = button.closest('.recording-row');
        const fileInput = row.find('.recording-file')[0];
        const file = fileInput.files[0];

        if (!$('#id_ensemble').val()) {
            $('#upload-summary').text('Select an ensemble before uploading recordings.').removeClass('text-success').addClass('text-danger');
            return;
        }
        if (!row.find('.recording-name').val().trim()) {
            setStatus(row, 'Enter a recording name.', 'text-danger');
            return;
        }
        if (!file) {
            setStatus(row, 'Choose an MP3 file first.', 'text-danger');
            return;
        }

        activeUpload = true;
        $('#upload-summary').text('Uploading one recording at a time.').removeClass('text-danger').addClass('text-info');
        startUpload(row, button, file);
    });
});
</script>
<?php endif; ?>
</body>
</html>
