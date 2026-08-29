<?php

declare(strict_types=1);

/**
 * PostgreSQL compatibility bootstrap for the legacy Samaaroh PHP codebase.
 *
 * The original MySQL schema represented boolean-like values as TINYINT(1),
 * and the PHP application uses the corresponding 0/1 values in SQL. When the
 * schema was recreated in PostgreSQL, those columns could become BOOLEAN,
 * making legacy expressions such as `is_verified = 1` invalid in PostgreSQL.
 *
 * On Render we normalize every BOOLEAN column in the public schema to INTEGER
 * with 0/1 semantics. This is deliberately idempotent and schema-driven so a
 * future table/column cannot reintroduce the same class of error.
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

$booleanColumns = $pdo->query(
    "SELECT table_name, column_name, column_default\n" .
    "FROM information_schema.columns\n" .
    "WHERE table_schema = 'public'\n" .
    "  AND data_type = 'boolean'\n" .
    "ORDER BY table_name, ordinal_position"
)->fetchAll();

$converted = 0;

foreach ($booleanColumns as $column) {
    $table = (string) $column['table_name'];
    $name = (string) $column['column_name'];
    $default = $column['column_default'];

    $qTable = '"' . str_replace('"', '""', $table) . '"';
    $qColumn = '"' . str_replace('"', '""', $name) . '"';

    // Drop the BOOLEAN default first so PostgreSQL can change the data type.
    $pdo->exec("ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} DROP DEFAULT");

    // Preserve the existing truth values as legacy-compatible 0/1 integers.
    $pdo->exec(
        "ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} TYPE INTEGER " .
        "USING CASE WHEN {$qColumn} IS TRUE THEN 1 ELSE 0 END"
    );

    // Preserve the common TRUE/FALSE default where PostgreSQL exposed one.
    if ($default !== null) {
        $defaultValue = preg_match('/TRUE/i', (string) $default) ? '1' : '0';
        $pdo->exec(
            "ALTER TABLE {$qTable} ALTER COLUMN {$qColumn} SET DEFAULT {$defaultValue}"
        );
    }

    $converted++;
    echo "Converted public.{$table}.{$name} BOOLEAN -> INTEGER.\n";
}

$remaining = $pdo->query(
    "SELECT COUNT(*) FROM information_schema.columns " .
    "WHERE table_schema = 'public' AND data_type = 'boolean'"
)->fetchColumn();

if ((int) $remaining !== 0) {
    throw new RuntimeException(
        "PostgreSQL compatibility check failed: {$remaining} BOOLEAN column(s) remain."
    );
}

echo "PostgreSQL legacy-boolean compatibility check complete. {$converted} column(s) normalized.\n";
