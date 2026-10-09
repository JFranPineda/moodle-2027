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
 * The door in front of a BigBlueButton guest link.
 *
 * Core's own guest page (`mod/bigbluebuttonbn/guest.php`) asks for a display
 * name and the passcode, lets the visitor in, and remembers nothing. This page
 * stands in front of it: it asks who they actually are, writes the lead down,
 * and only then hands them over to core with the passcode already filled in —
 * so the visitor never has to be told a passcode at all.
 *
 * Nothing in `mod/bigbluebuttonbn` is modified: the hand-off is an ordinary
 * POST to its form.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_richimath\lead;
use local_richimath\form\guest_register_form;

global $DB, $PAGE, $OUTPUT, $SITE, $CFG;

$uid = required_param('uid', PARAM_ALPHANUMEXT);

$PAGE->set_course($SITE);
$PAGE->set_url('/local/richimath/session.php', ['uid' => $uid]);
$PAGE->set_pagelayout('login');

$bbbid = $DB->get_field('bigbluebuttonbn', 'id', ['guestlinkuid' => trim($uid)]);
if (empty($bbbid)) {
    throw new moodle_exception('guestaccess_activitynotfound', 'mod_bigbluebuttonbn');
}

$instance = \mod_bigbluebuttonbn\instance::get_from_instanceid($bbbid);
if (!$instance->is_guest_allowed()) {
    throw new moodle_exception('guestaccess_feature_disabled', 'mod_bigbluebuttonbn');
}

$sessionname = format_string($instance->get_meeting_name());
$PAGE->set_title($sessionname);
$PAGE->set_heading($sessionname);

$form = new guest_register_form(null, ['uid' => $uid]);

if ($data = $form->get_data()) {
    lead::record($instance->get_instance_id(), $instance->get_course_id(), (array) $data);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($sessionname, 3);
    echo html_writer::tag('p', get_string('leadready', 'local_richimath',
        format_string($data->firstname)));

    // Hand-off to core. The passcode rides hidden so the visitor only sees a
    // button — it is the teacher's passcode, not a secret from the guest, and
    // they had to register to reach this page.
    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => new moodle_url('/mod/bigbluebuttonbn/guest.php', ['uid' => $uid]),
    ]);
    // The identifier has to be the one core's own form computes from its class
    // name (`get_form_identifier()`, protected), or `is_submitted()` is false
    // and the guest lands back on an empty password form with no error shown.
    $identifier = preg_replace('/[^a-z0-9_]/i', '_', \mod_bigbluebuttonbn\form\guest_login::class);

    foreach ([
        'sesskey' => sesskey(),
        '_qf__' . $identifier => 1,
        'uid' => $uid,
        'username' => lead::display_name((array) $data),
        'password' => $instance->get_guest_access_password(),
    ] as $name => $value) {
        echo html_writer::empty_tag('input',
            ['type' => 'hidden', 'name' => $name, 'value' => $value]);
    }
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'class' => 'btn btn-primary btn-lg',
        'value' => get_string('leadjoin', 'local_richimath'),
    ]);
    echo html_writer::end_tag('form');

    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($sessionname, 3);
echo html_writer::tag('p', get_string('leadintro', 'local_richimath'));
$form->display();
echo $OUTPUT->footer();
