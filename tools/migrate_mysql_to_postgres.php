<?php

declare(strict_types=1);

function qident(string $identifier): string
{
    return '"' . str_replace('"', '""', $identifier) . '"';
}

function qfull(string $schema, string $table): string
{
    return qident($schema) . '.' . qident($table);
}

function envOrFail(string $name): string
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException("Missing required environment variable: {$name}");
    }
    return trim($value);
}

function postgresPdoFromUrl(string $url): PDO
{
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('Invalid DATABASE_URL.');
    }

    $host = $parts['host'];
    $port = (string)($parts['port'] ?? 5432);
    $db = ltrim((string)($parts['path'] ?? ''), '/');
    $user = rawurldecode((string)($parts['user'] ?? ''));
    $pass = rawurldecode((string)($parts['pass'] ?? ''));

    if ($db === '' || $user === '') {
        throw new RuntimeException('DATABASE_URL is missing database or username.');
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode=require";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function mysqlPdoFromEnv(): PDO
{
    $host = envOrFail('LEGACY_MYSQL_HOST');
    $port = getenv('LEGACY_MYSQL_PORT') ?: '3306';
    $db = envOrFail('LEGACY_MYSQL_DB');
    $user = envOrFail('LEGACY_MYSQL_USER');
    $pass = envOrFail('LEGACY_MYSQL_PASSWORD');

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function mapMysqlType(array $column): string
{
    $type = strtolower((string)$column['Type']);

    if (preg_match('/^tinyint\\(1\\)/', $type)) return 'INTEGER';
    if (preg_match('/^tinyint/', $type)) return 'SMALLINT';
    if (preg_match('/^smallint/', $type)) return 'SMALLINT';
    if (preg_match('/^mediumint/', $type)) return 'INTEGER';
    if (preg_match('/^int/', $type)) return 'INTEGER';
    if (preg_match('/^bigint/', $type)) return 'BIGINT';
    if (preg_match('/^(decimal|numeric)\\((\\d+),(\\d+)\\)/', $type, $m)) return "NUMERIC({$m[2]},{$m[3]})";
    if (preg_match('/^(float|double|real)/', $type)) return 'DOUBLE PRECISION';
    if (preg_match('/^varchar\\((\\d+)\\)/', $type, $m)) return "VARCHAR({$m[1]})";
    if (preg_match('/^char\\((\\d+)\\)/', $type, $m)) return "CHAR({$m[1]})";
    if (str_starts_with($type, 'enum(') || str_starts_with($type, 'set(')) return 'TEXT';
    if (str_contains($type, 'text')) return 'TEXT';
    if (str_starts_with($type, 'json')) return 'JSONB';
    if (str_contains($type, 'blob') || str_starts_with($type, 'binary') || str_starts_with($type, 'varbinary')) return 'BYTEA';
    if (str_starts_with($type, 'date')) return 'DATE';
    if (str_starts_with($type, 'datetime')) return 'TIMESTAMP WITHOUT TIME ZONE';
    if (str_starts_with($type, 'timestamp')) return 'TIMESTAMP WITHOUT TIME ZONE';
    if (str_starts_with($type, 'time')) return 'TIME';
    if (str_starts_with($type, 'year')) return 'INTEGER';
    if (str_starts_with($type, 'bit')) return 'BIGINT';

    return 'TEXT';
}

function normalizeDefault(mixed $default): ?string
{
    if ($default === null) return null;
    $value = trim((string)$default);
    if ($value === '' || strtoupper($value) === 'NULL') return null;
    if (preg_match('/^(current_timestamp(?:\\(\\))?|now\\(\\))$/i', $value)) return 'CURRENT_TIMESTAMP';
    if (preg_match('/^-?\\d+(?:\\.\\d+)?$/', $value)) return $value;
    return "'" . str_replace("'", "''", $value) . "'";
}

function normalizeValue(mixed $value): mixed
{
    if ($value === null) return null;
    if (!is_string($value)) return $value;
    if (preg_match('/^0000-00-00(?:[  T].*)?$/', $value)) return null;
    return $value;
}

function mysqlTables(PDO $mysql): array
{
    $stmt = $mysql->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    $tables = [];
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = (string)$row[0];
    }
    return $tables;
}

function mysqlColumns(PDO $mysql, string $table): array
{
    return $mysql->query('SHOW COLUMNS FROM ' . qident($table))->fetchAll();
}

function mysqlIndexes(PDO $mysql, string $table): array
{
    return $mysql->query('SHOW INDEX FROM ' . qident($table))->fetchAll();
}

function mysqlForeignKeys(PDO $mysql, string $db): array
{
    $sql = <<<'SQL'
SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, ORDINAL_POSITION,
       REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = :db
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION
SQL;
    $stmt = $mysql->prepare($sql);
    $stmt->execute(['db' => $db]);
    return $stmt->fetchAll();
}

function groupByKey(array $rows, string $key): array
{
    $grouped = [];
    foreach ($rows as $row) {
        $grouped[(string)$row[$key]][] = $row;
    }
    return $grouped;
}

try {
    $databaseUrl = envOrFail('DATABASE_URL');
    $mysqlDb = envOrFail('LEGACY_MYSQL_DB');
    $mysql = mysqlPdoFromEnv();
    $pg = postgresPdoFromUrl($databaseUrl);
    $pg->exec('SET search_path TO public');

    $pg->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS "_samaaroh_migration_state" (
    "key" TEXT PRIMARY KEY,
    "value" TEXT NOT NULL,
    "updated_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL);

    $state = $pg->prepare('SELECT value FROM "_samaaroh_migration_state" WHERE key = ?');
    $state->execute(['legacy_mysql_import']);
    if ($state->fetchColumn() === 'done') {
        echo "Legacy MySQL migration already completed.\n";
        exit(0);
    }

    $tables = mysqlTables($mysql);
    if (!$tables) {
        throw new RuntimeException('Legacy MySQL database contains no base tables.');
    }

    $columnMetadata = [];
    $identityColumns = [];

    foreach (array_reverse($tables) as $table) {
        $pg->exec('DROP TABLE IF EXISTS ' . qfull('public', $table) . ' CASCADE');
    }

    foreach ($tables as $table) {
        $columns = mysqlColumns($mysql, $table);
        $columnMetadata[$table] = $columns;

        $indexes = mysqlIndexes($mysql, $table);
        $primaryRows = array_values(array_filter($indexes, static fn(array $i): bool => ($i['Key_name'] ?? '') === 'PRIMARY'));
        usort($primaryRows, static fn(array $a, array $b): int => ((int)$a['Seq_in_index']) <=> ((int)$b['Seq_in_index']));
        $primaryFields = array_map(static fn(array $i): string => (string)$i['Column_name'], $primaryRows);

        if (!$primaryFields) {
            $primaryFields = array_values(array_filter(
                array_map(static fn(array $c): ?string => ($c['Key'] ?? '') === 'PRI' ? (string)$c['Field'] : null, $columns),
                static fn(?string $v): bool => $v !== null
            ));
        }

        $defs = [];
        foreach ($columns as $column) {
            $field = (string)$column['Field'];
            $sqlType = mapMysqlType($column);
            $extra = strtolower((string)($column['Extra'] ?? ''));
            $definition = qident($field) . ' ' . $sqlType;

            if (str_contains($extra, 'auto_increment') && count($primaryFields) === 1 && $primaryFields[0] === $field) {
                $definition = qident($field) . ' BIGINT GENERATED BY DEFAULT AS IDENTITY';
                $identityColumns[$table] = $field;
            }

            if (($column['Null'] ?? 'YES') === 'NO') {
                $definition .= ' NOT NULL';
            }

            $default = normalizeDefault($column['Default'] ?? null);
            if ($default !== null && !str_contains($extra, 'auto_increment')) {
                $definition .= ' DEFAULT ' . $default;
            }

            $defs[] = $definition;
        }

        if ($primaryFields) {
            $defs[] = 'PRIMARY KEY (' . implode(', ', array_map('qident', $primaryFields)) . ')';
        }

        $pg->exec('CREATE TABLE ' . qfull('public', $table) . ' (' . implode(', ', $defs) . ')');
    }

    foreach ($tables as $table) {
        $columns = $columnMetadata[$table];
        if (!$columns) continue;
        $fields = array_map(static fn(array $c): string => (string)$c['Field'], $columns);
        $quoted = implode(', ', array_map('qident', $fields));
        $placeholders = implode(', ', array_map(static fn(int $i): string => '$' . ($i + 1), array_keys($fields)));
        $insert = $pg->prepare('INSERT INTO ' . qfull('public', $table) . " ({$quoted}) VALUES ({$placeholders})");

        $select = $mysql->query('SELECT * FROM ' . qident($table));
        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $values = [];
            foreach ($fields as $field) {
                $values[] = normalizeValue($row[$field] ?? null);
            }
            $insert->execute($values);
        }

        if (isset($identityColumns[$table])) {
            $field = $identityColumns[$table];
            $seqStmt = $pg->prepare('SELECT pg_get_serial_sequence(?, ?)');
            $seqStmt->execute(['public.' . $table, $field]);
            $sequence = $seqStmt->fetchColumn();
            if ($sequence) {
                $max = $pg->query('SELECT MAX(' . qident($field) . ') FROM ' . qfull('public', $table))->fetchColumn();
                $next = $max === null ? 1 : ((int)$max + 1);
                $setval = $pg->prepare('SELECT setval(?::regclass, ?, false)');
                $setval->execute([$sequence, $next]);
            }
        }
    }

    foreach ($tables as $table) {
        $indexes = mysqlIndexes($mysql, $table);
        foreach (groupByKey($indexes, 'Key_name') as $keyName => $rows) {
            if ($keyName === 'PRIMARY') continue;
            usort($rows, static fn(array $a, array $b): int => ((int)$a['Seq_in_index']) <=> ((int)$b['Seq_in_index']));
            $skip = false;
            $indexColumns = [];
            foreach ($rows as $row) {
                if (!empty($row['Sub_part'])) { $skip = true; break; }
                $indexColumns[] = qident((string)$row['Column_name']);
            }
            if ($skip || !$indexColumns) continue;
            $name = preg_replace('/[^a-zA-Z0-9_]+/', '_', $table . '_' . $keyName);
            $name = substr($name, 0, 55) . '_' . substr(md5($table . ':' . $keyName), 0, 8);
            $unique = isset($rows[0]['Non_unique']) && (int)$rows[0]['Non_unique'] === 0;
            $pg->exec('CREATE ' . ($unique ? 'UNIQUE ' : '') . 'INDEX ' . qident($name) . ' ON ' . qfull('public', $table) . ' (' . implode(', ', $indexColumns) . ')');
        }
    }

    $fkGroups = [];
    foreach (mysqlForeignKeys($mysql, $mysqlDb) as $fk) {
        $fkGroups[$fk['TABLE_NAME'] . '::' . $fk['CONSTRAINT_NAME']][] = $fk;
    }

    foreach ($fkGroups as $rows) {
        usort($rows, static fn(array $a, array $b): int => ((int)$a['ORDINAL_POSITION']) <=> ((int)$b['ORDINAL_POSITION']));
        $table = (string)$rows[0]['TABLE_NAME'];
        $refTable = (string)$rows[0]['REFERENCED_TABLE_NAME'];
        $local = [];
        $ref = [];
        foreach ($rows as $row) {
            $local[] = qident((string)$row['COLUMN_NAME']);
            $ref[] = qident((string)$row['REFERENCED_COLUMN_NAME']);
        }
        $constraint = preg_replace('/[^a-zA-Z0-9_]+/', '_', $table . '_' . $rows[0]['CONSTRAINT_NAME']);
        $constraint = substr($constraint, 0, 55) . '_' . substr(md5($table . ':' . $rows[0]['CONSTRAINT_NAME']), 0, 8);
        try {
            $pg->exec('ALTER TABLE ' . qfull('public', $table)
                . ' ADD CONSTRAINT ' . qident($constraint)
                . ' FOREIGN KEY (' . implode(', ', $local) . ')'
                . ' REFERENCES ' . qfull('public', $refTable)
                . ' (' . implode(', ', $ref) . ')');
        } catch (Throwable $e) {
            error_log('Samaaroh FK skipped: ' . $e->getMessage());
        }
    }

    $write = $pg->prepare('INSERT INTO "_samaaroh_migration_state" ("key", "value") VALUES (?, ?) ON CONFLICT ("key") DO UPDATE SET "value" = EXCLUDED."value", "updated_at" = CURRENT_TIMESTAMP');
    $write->execute(['legacy_mysql_import', 'done']);

    echo "Samaaroh MySQL -> PostgreSQL migration complete.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: {$e->getMessage()}\n");
    exit(1);
}
