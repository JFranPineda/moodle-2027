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
 * Richi Universidad callbacks.
 *
 * @package   theme_rmuniversidad
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The parent's SCSS with this level's palette prepended and its own rules
 * appended.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_rmuniversidad_get_main_scss_content($theme) {
    global $CFG;

    // The parent's rules, then this level's own: Academic Nexus keeps the
    // shared workspace but anchors it on a dark slate rail, so it is the one
    // child that adds rules instead of only re-pointing tokens.
    return theme_richimath_get_main_scss_content(
        $theme,
        file_get_contents($CFG->dirroot . '/theme/rmuniversidad/scss/palette.scss')
    ) . file_get_contents($CFG->dirroot . '/theme/rmuniversidad/scss/post.scss');
}
