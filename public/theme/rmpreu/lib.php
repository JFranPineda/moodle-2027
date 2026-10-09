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
 * Richi Pre Universitario callbacks.
 *
 * @package   theme_rmpreu
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The parent's SCSS with this level's palette prepended.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_rmpreu_get_main_scss_content($theme) {
    global $CFG;

    // The parent's rules, then this level's own: Red & Black adds the spec's
    // technical indicator rule and jet-black button on top of the palette.
    return theme_richimath_get_main_scss_content(
        $theme,
        file_get_contents($CFG->dirroot . '/theme/rmpreu/scss/palette.scss')
    ) . file_get_contents($CFG->dirroot . '/theme/rmpreu/scss/post.scss');
}
