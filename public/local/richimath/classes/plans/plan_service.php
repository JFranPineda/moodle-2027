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
 * What the administration screens and the login observer actually do.
 *
 * Everything that changes a plan or a user's plan goes through here, so the
 * rule about when a theme is written lives in exactly one place
 * (theme_assignment) and the pages stay free of business logic.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plan_service {
    /** @var plan_repository */
    private $plans;

    /** @var user_plan_repository */
    private $userplans;

    /** @var theme_assignment */
    private $themes;

    /**
     * @param plan_repository|null $plans
     * @param user_plan_repository|null $userplans
     * @param theme_assignment|null $themes
     */
    public function __construct(?plan_repository $plans = null, ?user_plan_repository $userplans = null,
            ?theme_assignment $themes = null) {
        $this->plans = $plans ?: new plan_repository();
        $this->userplans = $userplans ?: new user_plan_repository();
        $this->themes = $themes ?: new theme_assignment($this->plans, $this->userplans);
    }

    /**
     * The plans the academy starts with, each one wearing the appearance of
     * its own level — that is what makes a level look like its design.
     *
     * @return array[]
     */
    public static function seed(): array {
        return [
            ['shortname' => 'primaria', 'name' => 'Primaria', 'appearance' => 'elementary', 'isdefault' => true],
            ['shortname' => 'secundaria', 'name' => 'Secundaria', 'appearance' => 'highschool', 'isdefault' => false],
            ['shortname' => 'preuni', 'name' => 'Pre Uni', 'appearance' => 'preuniversity', 'isdefault' => false],
            ['shortname' => 'universitaria', 'name' => 'Universitaria', 'appearance' => 'university', 'isdefault' => false],
            ['shortname' => 'admin', 'name' => 'Admin', 'appearance' => 'university', 'isdefault' => false],
        ];
    }

    /**
     * Creates the starting plans. Idempotent: an existing short name is left
     * exactly as the administrator left it.
     */
    public function install_defaults(): void {
        $sortorder = 0;
        foreach (self::seed() as $seed) {
            $sortorder++;
            if ($this->plans->shortname_exists($seed['shortname'])) {
                continue;
            }
            $this->plans->save(new plan(
                $seed['shortname'],
                $seed['name'],
                $seed['appearance'],
                $sortorder,
                $seed['isdefault']
            ));
        }
    }

    /**
     * Creates or updates a plan.
     *
     * Deliberately does NOT touch the users already on it: re-pointing a plan
     * at another appearance reaches them at their next login, not while they
     * are working. Their theme is refreshed by the login observer.
     *
     * @param \stdClass $data From plan_form.
     * @return plan
     */
    public function save_plan(\stdClass $data): plan {
        $plan = new plan(
            $data->shortname,
            $data->name,
            $data->appearance,
            (int) $data->sortorder,
            !empty($data->isdefault),
            empty($data->id) ? null : (int) $data->id
        );

        return $this->plans->save($plan);
    }

    /**
     * Deletes a plan and moves its users to the default one, which is a
     * change of *their* plan, so their theme is applied straight away.
     *
     * @param int $planid
     * @return bool False when the plan is the default and cannot go.
     */
    public function delete_plan(int $planid): bool {
        $default = $this->plans->find_default();
        if (!$default || $default->id === $planid) {
            // Deleting the fallback would leave its users pointing nowhere.
            return false;
        }

        foreach ($this->userplans->user_ids_on($planid) as $userid) {
            $this->assign_user($userid, $default->id);
        }
        $this->userplans->delete_plan($planid);
        $this->plans->delete($planid);

        return true;
    }

    /**
     * Puts a user on a plan and dresses them in its appearance at once.
     *
     * @param int $userid
     * @param int $planid
     * @return bool False when the plan does not exist.
     */
    public function assign_user(int $userid, int $planid): bool {
        $plan = $this->plans->find($planid);
        if (!$plan) {
            return false;
        }

        $this->userplans->set($userid, $planid);
        $this->themes->apply($userid, $plan);

        return true;
    }

    /**
     * @param int $userid
     * @return plan|null The plan governing this user.
     */
    public function plan_of(int $userid): ?plan {
        return $this->themes->plan_of($userid);
    }

    /**
     * @param int $userid
     */
    public function sync_theme(int $userid): void {
        $this->themes->sync($userid);
    }

    /**
     * @return plan[]
     */
    public function all_plans(): array {
        return $this->plans->all();
    }

    /**
     * @return plan|null
     */
    public function default_plan(): ?plan {
        return $this->plans->find_default();
    }

    /**
     * @param int $planid
     * @return int
     */
    public function user_count(int $planid): int {
        return $this->userplans->count_on($planid);
    }

    /**
     * @return string[] id => name, for a select element.
     */
    public function plan_menu(): array {
        $menu = [];
        foreach ($this->plans->all() as $plan) {
            $menu[$plan->id] = $plan->name;
        }

        return $menu;
    }
}
