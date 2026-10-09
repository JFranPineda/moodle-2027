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

/**
 * The one shared link of a course (or of the site, courseid 0): stable,
 * never expires by itself, and only lets in the emails on the invitation
 * list — each of those still expires and is single-use on its own.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courselink {
    /** @var \stdClass The local_richimath_courselink row. */
    public $record;

    /**
     * @param \stdClass $record
     */
    public function __construct(\stdClass $record) {
        $this->record = $record;
    }

    /**
     * The link of this course, creating it on first use.
     *
     * @param int $courseid 0 = the site-wide link.
     * @return self
     */
    public static function get_or_create(int $courseid): self {
        global $DB, $USER;

        if ($record = $DB->get_record('local_richimath_courselink', ['courseid' => $courseid])) {
            return new self($record);
        }
        $record = (object) [
            'courseid' => $courseid,
            'token' => bin2hex(random_bytes(20)),
            'createdby' => $USER->id,
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('local_richimath_courselink', $record);

        return new self($record);
    }

    /**
     * @param int $courseid
     * @return self|null
     */
    public static function find(int $courseid): ?self {
        global $DB;

        $record = $DB->get_record('local_richimath_courselink', ['courseid' => $courseid]);
        return $record ? new self($record) : null;
    }

    /**
     * @param string $token
     * @return self|null
     */
    public static function from_token(string $token): ?self {
        global $DB;

        $record = $DB->get_record('local_richimath_courselink', ['token' => $token]);
        return $record ? new self($record) : null;
    }

    /**
     * @param string|null $email Prefills the identify form when given.
     * @return \moodle_url
     */
    public function url(?string $email = null): \moodle_url {
        $params = ['token' => $this->record->token];
        if ($email !== null && $email !== '') {
            $params['email'] = $email;
        }
        return new \moodle_url('/local/richimath/accept.php', $params);
    }

    /**
     * @return \moodle_url WhatsApp share with the message prefilled.
     */
    public function whatsapp_url(): \moodle_url {
        $a = (object) ['target' => $this->target_name(), 'url' => $this->url()->out(false)];
        return new \moodle_url('https://wa.me/', ['text' => get_string('courselinkwhatsapp', 'local_richimath', $a)]);
    }

    /**
     * @return string The course name, or the site name for the site link.
     */
    public function target_name(): string {
        if ($this->record->courseid) {
            $course = get_course($this->record->courseid);
            return format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]);
        }
        return format_string(get_site()->fullname);
    }
}
