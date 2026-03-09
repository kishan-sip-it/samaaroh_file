<?php
// START SESSION FIRST (MUST BE ABSOLUTELY FIRST)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DATABASE CONFIG
$host = 'localhost';
$db   = 'samaaroh_db_final';
$user = 'root';
$pass = 'kishan';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// PATH CONSTANTS (WINDOWS-SAFE)
define('BASE_URL', '/samaaroh_file/');
define('ROOT_PATH', __DIR__ . '/../'); // PHYSICAL PATH
define('UPLOADS_DIR', ROOT_PATH . 'uploads/'); // FOR UPLOADS
define('UPLOADS_URL', BASE_URL . 'uploads/'); // FOR <img> TAGS
define('IMAGES_URL', BASE_URL . 'images/');

// ✅ CRITICAL: ALERT HELPER FUNCTIONS (WAS MISSING!)
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