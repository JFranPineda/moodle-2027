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
 * Richimath theme callbacks.
 *
 * @package    theme_richimath
 * @copyright  2026 Richi Math
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @param string $palette SCSS a child theme prepends to re-point the tokens.
 * @return string
 */
function theme_richimath_get_main_scss_content($theme, string $palette = '') {
    global $CFG;

    $scss = '';
    // A child theme's palette goes first: every token in this theme carries
    // !default, so whatever the child already defined wins and every rule
    // follows it without a line being copied.
    $scss .= $palette;
    $scss .= file_get_contents($CFG->dirroot . '/theme/richimath/scss/pre.scss');
    // Boost preset as the base look; no configurable presets in this theme.
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $scss .= file_get_contents($CFG->dirroot . '/theme/richimath/scss/post.scss');

    return $scss;
}
