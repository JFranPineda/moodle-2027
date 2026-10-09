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
 * Teacher side: create invitations and see the ones issued here.
 *
 * With courseid: invitations that enrol in that course (course "More"
 * menu). Without it: site-only invitations (Site administration > Users).
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_richimath\courselink;
use local_richimath\invitation;

$courseid = optional_param('courseid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$created = optional_param('created', 0, PARAM_INT);
$sent = optional_param('sent', 0, PARAM_BOOL);

// Through the map: the page keeps its clean address, and so do the forms
// Moodle builds from it.
$url = \local_richimath\routes::url('invitations', $courseid ? ['courseid' => $courseid] : []);
if ($courseid) {
    $course = get_course($courseid);
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('local/richimath:invite', $context);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_title(get_string('invitestudents', 'local_richimath'));
    $PAGE->set_heading(format_string($course->fullname, true, ['context' => $context]));
} else {
    admin_externalpage_setup('local_richimath_invite');
    $context = context_system::instance();
}

if ($delete && confirm_sesskey()) {
    $DB->delete_records('local_richimath_invitation', ['id' => $delete, 'courseid' => $courseid]);
    redirect($url, get_string('invitationdeleted', 'local_richimath'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$form = new \local_richimath\form\invite_form($url->out(false), ['courseid' => $courseid]);
if ($data = $form->get_data()) {
    $invitation = invitation::create($courseid, $data->email);
    redirect(new moodle_url($url, ['created' => $invitation->record->id, 'sent' => (int) $invitation->send_email()]));
}

$courselink = courselink::get_or_create($courseid);

$dateformat = get_string('strftimedate', 'langconfig');
$data = [
    'heading' => get_string($courseid ? 'invitestudents' : 'invitesite', 'local_richimath'),
    'intro' => get_string($courseid ? 'inviteintro_course' : 'inviteintro_site', 'local_richimath'),
    'form' => $form->render(),
    'courselink' => [
        'sitewide' => !$courseid,
        'url' => $courselink->url()->out(false),
        'whatsapp' => $courselink->whatsapp_url()->out(false),
    ],
    'created' => null,
    'invitations' => [],
];

// The invitation just created: its link, ready to copy or send by WhatsApp.
if ($created && ($new = invitation::from_id($created)) && $new->record->courseid == $courseid) {
    $data['created'] = [
        'email' => $new->record->email,
        'url' => $new->share_url()->out(false),
        'whatsapp' => $new->whatsapp_url()->out(false),
        'sent' => $sent,
        'expires' => userdate($new->record->timeexpires, $dateformat),
    ];
}

$invitations = invitation::list_for($courseid);
$userids = array_filter(array_map(fn($invitation) => $invitation->record->userid, $invitations));
$users = $userids ? $DB->get_records_list('user', 'id', $userids) : [];
foreach ($invitations as $invitation) {
    $status = $invitation->status();
    $user = $users[$invitation->record->userid] ?? null;
    $data['invitations'][] = [
        'email' => $invitation->record->email,
        'status' => $status,
        'statuslabel' => get_string('status' . $status, 'local_richimath'),
        'ispending' => $status === invitation::STATUS_PENDING,
        'when' => $status === invitation::STATUS_ACCEPTED
            ? userdate($invitation->record->timeused, $dateformat)
            : get_string('expireson', 'local_richimath', userdate($invitation->record->timeexpires, $dateformat)),
        'url' => $invitation->share_url()->out(false),
        'whatsapp' => $invitation->whatsapp_url()->out(false),
        'deleteurl' => (new moodle_url($url, ['delete' => $invitation->record->id, 'sesskey' => sesskey()]))->out(false),
        'user' => $user ? [
            'name' => fullname($user),
            'profileurl' => (new moodle_url('/user/profile.php', ['id' => $user->id]))->out(false),
        ] : null,
    ];
}
$data['hasinvitations'] = !empty($data['invitations']);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_richimath/invite', $data);
echo $OUTPUT->footer();
