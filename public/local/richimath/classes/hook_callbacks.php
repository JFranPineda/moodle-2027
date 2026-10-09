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
 * Hook callbacks of the plugin (db/hooks.php).
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Keeps the clean login address out of the "where was I going" memory.
     *
     * After a successful login Moodle returns the user to $SESSION->wantsurl,
     * and on the login POST it fills that from the referer. It refuses to store
     * the login page itself — but it only knows its own spellings of it
     * (/login/ and /login/index.php), and ours is a third one. Without this,
     * logging in from /login sent the user straight back to /login, where an
     * open session is answered with "you are already logged in, log out first".
     *
     * Was the after_config callback in lib.php until 4.3; 5.3 still calls that
     * one but flags it as deprecated.
     *
     * @param \core\hook\after_config $hook
     */
    public static function after_config(\core\hook\after_config $hook): void {
        global $SESSION;

        if (empty($SESSION->wantsurl)) {
            return;
        }

        $wanted = rtrim(strtok($SESSION->wantsurl, '?'), '/');
        if ($wanted === rtrim(routes::url('login')->out(false), '/')) {
            unset($SESSION->wantsurl);
        }
    }
}
