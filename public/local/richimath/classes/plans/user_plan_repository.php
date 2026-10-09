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

namespace local_richimath\plans;

/**
 * Which plan each user is on. The only file that names its table.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_plan_repository {
    /** @var string */
    private const TABLE = 'local_richimath_userplan';

    /**
     * @param int $userid
     * @return int|null The plan id the user was explicitly put on.
     */
    public function plan_id_of(int $userid): ?int {
        global $DB;

        $planid = $DB->get_field(self::TABLE, 'planid', ['userid' => $userid]);

        return $planid ? (int) $planid : null;
    }

    /**
     * @param int $userid
     * @param int $planid
     */
    public function set(int $userid, int $planid): void {
        global $DB, $USER;

        $now = time();
        $existing = $DB->get_record(self::TABLE, ['userid' => $userid]);
        if ($existing) {
            $existing->planid = $planid;
            $existing->timemodified = $now;
            $existing->usermodified = $USER->id ?? 0;
            $DB->update_record(self::TABLE, $existing);

            return;
        }

        $DB->insert_record(self::TABLE, (object) [
            'userid' => $userid,
            'planid' => $planid,
            'timemodified' => $now,
            'usermodified' => $USER->id ?? 0,
        ]);
    }

    /**
     * @param int $planid
     * @return int[] The users explicitly on this plan.
     */
    public function user_ids_on(int $planid): array {
        global $DB;

        return array_map('intval', $DB->get_fieldset_select(self::TABLE, 'userid', 'planid = ?', [$planid]));
    }

    /**
     * @param int $planid
     * @return int
     */
    public function count_on(int $planid): int {
        global $DB;

        return $DB->count_records(self::TABLE, ['planid' => $planid]);
    }

    /**
     * @param int $planid
     */
    public function delete_plan(int $planid): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['planid' => $planid]);
    }
}
