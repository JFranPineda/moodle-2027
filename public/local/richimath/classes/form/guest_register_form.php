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

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * What a visitor fills in before being let into a BigBlueButton session.
 *
 * Four fields and a tick. Anything longer and people close the tab.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class guest_register_form extends \moodleform {

    /**
     * Build the form.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'firstname',
            get_string('leadfirstname', 'local_richimath'), ['maxlength' => 100]);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', null, 'required', null, 'client');

        $mform->addElement('text', 'lastname',
            get_string('leadlastname', 'local_richimath'), ['maxlength' => 100]);
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', null, 'required', null, 'client');

        $mform->addElement('text', 'university',
            get_string('leaduniversity', 'local_richimath'), ['maxlength' => 255]);
        $mform->setType('university', PARAM_TEXT);
        $mform->addRule('university', null, 'required', null, 'client');
        $mform->addHelpButton('university', 'leaduniversity', 'local_richimath');

        $mform->addElement('text', 'email',
            get_string('leademail', 'local_richimath'), ['maxlength' => 100]);
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addRule('email', null, 'required', null, 'client');

        // Keeping the address to write to them later is a separate thing from
        // letting them into the class, and Peru's Ley 29733 wants it asked for.
        // Unticked by default and never a condition of entry.
        $mform->addElement('advcheckbox', 'marketingconsent', '',
            get_string('leadconsent', 'local_richimath'));
        $mform->setType('marketingconsent', PARAM_BOOL);

        $mform->addElement('hidden', 'uid', $this->_customdata['uid']);
        $mform->setType('uid', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(false, get_string('leadsubmit', 'local_richimath'));
    }

    /**
     * Reject an address we could never write to.
     *
     * @param array $data submitted values
     * @param array $files
     * @return array errors keyed by field
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!validate_email($data['email'])) {
            $errors['email'] = get_string('leadinvalidemail', 'local_richimath');
        }
        return $errors;
    }
}
