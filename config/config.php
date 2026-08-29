<?php

// Start the session before any output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------
// Database selection
// ---------------------------------------------------------
// Local WAMP keeps using the existing MySQL database.
// Render/Supabase supplies DATABASE_URL and switches the app
// to PostgreSQL without requiring another source-code change.
$databaseUrl = trim((string) getenv('DATABASE_URL'));

$isPostgres = $databaseUrl !== '';

if ($isPostgres) {
    $parts = parse_url($databaseUrl);

    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('Invalid DATABASE_URL configuration.');
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? 5432;
    $db = ltrim($parts['path'] ?? '', '/');
    $user = isset($parts['user']) ? rawurldecode($parts['user']) : '';
    $pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
    $sslmode = 'require';

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $host,
        $port,
        $db,
        $sslmode
    );
} else {
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $db = getenv('DB_NAME') ?: 'samaaroh_db_final';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: 'kishan';
    $charset = 'utf8mb4';

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port,
        $db,
        $charset
    );
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Sorry, the website is currently experiencing technical issues. Please try again later.');
}

// ---------------------------------------------------------
// Base URL
// ---------------------------------------------------------
// Local WAMP commonly serves the project at:
//   http://localhost/samaaroh_file/
// Render serves it from the domain root:
//   https://<service>.onrender.com/
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$hostOnly = strtolower(preg_replace('/:\\d+$/', '', $httpHost));
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocal = in_array($hostOnly, $localHosts, true);

$basePath = '/';

if ($isLocal) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

    if (preg_match('#^/samaaroh_file(?:/|$)#i', $requestPath)) {
        $basePath = '/samaaroh_file/';
    }
}

define('BASE_URL', $basePath);
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('IMAGES_URL', BASE_URL . 'images/');

// ---------------------------------------------------------
// Alert helpers
// ---------------------------------------------------------
function setAlert($msg, $type = 'info')
{
    $_SESSION['alert'] = [
        'msg' => $msg,
        'type' => $type,
    ];
}

function displayAlert()
{
    if (!isset($_SESSION['alert'])) {
        return;
    }

    $a = $_SESSION['alert'];

    $color = $a['type'] === 'error'
        ? 'red'
        : ($a['type'] === 'success' ? 'green' : 'blue');

    echo "<div class='bg-{$color}-100 border border-{$color}-400 text-{$color}-700 px-4 py-3 rounded mb-4'>"
        . htmlspecialchars((string) $a['msg'], ENT_QUOTES, 'UTF-8')
        . '</div>';

    unset($_SESSION['alert']);
}
