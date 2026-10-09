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
 * Give the teacher the registration link of a BigBlueButton session, and mail
 * it to a list of addresses.
 *
 * The module's own "Add guests" mails the RAW guest link, which walks straight
 * past the registration form and leaves no lead behind. This page mails ours
 * instead. Both buttons exist; only this one records who came.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

global $DB, $PAGE, $OUTPUT, $CFG, $USER;

$cmid = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'bigbluebuttonbn');
require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/bigbluebuttonbn:addinstance', $context);

$instance = \mod_bigbluebuttonbn\instance::get_from_cmid($cm->id);

$PAGE->set_url('/local/richimath/sessionlink.php', ['id' => $cmid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('sessionlinktitle', 'local_richimath'));
$PAGE->set_heading(format_string($course->fullname));

$uid = $DB->get_field('bigbluebuttonbn', 'guestlinkuid',
    ['id' => $instance->get_instance_id()]);
$link = new moodle_url('/local/richimath/session.php', ['uid' => $uid]);

$sent = 0;
$emails = optional_param('emails', '', PARAM_RAW_TRIMMED);

if ($emails !== '' && confirm_sesskey()) {
    $subject = get_string('sessionlinksubject', 'local_richimath',
        format_string($instance->get_meeting_name()));
    $body = get_string('sessionlinkbody', 'local_richimath', (object) [
        'session' => format_string($instance->get_meeting_name()),
        'link' => $link->out(false),
    ]);

    foreach (preg_split('/[\s,;]+/', $emails, -1, PREG_SPLIT_NO_EMPTY) as $address) {
        if (!validate_email($address)) {
            continue;
        }
        // A recipient built by hand needs emailstop = 0 explicitly: cloned from
        // the noreply user it arrives as 1 and email_to_user goes quiet without
        // an error.
        $to = \core_user::get_noreply_user();
        $to->id = -1;
        $to->email = $address;
        $to->firstname = '';
        $to->lastname = '';
        $to->emailstop = 0;
        $to->maildisplay = 1;
        $to->mailformat = 1;

        if (email_to_user($to, $USER, $subject, $body, nl2br($body))) {
            $sent++;
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('sessionlinktitle', 'local_richimath'));

if (!$instance->is_guest_allowed()) {
    echo $OUTPUT->notification(get_string('sessionlinkdisabled', 'local_richimath'), 'warning');
    echo $OUTPUT->footer();
    exit;
}

if ($sent) {
    echo $OUTPUT->notification(get_string('sessionlinksent', 'local_richimath', $sent), 'success');
}

echo html_writer::tag('p', get_string('sessionlinkintro', 'local_richimath'));
echo html_writer::tag('p', html_writer::tag('code', $link->out(false),
    ['class' => 'd-block p-3 bg-light rounded']));

echo html_writer::start_tag('form', ['method' => 'post', 'class' => 'mt-3']);
echo html_writer::empty_tag('input',
    ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::tag('label', get_string('sessionlinkemails', 'local_richimath'),
    ['for' => 'rm-emails', 'class' => 'fw-bold']);
echo html_writer::tag('textarea', '', [
    'id' => 'rm-emails', 'name' => 'emails', 'rows' => 5,
    'class' => 'form-control mb-2',
    'placeholder' => 'alumno1@correo.com, alumno2@correo.com',
]);
echo html_writer::empty_tag('input', [
    'type' => 'submit', 'class' => 'btn btn-primary',
    'value' => get_string('sessionlinksend', 'local_richimath'),
]);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
