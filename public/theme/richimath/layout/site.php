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
 * The institutional site: the front page for visitors without a session.
 *
 * A page of its own, not the classroom with a banner on top — which is why it
 * is a layout and not a template override. A user with a session never sees
 * it: they get the front page Boost draws, exactly as before.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (isloggedin() && !isguestuser()) {
    require($CFG->dirroot . '/theme/boost/layout/drawers.php');
    return;
}

$templatecontext = [
    'output' => $OUTPUT,
    'bodyattributes' => $OUTPUT->body_attributes(),
    'sitename' => format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID)]),
    'shortname' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID)]),
    'divisions' => $OUTPUT->site_divisions(),
    'levels' => \theme_richimath\levels::all(),
    'login' => $OUTPUT->site_login(),
    'studentsurl' => \local_richimath\routes::url('students')->out(false),
    'loginurl' => \local_richimath\routes::url('login')->out(false),
    'year' => userdate(time(), '%Y'),
];

echo $OUTPUT->render_from_template('theme_richimath/local/site', $templatecontext);
