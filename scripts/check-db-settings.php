<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Check the settings that travel with the database (section E of
 * docs/migration-tickets.md): an upgrade can reset them, and nothing in the
 * code would notice. Run after every rehearsal and right after the cutover.
 *
 * Usage, as www-data from the Moodle root:
 *   php scripts/check-db-settings.php
 *
 * Exits 1 when an expected value is off. INFO lines have no single right value
 * (they must match production, or depend on the server) and are for reading.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../config.php');

use local_richimath\privacy_lockdown;
use local_richimath\whatsapp;
use mod_bigbluebuttonbn\local\config as bbb;

$failed = 0;
$check = function (string $name, $actual, $expected) use (&$failed): void {
    $ok = (string) $actual === (string) $expected;
    $failed += $ok ? 0 : 1;
    printf("%-4s %s = %s%s\n", $ok ? 'OK' : 'FAIL', $name, var_export($actual, true),
        $ok ? '' : ' (expected ' . var_export($expected, true) . ')');
};
$info = fn(string $name, $value) => printf("INFO %s = %s\n", $name, var_export($value, true));

// One collation everywhere, the one config.php creates new tables with: two
// string columns in different collations cannot be compared in a query.
$collation = $DB->get_dbcollation();
$check('dbcollation (config.php)', $collation, 'utf8mb4_unicode_ci');
$check('tables in another collation', $DB->count_records_sql(
    "SELECT COUNT(*) FROM information_schema.tables
      WHERE table_schema = ? AND table_name LIKE ? AND table_collation <> ?",
    [$CFG->dbname, $CFG->prefix . '%', $collation]), 0);

// Look and front page.
$check('theme', $CFG->theme, 'richimath');
$check('allowuserthemes', $CFG->allowuserthemes, 1);
$check('frontpage', $CFG->frontpage, '');
$check('frontpageloggedin', $CFG->frontpageloggedin, '2');
$check('forcelogin', (int) $CFG->forcelogin, 0);
$check('enablemyhome', (int) get_config('core', 'enablemyhome'), 1);
$check('enablemycourses', (int) get_config('core', 'enablemycourses'), 1);
$info('local_richimath/prettyurls', get_config('local_richimath', 'prettyurls'));
$info('theme_richimath/sidebarstyle', get_config('theme_richimath', 'sidebarstyle'));
$check('admin/userlist (es)', get_string_manager()->get_string('userlist', 'admin', null, 'es'), 'Lista de usuarios');

// BigBlueButton on the VPS 6, with the guest link (MIG-16).
$check('bigbluebuttonbn module visible', (int) $DB->get_field('modules', 'visible', ['name' => 'bigbluebuttonbn']), 1);
$check('bigbluebuttonbn server host', parse_url((string) bbb::get('server_url'), PHP_URL_HOST), 'clases.richiacademy.com');
$check('bigbluebuttonbn shared secret set', bbb::get('shared_secret') !== '', true);
$check('bigbluebuttonbn guestaccess_enabled', (int) bbb::get('guestaccess_enabled'), 1);

// Student privacy (MIG-17): read back from the class that applies it.
$system = context_system::instance();
$granted = function (array $roles, array $capabilities) use ($DB, $system): int {
    if (!$roles) {
        return 0;
    }
    [$rolesql, $params] = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
    [$capsql, $capparams] = $DB->get_in_or_equal($capabilities, SQL_PARAMS_NAMED, 'c');
    return $DB->count_records_select('role_capabilities',
        "roleid $rolesql AND capability $capsql AND contextid = :ctx AND permission = :allow",
        $params + $capparams + ['ctx' => $system->id, 'allow' => CAP_ALLOW]);
};
$check('student roles granted a locked capability',
    $granted(get_archetype_roles('student'), privacy_lockdown::STUDENT_CAPABILITIES), 0);
$check('authenticated roles granted a locked capability',
    $granted(get_archetype_roles('user'), privacy_lockdown::AUTHENTICATED_CAPABILITIES), 0);
$hidden = explode(',', (string) $CFG->hiddenuserfields);
$check('hiddenuserfields missing', implode(',', array_diff(privacy_lockdown::HIDDEN_FIELDS, $hidden)), '');
$check('defaultpreference_maildisplay', (int) get_config('core', 'defaultpreference_maildisplay'), 0);
$check('accounts with a visible email', $DB->count_records_select('user', 'maildisplay <> 0 AND deleted = 0'), 0);

// Teacher WhatsApp button (MIG-15).
$check('profile field ' . whatsapp::FIELD_NUMBER, $DB->record_exists('user_info_field', ['shortname' => whatsapp::FIELD_NUMBER]), true);
$check('profile field ' . whatsapp::FIELD_ENABLED, $DB->record_exists('user_info_field', ['shortname' => whatsapp::FIELD_ENABLED]), true);
$check('course field ' . whatsapp::FIELD_COURSE, $DB->record_exists('customfield_field', ['shortname' => whatsapp::FIELD_COURSE]), true);

// Mail, cron and registration depend on the server, not on the upgrade.
$info('smtphosts set', !empty($CFG->smtphosts));
$info('noemailever', !empty($CFG->noemailever));
$lastcron = (int) get_config('tool_task', 'lastcronstart');
$info('minutes since cron last started', $lastcron ? intdiv(time() - $lastcron, 60) : null);
$info('registered on moodle.org', \core\hub\registration::is_registered());

exit($failed ? 1 : 0);
