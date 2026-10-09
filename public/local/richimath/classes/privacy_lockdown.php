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

namespace local_richimath;

/**
 * A student sees what belongs to them, and nothing of their classmates.
 *
 * Stock Moodle lets a student open the course's participant list and any
 * classmate's profile — name, email, time zone, the courses they are in, when
 * they last logged in. A student reported exactly that to their teacher. This
 * closes it with Moodle's own switches, not with code in the pages:
 *
 * - the student role loses the capabilities that open a classmate's data;
 * - the profile fields that are personal are hidden from everyone who lacks
 *   `moodle/user:viewhiddendetails` — which teachers and managers keep;
 * - every account's email is set to hidden, and new ones start that way.
 *
 * Grades were already private (a student only ever gets their own report), and
 * teachers keep seeing their students as before. A student can still open
 * their teachers' profiles and write to them: teachers are course contacts.
 *
 * Every change is visible and reversible in the admin UI (Define roles, Hide
 * user fields). Running it twice changes nothing the second time.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_lockdown {

    /** @var string[] Taken from every role of the student archetype. */
    public const STUDENT_CAPABILITIES = [
        // The participant list: names, roles, groups and last access.
        'moodle/course:viewparticipants',
        // A classmate's profile. It also takes classmates out of the message
        // search, which only lists users whose course profile is visible.
        'moodle/user:viewdetails',
        // A classmate's forum posts and blog, collected on one page.
        'moodle/user:readuserposts',
        'moodle/user:readuserblogs',
        // Who is online right now.
        'block/online_users:viewlist',
    ];

    /** @var string[] Taken from the authenticated-user role, which every account has. */
    public const AUTHENTICATED_CAPABILITIES = [
        'block/online_users:viewlist',
        'moodle/badges:viewotherbadges',
    ];

    /** @var string[] Profile fields only staff may see on someone else's profile. */
    public const HIDDEN_FIELDS = [
        'description', 'email', 'city', 'country', 'moodlenetprofile', 'timezone',
        'firstaccess', 'lastaccess', 'lastip', 'mycourses', 'groups', 'suspended',
    ];

    /**
     * Apply the lockdown.
     */
    public static function apply(): void {
        global $DB;

        $system = \context_system::instance();

        $roles = get_archetype_roles('student');
        foreach ($roles as $role) {
            foreach (self::STUDENT_CAPABILITIES as $capability) {
                unassign_capability($capability, $role->id, $system->id);
            }
        }

        foreach (get_archetype_roles('user') as $role) {
            foreach (self::AUTHENTICATED_CAPABILITIES as $capability) {
                unassign_capability($capability, $role->id, $system->id);
            }
        }

        // Added to what the site already hides, never replacing it.
        $hidden = array_filter(explode(',', (string) get_config('core', 'hiddenuserfields')));
        set_config('hiddenuserfields', implode(',', array_unique(array_merge($hidden, self::HIDDEN_FIELDS))));

        // 0 = hidden from everyone but staff, for new accounts and existing
        // ones. The hidden email field above already keeps the address off
        // other profiles; this makes each student's own profile say so —
        // left at 2 it read "visible to other course participants", which
        // is no longer true and is the one thing a worried student checks.
        set_config('defaultpreference_maildisplay', 0);
        $DB->set_field_select('user', 'maildisplay', 0, 'maildisplay <> 0 AND deleted = 0');
    }
}
