<?php

declare(strict_types=1);

/**
 * Keep the existing MySQL/PHP application compatible with PostgreSQL.
 *
 * The original MySQL schema used TINYINT(1) for boolean-like fields and
 * the PHP application already reads/writes those values as 0/1. Supabase
 * was initially created with native PostgreSQL BOOLEAN columns, which makes
 * queries such as `WHERE is_verified = 1` fail. This idempotent bootstrap
 * converts only the known legacy boolean-like columns from BOOLEAN to INTEGER
 * and restores their 0/1 defaults.
 */

$databaseUrl = trim((string) getenv('DATABASE_URL'));

if ($databaseUrl === '') {
    exit(0);
}

$parts = parse_url($databaseUrl);
if ($parts === false || empty($parts['host'])) {
    fwrite(STDERR, "Invalid DATABASE_URL.\n");
    exit(1);
}

$host = $parts['host'];
$port = $parts['port'] ?? 5432;
$db = ltrim((string) ($parts['path'] ?? ''), '/');
$user = rawurldecode((string) ($parts['user'] ?? ''));
$pass = rawurldecode((string) ($parts['pass'] ?? ''));

if ($db === '' || $user === '') {
    fwrite(STDERR, "DATABASE_URL is missing database or username.\n");
    exit(1);
}

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
    $host,
    $port,
    $db
);

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$columns = [
    ['users', 'is_verified', 1],
    ['services', 'is_available', 1],
    ['bookings', 'advance_paid', 0],
    ['notifications', 'is_read', 0],
    ['reviews', 'is_verified_purchase', 0],
];

foreach ($columns as [$table, $column, $default]) {
    $check = $pdo->prepare(
        'SELECT data_type FROM information_schema.columns '
        . 'WHERE table_schema = \'public\' AND table_name = ? AND column_name = ?'
    );
    $check->execute([$table, $column]);
    $type = $check->fetchColumn();

    if ($type === false) {
        continue;
    }

    if (strtolower((string) $type) === 'boolean') {
        $qTable = '"' . str_replace('"', '""', $table) . '"';
        $qColumn = '"' . str_replace('"', '""', $column) . '"';

        // Drop BOOLEAN default before converting the column to INTEGER.
        $pdo->exec("ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} DROP DEFAULT");
        $pdo->exec(
            "ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} TYPE INTEGER "
            . "USING CASE WHEN {$qColumn} IS TRUE THEN 1 ELSE 0 END"
        );
        $pdo->exec("ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} SET DEFAULT {$default}");

        echo "Converted public.{$table}.{$column} BOOLEAN -> INTEGER.\n";
    }
}

// Mark the compatibility check as complete without creating a permanent
// application table. The operation is intentionally idempotent.
echo "PostgreSQL legacy-boolean compatibility check complete.\n";
