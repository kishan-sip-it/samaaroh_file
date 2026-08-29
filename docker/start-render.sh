#!/bin/sh
set -eu

# ---------------------------------------------------------
# Optional one-time legacy MySQL -> PostgreSQL migration.
# Set RUN_LEGACY_MYSQL_MIGRATION=1 in Render only while
# DATABASE_URL and LEGACY_MYSQL_* credentials are configured.
# The migration script records completion in PostgreSQL and
# safely skips itself on subsequent container restarts.
# ---------------------------------------------------------
if [ "${RUN_LEGACY_MYSQL_MIGRATION:-0}" = "1" ]; then
    echo "Starting Samaaroh legacy MySQL -> PostgreSQL migration..."
    php /var/www/html/tools/migrate_mysql_to_postgres.php
fi

# Render expects a public HTTP listener.
PORT="${PORT:-10000}"

sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf

exec apache2-foreground
