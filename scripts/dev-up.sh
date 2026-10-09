#!/usr/bin/env bash
# Local Moodle 5.3 (docker-compose.yml): PHP 8.3, MySQL 8.4, cron, router.
# Brings the stack up, then upgrades and purges so code changes show at once.
set -euo pipefail
cd "$(dirname "$0")/.."

docker compose up -d

for _ in $(seq 1 30); do
    docker compose exec -T web true 2>/dev/null && break
    sleep 2
done

docker compose exec -T web bash -c 'mkdir -p /var/moodledata && chown -R www-data:www-data /var/moodledata'
docker compose exec -T -u www-data web php admin/cli/upgrade.php --non-interactive
docker compose exec -T -u www-data web php admin/cli/purge_caches.php
# Build every theme's CSS now: the first browser request after a purge would
# otherwise get the previous CSS under the new revision and cache it.
docker compose exec -T -u www-data web php admin/cli/build_theme_css.php \
    --themes=richimath,rmprimaria,rmsecundaria,rmpreu,rmuniversidad

echo "Moodle 5.3 listo: http://localhost:8083 (admin local: qa.admin)"
