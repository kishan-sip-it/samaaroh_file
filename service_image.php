<?php
require_once __DIR__ . '/config/config.php';

$path = trim((string) ($_GET['path'] ?? ''));
if ($path === '' || !preg_match('#^services/[A-Za-z0-9._-]+$#', $path)) {
    http_response_code(400);
    exit('Invalid image path');
}

try {
    // Render uses PostgreSQL. Uploaded service images are stored in services.image_data
    // so they survive container restarts and redeploys.
    if (trim((string) getenv('DATABASE_URL')) !== '') {
        $stmt = $pdo->prepare(
            'SELECT image_data, image_mime_type FROM services WHERE image_path = ? LIMIT 1'
        );
        $stmt->execute([$path]);
        $image = $stmt->fetch();

        if ($image && !empty($image['image_data'])) {
            $binary = base64_decode((string) $image['image_data'], true);
            if ($binary !== false) {
                header('Content-Type: ' . ($image['image_mime_type'] ?: 'application/octet-stream'));
                header('Cache-Control: public, max-age=86400');
                header('X-Content-Type-Options: nosniff');
                echo $binary;
                exit();
            }
        }
    }

    // Local/WAMP fallback and compatibility for older files still on disk.
    $file = UPLOADS_DIR . $path;
    $base = realpath(UPLOADS_DIR);
    $real = realpath($file);

    if ($base === false || $real === false || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) {
        http_response_code(404);
        exit('Image not found');
    }

    $mime = function_exists('mime_content_type')
        ? (mime_content_type($real) ?: 'application/octet-stream')
        : 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    readfile($real);
} catch (Throwable $e) {
    error_log('Service image error: ' . $e->getMessage());
    http_response_code(404);
    exit('Image not found');
}
