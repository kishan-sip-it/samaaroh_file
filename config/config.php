<?php
session_start();

// Database Configuration
$host = 'localhost';
$db = 'samaaroh_db_final';
$user = 'root';
$pass = 'kishan';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Base URL Configuration
// Local WAMP installs commonly serve the project from /samaaroh_file/,
// while production hosting serves it from the domain root (/).
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$hostOnly = strtolower(preg_replace('/:\\d+$/', '', $httpHost));
$localHosts = ['localhost', '127.0.0.1', '::1'];
$isLocal = in_array($hostOnly, $localHosts, true);

$basePath = '/';

if ($isLocal) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

    // Keep WAMP compatibility when the project is served as:
    // http://localhost/samaaroh_file/
    if (preg_match('#^/samaaroh_file(?:/|$)#i', $requestPath)) {
        $basePath = '/samaaroh_file/';
    }
}

define('BASE_URL', $basePath);
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');

// Alert Helper Functions
function setAlert($msg, $type = 'info') {
    $_SESSION['alert'] = ['msg' => $msg, 'type' => $type];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        $color = $a['type'] == 'error' ? 'red' : ($a['type'] == 'success' ? 'green' : 'blue');
        echo "<div class='bg-{$color}-100 border border-{$color}-400 text-{$color}-700 px-4 py-3 rounded mb-4'>{$a['msg']}</div>";
        unset($_SESSION['alert']);
    }
}
?>
