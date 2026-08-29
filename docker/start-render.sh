#!/bin/sh
set -eu

# Render expects a public HTTP listener. Keep Apache aligned with the
# container port used by this service.
PORT="${PORT:-10000}"

sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf

exec apache2-foreground
