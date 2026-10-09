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

namespace local_richimath\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Invitee form: name and password for the new account. The email is fixed
 * by the invitation and only displayed.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup_form extends \moodleform {
    /**
     * Form definition.
     */
    protected function definition(): void {
        $mform = $this->_form;
        $invitation = $this->_customdata['invitation'];

        $mform->addElement('hidden', 'token', $invitation->record->token);
        $mform->setType('token', PARAM_ALPHANUM);

        $mform->addElement('static', 'emaildisplay', get_string('email'), s($invitation->record->email));

        $mform->addElement('text', 'firstname', get_string('firstname'), ['size' => 25]);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', null, 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'), ['size' => 25]);
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', null, 'required', null, 'client');

        $mform->addElement('static', 'passwordpolicy', '', print_password_policy());

        $mform->addElement('password', 'password', get_string('password'), ['size' => 25]);
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', null, 'required', null, 'client');

        $mform->addElement('password', 'password2', get_string('passwordagain', 'local_richimath'), ['size' => 25]);
        $mform->setType('password2', PARAM_RAW);
        $mform->addRule('password2', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('createandenter', 'local_richimath'));
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if ($data['password'] !== $data['password2']) {
            $errors['password2'] = get_string('passwordsdiffer', 'local_richimath');
        }
        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }

        return $errors;
    }
}
