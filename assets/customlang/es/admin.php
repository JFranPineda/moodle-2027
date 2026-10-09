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
 * Spanish customisations of core_admin strings (MIG-26). They live in the
 * database and moodledata, not in code: import them on every site, as www-data
 * from the Moodle root, with an absolute source (a relative one is reported missing):
 *   php public/admin/tool/customlang/cli/import.php --lang=es --source="$PWD/assets/customlang/es" --checkin
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['userlist'] = 'Lista de usuarios';
