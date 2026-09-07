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
// Render service image persistence
// ---------------------------------------------------------
// Render's writable filesystem is ephemeral. The legacy provider add/edit
// handlers also contain a Windows-specific path conversion which does not
// work on Linux. Capture valid uploaded service images before those handlers
// run, then attach the bytes to the affected service after a successful save.
if ($isPostgres && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $isServiceForm = (bool) preg_match(
        '#/(provider|admin)/(add_service|edit_service)\.php$#i',
        $scriptPath
    );

    $upload = $_FILES['image'] ?? null;
    if ($isServiceForm && is_array($upload) && (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $tmp = (string) ($upload['tmp_name'] ?? '');
        $size = (int) ($upload['size'] ?? 0);
        $originalName = (string) ($upload['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

        if ($tmp !== '' && is_uploaded_file($tmp) && $size > 0 && $size <= 5 * 1024 * 1024 && in_array($ext, $allowedExt, true)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp) ?: '';
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
            $binary = file_get_contents($tmp);

            if ($binary !== false && in_array($mime, $allowedMime, true)) {
                $pendingPath = 'services/service_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                $pendingImage = [
                    'data' => base64_encode($binary),
                    'mime' => $mime,
                    'path' => $pendingPath,
                    'service_id' => isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0,
                    'provider_id' => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0,
                    'title' => trim((string) ($_POST['title'] ?? '')),
                    'description' => trim((string) ($_POST['description'] ?? '')),
                    'category' => trim((string) ($_POST['category'] ?? '')),
                    'tier' => trim((string) ($_POST['tier'] ?? '')),
                    'price' => (float) ($_POST['price'] ?? 0),
                ];

                // Prevent the old Windows-only upload block from trying to move
                // the same file. We only need it to proceed with the DB save.
                $_FILES['image']['name'] = '';

                register_shutdown_function(function () use (&$pdo, $pendingImage): void {
                    try {
                        // A successful add/edit redirects to the provider/admin dashboard.
                        // Validation/auth/database failures do not, so do not persist the
                        // captured image for a failed form submission.
                        $redirectSent = false;
                        foreach (headers_list() as $header) {
                            if (stripos($header, 'Location:') === 0) {
                                $redirectSent = true;
                                break;
                            }
                        }

                        if (!$redirectSent) {
                            return;
                        }

                        if ($pendingImage['service_id'] > 0) {
                            $check = $pdo->prepare(
                                'SELECT id FROM services WHERE id = ? AND provider_id = ? LIMIT 1'
                            );
                            $check->execute([$pendingImage['service_id'], $pendingImage['provider_id']]);
                            $exists = $check->fetchColumn();
                            if ($exists !== false) {
                                $stmt = $pdo->prepare(
                                    'UPDATE services\n'
                                    . 'SET image_path = ?, image_data = ?, image_mime_type = ?\n'
                                    . 'WHERE id = ? AND provider_id = ?'
                                );
                                $stmt->execute([
                                    $pendingImage['path'],
                                    $pendingImage['data'],
                                    $pendingImage['mime'],
                                    $pendingImage['service_id'],
                                    $pendingImage['provider_id'],
                                ]);
                            }
                            return;
                        }

                        // New-service flow: find the service just inserted by the legacy
                        // handler. Matching all submitted fields avoids touching an older
                        // service with the same title.
                        $stmt = $pdo->prepare(
                            'SELECT id FROM services\n'
                            . 'WHERE provider_id = ? AND title = ? AND description = ?\n'
                            . '  AND category = ? AND tier = ? AND price = ?\n'
                            . '  AND image_path IS NULL\n'
                            . 'ORDER BY id DESC LIMIT 1'
                        );
                        $stmt->execute([
                            $pendingImage['provider_id'],
                            $pendingImage['title'],
                            $pendingImage['description'],
                            $pendingImage['category'],
                            $pendingImage['tier'],
                            $pendingImage['price'],
                        ]);
                        $serviceId = $stmt->fetchColumn();

                        if ($serviceId !== false) {
                            $stmt = $pdo->prepare(
                                'UPDATE services\n'
                                . 'SET image_path = ?, image_data = ?, image_mime_type = ?\n'
                                . 'WHERE id = ? AND provider_id = ?'
                            );
                            $stmt->execute([
                                $pendingImage['path'],
                                $pendingImage['data'],
                                $pendingImage['mime'],
                                $serviceId,
                                $pendingImage['provider_id'],
                            ]);
                        }
                    } catch (Throwable $e) {
                        error_log('Persistent service image error: ' . $e->getMessage());
                    }
                });
            }
        }
    }
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