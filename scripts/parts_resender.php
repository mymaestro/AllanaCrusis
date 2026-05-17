<?php
// parts_resender.php
// Admin CLI utility to (re)send part distribution emails with download tokens.
//
// Examples:
//   php scripts/parts_resender.php \
//     --admin-user=admin \
//     --email=musician@example.com \
//     --token=0123456789abcdef0123456789abcdef
//
//   php scripts/parts_resender.php \
//     --admin-user=admin \
//     --email=musician@example.com \
//     --playgram-id=3 \
//     --section-id=7 \
//     --zip-filename=Spring_Concert_Flutes_Parts.zip
//
// Notes:
// - With --token, default behavior is to create a NEW token derived from the old one.
// - Add --reuse-token to send using the existing token (must be unused and unexpired).
// - You can also resolve by token suffix shown in UI (example: d38b00c7).

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../src/includes/functions.php');

function usage() {
    $help = <<<TXT
Usage:
    php scripts/parts_resender.php --admin-user=USERNAME [options]

Required:
  --admin-user=USERNAME    Existing admin/librarian username running this resend
    --email=EMAIL            Recipient email address (required for send mode)

Token mode:
  --token=HEX32            Existing 32-char token to resend from
    --token-tail=HEX         Resolve by token suffix (4-32 hex chars, e.g. d38b00c7)
        --token-suffix=HEX       Alias for --token-tail
    --token-end=HEX          Alias for --token-tail
  --reuse-token            Reuse the provided token (default is to mint a new token)
    --pick-latest            If suffix matches multiple tokens, pick the newest one

Lookup mode:
    --list-matches           List matching token rows and exit (no send)
    --match-email=EMAIL      Filter token lookup/list by recipient email on token row
    --match-created-by=USER  Filter token lookup/list by created-by username
    --limit=N                Max rows in list mode (default 50, max 200)

Direct mode (no --token):
  --playgram-id=ID         Playgram ID
  --section-id=ID          Section ID
  --zip-filename=FILE.zip  ZIP filename in ORGPRIVATE/distributions/

Optional:
  --subject="..."          Override email subject
  --dry-run                Do everything except send email / DB writes
  --help                   Show this help

Common examples:
    1) Resend from an existing full token (create a new token by default):
         php scripts/parts_resender.php \
             --admin-user=admin \
             --email=musician@example.com \
             --token=0123456789abcdef0123456789abcdef

    2) Reuse the existing token instead of minting a new one:
         php scripts/parts_resender.php \
             --admin-user=admin \
             --email=musician@example.com \
             --token=0123456789abcdef0123456789abcdef \
             --reuse-token

    3) Find a token by last characters (suffix) without sending:
         php scripts/parts_resender.php \
             --admin-user=admin \
             --token-tail=d38b00c7 \
             --list-matches

    4) Send using suffix resolution when one match is expected:
         php scripts/parts_resender.php \
             --admin-user=admin \
             --email=musician@example.com \
             --token-tail=d38b00c7 \
             --pick-latest

    5) Build from IDs/ZIP (direct mode):
         php scripts/parts_resender.php \
             --admin-user=admin \
             --email=musician@example.com \
             --playgram-id=3 \
             --section-id=7 \
             --zip-filename=Spring_Concert_Flutes_Parts.zip

    6) Dry run (validate inputs and generated link, no send/write):
         php scripts/parts_resender.php \
             --admin-user=admin \
             --email=musician@example.com \
             --token-tail=d38b00c7 \
             --pick-latest \
             --dry-run

TXT;
    fwrite(STDOUT, $help);
}

function fail($message, $code = 1) {
    fwrite(STDERR, "ERROR: " . $message . "\n");
    exit($code);
}

function htmlToPlainText($html) {
    $text = preg_replace('/<style[\\s\\S]*?<\\/style>/i', '', $html);
    $text = preg_replace('/<\\/?(p|div|ol|ul|li|br|h1|h2|h3|h4|h5|h6|footer)[^>]*>/i', "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{2,}/', "\n", $text);
    return trim($text);
}

function getAdminUser(mysqli $db, $username) {
    $sql = "SELECT id_users, username, name, address, roles FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function getTokenRow(mysqli $db, $token) {
    $sql = "SELECT dt.*, pg.name AS playgram_name, s.name AS section_name\n"
         . "FROM download_tokens dt\n"
         . "LEFT JOIN playgrams pg ON dt.id_playgram = pg.id_playgram\n"
         . "LEFT JOIN sections s ON dt.id_section = s.id_section\n"
         . "WHERE dt.token = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function findTokensBySuffix(mysqli $db, $tokenSuffix, $filters = [], $limit = 50) {
    $suffix = strtolower(trim((string)$tokenSuffix));
    if ($suffix === '') {
        return [];
    }

    $limit = max(1, min(200, (int)$limit));

    $where = [];
    $where[] = "dt.token LIKE '%" . mysqli_real_escape_string($db, $suffix) . "'";

    if (!empty($filters['zip_filename'])) {
        $where[] = "dt.zip_filename = '" . mysqli_real_escape_string($db, (string)$filters['zip_filename']) . "'";
    }
    if (!empty($filters['email'])) {
        $where[] = "dt.email = '" . mysqli_real_escape_string($db, (string)$filters['email']) . "'";
    }
    if (!empty($filters['created_by'])) {
        $where[] = "u.username = '" . mysqli_real_escape_string($db, (string)$filters['created_by']) . "'";
    }

    $sql = "SELECT dt.token, dt.zip_filename, dt.email, dt.expires_at, dt.used, dt.created_at,\n"
         . "       pg.name AS playgram_name, s.name AS section_name, u.username AS created_by\n"
         . "FROM download_tokens dt\n"
         . "LEFT JOIN playgrams pg ON dt.id_playgram = pg.id_playgram\n"
         . "LEFT JOIN sections s ON dt.id_section = s.id_section\n"
         . "LEFT JOIN users u ON dt.id_user = u.id_users\n"
         . "WHERE " . implode(' AND ', $where) . "\n"
         . "ORDER BY dt.created_at DESC\n"
         . "LIMIT " . $limit;

    $res = mysqli_query($db, $sql);
    if (!$res) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_free_result($res);
    return $rows;
}

function printTokenMatches($rows) {
    if (empty($rows)) {
        fwrite(STDOUT, "No matching tokens found.\n");
        return;
    }

    fwrite(STDOUT, "Matches: " . count($rows) . "\n");
    fwrite(STDOUT, "Token                                Last8     Playgram               Section         ZIP                                Email                        Status     Expires              Created By\n");
    fwrite(STDOUT, str_repeat('-', 190) . "\n");

    foreach ($rows as $row) {
        $token = (string)($row['token'] ?? '');
        $last8 = substr($token, -8);
        $playgram = substr((string)($row['playgram_name'] ?? ''), 0, 22);
        $section = substr((string)($row['section_name'] ?? ''), 0, 14);
        $zip = substr((string)($row['zip_filename'] ?? ''), 0, 34);
        $email = substr((string)($row['email'] ?? ''), 0, 27);
        $status = ((int)($row['used'] ?? 0) === 1) ? 'used' : 'available';
        $expires = (string)($row['expires_at'] ?? '');
        $createdBy = substr((string)($row['created_by'] ?? ''), 0, 10);

        fwrite(STDOUT, sprintf(
            "%-36s %-8s %-22s %-14s %-34s %-27s %-9s %-20s %-10s\n",
            $token,
            $last8,
            $playgram,
            $section,
            $zip,
            $email,
            $status,
            $expires,
            $createdBy
        ));
    }
}

function getPlaygramSectionInfo(mysqli $db, $playgramId, $sectionId) {
    $sql = "SELECT pg.id_playgram, pg.name AS playgram_name, s.id_section, s.name AS section_name\n"
         . "FROM playgrams pg CROSS JOIN sections s\n"
         . "WHERE pg.id_playgram = ? AND s.id_section = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $playgramId, $sectionId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function insertDownloadToken(mysqli $db, $token, $playgramId, $sectionId, $zipFilename, $expiresAt, $idUser) {
    $sql = "INSERT INTO download_tokens (token, id_playgram, id_section, zip_filename, expires_at, id_user)\n"
         . "VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'siissi', $token, $playgramId, $sectionId, $zipFilename, $expiresAt, $idUser);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateTokenEmail(mysqli $db, $token, $email) {
    $sql = "UPDATE download_tokens SET email = ? WHERE token = ?";
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ss', $email, $token);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

$options = getopt('', [
    'admin-user:',
    'email:',
    'token:',
    'token-tail:',
    'token-suffix:',
    'token-end:',
    'reuse-token',
    'pick-latest',
    'list-matches',
    'match-email:',
    'match-created-by:',
    'limit:',
    'playgram-id:',
    'section-id:',
    'zip-filename:',
    'subject:',
    'dry-run',
    'help'
]);

if (isset($options['help'])) {
    usage();
    exit(0);
}

$adminUser = $options['admin-user'] ?? '';
$email = $options['email'] ?? '';
$providedToken = $options['token'] ?? '';
$tokenTail = strtolower(trim((string)($options['token-tail'] ?? ($options['token-suffix'] ?? ($options['token-end'] ?? '')))));
$reuseToken = isset($options['reuse-token']);
$pickLatest = isset($options['pick-latest']);
$listMatches = isset($options['list-matches']);
$matchEmail = trim((string)($options['match-email'] ?? ''));
$matchCreatedBy = trim((string)($options['match-created-by'] ?? ''));
$listLimit = isset($options['limit']) ? (int)$options['limit'] : 50;
$dryRun = isset($options['dry-run']);

if ($adminUser === '') {
    usage();
    fail('The --admin-user option is required.');
}

if ($providedToken !== '' && !preg_match('/^[a-f0-9]{32}$/', $providedToken)) {
    fail('Token must be a 32-character lowercase hex string.');
}
if ($tokenTail !== '' && !preg_match('/^[a-f0-9]{4,32}$/', $tokenTail)) {
    fail('Token tail must be 4-32 lowercase hex characters.');
}
if ($matchEmail !== '' && !filter_var($matchEmail, FILTER_VALIDATE_EMAIL)) {
    fail('Invalid --match-email value.');
}

$hasDirectModeArgs = isset($options['playgram-id']) || isset($options['section-id']) || isset($options['zip-filename']);
if (!$listMatches && $providedToken === '' && $tokenTail === '' && !$hasDirectModeArgs) {
    fail('Missing lookup arguments. Use token mode (--token, --token-tail, --token-suffix, or --token-end) or direct mode (--playgram-id, --section-id, --zip-filename).');
}

$sendMode = !$listMatches;
if ($sendMode && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Valid --email is required for send mode.');
}

$db = f_sqlConnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$admin = getAdminUser($db, $adminUser);
if (!$admin) {
    mysqli_close($db);
    fail('Admin user not found: ' . $adminUser);
}

$roles = strtolower((string)($admin['roles'] ?? ''));
if (strpos($roles, 'administrator') === false && strpos($roles, 'librarian') === false) {
    mysqli_close($db);
    fail('User lacks administrator/librarian role: ' . $adminUser);
}

if ($providedToken === '' && $tokenTail !== '') {
    $matches = findTokensBySuffix($db, $tokenTail, [
        'zip_filename' => $options['zip-filename'] ?? '',
        'email' => $matchEmail,
        'created_by' => $matchCreatedBy
    ], $listLimit);

    if ($listMatches) {
        printTokenMatches($matches);
        mysqli_close($db);
        exit(0);
    }

    if (count($matches) === 0) {
        mysqli_close($db);
        fail('No token found ending with: ' . $tokenTail);
    }

    if (count($matches) > 1 && !$pickLatest) {
        fwrite(STDERR, "Multiple tokens match suffix '" . $tokenTail . "'.\n");
        printTokenMatches($matches);
        mysqli_close($db);
        fail('Refine with --match-email, --match-created-by, --zip-filename, or use --pick-latest.');
    }

    $providedToken = (string)$matches[0]['token'];
    fwrite(STDOUT, "Resolved token from suffix '" . $tokenTail . "': " . $providedToken . "\n");
}

if ($listMatches && $tokenTail === '') {
    fwrite(STDERR, "Use --token-tail (or --token-suffix / --token-end) with --list-matches.\n");
    mysqli_close($db);
    usage();
    exit(1);
}

$adminName = trim((string)($admin['name'] ?? ''));
if ($adminName === '') {
    $adminName = $adminUser;
}
$adminEmail = trim((string)($admin['address'] ?? ''));

$playgramId = null;
$sectionId = null;
$zipFilename = '';
$playgramName = '';
$sectionName = '';
$tokenToUse = '';
$expiresAt = '';

if ($providedToken !== '') {
    $tokenRow = getTokenRow($db, $providedToken);
    if (!$tokenRow) {
        mysqli_close($db);
        fail('Token not found: ' . $providedToken);
    }

    $playgramId = (int)$tokenRow['id_playgram'];
    $sectionId = (int)$tokenRow['id_section'];
    $zipFilename = (string)$tokenRow['zip_filename'];
    $playgramName = (string)($tokenRow['playgram_name'] ?? ('Playgram #' . $playgramId));
    $sectionName = (string)($tokenRow['section_name'] ?? ('Section #' . $sectionId));

    if ($reuseToken) {
        if ((int)$tokenRow['used'] === 1) {
            mysqli_close($db);
            fail('Cannot reuse token; it is already marked used.');
        }
        if (strtotime((string)$tokenRow['expires_at']) < time()) {
            mysqli_close($db);
            fail('Cannot reuse token; it is expired.');
        }
        $tokenToUse = $providedToken;
        $expiresAt = (string)$tokenRow['expires_at'];
    } else {
        $tokenToUse = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . DOWNLOAD_TOKEN_EXPIRY_DAYS . ' days'));
        if (!$dryRun) {
            $ok = insertDownloadToken($db, $tokenToUse, $playgramId, $sectionId, $zipFilename, $expiresAt, (int)$admin['id_users']);
            if (!$ok) {
                mysqli_close($db);
                fail('Failed to insert new download token.');
            }
        }
    }
} else {
    $playgramId = isset($options['playgram-id']) ? (int)$options['playgram-id'] : 0;
    $sectionId = isset($options['section-id']) ? (int)$options['section-id'] : 0;
    $zipFilename = isset($options['zip-filename']) ? trim((string)$options['zip-filename']) : '';

    if ($playgramId <= 0 || $sectionId <= 0 || $zipFilename === '') {
        mysqli_close($db);
        usage();
        fail('Direct mode requires --playgram-id, --section-id, and --zip-filename.');
    }

    $info = getPlaygramSectionInfo($db, $playgramId, $sectionId);
    if (!$info) {
        mysqli_close($db);
        fail('Could not resolve playgram/section names for provided IDs.');
    }
    $playgramName = (string)$info['playgram_name'];
    $sectionName = (string)$info['section_name'];

    $tokenToUse = bin2hex(random_bytes(16));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . DOWNLOAD_TOKEN_EXPIRY_DAYS . ' days'));
    if (!$dryRun) {
        $ok = insertDownloadToken($db, $tokenToUse, $playgramId, $sectionId, $zipFilename, $expiresAt, (int)$admin['id_users']);
        if (!$ok) {
            mysqli_close($db);
            fail('Failed to insert new download token.');
        }
    }
}

$distPath = rtrim(ORGPRIVATE, '/') . '/distributions/';
$zipPath = $distPath . $zipFilename;
if (!file_exists($zipPath)) {
    mysqli_close($db);
    fail('ZIP file does not exist: ' . $zipPath);
}

$orgHome = rtrim((string)ORGHOME, '/');
$downloadLink = $orgHome . '/d/' . $tokenToUse;
$bandName = defined('ORGDESC') && ORGDESC ? ORGDESC : ORGNAME;
$logoUrl = rtrim((string)ORGHOME, '/') . '/' . ltrim((string)ORGLOGO, '/');

$templatePath = __DIR__ . '/../config/download-contract.html';
$templateHtml = file_exists($templatePath) ? file_get_contents($templatePath) : '';
if ($templateHtml === '') {
    mysqli_close($db);
    fail('Template not found or empty: config/download-contract.html');
}

$replacements = [
    '{{sectionName}}' => $sectionName,
    '{{bandName}}' => $bandName,
    '{{playgramName}}' => $playgramName,
    '{{download_link}}' => $downloadLink,
    '{{contactName}}' => $adminName,
    '{{logoUrl}}' => $logoUrl,
    '{{expiryDays}}' => (string)DOWNLOAD_TOKEN_EXPIRY_DAYS
];

$htmlMessage = strtr($templateHtml, $replacements);
$subject = isset($options['subject']) ? trim((string)$options['subject']) : ('Your ' . $sectionName . ' parts for ' . $bandName . ' (' . $playgramName . ')');

if (!$dryRun) {
    $sent = f_sendEmail($email, $subject, $htmlMessage, [
        'from' => $adminEmail,
        'replyTo' => $adminEmail,
        'isHtml' => true,
        'context' => 'scripts/parts_resender.php',
        'actor' => $adminUser
    ]);

    if (!$sent) {
        mysqli_close($db);
        fail('Email send failed. Check mail logs for details.');
    }

    if (!updateTokenEmail($db, $tokenToUse, $email)) {
        mysqli_close($db);
        fail('Email sent, but failed to update token email field.');
    }
}

mysqli_close($db);

fwrite(STDOUT, "Success.\n");
fwrite(STDOUT, "Recipient: " . $email . "\n");
fwrite(STDOUT, "Playgram: " . $playgramName . " (ID " . $playgramId . ")\n");
fwrite(STDOUT, "Section: " . $sectionName . " (ID " . $sectionId . ")\n");
fwrite(STDOUT, "ZIP: " . $zipFilename . "\n");
fwrite(STDOUT, "Token: " . $tokenToUse . "\n");
fwrite(STDOUT, "Expires: " . $expiresAt . "\n");
fwrite(STDOUT, "Link: " . $downloadLink . "\n");
if ($dryRun) {
    fwrite(STDOUT, "Dry run only: no token/email DB updates and no email send were performed.\n");
}
