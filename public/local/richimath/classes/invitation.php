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
 * One invitation: a single-use link bound to an email, optionally to a course.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class invitation {
    /** @var string Pending: open and not expired. */
    public const STATUS_PENDING = 'pending';
    /** @var string Accepted: redeemed by a user. */
    public const STATUS_ACCEPTED = 'accepted';
    /** @var string Expired: past its deadline without being used. */
    public const STATUS_EXPIRED = 'expired';

    /** @var \stdClass The local_richimath_invitation row. */
    public $record;

    /**
     * @param \stdClass $record The local_richimath_invitation row.
     */
    public function __construct(\stdClass $record) {
        $this->record = $record;
    }

    /**
     * Creates and stores an invitation issued by the current user.
     *
     * @param int $courseid 0 for a site-only invitation.
     * @param string $email
     * @return self
     */
    public static function create(int $courseid, string $email): self {
        global $DB, $USER;

        $days = (int) get_config('local_richimath', 'expirydays') ?: 7;
        $record = (object) [
            'courseid' => $courseid,
            'email' => self::normalise_email($email),
            'token' => bin2hex(random_bytes(20)),
            'createdby' => $USER->id,
            'timecreated' => time(),
            'timeexpires' => time() + $days * DAYSECS,
            'timeused' => 0,
            'userid' => 0,
        ];
        $record->id = $DB->insert_record('local_richimath_invitation', $record);

        return new self($record);
    }

    /**
     * @param string $token
     * @return self|null
     */
    public static function from_token(string $token): ?self {
        global $DB;

        $record = $DB->get_record('local_richimath_invitation', ['token' => $token]);
        return $record ? new self($record) : null;
    }

    /**
     * @param int $id
     * @return self|null
     */
    public static function from_id(int $id): ?self {
        global $DB;

        $record = $DB->get_record('local_richimath_invitation', ['id' => $id]);
        return $record ? new self($record) : null;
    }

    /**
     * Whether an open invitation for this email already exists in the same place.
     *
     * @param int $courseid
     * @param string $email
     * @return bool
     */
    public static function pending_exists(int $courseid, string $email): bool {
        global $DB;

        return $DB->record_exists_select(
            'local_richimath_invitation',
            'courseid = :courseid AND email = :email AND timeused = 0 AND timeexpires > :now',
            ['courseid' => $courseid, 'email' => self::normalise_email($email), 'now' => time()]
        );
    }

    /**
     * Invitations issued in one place, newest first.
     *
     * @param int $courseid 0 for site-only invitations.
     * @return self[]
     */
    public static function list_for(int $courseid): array {
        global $DB;

        $records = $DB->get_records('local_richimath_invitation', ['courseid' => $courseid], 'timecreated DESC');
        return array_map(fn($record) => new self($record), array_values($records));
    }

    /**
     * Newest invitation of this email in this place, whatever its status.
     *
     * @param int $courseid
     * @param string $email
     * @return self|null
     */
    public static function find_latest(int $courseid, string $email): ?self {
        global $DB;

        $records = $DB->get_records(
            'local_richimath_invitation',
            ['courseid' => $courseid, 'email' => self::normalise_email($email)],
            'timecreated DESC', '*', 0, 1
        );
        return $records ? new self(reset($records)) : null;
    }

    /**
     * @param string $email
     * @return string
     */
    public static function normalise_email(string $email): string {
        return \core_text::strtolower(trim($email));
    }

    /**
     * @return string One of the STATUS_* constants.
     */
    public function status(): string {
        if ($this->record->timeused) {
            return self::STATUS_ACCEPTED;
        }
        if ($this->record->timeexpires < time()) {
            return self::STATUS_EXPIRED;
        }
        return self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function is_open(): bool {
        return $this->status() === self::STATUS_PENDING;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function matches_email(string $email): bool {
        return self::normalise_email($email) === $this->record->email;
    }

    /**
     * @return \moodle_url The personal link.
     */
    public function accept_url(): \moodle_url {
        return new \moodle_url('/local/richimath/accept.php', ['token' => $this->record->token]);
    }

    /**
     * The address that circulates: the shared course link (with this email
     * prefilled) once it exists, the personal link otherwise. One link for
     * everyone, the invitation list does the filtering.
     *
     * @return \moodle_url
     */
    public function share_url(): \moodle_url {
        if ($link = courselink::find((int) $this->record->courseid)) {
            return $link->url($this->record->email);
        }
        return $this->accept_url();
    }

    /**
     * @return \moodle_url A WhatsApp share link with the message prefilled.
     */
    public function whatsapp_url(): \moodle_url {
        return new \moodle_url('https://wa.me/', ['text' => get_string('whatsappmessage', 'local_richimath', $this->placeholders())]);
    }

    /**
     * What the invitation opens: the course name, or the site name.
     *
     * @return string
     */
    public function target_name(): string {
        if ($this->record->courseid) {
            $course = get_course($this->record->courseid);
            return format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]);
        }
        return format_string(get_site()->fullname);
    }

    /**
     * Marks the invitation used by $user and enrols them where it points to.
     *
     * The course enrolment goes through the course's manual enrolment
     * instance (created with its defaults when missing), so the student
     * shows up in Participants exactly like one enrolled by hand and the
     * role is the instance's default one.
     *
     * @param \stdClass $user
     * @return \moodle_url Where to send the user next.
     */
    public function redeem(\stdClass $user): \moodle_url {
        global $DB;

        if ($this->record->courseid) {
            $course = get_course($this->record->courseid);
            $plugin = enrol_get_plugin('manual');
            $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE);
            if (!$instance) {
                $instance = $DB->get_record('enrol', ['id' => $plugin->add_default_instance($course)], '*', MUST_EXIST);
            }
            $plugin->enrol_user($instance, $user->id, $instance->roleid);
            $landing = new \moodle_url('/course/view.php', ['id' => $course->id]);
        } else {
            $landing = new \moodle_url('/my/');
        }

        $this->record->timeused = time();
        $this->record->userid = $user->id;
        $DB->update_record('local_richimath_invitation', $this->record);

        return $landing;
    }

    /**
     * Emails the personal link to the invited address.
     *
     * The recipient has no account yet, so this goes to a throwaway user
     * record built from core's no-reply dummy (email_to_user only needs a
     * non-empty id and the address).
     *
     * @return bool Whether the mail left the server.
     */
    public function send_email(): bool {
        $to = clone \core_user::get_noreply_user();
        $to->id = -99;
        $to->email = $this->record->email;
        $to->firstname = '';
        $to->lastname = '';
        $to->mailformat = 1;
        // The no-reply dummy carries emailstop = 1 (it is meant to send, not receive).
        $to->emailstop = 0;

        $a = $this->placeholders();
        $text = get_string('emailbody', 'local_richimath', $a);
        return email_to_user(
            $to,
            \core_user::get_noreply_user(),
            get_string('emailsubject', 'local_richimath', $a),
            $text,
            text_to_html($text, false, false, true)
        );
    }

    /**
     * Placeholders shared by the email and the WhatsApp message.
     *
     * @return \stdClass
     */
    private function placeholders(): \stdClass {
        global $DB;

        $inviter = $DB->get_record('user', ['id' => $this->record->createdby]);
        return (object) [
            'email' => $this->record->email,
            'target' => $this->target_name(),
            'site' => format_string(get_site()->fullname),
            'inviter' => $inviter ? fullname($inviter) : format_string(get_site()->fullname),
            'url' => $this->share_url()->out(false),
            'expires' => userdate($this->record->timeexpires, get_string('strftimedate', 'langconfig')),
        ];
    }
}
