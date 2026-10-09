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
 * The student catalog, at /students.
 *
 * The page the site had at its root until the institutional site took over:
 * hero, tagline and the real catalog in tabs. Public, like the front page it
 * came from.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(\local_richimath\routes::url('students'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('studentstitle', 'local_richimath'));
$PAGE->set_heading('');
$PAGE->add_body_class('richimath-students');

echo $OUTPUT->header();
// `output` is a context key, not magic: without it every {{{output.*}}} in
// the template renders empty.
echo $OUTPUT->render_from_template('theme_richimath/local/students', ['output' => $OUTPUT]);
echo $OUTPUT->footer();
