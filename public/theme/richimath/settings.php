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
 * Richimath theme settings.
 *
 * admin/settings/appearance.php hands us a ready $settings page named
 * "themesettingrichimath" and adds it under Appearance > Themes after this
 * file runs, so we only fill it in.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Sidebar layout: wide (label beside the icon) or the Canvas-style
    // compact column. Read back in core_renderer::body_attributes().
    $name = 'theme_richimath/sidebarstyle';
    $title = get_string('sidebarstyle', 'theme_richimath');
    $description = get_string('sidebarstyle_desc', 'theme_richimath');
    $choices = [
        'wide' => get_string('sidebarstylewide', 'theme_richimath'),
        'compact' => get_string('sidebarstylecompact', 'theme_richimath'),
    ];
    $setting = new admin_setting_configselect($name, $title, $description, 'compact', $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // 5.3 hands every theme its page hidden, and lists it only once the theme
    // unhides it: left hidden, Appearance > Themes > Richimath answered
    // "section error" and the sidebar style could not be changed.
    $settings->hidden = false;
}
