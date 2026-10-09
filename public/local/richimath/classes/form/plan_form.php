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

use local_richimath\plans\appearance;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Create or edit a plan and the appearance it wears.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plan_form extends \moodleform {
    /**
     * Form definition.
     */
    protected function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('planname', 'local_richimath'), ['size' => 40]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'shortname', get_string('planshortname', 'local_richimath'), ['size' => 25]);
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addHelpButton('shortname', 'planshortname', 'local_richimath');

        $mform->addElement('select', 'appearance', get_string('appearance', 'local_richimath'), appearance::menu());
        $mform->setDefault('appearance', appearance::DEFAULT_KEY);
        $mform->addHelpButton('appearance', 'appearance', 'local_richimath');

        $mform->addElement('text', 'sortorder', get_string('plansortorder', 'local_richimath'), ['size' => 5]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $mform->addElement('advcheckbox', 'isdefault', get_string('plandefault', 'local_richimath'));
        $mform->addHelpButton('isdefault', 'plandefault', 'local_richimath');

        $this->add_action_buttons();
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        global $DB;

        $errors = parent::validation($data, $files);

        $clash = $DB->get_record('local_richimath_plan', ['shortname' => $data['shortname']], 'id');
        if ($clash && $clash->id != $data['id']) {
            $errors['shortname'] = get_string('planshortnametaken', 'local_richimath');
        }

        return $errors;
    }
}
