<?php
// Usage: php extract_pdf_metadata.php /path/to/file.pdf

if ($argc < 2) {
    fwrite(STDERR, "Usage: php extract_pdf_metadata.php /path/to/file.pdf\n");
    exit(1);
}

$pdf_path = $argv[1];
if (!file_exists($pdf_path)) {
    fwrite(STDERR, "File not found: $pdf_path\n");
    exit(1);
}

// Build the Ghostscript command
$gs_cmd = sprintf(
    'gs -q -dNOSAFER -dNODISPLAY -c "(%s) (r) file runpdfbegin pdfdict /Info known { pdfdict /Info get { exch ==only (:) print == } forall } { (No info dictionary found) == } ifelse quit"',
    addcslashes($pdf_path, '()\\')
);

exec($gs_cmd, $output, $status);

if ($status !== 0) {
    fwrite(STDERR, "Ghostscript error or no metadata found.\n");
    exit($status);
}

foreach ($output as $line) {
    echo $line . PHP_EOL;
}