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

namespace local_richimath;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/user/profile/lib.php');

/**
 * Which teacher's WhatsApp a course shows, if any.
 *
 * Nothing here is stored by this plugin: the number and the teacher's own
 * switch are **custom profile fields**, and the per-course override is a
 * **course custom field**. Both are native, so the teacher edits them in the
 * screens they already know and there is no form of ours to maintain. The
 * upgrade step creates them with fixed shortnames, which is what lets this
 * code find them again.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class whatsapp {

    /** @var string Profile field holding the teacher's number. */
    public const FIELD_NUMBER = 'rmwhatsapp';

    /** @var string Profile field: the teacher's switch for all their courses. */
    public const FIELD_ENABLED = 'rmwhatsappon';

    /** @var string Course custom field overriding the teacher's switch. */
    public const FIELD_COURSE = 'rmwhatsapp';

    /**
     * The wa.me link to show in this course, or null if there is none.
     *
     * @param int $courseid
     * @return array|null ['url' => string, 'teacher' => string] or null
     */
    public static function for_course(int $courseid): ?array {
        global $DB;

        if (!$courseid || $courseid == SITEID) {
            return null;
        }

        $override = self::course_override($courseid);
        if ($override === 'off') {
            return null;
        }

        $context = \context_course::instance($courseid);
        // Only an editing teacher answers for a course; a non-editing teacher
        // or a manager is not who the student should be writing to.
        // fullname() needs every name field — firstnamephonetic and friends
        // included — or each course page fills with debugging notices.
        $namefields = \core_user\fields::for_name()->get_sql('u', false, '', '', true)->selects;
        $teachers = get_role_users(
            $DB->get_field('role', 'id', ['shortname' => 'editingteacher']),
            $context, false, 'u.id' . $namefields, 'u.id ASC');

        foreach ($teachers as $teacher) {
            $profile = profile_user_record($teacher->id, false);
            $number = self::clean_number($profile->{self::FIELD_NUMBER} ?? '');
            if ($number === '') {
                continue;
            }
            // The course can force the button on for a teacher who keeps it
            // off everywhere else; otherwise their own switch decides.
            $enabled = !empty($profile->{self::FIELD_ENABLED});
            if ($override !== 'on' && !$enabled) {
                continue;
            }

            return [
                'url' => 'https://wa.me/' . $number,
                'teacher' => fullname($teacher),
            ];
        }

        return null;
    }

    /**
     * What the course says: 'on', 'off' or 'inherit'.
     *
     * @param int $courseid
     * @return string
     */
    private static function course_override(int $courseid): string {
        $handler = \core_course\customfield\course_handler::create();
        foreach ($handler->get_instance_data($courseid, true) as $data) {
            if ($data->get_field()->get('shortname') !== self::FIELD_COURSE) {
                continue;
            }
            // A select stores the index, not the label, so comparing against a
            // literal would break the day someone translates the options. Both
            // sides read the same language string instead.
            $options = array_map('trim',
                explode("\n", get_string('whatsappcourseoptions', 'local_richimath')));
            $chosen = trim((string) $data->export_value());
            if ($chosen !== '' && $chosen === ($options[1] ?? null)) {
                return 'on';
            }
            if ($chosen !== '' && $chosen === ($options[2] ?? null)) {
                return 'off';
            }
            return 'inherit';
        }
        return 'inherit';
    }

    /**
     * Strip a number down to what wa.me accepts: digits, no plus, no spaces.
     *
     * A Peruvian number written as "+51 987 654 321" has to reach WhatsApp as
     * "51987654321" or the link opens an empty chat.
     *
     * @param string $raw
     * @return string
     */
    public static function clean_number(string $raw): string {
        return preg_replace('/\D+/', '', $raw);
    }
}
