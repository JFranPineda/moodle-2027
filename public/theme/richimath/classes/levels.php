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

namespace theme_richimath;

/**
 * The five academic levels of the public site, and the courses under each.
 *
 * The levels do not line up with the category tree: Primaria and Secundaria
 * live two levels down, under ESCOLAR, while Pre universitario and
 * Universitario are top-level. So each level FINDS its category instead of
 * taking a position in the tree:
 *
 * 1. a category whose ID number is the level's key (`primaria`,
 *    `secundaria`, `preuniversitario`, `universitario`, `ib`) — set it in the
 *    category's settings to pin a level to a category whatever its name;
 * 2. otherwise, the shallowest category whose name matches the level.
 *
 * Nothing is stored: a course added, renamed or hidden shows up on the next
 * page load. A level with no category (IB, until the academy creates it) is
 * still listed, as "coming soon".
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levels {

    /**
     * Level key => name pattern, matched against the category name in upper
     * case and without accents.
     *
     * Order matters: Pre universitario is resolved before Universitario
     * because "PRE UNIVERSITARIO" also contains "UNIVERSITARIO" — and a
     * category already claimed is not offered to the next level.
     */
    private const PATTERNS = [
        'primaria' => '/PRIMARIA/',
        'secundaria' => '/SECUNDARIA/',
        'preuniversitario' => '/\bPRE\b.*UNIVERSI|PREUNIV/',
        'universitario' => '/^UNIVERSI/',
        'ib' => '/BACHILLERATO|\bIB\b/',
    ];

    /**
     * Every level, in teaching order, with its courses.
     *
     * Visibility is the viewer's own: an anonymous visitor gets exactly the
     * categories and courses Moodle lets them see, so a hidden course never
     * reaches the public site.
     *
     * @return array[] Rows for the site templates.
     */
    public static function all(): array {
        $categories = self::visible_categories();
        $claimed = self::claim($categories);

        $levels = [];
        $position = 0;
        foreach (array_keys(self::PATTERNS) as $key) {
            $category = $claimed[$key] ?? null;
            $groups = $category ? self::groups($category, $categories) : [];
            $count = array_sum(array_map(fn($group) => count($group['courses']), $groups));

            $levels[] = [
                'key' => $key,
                'name' => get_string('level' . $key, 'theme_richimath'),
                'planet' => get_string('level' . $key . 'planet', 'theme_richimath'),
                'short' => get_string('level' . $key . 'short', 'theme_richimath'),
                'tagline' => get_string('level' . $key . 'tagline', 'theme_richimath'),
                'position' => ++$position,
                'groups' => $groups,
                'count' => $count,
                'countlabel' => get_string($count == 1 ? 'levelcourse' : 'levelcourses', 'theme_richimath', $count),
                'empty' => $count == 0,
                'first' => $position == 1,
            ];
        }

        return $levels;
    }

    /**
     * The categories this viewer can see, shallowest first.
     *
     * @return \core_course_category[] Keyed by id.
     */
    private static function visible_categories(): array {
        $categories = \core_course_category::get_many(
            array_keys(\core_course_category::make_categories_list()));

        uasort($categories, fn($a, $b) => [$a->depth, $a->sortorder] <=> [$b->depth, $b->sortorder]);

        return $categories;
    }

    /**
     * Which category each level stands for.
     *
     * @param \core_course_category[] $categories Shallowest first.
     * @return \core_course_category[] Level key => category.
     */
    private static function claim(array $categories): array {
        $claimed = [];
        $taken = [];

        // An ID number is the administrator saying so: it wins over any name.
        foreach ($categories as $category) {
            $key = \core_text::strtolower(trim((string) $category->idnumber));
            if (isset(self::PATTERNS[$key]) && !isset($claimed[$key])) {
                $claimed[$key] = $category;
                $taken[$category->id] = true;
            }
        }

        foreach (self::PATTERNS as $key => $pattern) {
            if (isset($claimed[$key])) {
                continue;
            }
            foreach ($categories as $category) {
                if (isset($taken[$category->id])) {
                    continue;
                }
                if (preg_match($pattern, self::normalise($category->name))) {
                    $claimed[$key] = $category;
                    $taken[$category->id] = true;
                    break;
                }
            }
        }

        return $claimed;
    }

    /**
     * The level's courses, grouped by the subcategory they sit in.
     *
     * Most courses are called "MATEMÁTICA": the subcategory path below the
     * level ("PRE - CAYETANO", "UNIVERSIDAD DEL PACÍFICO · CICLO REGULAR") is
     * what tells them apart, so it is the group heading.
     *
     * @param \core_course_category $level
     * @param \core_course_category[] $categories Every visible category, by id.
     * @return array[] Groups: label, courses.
     */
    private static function groups(\core_course_category $level, array $categories): array {
        $courses = $level->get_courses(['recursive' => true, 'sort' => ['sortorder' => 1]]);

        $groups = [];
        foreach ($courses as $course) {
            if (!isset($groups[$course->category])) {
                $groups[$course->category] = [
                    'label' => self::path_below($level, $course->category, $categories),
                    'courses' => [],
                ];
            }
            $groups[$course->category]['courses'][] = ['name' => $course->get_formatted_name()];
        }

        return array_values($groups);
    }

    /**
     * Category names from just below the level down to $categoryid.
     *
     * @param \core_course_category $level
     * @param int $categoryid
     * @param \core_course_category[] $categories
     * @return string Empty when the course sits in the level itself.
     */
    private static function path_below(\core_course_category $level, int $categoryid, array $categories): string {
        $ids = array_map('intval', explode('/', trim($categories[$categoryid]->path ?? '', '/')));
        $below = array_slice($ids, array_search((int) $level->id, $ids, true) + 1);

        $names = [];
        foreach ($below as $id) {
            if (isset($categories[$id])) {
                $names[] = $categories[$id]->get_formatted_name();
            }
        }

        return implode(' · ', $names);
    }

    /**
     * Upper case, no accents: "Pre universitario", "PRE UNIVERSITARIO" and
     * "Preuniversitário" all match the same pattern.
     *
     * @param string $name
     * @return string
     */
    private static function normalise(string $name): string {
        return \core_text::strtoupper(\core_text::specialtoascii(strip_tags($name)));
    }
}
