#!/usr/bin/env bash
# Phase 2 rehearsal (docs/migration-plan.md §5): a production copy taken with
# docs/migration/phase-2-production-copy.md, upgraded 4.3 → 4.5 → 5.3 exactly
# as production will be, timing every step. Ends serving 5.3 on :8084.
#
# Usage: scripts/rehearsal/rehearse-upgrade.sh ~/richimath-prod-copy
#
# Every run starts from scratch: the plan asks for two green runs in a row, each
# from a clean copy. Work files (they hold student data) go to <copy>/work,
# next to the copy and outside every repo.
set -euo pipefail

COPY=$(realpath "${1:?usage: $0 <production copy dir>}")
HERE=$(cd "$(dirname "$0")" && pwd)
REPO53=$(cd "$HERE/../.." && pwd)
GITHUB=$(dirname "$REPO53")
MOODLE43=${MOODLE43:-$GITHUB/moodle}
MOODLE45=${MOODLE45:-$GITHUB/moodle-2026}
WORK=$COPY/work
LOG=$WORK/rehearsal.log

DUMP=$(ls "$COPY"/moodle-db-*.sql.gz | tail -1)
DATA=$(ls "$COPY"/moodledata-*.tar.gz | tail -1)
# The plugins go in as production runs them: the commit deployed when the copy was taken.
PRODCOMMIT=$(grep -oE '^[0-9a-f]{7,}' "$COPY/version.txt" | head -1)

export COMPOSE_PROJECT_NAME=rmrehearsal
dc() { docker compose -f "$HERE/docker-compose.yml" "$@"; }
web() { dc exec -T -u www-data web php "$@"; }
step() {
    local name=$1 t0=$SECONDS
    shift
    "$@"
    printf '%-40s %5ds\n' "$name" $((SECONDS - t0)) | tee -a "$LOG"
}

hop() {
    export DB_IMAGE=$1 PHP_IMAGE=$2 CODE=$3 DOCROOT=$4 CONFIG=$5
}

# utf8mb4_unicode_ci, core's collation. Production's config.php and
# local_richimath's five tables were general_ci until 2026-10-09, when they
# were unified on the server: copies taken before that still carry the five,
# so the rehearsal converts them (on newer copies the step converts nothing).
write_config() {
    cat > "$1" <<'PHP'
<?php  // Rehearsal only (scripts/rehearsal): a copy of production on localhost.
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'moodle';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = ['dbpersist' => 0, 'dbport' => '', 'dbsocket' => '', 'dbcollation' => 'utf8mb4_unicode_ci'];

$CFG->wwwroot   = 'http://localhost:8084';
$CFG->dataroot  = '/var/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 02777;
$CFG->sessioncookie = 'rehearsal';
$CFG->routerconfigured = true;

// Real students in this database: no mail leaves, whatever the copied SMTP
// settings say. Production runs on https; the copy is served over http.
$CFG->noemailever = true;
$CFG->cookiesecure = false;

$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;

require_once(__DIR__ . '/lib/setup.php');
PHP
}

reset_all() {
    dc down -v --remove-orphans >/dev/null 2>&1 || true
    rm -rf "$WORK"
    mkdir -p "$WORK"
    chmod 700 "$WORK"
}

build_code45() {
    rsync -a --exclude=.git "$MOODLE45/" "$WORK/code45/"
    git -C "$MOODLE43" archive "$PRODCOMMIT" local/richimath theme/richimath \
        theme/rmprimaria theme/rmsecundaria theme/rmpreu theme/rmuniversidad | tar -x -C "$WORK/code45"
    write_config "$WORK/config45.php"
    write_config "$WORK/config53.php"
}

import_db() {
    gunzip -c "$DUMP" | dc exec -T -e MYSQL_PWD=root db mysql -uroot moodle
}

restore_moodledata() {
    dc exec -T web bash -c 'rm -rf /var/moodledata/* &&
        tar -xz -C /var/moodledata --strip-components=1 &&
        chown -R www-data:www-data /var/moodledata' < "$DATA"
}

upgrade() {
    web admin/cli/upgrade.php --non-interactive 2>&1 | tee "$WORK/upgrade-$1.log"
}

# MySQL upgrades its data dictionary in place on the first start of 8.4; a
# clean stop of 8.0 first, as on the server.
switch_to_53() {
    dc stop web db
    hop mysql:8.4 moodlehq/moodle-php-apache:8.3 "$REPO53" /var/www/html/public "$WORK/config53.php"
    dc up -d --wait
}

# The copy only has production's accounts: a local admin drives the acceptance
# tests and sees each student through "Log in as". Its password stays in $WORK.
create_admin() {
    local pw
    pw="Rh-$(openssl rand -hex 8)-9"
    dc exec -T -u www-data -e QA_PW="$pw" web php -r '
        define("CLI_SCRIPT", true);
        require("/var/www/html/config.php");
        require_once($CFG->dirroot . "/user/lib.php");
        $id = user_create_user((object) ["username" => "qa.admin", "password" => getenv("QA_PW"),
            "firstname" => "QA", "lastname" => "Admin", "email" => "qa.admin@example.invalid",
            "auth" => "manual", "confirmed" => 1, "mnethostid" => $CFG->mnet_localhost_id, "lang" => "es"]);
        set_config("siteadmins", $CFG->siteadmins . "," . $id);'
    printf 'qa.admin\n%s\n' "$pw" > "$WORK/qa-admin.txt"
    chmod 600 "$WORK/qa-admin.txt"
}

hop mysql:8.0 moodlehq/moodle-php-apache:8.2 "$WORK/code45" /var/www/html "$WORK/config45.php"
reset_all
echo "Copy: $(basename "$DUMP") · $(basename "$DATA") · code $PRODCOMMIT" | tee "$LOG"

step "build 4.5 tree + 4.3 plugins"   build_code45
step "start PHP 8.2 + MySQL 8.0"      dc up -d --wait
step "import database"                import_db
step "restore moodledata"             restore_moodledata
# 4.5 prints a debugging trace first: the 4.3 plugin still uses the legacy after_config callback.
echo "Production release: $(web admin/cli/cfg.php --name=release | tail -1)" | tee -a "$LOG"
step "unify collation (unicode_ci)"   web admin/cli/mysql_collation.php --collation=utf8mb4_unicode_ci
step "upgrade 4.3 → 4.5"              upgrade 4.5
step "switch to PHP 8.3 + MySQL 8.4"  switch_to_53
step "upgrade 4.5 → 5.3"              upgrade 5.3
# The upgrade queues core's CSS build for every installed theme (nearly all of
# this step) and the question bank move to mod_qbank. Both belong to the
# cutover window. No purge after it, or the CSS gets built a second time.
step "run queued adhoc tasks"         web admin/cli/adhoc_task.php --execute
# MIG-26. Each upgrade.php already installed its own Spanish pack; this goes on
# top. An absolute source: the CLI reports a relative one as missing.
step "apply Spanish customisations"   web public/admin/tool/customlang/cli/import.php \
                                          --lang=es --source=/var/www/html/assets/customlang/es --checkin

echo "Release now: $(web admin/cli/cfg.php --name=release | tail -1)" | tee -a "$LOG"
create_admin
web admin/cli/checks.php 2>&1 | tee -a "$LOG" || true
web admin/cli/check_database_schema.php > "$WORK/schema.txt" 2>&1 || true
web scripts/check-db-settings.php 2>&1 | tee -a "$LOG" || true
grep -hiE 'warn|notice|deprecat|error|exception' "$WORK"/upgrade-*.log | sort | uniq -c | sort -rn > "$WORK/warnings.txt" || true
echo "Done: http://localhost:8084 as qa.admin (password in $WORK/qa-admin.txt) · times and settings in $LOG · upgrade notices in $WORK/warnings.txt · schema in $WORK/schema.txt"
