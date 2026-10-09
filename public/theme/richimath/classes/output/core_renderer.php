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

namespace theme_richimath\output;

/**
 * Core renderer overrides.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Courses listed per landing tab before falling back to a "+N more"
     * line. Enough to show what a level covers without turning the shop
     * window into the course index (Universidad alone has 14).
     */
    private const OFFER_COURSE_LIMIT = 8;

    /** @var array|null Landing catalog, built once per request (the template reads it twice). */
    private $offercategories = null;

    /**
     * Course chips on the dashboard before the "view all" tail (T-03). A
     * fourth row of pills already wraps at 1440px, so more would push the
     * blocks below the fold — the tail chip covers the rest.
     */
    private const DASHBOARD_COURSE_LIMIT = 12;

    /** @var array|null|false Dashboard chips memo; false = not built yet (null is a valid answer). */
    private $dashboardchips = false;

    /**
     * Whether this page is the student catalog.
     *
     * The catalog used to be the front page for visitors without a session.
     * Since the institutional site took the root it lives at /students, and
     * the page itself asks for it — hence the body class, set in students.php.
     *
     * @return bool
     */
    public function show_offer(): bool {
        return strpos($this->page->bodyclasses, 'richimath-students') !== false;
    }

    /**
     * Site name for the landing hero. The heading Moodle prints on the front
     * page carries the full name and the landing hides it (section A2b), so
     * the hero has to take it over — the navbar brand only has the shortname.
     *
     * @return string
     */
    public function offer_sitename(): string {
        global $SITE;

        return format_string($SITE->fullname, true, ['context' => \context_course::instance(SITEID)]);
    }

    /**
     * The catalog behind the public landing: every top-level category that
     * has something to show, in sortorder, with the first courses found in
     * it or in any of its descendants.
     *
     * Visibility is core's, not ours. core_course_category::top()->get_children()
     * already drops the categories the visitor cannot see, and get_courses()
     * runs every row through can_view_course_info(): a hidden course needs
     * moodle/course:viewhiddencourses and any course needs
     * moodle/category:viewcourselist on its own category. Hiding a category
     * hides its courses too, so a whole hidden branch disappears from here
     * without a special case. Same path /course/index.php walks — an
     * anonymous visitor sees exactly what the course index would show them.
     *
     * Cost matters here — this is the page anonymous traffic lands on. The
     * category tree and the per-category course id lists live in the
     * 'coursecat' MUC cache, so a warm front page is one "courses IN (ids)"
     * query per non-empty category plus a single get_many() for the parent
     * names: 4 reads for today's three levels. Never a query per course.
     * The list is fetched whole (the cap is applied in PHP, see
     * offer_sample_courses) — it is one query either way, and sampling
     * needs to see every branch. Cold (right after a purge) each category
     * adds the one query that builds its id list.
     *
     * @return array Category rows for the landing template.
     */
    public function offer_categories(): array {
        if ($this->offercategories !== null) {
            return $this->offercategories;
        }

        $this->offercategories = [];
        $first = true;
        foreach (\core_course_category::top()->get_children() as $category) {
            $courses = $category->get_courses([
                'recursive' => true,
                'sort' => ['sortorder' => 1],
            ]);
            if (!$courses) {
                // An empty category is shop-window noise ("Categoría 1").
                continue;
            }

            // Resolve every subcategory name in one go: half the catalog
            // shares a fullname ("MATEMÁTICA" four times under PRE), and the
            // subcategory is the only thing that tells them apart.
            $parents = \core_course_category::get_many(
                array_values(array_unique(array_map(fn($course) => $course->category, $courses)))
            );

            $items = [];
            foreach ($this->offer_sample_courses($category, $courses, $parents) as $course) {
                $parent = $parents[$course->category] ?? false;
                $items[] = [
                    'id' => $course->id,
                    'fullname' => $course->get_formatted_name(),
                    'context' => ($parent && $course->category != $category->id) ? $parent->get_formatted_name() : '',
                ];
            }

            $total = $category->get_courses_count(['recursive' => true]);
            $this->offercategories[] = [
                'id' => $category->id,
                'name' => $category->get_formatted_name(),
                'courses' => $items,
                'more' => max(0, $total - count($items)),
                'first' => $first,
            ];
            $first = false;
        }

        return $this->offercategories;
    }

    /**
     * The three academic divisions of the institutional site.
     *
     * Real data with a designed skin: the visible top-level categories, in
     * order, dressed with the neon key its design system gives each division
     * (school green, pre-university cyan, university crimson) and the copy
     * that sells it. A site with more than three top-level categories shows
     * the first three — the design has three lanes, not a variable grid.
     *
     * @return array Division rows for the site template.
     */
    public function site_divisions(): array {
        $keys = ['school', 'preuniversity', 'university'];
        $divisions = [];

        foreach (array_slice($this->offer_categories(), 0, count($keys)) as $index => $category) {
            $key = $keys[$index];
            $divisions[] = [
                'key' => $key,
                'name' => $category['name'],
                'title' => get_string('division' . $key . 'title', 'theme_richimath'),
                'summary' => get_string('division' . $key . 'summary', 'theme_richimath'),
                'meta' => get_string('division' . $key . 'meta', 'theme_richimath'),
                'courses' => array_slice($category['courses'], 0, 4),
                'url' => (new \moodle_url('/course/index.php', ['categoryid' => $category['id']]))->out(false),
            ];
        }

        return $divisions;
    }

    /**
     * Account links of the institutional site's footer.
     *
     * The site no longer carries a login form of its own: the classroom key
     * in the bar opens Moodle's login page, which keeps the token, the error
     * messages and the alternatives in one place.
     *
     * @return array
     */
    public function site_login(): array {
        return [
            'forgoturl' => \local_richimath\routes::url('recover')->out(false),
        ];
    }

    /**
     * The login form, posting to the address the site actually serves.
     *
     * Core builds the form's action from $CFG->wwwroot . '/login/index.php'.
     * A failed login re-renders in place, so that action is what the browser
     * ends up showing: with clean URLs on it has to be /login, and with them
     * off it has to stay the plain script, or the post would land nowhere.
     *
     * @param \core_auth\output\login $form
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        // 5.3: export_for_template() already brings errorformatted, logourl and
        // sitename, which the 4.3 parent added here and this override had to
        // repeat. The only difference left is where the form posts.
        $context = $form->export_for_template($this);

        // The form posts to the address the site serves, so a failed login
        // re-renders there instead of on the .php.
        $context->loginurl = \local_richimath\routes::url('login')->out(false);

        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * Every clean address, keyed by name, for the templates of this theme.
     *
     * Templates cannot call PHP, so this is how they reach the URL map in
     * local/richimath/routes.php instead of writing paths by hand.
     *
     * @return array Route name => absolute URL.
     */
    public function routes(): array {
        $urls = [];
        foreach (array_keys(\local_richimath\routes::all()) as $name) {
            $urls[$name] = \local_richimath\routes::url($name)->out(false);
        }

        return $urls;
    }

    /**
     * At most OFFER_COURSE_LIMIT courses for one landing tab, taken one per
     * immediate subcategory per pass.
     *
     * A flat prefix by sortorder hid whole sub-brands: UNIVERSIDAD's first
     * eight courses all sit under UNIVERSIDAD DEL PACÍFICO, so a visitor from
     * Universidad de Lima or de Piura was told the academy does not cover
     * their university. Round robin guarantees every branch a chip.
     *
     * @param \core_course_category $category Top-level category behind the tab.
     * @param array $courses Its courses, recursive, in sortorder.
     * @param array $parents Course categories by id (from get_many).
     * @return array
     */
    private function offer_sample_courses(\core_course_category $category, array $courses, array $parents): array {
        if (count($courses) <= self::OFFER_COURSE_LIMIT) {
            return $courses;
        }

        $branches = [];
        foreach ($courses as $course) {
            $coursecat = ($parents[$course->category] ?? false) ?: null;
            $branches[$this->offer_branch_id($category, $coursecat)][] = $course;
        }

        $picked = [];
        while ($branches) {
            foreach (array_keys($branches) as $branch) {
                $picked[] = array_shift($branches[$branch]);
                if (!$branches[$branch]) {
                    unset($branches[$branch]);
                }
                if (count($picked) >= self::OFFER_COURSE_LIMIT) {
                    break 2;
                }
            }
        }

        return $picked;
    }

    /**
     * The child of $category the course hangs from, however deep it sits.
     * That is the level a prospect recognises ("Universidad de Piura"), not
     * the leaf the course happens to live in ("CICLO REGULAR").
     *
     * @param \core_course_category $category Top-level category behind the tab.
     * @param \core_course_category|null $coursecat The course's own category.
     * @return int
     */
    private function offer_branch_id(\core_course_category $category, ?\core_course_category $coursecat): int {
        if (!$coursecat) {
            return $category->id;
        }

        // get_parents() splits the path, so it hands back strings.
        $chain = array_map('intval', array_merge($coursecat->get_parents(), [$coursecat->id]));
        $at = array_search((int) $category->id, $chain, true);

        return ($at !== false && isset($chain[$at + 1])) ? $chain[$at + 1] : $category->id;
    }

    /**
     * Whether the current user should see the course catalog links.
     *
     * Students only browse their own enrolments ("My courses"); the full
     * catalog is a dead end for them (manual enrolments, no self-enrol).
     * Course managers and admins keep it.
     *
     * @return bool
     */
    public function show_catalog(): bool {
        return isloggedin() && !isguestuser()
            && has_capability('moodle/category:viewcourselist', \context_system::instance())
            && has_any_capability(
                ['moodle/category:manage', 'moodle/course:create', 'moodle/course:update'],
                \context_system::instance()
            );
    }

    /**
     * Quick-access chips for the top of the dashboard (T-03): one pill per
     * enrolled course, most recently accessed first, plus — for staff only,
     * same gate as the catalog links — one pill per top-level category with
     * visible courses. Null anywhere else, so the template renders nothing.
     *
     * Gate detail that matters: /my/ and /my/courses.php SHARE pagetype
     * 'my-index' (both bodies are #page-my-index), so the pagelayout is the
     * only safe discriminator — 'mydashboard' here vs 'mycourses' there.
     *
     * Course order is block_myoverview's own "Last accessed" sort
     * ('ul.timeaccess desc': COALESCE(0) puts never-opened courses last),
     * with sortorder as the tiebreaker. The list is fetched whole and capped
     * in PHP: enrol_get_my_courses applies its SQL limit BEFORE dropping the
     * hidden courses the user cannot see, so a SQL cap could under-fill the
     * row — and the count is needed for the tail chip anyway.
     *
     * Cost, measured with $DB->perf_get_reads() on the production mirror:
     * the course row is 1 query however many enrolments (single JOIN through
     * enrol/user_enrolments/user_lastaccess, contexts preloaded — 29 courses,
     * still 1 read; format_string adds 0). The staff category row rides the
     * 'coursecat' MUC like the landing: ~10 reads warm, 29 cold after a
     * purge. Students never pay it.
     *
     * @return array|null Chip rows for the template, null when the strip must not render.
     */
    public function dashboard_chips(): ?array {
        if ($this->dashboardchips !== false) {
            return $this->dashboardchips;
        }

        $this->dashboardchips = null;
        if ($this->page->pagelayout !== 'mydashboard' || !isloggedin() || isguestuser()) {
            return $this->dashboardchips;
        }

        $courses = enrol_get_my_courses(null, 'ul.timeaccess desc, c.sortorder asc');
        $total = count($courses);
        $chips = [];
        foreach (array_slice($courses, 0, self::DASHBOARD_COURSE_LIMIT) as $course) {
            $chips[] = [
                'name' => format_string($course->fullname, true, ['context' => \context_course::instance($course->id)]),
                'url' => (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            ];
        }

        $categories = [];
        if ($this->show_catalog()) {
            foreach (\core_course_category::top()->get_children() as $category) {
                if (!$category->get_courses_count(['recursive' => true])) {
                    // Same rule as the landing: an empty category ("Categoría 1") is noise.
                    continue;
                }
                $categories[] = [
                    'name' => $category->get_formatted_name(),
                    'url' => (new \moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false),
                ];
            }
        }

        if (!$chips && !$categories) {
            // Nothing to offer: no empty shell on the page.
            return $this->dashboardchips;
        }

        $this->dashboardchips = [
            'hascourses' => !empty($chips),
            'courses' => $chips,
            'morecoursesurl' => $total > self::DASHBOARD_COURSE_LIMIT
                ? (new \moodle_url('/my/courses.php'))->out(false) : null,
            'hascategories' => !empty($categories),
            'categories' => $categories,
        ];

        return $this->dashboardchips;
    }

    /**
     * Target of the floating "Consultas" button: the conversation with the
     * site admin. Null whenever the button must not be rendered at all —
     * logged out or guest, the login artwork page, or the messaging app
     * itself (where the button would only shadow the UI it links to).
     *
     * The admin id is read from the site, never hardcoded: it differs
     * between local and production.
     *
     * @return string|null
     */
    /**
     * The course teacher's WhatsApp, for the floating button.
     *
     * The whole decision — which teacher, whether they switched it on, whether
     * this course overrode it — lives in local_richimath. The theme only
     * paints what it is handed.
     *
     * @return array|null ['url' => string, 'label' => string]
     */
    public function teacher_whatsapp(): ?array {
        global $PAGE;

        if ($PAGE->pagelayout === 'login' || empty($PAGE->course->id)) {
            return null;
        }

        $found = \local_richimath\whatsapp::for_course((int) $PAGE->course->id);
        if (!$found) {
            return null;
        }

        return [
            'url' => $found['url'],
            'label' => get_string('whatsappwrite', 'local_richimath', $found['teacher']),
        ];
    }

    public function admin_message_url(): ?string {
        global $PAGE;

        if (!isloggedin() || isguestuser()) {
            return null;
        }

        if ($PAGE->pagelayout === 'login' || strpos($PAGE->pagetype, 'message-') === 0) {
            return null;
        }

        $admin = get_admin();
        if (!$admin) {
            return null;
        }

        return (new \moodle_url('/message/index.php', ['id' => $admin->id]))->out(false);
    }

    /**
     * Body attributes plus the compact-sidebar flag from the theme setting
     * and the landing flag section A2b hangs its cleanup on.
     *
     * The whole sidebar geometry (its own width, the page margin, the drawer
     * offsets) reads one CSS custom property that this class re-points, so
     * both variants share the same rules.
     *
     * The landing flag exists because core's own 'notloggedin' is not the
     * same set of visitors as show_offer(): a guest IS logged in, so keying
     * the CSS on it left every guest with the landing AND the core chrome it
     * replaces (two site headings on one page). One gate, decided here.
     *
     * @param array|string $additionalclasses Extra classes for the body tag.
     * @return string
     */
    /**
     * Every browser tab ends in the brand.
     *
     * Core appends the site SHORTNAME to each title, so tabs read
     * "… | AulaVirtual" — a database value that also feeds the mobile navbar
     * and is not the name the academy goes by. The brand string is the
     * theme's, travels by git, and replaces that trailing segment; the site
     * home, whose title IS the brand, gets it alone.
     *
     * @return string
     */
    public function page_title() {
        $brand = get_string('brandname', 'theme_richimath');
        if ($this->page->pagetype === 'site-index') {
            return $brand;
        }

        $parts = explode(\moodle_page::TITLE_SEPARATOR, parent::page_title());
        if (count($parts) > 1) {
            array_pop($parts);
        }
        $parts[] = $brand;

        return implode(\moodle_page::TITLE_SEPARATOR, $parts);
    }

    /**
     * @param array|string $additionalclasses Extra classes for the body tag.
     * @return string
     */
    public function body_attributes($additionalclasses = []): string {
        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        if (get_config('theme_richimath', 'sidebarstyle') === 'compact') {
            $additionalclasses[] = 'richimath-sidebar-compact';
        }

        if ($this->show_offer()) {
            $additionalclasses[] = 'richimath-has-landing';
        }

        if ($this->page->pagelayout === 'frontpage' && (!isloggedin() || isguestuser())) {
            $additionalclasses[] = 'richimath-site';
        }

        return parent::body_attributes($additionalclasses);
    }
}
