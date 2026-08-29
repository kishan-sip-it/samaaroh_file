#!/bin/sh
set -eu

# Keep the legacy PHP application compatible with the PostgreSQL schema.
# The old MySQL database represented boolean-like values as TINYINT(1),
# and the application consistently reads/writes them as 0/1.
if [ -n "${DATABASE_URL:-}" ]; then
    echo "Checking PostgreSQL compatibility..."
    php /var/www/html/tools/ensure_postgres_legacy_compat.php
fi

# Render expects a public HTTP listener.
PORT="${PORT:-10000}"

sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf

exec apache2-foreground
