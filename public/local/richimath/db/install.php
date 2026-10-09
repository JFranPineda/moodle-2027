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

/**
 * Fresh-install steps. The upgrade path does the same for sites that already
 * had the plugin; a brand new site only runs install.xml and this file.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Seeds the plans and lets core honour the theme they assign.
 */
function xmldb_local_richimath_install(): void {
    (new \local_richimath\plans\plan_service())->install_defaults();

    // Without this core ignores user.theme and the plans decide nothing.
    set_config('allowuserthemes', 1);

    // Upgrade steps 2026100300/01 and 2026100400 never run on a new site, so
    // the WhatsApp fields and the privacy lockdown would be missing there.
    \local_richimath\whatsapp_fields::install();
    \local_richimath\privacy_lockdown::apply();
}
