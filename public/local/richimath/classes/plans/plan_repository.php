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
 * Plans in and out of the database. The only file that names the table.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plan_repository {
    /** @var string */
    private const TABLE = 'local_richimath_plan';

    /**
     * @return plan[] Every plan, in display order, keyed by id.
     */
    public function all(): array {
        global $DB;

        $plans = [];
        foreach ($DB->get_records(self::TABLE, null, 'sortorder ASC, name ASC') as $record) {
            $plans[$record->id] = plan::from_record($record);
        }

        return $plans;
    }

    /**
     * @param int $id
     * @return plan|null
     */
    public function find(int $id): ?plan {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['id' => $id]);

        return $record ? plan::from_record($record) : null;
    }

    /**
     * @param string $shortname
     * @return plan|null
     */
    public function find_by_shortname(string $shortname): ?plan {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['shortname' => $shortname]);

        return $record ? plan::from_record($record) : null;
    }

    /**
     * The plan users without one fall back to. Falls back itself to the
     * seeded shortname, so a site whose default flag was lost still resolves.
     *
     * @return plan|null
     */
    public function find_default(): ?plan {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['isdefault' => 1], '*', IGNORE_MULTIPLE);
        if ($record) {
            return plan::from_record($record);
        }

        return $this->find_by_shortname(plan::DEFAULT_SHORTNAME);
    }

    /**
     * Inserts or updates, and keeps "exactly one default" true.
     *
     * @param plan $plan
     * @return plan The stored plan, with its id.
     */
    public function save(plan $plan): plan {
        global $DB;

        $now = time();
        $record = $plan->to_record();
        $record->timemodified = $now;

        if ($plan->id) {
            $DB->update_record(self::TABLE, $record);
        } else {
            $record->timecreated = $now;
            $plan->id = $DB->insert_record(self::TABLE, $record);
        }

        if ($plan->isdefault) {
            $DB->set_field_select(self::TABLE, 'isdefault', 0, 'id <> ?', [$plan->id]);
        }

        return $plan;
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['id' => $id]);
    }

    /**
     * @param string $shortname
     * @return bool
     */
    public function shortname_exists(string $shortname): bool {
        global $DB;

        return $DB->record_exists(self::TABLE, ['shortname' => $shortname]);
    }
}
