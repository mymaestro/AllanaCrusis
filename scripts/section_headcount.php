<?php
// section_headcount.php
// Estimate how many people are in each section by counting the DISTINCT email
// addresses that part-distribution emails have been sent to, grouped by section.
//
// Examples:
//   php scripts/section_headcount.php
//   php scripts/section_headcount.php --since=2025-09-01
//   php scripts/section_headcount.php --playgram-id=3 --list-emails
//   php scripts/section_headcount.php --csv > headcount.csv
//
// IMPORTANT — this is a proxy for headcount, not a roster. See the notes printed
// at the bottom of the default report, and the caveats in usage() below.

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../src/includes/functions.php');

function usage() {
    $help = <<<TXT
Usage:
    php scripts/section_headcount.php [options]

Counts the distinct email addresses that received part-distribution emails,
grouped by section, as an estimate of how many players are in each section.

Filters:
  --since=YYYY-MM-DD     Only count sends on/after this date
  --until=YYYY-MM-DD     Only count sends on/before this date (inclusive)
  --playgram-id=ID       Only count sends for one playgram (concert series)

Output:
  --list-emails          List the distinct addresses under each section
  --csv                  Emit CSV to stdout instead of a text table
  --include-disabled     Include sections where enabled = 0
  --hide-empty           Omit sections that have received no emails
  --help                 Show this help

How the count works:
  Every part-distribution email writes a row to download_tokens with the
  recipient address and the section the ZIP was built for. This script counts
  DISTINCT LOWER(TRIM(email)) per section, so a musician who received parts for
  six different concerts counts once, not six times.

Caveats — read these before quoting the numbers to a director:
  * Undercounts stand partners. If two flute players share one PDF link, or a
    section leader forwards their email onward, that is one address and one
    count. Sections that distribute via a leader will look far too small.
  * Undercounts anyone who has never been emailed parts. A player who only ever
    reads from paper never appears here at all.
  * Overcounts people with more than one address. someone@gmail.com and
    someone@work.com are two counts. Case and surrounding whitespace are
    normalized, but nothing else is.
  * Overcounts people who have left. A player emailed once in 2019 still counts
    unless you pass --since. For a current-roster estimate, limit to the
    present season, e.g. --since=2025-09-01.
  * Counts recipients, not players. Addresses for conductors, board members, or
    the librarian's own test sends are included if parts were mailed to them.

  The SENDS column is shown next to PLAYERS so you can sanity-check: a section
  with 4 players and 200 sends is being mailed heavily, which is expected over
  many concerts. A section with PLAYERS very close to SENDS has probably only
  been mailed once, so its count is on thin evidence.

TXT;
    fwrite(STDOUT, $help);
}

// ---------------------------------------------------------------- arg parsing

$opts = [
    'since'           => null,
    'until'           => null,
    'playgram-id'     => null,
    'list-emails'     => false,
    'csv'             => false,
    'include-disabled'=> false,
    'hide-empty'      => false,
];

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--help' || $arg === '-h') {
        usage();
        exit(0);
    }
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $arg, $m)) {
        $key = $m[1];
        $val = $m[2] ?? null;
        if (!array_key_exists($key, $opts)) {
            fwrite(STDERR, "Unknown option: {$arg}\n\nRun with --help for usage.\n");
            exit(1);
        }
        // Flags take no value; valued options require one.
        if (is_bool($opts[$key])) {
            $opts[$key] = true;
        } else {
            if ($val === null || $val === '') {
                fwrite(STDERR, "Option --{$key} requires a value.\n");
                exit(1);
            }
            $opts[$key] = $val;
        }
    } else {
        fwrite(STDERR, "Unrecognized argument: {$arg}\n\nRun with --help for usage.\n");
        exit(1);
    }
}

function validate_date($value, $label) {
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) {
        fwrite(STDERR, "Invalid {$label} date '{$value}'. Expected YYYY-MM-DD.\n");
        exit(1);
    }
    return $value;
}

if ($opts['since'] !== null) {
    $opts['since'] = validate_date($opts['since'], '--since');
}
if ($opts['until'] !== null) {
    $opts['until'] = validate_date($opts['until'], '--until');
}
if ($opts['since'] !== null && $opts['until'] !== null && $opts['since'] > $opts['until']) {
    fwrite(STDERR, "--since ({$opts['since']}) is after --until ({$opts['until']}).\n");
    exit(1);
}
if ($opts['playgram-id'] !== null) {
    if (!ctype_digit((string)$opts['playgram-id'])) {
        fwrite(STDERR, "--playgram-id must be a positive integer.\n");
        exit(1);
    }
    $opts['playgram-id'] = (int)$opts['playgram-id'];
}

// ------------------------------------------------------------------ the query

$link = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// A row in download_tokens only represents a SENT email once email is populated;
// fetch_playgram_distribution.php inserts the token first and fills in email
// afterwards, so rows with a NULL/blank email are generated-but-never-sent ZIPs
// and must not be counted.
$conds = ["dt.email IS NOT NULL", "TRIM(dt.email) != ''"];
$params = [];
$types  = '';

if (!$opts['include-disabled']) {
    $conds[] = 's.enabled = 1';
}
if ($opts['since'] !== null) {
    $conds[] = 'dt.created_at >= ?';
    $params[] = $opts['since'] . ' 00:00:00';
    $types .= 's';
}
if ($opts['until'] !== null) {
    $conds[] = 'dt.created_at <= ?';
    $params[] = $opts['until'] . ' 23:59:59';
    $types .= 's';
}
if ($opts['playgram-id'] !== null) {
    $conds[] = 'dt.id_playgram = ?';
    $params[] = $opts['playgram-id'];
    $types .= 'i';
}

$where = implode(' AND ', $conds);

// LEFT JOIN from sections so a section with no sends still reports as 0,
// which is itself a useful answer for the director.
$join_conds = ['dt.id_section = s.id_section'];
foreach ($conds as $c) {
    if (strpos($c, 'dt.') === 0) {
        $join_conds[] = $c;
    }
}
$section_where = $opts['include-disabled'] ? '1=1' : 's.enabled = 1';

$sql = "SELECT s.id_section,
               s.name AS section_name,
               s.enabled,
               COUNT(DISTINCT LOWER(TRIM(dt.email))) AS players,
               COUNT(dt.id_download_token)           AS sends,
               MAX(dt.created_at)                    AS last_send
          FROM sections s
          LEFT JOIN download_tokens dt
                 ON " . implode(' AND ', $join_conds) . "
         WHERE {$section_where}
      GROUP BY s.id_section, s.name, s.enabled
      ORDER BY players DESC, s.name ASC";

$stmt = mysqli_prepare($link, $sql);
if ($stmt === false) {
    fwrite(STDERR, 'Query preparation failed: ' . mysqli_error($link) . "\n");
    exit(1);
}
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    if ($opts['hide-empty'] && (int)$row['players'] === 0) {
        continue;
    }
    $rows[] = $row;
}
mysqli_stmt_close($stmt);

// Distinct people across the whole ensemble, which is NOT the sum of the
// per-section counts: a player who gets both Brass and Percussion parts is
// counted in each section but is one person overall.
$total_sql = "SELECT COUNT(DISTINCT LOWER(TRIM(dt.email))) AS total_players
                FROM download_tokens dt
                JOIN sections s ON dt.id_section = s.id_section
               WHERE {$where}";
$total_stmt = mysqli_prepare($link, $total_sql);
$total_players = null;
if ($total_stmt !== false) {
    if ($types !== '') {
        mysqli_stmt_bind_param($total_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($total_stmt);
    $total_res = mysqli_stmt_get_result($total_stmt);
    if ($total_row = mysqli_fetch_assoc($total_res)) {
        $total_players = (int)$total_row['total_players'];
    }
    mysqli_stmt_close($total_stmt);
}

// Per-section address lists, only when asked for.
$emails_by_section = [];
if ($opts['list-emails']) {
    $email_sql = "SELECT dt.id_section,
                         LOWER(TRIM(dt.email)) AS email,
                         COUNT(*)              AS sends,
                         MAX(dt.created_at)    AS last_send
                    FROM download_tokens dt
                    JOIN sections s ON dt.id_section = s.id_section
                   WHERE {$where}
                GROUP BY dt.id_section, LOWER(TRIM(dt.email))
                ORDER BY dt.id_section, email";
    $email_stmt = mysqli_prepare($link, $email_sql);
    if ($email_stmt !== false) {
        if ($types !== '') {
            mysqli_stmt_bind_param($email_stmt, $types, ...$params);
        }
        mysqli_stmt_execute($email_stmt);
        $email_res = mysqli_stmt_get_result($email_stmt);
        while ($er = mysqli_fetch_assoc($email_res)) {
            $emails_by_section[(int)$er['id_section']][] = $er;
        }
        mysqli_stmt_close($email_stmt);
    }
}

mysqli_close($link);

// ------------------------------------------------------------------- reporting

function filter_description(array $opts) {
    $bits = [];
    if ($opts['since'] !== null)       { $bits[] = 'on/after ' . $opts['since']; }
    if ($opts['until'] !== null)       { $bits[] = 'on/before ' . $opts['until']; }
    if ($opts['playgram-id'] !== null) { $bits[] = 'playgram ' . $opts['playgram-id']; }
    return $bits ? implode(', ', $bits) : 'all sends, all time';
}

if ($opts['csv']) {
    $fp = fopen('php://stdout', 'w');
    if ($opts['list-emails']) {
        fputcsv($fp, ['Section', 'Email', 'Sends', 'LastSend']);
        foreach ($rows as $row) {
            foreach ($emails_by_section[(int)$row['id_section']] ?? [] as $e) {
                fputcsv($fp, [$row['section_name'], $e['email'], $e['sends'], $e['last_send']]);
            }
        }
    } else {
        fputcsv($fp, ['Section', 'Players', 'Sends', 'LastSend', 'Enabled']);
        foreach ($rows as $row) {
            fputcsv($fp, [
                $row['section_name'],
                $row['players'],
                $row['sends'],
                $row['last_send'] ?? '',
                $row['enabled'] ? 'yes' : 'no',
            ]);
        }
    }
    fclose($fp);
    exit(0);
}

if (empty($rows)) {
    echo "No sections matched (" . filter_description($opts) . ").\n";
    exit(0);
}

$name_w = 4;
foreach ($rows as $row) {
    $label = $row['section_name'] . ($row['enabled'] ? '' : ' (disabled)');
    $name_w = max($name_w, strlen($label));
}

echo "Section headcount, estimated from part-distribution emails\n";
echo "Filter: " . filter_description($opts) . "\n\n";
printf("%-{$name_w}s  %7s  %7s  %s\n", 'SECTION', 'PLAYERS', 'SENDS', 'LAST SEND');
echo str_repeat('-', $name_w + 2 + 7 + 2 + 7 + 2 + 10) . "\n";

$sum_players = 0;
$sum_sends   = 0;
foreach ($rows as $row) {
    $label = $row['section_name'] . ($row['enabled'] ? '' : ' (disabled)');
    $last  = $row['last_send'] ? substr($row['last_send'], 0, 10) : '-';
    printf("%-{$name_w}s  %7d  %7d  %s\n", $label, $row['players'], $row['sends'], $last);
    $sum_players += (int)$row['players'];
    $sum_sends   += (int)$row['sends'];
    if ($opts['list-emails']) {
        foreach ($emails_by_section[(int)$row['id_section']] ?? [] as $e) {
            printf("%s- %s (%d send%s, last %s)\n",
                str_repeat(' ', 4),
                $e['email'],
                $e['sends'],
                $e['sends'] == 1 ? '' : 's',
                substr($e['last_send'], 0, 10)
            );
        }
    }
}

echo str_repeat('-', $name_w + 2 + 7 + 2 + 7 + 2 + 10) . "\n";
printf("%-{$name_w}s  %7d  %7d\n", 'SUM OF SECTIONS', $sum_players, $sum_sends);
if ($total_players !== null) {
    printf("%-{$name_w}s  %7d\n", 'DISTINCT PEOPLE', $total_players);
}

echo "\nNotes:\n";
echo "  * PLAYERS counts distinct email addresses, so one person mailed for six\n";
echo "    concerts counts once.\n";
if ($total_players !== null && $total_players !== $sum_players) {
    $overlap = $sum_players - $total_players;
    echo "  * SUM OF SECTIONS ({$sum_players}) exceeds DISTINCT PEOPLE ({$total_players}) by {$overlap}.\n";
    echo "    That gap is people receiving parts for more than one section; they are\n";
    echo "    counted in each. Do not add the section numbers to get ensemble size.\n";
}
echo "  * This is a proxy, not a roster. It undercounts stand partners sharing a\n";
echo "    link and anyone never emailed parts; it overcounts people with two\n";
echo "    addresses and players who have since left. Run --help for the full list.\n";
if ($opts['since'] === null) {
    echo "  * No --since given, so this spans all history and includes former players.\n";
    echo "    For a current-roster estimate, limit to this season (e.g. --since=2025-09-01).\n";
}
