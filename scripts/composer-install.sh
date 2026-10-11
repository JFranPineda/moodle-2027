#!/usr/bin/env bash
# Moodle 5.1+ keeps part of its runtime libraries (league/oauth2-server,
# symfony/console) in vendor/, installed by Composer from composer.lock; without
# them the REST API router dies with a fatal error. Production runs the same
# install (scripts/deploy-contabo.sh). Here it runs inside the PHP image the dev
# site and the rehearsal use, so the laptop needs no PHP or Composer of its own.
set -euo pipefail
cd "$(dirname "$0")/.."

docker run --rm -u "$(id -u):$(id -g)" -v "$PWD":/app -w /app --entrypoint bash \
    moodlehq/moodle-php-apache:8.4 -c '
    set -e
    export HOME=/tmp COMPOSER_HOME=/tmp/composer-home
    expected=$(curl -s https://composer.github.io/installer.sig)
    curl -sSo /tmp/composer-setup.php https://getcomposer.org/installer
    actual=$(php -r "echo hash_file(\"sha384\", \"/tmp/composer-setup.php\");")
    [ "$expected" = "$actual" ] || { echo "Composer installer checksum mismatch" >&2; exit 1; }
    php /tmp/composer-setup.php --install-dir=/tmp --filename=composer --quiet
    php /tmp/composer install --no-dev --classmap-authoritative --no-interaction'
