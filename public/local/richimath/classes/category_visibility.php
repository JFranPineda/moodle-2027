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
 * Whether a category should be dropped from the browse views.
 *
 * Moodle already makes visibility configurable per category (the eye in
 * Courses > Manage courses and categories), but a user holding
 * moodle/category:viewhiddencategories — every admin and manager — still
 * sees the hidden ones, dimmed. That is right for a staging category and
 * wrong for one the academy simply does not use: "Categoría 1" greeted the
 * admin on the front page and on /course/index.php with an empty card.
 *
 * With this on, a hidden category is hidden for everyone in those listings.
 * Nothing else changes: the category keeps working, staff still reach it
 * from Manage courses and categories, and its courses stay reachable by URL.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_visibility {
    /**
     * @param \core_course_category|\stdClass $coursecat
     * @return bool
     */
    public static function should_hide($coursecat): bool {
        return empty($coursecat->visible) && (bool) get_config('local_richimath', 'hidehiddencategories');
    }
}
