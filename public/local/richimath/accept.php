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
 * Student side: the personal link. Creates the account when the invited
 * email has none, asks for a login when it has one, and enrols on the way in.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_richimath\courselink;
use local_richimath\invitation;

$token = required_param('token', PARAM_ALPHANUM);

$PAGE->set_url(\local_richimath\routes::url('join', ['token' => $token]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('acceptinvitation', 'local_richimath'));
$PAGE->set_heading(format_string($SITE->fullname));

/**
 * Prints a one-message page with an optional button and stops.
 *
 * @param string $message
 * @param string $type Notification type.
 * @param moodle_url|null $buttonurl
 * @param string $buttontext
 */
function local_richimath_stop(string $message, string $type, ?moodle_url $buttonurl = null, string $buttontext = ''): void {
    global $OUTPUT;

    echo $OUTPUT->header();
    echo $OUTPUT->notification($message, $type, false);
    if ($buttonurl) {
        echo $OUTPUT->single_button($buttonurl, $buttontext, 'get');
    }
    echo $OUTPUT->footer();
    exit;
}

/**
 * Shared course link: asks which invited email this is, then continues on
 * that email's own invitation (same paths as a personal link). Never returns.
 *
 * @param \local_richimath\courselink $courselink
 */
function local_richimath_identify(courselink $courselink): void {
    global $OUTPUT, $PAGE;

    $form = new \local_richimath\form\identify_form($PAGE->url->out(false), ['courselink' => $courselink]);
    if ($data = $form->get_data()) {
        // Validation guarantees the row exists; its own status decides what happens next.
        $invitation = invitation::find_latest((int) $courselink->record->courseid, $data->email);
        redirect($invitation->accept_url());
    }
    if ($prefill = optional_param('email', '', PARAM_EMAIL)) {
        $form->set_data(['email' => $prefill]);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('courseentry', 'local_richimath', $courselink->target_name()));
    echo html_writer::tag('p', get_string('courseentryintro', 'local_richimath'));
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

$invitation = invitation::from_token($token);
$loginurl = new moodle_url('/login/index.php');
if (!$invitation) {
    if ($courselink = courselink::from_token($token)) {
        local_richimath_identify($courselink);
    }
    local_richimath_stop(get_string('invitationinvalid', 'local_richimath'), 'error', $loginurl, get_string('login'));
}
if ($invitation->status() === invitation::STATUS_EXPIRED) {
    local_richimath_stop(get_string('invitationexpired', 'local_richimath'), 'error', $loginurl, get_string('login'));
}
if ($invitation->status() === invitation::STATUS_ACCEPTED) {
    local_richimath_stop(get_string('invitationused', 'local_richimath'), 'info', $loginurl, get_string('login'));
}

$target = $invitation->target_name();

// Already signed in: the link belongs to this account, or to somebody else's.
if (isloggedin() && !isguestuser()) {
    if ($invitation->matches_email($USER->email)) {
        $landing = $invitation->redeem($USER);
        redirect($landing, get_string('welcomeredeemed', 'local_richimath', $target), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }
    local_richimath_stop(
        get_string('wrongaccount', 'local_richimath', (object) ['email' => $invitation->record->email, 'current' => $USER->email]),
        'warning',
        new moodle_url('/login/logout.php', ['sesskey' => sesskey()]),
        get_string('logout')
    );
}

// An account with that email exists: sign in, then come back here.
$existing = $DB->get_record_select(
    'user',
    $DB->sql_equal('email', ':email', false, false) . ' AND deleted = 0 AND mnethostid = :mnethostid',
    ['email' => $invitation->record->email, 'mnethostid' => $CFG->mnet_localhost_id],
    'id',
    IGNORE_MULTIPLE
);
if ($existing) {
    $SESSION->wantsurl = $PAGE->url->out(false);
    local_richimath_stop(
        get_string('loginexisting', 'local_richimath', s($invitation->record->email)),
        'info',
        $loginurl,
        get_string('login')
    );
}

// New account, bound to the invited email.
$form = new \local_richimath\form\signup_form(null, ['invitation' => $invitation]);
if ($data = $form->get_data()) {
    $username = clean_param($invitation->record->email, PARAM_USERNAME);
    if ($DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
        $username .= '.' . random_string(4);
    }
    $user = (object) [
        'auth' => 'manual',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'username' => $username,
        'email' => $invitation->record->email,
        'firstname' => $data->firstname,
        'lastname' => $data->lastname,
        'password' => $data->password,
        'lang' => $CFG->lang,
    ];
    $userid = \core\user::create_user($user, true, true);
    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
    complete_user_login($user);

    $landing = $invitation->redeem($user);
    redirect($landing, get_string('welcomeredeemed', 'local_richimath', $target), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('createaccount', 'local_richimath'));
echo html_writer::tag('p', get_string('createaccountintro', 'local_richimath',
    (object) ['target' => $target, 'email' => s($invitation->record->email)]));
$form->display();
echo $OUTPUT->footer();
