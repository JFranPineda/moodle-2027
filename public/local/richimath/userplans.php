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
 * Site administration > Plans > Users: one plan per user.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

use local_richimath\plans\plan_service;

const USERS_PER_PAGE = 50;

$service = new plan_service();

$planid = optional_param('planid', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$setuser = optional_param('setuser', 0, PARAM_INT);
$setplan = optional_param('setplan', 0, PARAM_INT);

admin_externalpage_setup('local_richimath_userplans');
$url = \local_richimath\routes::url('members', array_filter([
    'planid' => $planid,
    'search' => $search,
]));

if ($setuser && $setplan && confirm_sesskey()) {
    $service->assign_user($setuser, $setplan);
    redirect(new moodle_url($url, ['page' => $page]), get_string('userplansaved', 'local_richimath'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

$plans = $service->all_plans();
if (!$plans) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('noplansyet', 'local_richimath'), 'warning');
    echo $OUTPUT->footer();
    exit;
}
$planmenu = $service->plan_menu();
$default = $service->default_plan();

// Real accounts only: the guest and deleted users have no level.
$where = 'u.deleted = 0 AND u.id <> :guestid';
$params = ['guestid' => (int) $CFG->siteguest];
if ($search !== '') {
    $like = $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname', "' '", 'u.email'), ':search', false);
    $where .= " AND $like";
    $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
}
if ($planid) {
    $where .= ' AND up.planid = :planid';
    $params['planid'] = $planid;
}

// fullname() reads more than firstname and lastname (phonetics, middle and
// alternate names, whichever the site shows), so let core name the columns.
// The last argument asks for the leading comma, so the column list below
// stays readable.
$namefields = \core_user\fields::for_name()->get_sql('u', false, '', '', true);

$from = '{user} u LEFT JOIN {local_richimath_userplan} up ON up.userid = u.id';
$total = $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
$users = $DB->get_records_sql(
    "SELECT u.id, u.email, u.theme, up.planid {$namefields->selects}
       FROM $from
      WHERE $where
   ORDER BY u.lastname ASC, u.firstname ASC",
    $params + $namefields->params,
    $page * USERS_PER_PAGE,
    USERS_PER_PAGE
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('userplans', 'local_richimath'));
echo html_writer::tag('p', get_string('userplansintro', 'local_richimath', $default ? $default->name : ''));

// Filters: by plan and by name or email.
$filter = html_writer::start_tag('form', ['method' => 'get', 'action' => $url->out_omit_querystring(), 'class' => 'form-inline mb-3']);
$filter .= html_writer::select($planmenu, 'planid', $planid, ['' => get_string('allplans', 'local_richimath')],
    ['class' => 'custom-select mr-2']);
$filter .= html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'search',
    'value' => $search,
    'class' => 'form-control mr-2',
    'placeholder' => get_string('searchusers', 'local_richimath'),
]);
$filter .= html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-secondary', 'value' => get_string('filter')]);
$filter .= html_writer::end_tag('form');
echo $filter;

$table = new html_table();
$table->attributes['class'] = 'generaltable';
$table->head = [
    get_string('fullname'),
    get_string('email'),
    get_string('plan', 'local_richimath'),
    get_string('appearance', 'local_richimath'),
];
foreach ($users as $user) {
    $current = $user->planid ?: ($default ? $default->id : 0);
    $plan = $plans[$current] ?? null;

    // One select per row, submitted on change: assigning a level to a list of
    // students is a scanning job, and a form per row would double the clicks.
    $form = html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false), 'class' => 'm-0']);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'setuser', 'value' => $user->id]);
    $form .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'page', 'value' => $page]);
    $form .= html_writer::select($planmenu, 'setplan', $current, false, [
        'class' => 'custom-select',
        'onchange' => 'this.form.submit();',
    ]);
    $form .= html_writer::end_tag('form');

    $table->data[] = [
        html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user)),
        s($user->email),
        $form,
        $plan ? s($plan->appearance_name()) : '—',
    ];
}
echo html_writer::table($table);
echo $OUTPUT->paging_bar($total, $page, USERS_PER_PAGE, $url);
echo $OUTPUT->footer();
