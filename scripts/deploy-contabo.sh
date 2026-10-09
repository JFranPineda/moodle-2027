#!/usr/bin/env bash
# Deploy the latest pushed code on the Contabo VPS.
# Run ON the server as root:  bash /var/www/html/scripts/deploy-contabo.sh
set -euo pipefail

MOODLE=/var/www/html
RUN="sudo -u www-data"

cd "$MOODLE"

# If anything fails mid-deploy, never leave the site locked in maintenance.
trap '$RUN php admin/cli/maintenance.php --disable >/dev/null 2>&1 || true; echo "✗ Deploy FAILED — maintenance lifted, site back on previous code."' ERR

echo "→ maintenance ON"
$RUN php admin/cli/maintenance.php --enable

echo "→ git pull (as root: the GitHub credentials belong to root)"
git pull
chown -R www-data:www-data "$MOODLE" 

echo "→ upgrade (installs plugin/theme version bumps)"
$RUN php admin/cli/upgrade.php --non-interactive

echo "→ purge caches"
$RUN php admin/cli/purge_caches.php

echo "→ maintenance OFF"
$RUN php admin/cli/maintenance.php --disable
trap - ERR

echo "→ verify"
# Moodle 303-redirects any host that differs from wwwroot: always probe the
# real wwwroot and follow redirects.
WWWROOT=$($RUN php -r 'define("CLI_SCRIPT", 1); require "/var/www/html/config.php"; echo $CFG->wwwroot;')
# -L on purpose: /login/index.php now 301s to the clean /login address, and
# without following it the probe reads an empty body and calls the deploy bad.
THEME=$(curl -sL "$WWWROOT/login" | grep -o 'styles.php/[a-z]*' | head -1 || true)
STATUS=$(curl -sL -o /dev/null -w '%{http_code}' "$WWWROOT/")
# The clean URLs need mod_rewrite AND AllowOverride FileInfo Indexes in the
# vhost; without them the .htaccess is either ignored (ugly URLs) or refused
# (500). Probe one route rather than trusting the config.
ROUTE=$(curl -s -o /dev/null -w '%{http_code}' "$WWWROOT/students")
PRETTY=$($RUN php -r 'define("CLI_SCRIPT",1); require "/var/www/html/config.php"; echo (int)get_config("local_richimath","prettyurls");')
echo "  $WWWROOT → HTTP $STATUS · compiled theme: ${THEME:-unknown} · /students → HTTP $ROUTE · URL limpias: $PRETTY"
if [ "$ROUTE" = "200" ] && [ "$PRETTY" = "0" ]; then
    echo "  → el servidor ya sirve las URL limpias; enciéndelas con:"
    echo "    sudo -u www-data php admin/cli/cfg.php --component=local_richimath --name=prettyurls --set=1"
fi
if [ "$ROUTE" != "200" ] && [ "$PRETTY" = "1" ]; then
    echo "  ! URL limpias encendidas pero /students no responde. Apágalas ya:"
    echo "    sudo -u www-data php admin/cli/cfg.php --component=local_richimath --name=prettyurls --set=0"
fi
{ [ "$STATUS" = "200" ] && [ "$THEME" = "styles.php/richimath" ]; } && echo "✓ Deploy OK" || { echo "✗ Verification failed — check apache logs and admin/cli/checks.php"; exit 1; }
