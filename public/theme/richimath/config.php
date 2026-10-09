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
 * Richimath config.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'richimath';
// Plain sheet, loaded before the compiled SCSS: it carries the Google Fonts
// @import, which the SCSS compiler cannot pass through (it resolves @import
// as a file path). See style/fonts.css.
$THEME->sheets = ['fonts'];
$THEME->editor_sheets = [];
$THEME->parents = ['boost'];
$THEME->enable_dock = false;
$THEME->yuicssmodules = [];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->scss = function($theme) {
    return theme_richimath_get_main_scss_content($theme);
};
// The front page is the institutional site for visitors without a session
// (layout/site.php falls back to Boost's drawers for everyone else). Layouts
// cascade key by key, so every other page keeps the parent's layout.
$THEME->layouts = [
    'frontpage' => [
        'file' => 'site.php',
        'regions' => [],
        'options' => ['nonavbar' => true, 'nocontextheader' => true],
    ],
];

$THEME->usefallback = true;
// These flags are not inherited from the parent theme, so mirror Boost explicitly.
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;
$THEME->activityheaderconfig = [
    'notitle' => true,
];
