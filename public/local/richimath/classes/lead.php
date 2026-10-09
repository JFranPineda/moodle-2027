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
 * Someone who registered to attend a BigBlueButton session as a guest.
 *
 * Core keeps nothing about a guest: the name they type only labels them inside
 * the room and is gone when the meeting ends. These rows are the record of who
 * actually showed up, which is the point of running open sessions at all.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lead {

    /** @var string Table holding the leads. */
    private const TABLE = 'local_richimath_lead';

    /**
     * Record a registration, or update the one this person already has.
     *
     * Someone attending three sessions is one lead who came three times, not
     * three leads: the unique index is (email, session).
     *
     * @param int $bbbinstanceid bigbluebuttonbn.id of the session
     * @param int $courseid course the session belongs to
     * @param array $data firstname, lastname, university, email, marketingconsent
     * @return int id of the row
     */
    public static function record(int $bbbinstanceid, int $courseid, array $data): int {
        global $DB;

        $email = \core_text::strtolower(trim($data['email']));
        $now = time();

        $existing = $DB->get_record(self::TABLE,
            ['email' => $email, 'bbbinstanceid' => $bbbinstanceid]);

        if ($existing) {
            $existing->firstname = trim($data['firstname']);
            $existing->lastname = trim($data['lastname']);
            $existing->university = trim($data['university']);
            // Consent only ever moves forward by their own hand; coming back
            // without ticking it does not silently revoke what they granted.
            $existing->marketingconsent = max($existing->marketingconsent,
                empty($data['marketingconsent']) ? 0 : 1);
            $existing->timelastjoined = $now;
            $existing->joincount++;
            $DB->update_record(self::TABLE, $existing);
            return (int) $existing->id;
        }

        return (int) $DB->insert_record(self::TABLE, (object) [
            'bbbinstanceid' => $bbbinstanceid,
            'courseid' => $courseid,
            'firstname' => trim($data['firstname']),
            'lastname' => trim($data['lastname']),
            'university' => trim($data['university']),
            'email' => $email,
            'marketingconsent' => empty($data['marketingconsent']) ? 0 : 1,
            'timecreated' => $now,
            'timelastjoined' => $now,
            'joincount' => 1,
        ]);
    }

    /**
     * Every lead, newest first, for the admin list and the CSV.
     *
     * @param bool $consentedonly only those who agreed to be contacted
     * @return array of records
     */
    public static function all(bool $consentedonly = false): array {
        global $DB;
        return $DB->get_records(self::TABLE,
            $consentedonly ? ['marketingconsent' => 1] : null, 'timecreated DESC');
    }

    /**
     * How many leads there are, and how many of them can be written to.
     *
     * @return array [total, contactable]
     */
    public static function counts(): array {
        global $DB;
        return [
            (int) $DB->count_records(self::TABLE),
            (int) $DB->count_records(self::TABLE, ['marketingconsent' => 1]),
        ];
    }

    /**
     * The display name BigBlueButton will show inside the room.
     *
     * @param array $data firstname and lastname
     * @return string
     */
    public static function display_name(array $data): string {
        return trim(trim($data['firstname']) . ' ' . trim($data['lastname']));
    }
}
