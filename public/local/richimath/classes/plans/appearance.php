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
 * The appearances the academy ships: one per design system in docs/design/.
 *
 * Defined in code on purpose. An appearance is not data an administrator
 * invents — it exists only because a theme and a design document exist in
 * the repository, and adding one means writing a child theme. The plans, on
 * the other hand, are data: they are created and renamed from the UI and
 * only pick one of these keys.
 *
 * Nothing here decides what a user sees; that is the plan's job. This is the
 * catalogue the plan chooses from, and the bridge to the Moodle theme that
 * implements each design.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appearance {
    /** @var string Fallback for a plan whose appearance no longer exists. */
    public const DEFAULT_KEY = 'elementary';

    /**
     * key => [theme component, design folder under docs/design/].
     *
     * @return array[]
     */
    public static function all(): array {
        return [
            'elementary' => ['theme' => 'rmprimaria', 'design' => '01_elementary_school'],
            'highschool' => ['theme' => 'rmsecundaria', 'design' => '02_high_school'],
            'preuniversity' => ['theme' => 'rmpreu', 'design' => '03_pre_university'],
            'university' => ['theme' => 'rmuniversidad', 'design' => '04_university'],
        ];
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function exists(string $key): bool {
        return array_key_exists($key, self::all());
    }

    /**
     * The Moodle theme that implements an appearance.
     *
     * @param string $key
     * @return string Theme component name, without the theme_ prefix.
     */
    public static function theme(string $key): string {
        $all = self::all();
        $key = self::exists($key) ? $key : self::DEFAULT_KEY;

        return $all[$key]['theme'];
    }

    /**
     * @param string $key
     * @return string Human name, taken from the theme's own pluginname.
     */
    public static function name(string $key): string {
        if (!self::exists($key)) {
            return $key;
        }

        return get_string('pluginname', 'theme_' . self::theme($key));
    }

    /**
     * @param string $key
     * @return string The folder under docs/design/ this appearance came from.
     */
    public static function design(string $key): string {
        $all = self::all();

        return $all[$key]['design'] ?? '';
    }

    /**
     * Options for a select element.
     *
     * @return string[] key => human name
     */
    public static function menu(): array {
        $menu = [];
        foreach (array_keys(self::all()) as $key) {
            $menu[$key] = self::name($key);
        }

        return $menu;
    }
}
