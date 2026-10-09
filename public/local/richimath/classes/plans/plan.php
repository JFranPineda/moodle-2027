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
 * A plan: the level a user belongs to, and the appearance that comes with it.
 *
 * The entity, and only the entity — it knows what a plan is and what makes
 * one valid, and nothing about tables, forms or themes. Persistence lives in
 * plan_repository, the use cases in plan_service.
 *
 * A plan carries no price. It groups people; if billing ever arrives it only
 * has to move a user from one plan to another.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plan {
    /** @var string Short name of the plan users fall back to. */
    public const DEFAULT_SHORTNAME = 'primaria';

    /** @var int|null Null until the repository has stored it. */
    public $id;
    /** @var string Stable key for code and imports. */
    public $shortname;
    /** @var string Display name. */
    public $name;
    /** @var string Key from the appearance catalogue. */
    public $appearance;
    /** @var int Display order. */
    public $sortorder;
    /** @var bool Whether users without a plan fall back to this one. */
    public $isdefault;

    /**
     * @param string $shortname
     * @param string $name
     * @param string $appearance
     * @param int $sortorder
     * @param bool $isdefault
     * @param int|null $id
     */
    public function __construct(string $shortname, string $name, string $appearance,
            int $sortorder = 0, bool $isdefault = false, ?int $id = null) {
        $this->id = $id;
        $this->shortname = $shortname;
        $this->name = $name;
        // A plan pointing at an appearance nobody wrote a theme for would
        // leave its users themeless; fall back rather than trust the input.
        $this->appearance = appearance::exists($appearance) ? $appearance : appearance::DEFAULT_KEY;
        $this->sortorder = $sortorder;
        $this->isdefault = $isdefault;
    }

    /**
     * @param \stdClass $record A local_richimath_plan row.
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        return new self(
            $record->shortname,
            $record->name,
            $record->appearance,
            (int) $record->sortorder,
            !empty($record->isdefault),
            (int) $record->id
        );
    }

    /**
     * @return \stdClass Ready for the database, minus the timestamps.
     */
    public function to_record(): \stdClass {
        $record = (object) [
            'shortname' => $this->shortname,
            'name' => $this->name,
            'appearance' => $this->appearance,
            'sortorder' => $this->sortorder,
            'isdefault' => $this->isdefault ? 1 : 0,
        ];
        if ($this->id) {
            $record->id = $this->id;
        }

        return $record;
    }

    /**
     * @return string The Moodle theme this plan's users wear.
     */
    public function theme(): string {
        return appearance::theme($this->appearance);
    }

    /**
     * @return string Human name of the appearance.
     */
    public function appearance_name(): string {
        return appearance::name($this->appearance);
    }
}
