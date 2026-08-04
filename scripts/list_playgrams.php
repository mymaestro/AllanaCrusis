<?php
// list_playgrams.php
// List playgram IDs and titles, for looking up the --playgram-id value that
// other scripts in this directory take.
//
// Examples:
//   php scripts/list_playgrams.php
//   php scripts/list_playgrams.php --csv

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../src/includes/functions.php');

$as_csv = false;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--help' || $arg === '-h') {
        echo <<<TXT
Usage:
    php scripts/list_playgrams.php [--csv]

Lists every playgram with its ID, title, and enabled flag, oldest ID first.
Use the ID with the other scripts here, e.g.:

    php scripts/section_headcount.php --playgram-id=124

Options:
  --csv    Emit CSV to stdout instead of a text table
  --help   Show this help

Descriptions are omitted on purpose: they are usually either blank or too long
to read in a table. Query the playgrams table directly if you need them.

TXT;
        exit(0);
    }
    if ($arg === '--csv') {
        $as_csv = true;
        continue;
    }
    fwrite(STDERR, "Unrecognized argument: {$arg}\n\nRun with --help for usage.\n");
    exit(1);
}

$link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$sql = 'SELECT id_playgram, name, enabled FROM playgrams ORDER BY id_playgram ASC';
$res = mysqli_query($link, $sql);
if (!$res) {
    fwrite(STDERR, 'Query failed: ' . mysqli_error($link) . "\n");
    exit(1);
}

$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}
mysqli_close($link);

// playgrams.enabled is nullable, so treat anything that is not exactly 1 as No.
// That matches how the app filters elsewhere (`pg.enabled = 1`).
function enabled_label($value) {
    return (int)$value === 1 ? 'Yes' : 'No';
}

if ($as_csv) {
    $fp = fopen('php://stdout', 'w');
    fputcsv($fp, ['ID', 'Title', 'Enabled']);
    foreach ($rows as $row) {
        fputcsv($fp, [$row['id_playgram'], $row['name'], enabled_label($row['enabled'])]);
    }
    fclose($fp);
    exit(0);
}

if (empty($rows)) {
    echo "No playgrams found.\n";
    exit(0);
}

$id_w = 2;
$title_w = 5;
foreach ($rows as $row) {
    $id_w    = max($id_w, strlen((string)$row['id_playgram']));
    $title_w = max($title_w, strlen($row['name']));
}

printf("%-{$id_w}s  %-{$title_w}s  %s\n", 'ID', 'TITLE', 'ENABLED');
echo str_repeat('-', $id_w + 2 + $title_w + 2 + 7) . "\n";
foreach ($rows as $row) {
    printf("%-{$id_w}s  %-{$title_w}s  %s\n",
        $row['id_playgram'],
        $row['name'],
        enabled_label($row['enabled'])
    );
}
echo "\n" . count($rows) . " playgram" . (count($rows) === 1 ? '' : 's') . ".\n";
