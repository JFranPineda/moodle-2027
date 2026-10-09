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

namespace theme_richimath\output\core;

/**
 * Course renderer overrides.
 *
 * The factory asks for "core"/"course", so the class it looks for is
 * theme_<name>\output\core\course_renderer — not the flat
 * core_course_renderer that the core class itself is called.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \core_course_renderer {
    /**
     * One category in the browse tree — nothing at all when the site says a
     * hidden category is hidden for everyone.
     *
     * A theme is the only place a core renderer can be overridden, but the
     * rule itself belongs to the site, so it lives in local_richimath and
     * every theme of the family asks it the same question. This one method
     * covers both listings that draw the tree: /course/index.php and the
     * category list of the logged-in front page.
     *
     * @param \coursecat_helper $chelper
     * @param \core_course_category $coursecat
     * @param int $depth
     * @return string
     */
    protected function coursecat_category(\coursecat_helper $chelper, $coursecat, $depth) {
        if (\local_richimath\category_visibility::should_hide($coursecat)) {
            return '';
        }

        return parent::coursecat_category($chelper, $coursecat, $depth);
    }
}
