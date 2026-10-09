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
 * Admin pages: the site-level invitation page under Users > Accounts, and
 * the plugin settings under Local plugins.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('accounts', new admin_externalpage(
    'local_richimath_leads',
    get_string('leadstitle', 'local_richimath'),
    new moodle_url('/local/richimath/leads.php'),
    'moodle/site:config'
));

$ADMIN->add('accounts', new admin_externalpage(
    'local_richimath_invite',
    get_string('invitations', 'local_richimath'),
    new moodle_url('/local/richimath/invite.php'),
    'local/richimath:invite'
));

$ADMIN->add('root', new admin_category('local_richimath_plans_cat', get_string('plans', 'local_richimath')), 'users');
$ADMIN->add('local_richimath_plans_cat', new admin_externalpage(
    'local_richimath_plans',
    get_string('plansmanage', 'local_richimath'),
    new moodle_url('/local/richimath/plans.php'),
    'moodle/site:config'
));
$ADMIN->add('local_richimath_plans_cat', new admin_externalpage(
    'local_richimath_userplans',
    get_string('userplans', 'local_richimath'),
    new moodle_url('/local/richimath/userplans.php'),
    'moodle/site:config'
));

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_richimath', get_string('pluginname', 'local_richimath'));
    $ADMIN->add('localplugins', $settings);

    // Off by default on purpose: the clean addresses only work once Apache
    // reads the .htaccess (mod_rewrite + AllowOverride FileInfo Indexes).
    // With this off, every link this code builds is the plain Moodle URL, so
    // a site whose web server is not ready keeps working exactly as before.
    $settings->add(new admin_setting_configcheckbox(
        'local_richimath/prettyurls',
        get_string('prettyurls', 'local_richimath'),
        get_string('prettyurls_desc', 'local_richimath'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_richimath/hidehiddencategories',
        get_string('hidehiddencategories', 'local_richimath'),
        get_string('hidehiddencategories_desc', 'local_richimath'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_richimath/expirydays',
        get_string('expirydays', 'local_richimath'),
        get_string('expirydays_desc', 'local_richimath'),
        7,
        PARAM_INT
    ));
}
