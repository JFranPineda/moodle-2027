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
 * Plugin callbacks.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * "Invite students" under the course "More" menu, for anyone who may invite there.
 *
 * @param \core\navigation\navigation_node $navigation The course settings node.
 * @param stdClass $course The course.
 * @param \core\context\course $context Its context.
 */
function local_richimath_extend_navigation_course(\core\navigation\navigation_node $navigation, stdClass $course,
        \core\context\course $context): void {
    if ($course->id == SITEID || !has_capability('local/richimath:invite', $context)) {
        return;
    }

    $navigation->add(
        get_string('invitestudents', 'local_richimath'),
        new moodle_url('/local/richimath/invite.php', ['courseid' => $course->id]),
        \core\navigation\navigation_node::TYPE_SETTING,
        null,
        'local_richimath_invite',
        new pix_icon('i/email', '')
    );
}

/**
 * Put "Invite guests" in the menu of a BigBlueButton activity.
 *
 * The module has its own "Add guests", which mails the raw guest link and
 * leaves no trace of who turned up. This entry leads to the one that registers
 * them first.
 *
 * Moodle calls this with the page CONTEXT as the second argument, not a node
 * (`settings_navigation::load_local_plugin_settings()`). Typing it as a
 * navigation_node throws a TypeError on EVERY page that builds the settings
 * navigation — which is every course page. It took production down once.
 *
 * @param \core\navigation\settings_navigation $settings
 * @param \core\context $context the context of the current page
 */
function local_richimath_extend_settings_navigation(\core\navigation\settings_navigation $settings,
        \core\context $context): void {
    $cm = $settings->get_page()->cm;
    if (!$cm || $cm->modname !== 'bigbluebuttonbn') {
        return;
    }
    if (!has_capability('mod/bigbluebuttonbn:addinstance', $cm->context)) {
        return;
    }

    $node = $settings->get('modulesettings');
    if (!$node) {
        return;
    }

    $node->add(
        get_string('sessionlinktitle', 'local_richimath'),
        new moodle_url('/local/richimath/sessionlink.php', ['id' => $cm->id]),
        \core\navigation\navigation_node::TYPE_SETTING,
        null,
        'local_richimath_sessionlink',
        new pix_icon('i/email', '')
    );
}
