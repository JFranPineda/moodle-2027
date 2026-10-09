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
 * Upgrade steps.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_richimath_upgrade($oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026083000) {
        // The invitee types their own name on the signup form; the inviter only gives the email.
        $table = new xmldb_table('local_richimath_invitation');
        foreach (['firstname', 'lastname'] as $name) {
            $field = new xmldb_field($name);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }
        upgrade_plugin_savepoint(true, 2026083000, 'local', 'richimath');
    }

    if ($oldversion < 2026083100) {
        // One shared link per course, gated by the invitation list.
        $table = new xmldb_table('local_richimath_courselink');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('token', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('createdby', XMLDB_KEY_FOREIGN, ['createdby'], 'user', ['id']);
        $table->add_index('courseid', XMLDB_INDEX_UNIQUE, ['courseid']);
        $table->add_index('token', XMLDB_INDEX_UNIQUE, ['token']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026083100, 'local', 'richimath');
    }

    if ($oldversion < 2026090400) {
        // Plans and the appearance each one wears.
        $table = new xmldb_table('local_richimath_plan');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('appearance', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('isdefault', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('shortname', XMLDB_INDEX_UNIQUE, ['shortname']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // One plan per user.
        $table = new xmldb_table('local_richimath_userplan');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('planid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN_UNIQUE, ['userid'], 'user', ['id']);
        $table->add_key('planid', XMLDB_KEY_FOREIGN, ['planid'], 'local_richimath_plan', ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        (new \local_richimath\plans\plan_service())->install_defaults();

        upgrade_plugin_savepoint(true, 2026090400, 'local', 'richimath');
    }

    if ($oldversion < 2026090401) {
        // Without this core ignores user.theme and the plans decide nothing.
        set_config('allowuserthemes', 1);

        upgrade_plugin_savepoint(true, 2026090401, 'local', 'richimath');
    }

    if ($oldversion < 2026090403) {
        // The plans were seeded all wearing 'elementary', so Secundaria and
        // Pre Uni looked exactly like Primaria — same palette, same logo. Give
        // each seeded plan the appearance of its own level, and only while it
        // still carries that flat default: an appearance an administrator
        // chose is never overwritten.
        $plans = new \local_richimath\plans\plan_repository();
        foreach (\local_richimath\plans\plan_service::seed() as $seed) {
            $plan = $plans->find_by_shortname($seed['shortname']);
            if (!$plan || $plan->appearance !== \local_richimath\plans\appearance::DEFAULT_KEY) {
                continue;
            }
            $plan->appearance = $seed['appearance'];
            $plans->save($plan);
        }

        upgrade_plugin_savepoint(true, 2026090403, 'local', 'richimath');
    }

    if ($oldversion < 2026092700) {
        // Guests who join a BigBlueButton session by link leave no trace: core
        // only uses the display name they type to label them inside the room.
        // This table is where they become people we can talk to later.
        $table = new xmldb_table('local_richimath_lead');
        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file(
                __DIR__ . '/install.xml', 'local_richimath_lead');
        }

        upgrade_plugin_savepoint(true, 2026092700, 'local', 'richimath');
    }

    if ($oldversion < 2026100300) {
        // The WhatsApp button reads native fields on purpose: the teacher edits
        // their number in their own profile and the override in the course
        // settings, both screens they already know. Creating them here is what
        // guarantees the shortnames the code looks them up by.
        \local_richimath\whatsapp_fields::install();

        upgrade_plugin_savepoint(true, 2026100300, 'local', 'richimath');
    }

    if ($oldversion < 2026100301) {
        // The course field was created without its options, so every value
        // read back as NULL and the per-course override never applied.
        \local_richimath\whatsapp_fields::install();

        upgrade_plugin_savepoint(true, 2026100301, 'local', 'richimath');
    }

    if ($oldversion < 2026100400) {
        // A student could open the participant list and any classmate's
        // profile, email and courses included. Done here and not as manual
        // admin steps so production gets it with the deploy.
        \local_richimath\privacy_lockdown::apply();

        upgrade_plugin_savepoint(true, 2026100400, 'local', 'richimath');
    }

    return true;
}
