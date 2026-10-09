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
 * The one place that decides when a user's theme is written.
 *
 * The theme is a snapshot on the user record, never resolved while a page
 * renders. That single decision is what makes the two rules the academy
 * asked for hold at the same time:
 *
 * - Moving a user to another plan writes the new theme there and then, so
 *   nobody has to go and set a theme by hand afterwards.
 * - Re-pointing a plan at another appearance writes nothing. Everyone
 *   already on that plan keeps what they are wearing until their next login,
 *   which is when this class looks the plan up again.
 *
 * Core honours the column through $CFG->allowuserthemes, turned on by the
 * plugin's install step; without it none of this decides anything.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_assignment {
    /** @var plan_repository */
    private $plans;

    /** @var user_plan_repository */
    private $userplans;

    /**
     * @param plan_repository|null $plans
     * @param user_plan_repository|null $userplans
     */
    public function __construct(?plan_repository $plans = null, ?user_plan_repository $userplans = null) {
        $this->plans = $plans ?: new plan_repository();
        $this->userplans = $userplans ?: new user_plan_repository();
    }

    /**
     * The plan that governs a user: the one they were put on, or the default.
     *
     * @param int $userid
     * @return plan|null Null only on a site with no plans at all.
     */
    public function plan_of(int $userid): ?plan {
        $planid = $this->userplans->plan_id_of($userid);
        if ($planid && ($plan = $this->plans->find($planid))) {
            return $plan;
        }

        return $this->plans->find_default();
    }

    /**
     * Writes a plan's theme onto a user.
     *
     * @param int $userid
     * @param plan $plan
     */
    public function apply(int $userid, plan $plan): void {
        global $DB, $USER;

        $theme = $plan->theme();
        if ($DB->get_field('user', 'theme', ['id' => $userid]) === $theme) {
            return;
        }

        $DB->set_field('user', 'theme', $theme, ['id' => $userid]);

        // The session holds its own copy of the user record. Refreshing it
        // here is what makes an administrator changing their own plan see
        // the new look on the very next page instead of after logging in.
        if (!empty($USER->id) && (int) $USER->id === $userid) {
            $USER->theme = $theme;
        }
    }

    /**
     * Re-reads the user's plan and applies it. Called at login, which is
     * where edits to a plan's appearance finally reach the people on it.
     *
     * @param int $userid
     */
    public function sync(int $userid): void {
        $plan = $this->plan_of($userid);
        if ($plan) {
            $this->apply($userid, $plan);
        }
    }
}
