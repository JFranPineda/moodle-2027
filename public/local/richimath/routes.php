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
 * The site's URL map — one English word per address, and no .php anywhere in
 * the address bar.
 *
 * This file is the single source of truth. The `routes` helper builds links
 * from it and scripts/build-routes.php writes the Apache rules from it.
 * Adding an address is adding a line here.
 *
 * Every route works in both directions:
 *
 * - The pretty address is served **in place** (internal rewrite), so it stays
 *   in the address bar for the whole visit.
 * - The real script is **redirected to the pretty address** (301) whenever a
 *   browser asks for it directly. That is what keeps `.php` out of the bar
 *   even when a link Moodle generated points at the script — and it is
 *   skipped for POST, where a redirect would drop the form data.
 *
 * 'aliases' lists any other real path that must land on the same address: a
 * directory ('my/') on top of the script it runs ('my/index.php').
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

return [
    // Pages this plugin owns.
    'students' => [
        'target' => '/local/richimath/students.php',
        'public' => true,
    ],
    'join' => [
        'target' => '/local/richimath/accept.php',
        'public' => true,
    ],
    'invitations' => [
        'target' => '/local/richimath/invite.php',
    ],
    'plans' => [
        'target' => '/local/richimath/plans.php',
    ],
    'members' => [
        'target' => '/local/richimath/userplans.php',
    ],

    // Moodle core: the same address in the bar, whoever built the link.
    'login' => [
        'target' => '/login/index.php',
        'aliases' => ['/login/'],
        'public' => true,
    ],
    'signup' => [
        'target' => '/login/signup.php',
        'public' => true,
    ],
    'recover' => [
        'target' => '/login/forgot_password.php',
        'public' => true,
    ],
    'dashboard' => [
        'target' => '/my/index.php',
        'aliases' => ['/my/'],
    ],
    'courses' => [
        'target' => '/course/index.php',
        'aliases' => ['/course/'],
    ],
    'calendar' => [
        'target' => '/calendar/view.php',
    ],
    'grades' => [
        'target' => '/grade/report/overview/index.php',
    ],
    'messages' => [
        'target' => '/message/index.php',
        'aliases' => ['/message/'],
    ],
    'profile' => [
        'target' => '/user/profile.php',
    ],
    'preferences' => [
        'target' => '/user/preferences.php',
    ],
    'mycourses' => [
        'target' => '/my/courses.php',
    ],
    'notifications' => [
        'target' => '/message/output/popup/notifications.php',
    ],
    'search' => [
        'target' => '/search/index.php',
    ],
    'admin' => [
        'target' => '/admin/search.php',
    ],
    // Logout carries a sesskey; QSA keeps it through the rewrite.
    'logout' => [
        'target' => '/login/logout.php',
    ],
];
