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

namespace local_richimath\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API: invitations name an email address and, once redeemed, a user.
 * Everything is stored against the system context.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_richimath_invitation', [
            'email' => 'privacy:metadata:local_richimath_invitation:email',
            'createdby' => 'privacy:metadata:local_richimath_invitation:createdby',
            'userid' => 'privacy:metadata:local_richimath_invitation:userid',
            'timecreated' => 'privacy:metadata:local_richimath_invitation:timecreated',
            'timeused' => 'privacy:metadata:local_richimath_invitation:timeused',
        ], 'privacy:metadata:local_richimath_invitation');

        $collection->add_database_table('local_richimath_courselink', [
            'createdby' => 'privacy:metadata:local_richimath_courselink:createdby',
        ], 'privacy:metadata:local_richimath_courselink');

        // These people have no account on the site: the table is the only
        // place their data lives, so it has to be declared here even though
        // no userid ever points at it.
        $collection->add_database_table('local_richimath_lead', [
            'firstname' => 'privacy:metadata:local_richimath_lead:firstname',
            'lastname' => 'privacy:metadata:local_richimath_lead:lastname',
            'university' => 'privacy:metadata:local_richimath_lead:university',
            'email' => 'privacy:metadata:local_richimath_lead:email',
            'marketingconsent' => 'privacy:metadata:local_richimath_lead:marketingconsent',
        ], 'privacy:metadata:local_richimath_lead');

        return $collection;
    }

    /**
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql(
            'SELECT id FROM {context} WHERE contextlevel = :level AND EXISTS (
                SELECT 1 FROM {local_richimath_invitation} WHERE userid = :userid OR createdby = :createdby
            )',
            ['level' => CONTEXT_SYSTEM, 'userid' => $userid, 'createdby' => $userid]
        );

        return $contextlist;
    }

    /**
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        if ($userlist->get_context()->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_richimath_invitation} WHERE userid > 0', []);
        $userlist->add_from_sql('createdby', 'SELECT createdby FROM {local_richimath_invitation}', []);
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $rows = $DB->get_records_select(
            'local_richimath_invitation',
            'userid = :userid OR createdby = :createdby',
            ['userid' => $userid, 'createdby' => $userid]
        );
        if (!$rows) {
            return;
        }

        $data = array_map(fn($row) => (object) [
            'email' => $row->email,
            'courseid' => $row->courseid,
            'invited_by_me' => $row->createdby == $userid,
            'redeemed_by_me' => $row->userid == $userid,
            'timecreated' => transform::datetime($row->timecreated),
            'timeused' => $row->timeused ? transform::datetime($row->timeused) : null,
        ], array_values($rows));

        writer::with_context(\context_system::instance())
            ->export_data([get_string('invitations', 'local_richimath')], (object) ['invitations' => $data]);
    }

    /**
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel === CONTEXT_SYSTEM) {
            $DB->delete_records('local_richimath_invitation');
        }
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        self::delete_for_users([$contextlist->get_user()->id]);
    }

    /**
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        if ($userlist->get_context()->contextlevel === CONTEXT_SYSTEM) {
            self::delete_for_users($userlist->get_userids());
        }
    }

    /**
     * Redeemed invitations of these users go away; the ones they issued
     * only lose the link to them.
     *
     * @param int[] $userids
     */
    private static function delete_for_users(array $userids): void {
        global $DB;

        if (!$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_richimath_invitation', "userid $insql", $params);
        $DB->set_field_select('local_richimath_invitation', 'createdby', 0, "createdby $insql", $params);
        $DB->set_field_select('local_richimath_courselink', 'createdby', 0, "createdby $insql", $params);
    }
}
