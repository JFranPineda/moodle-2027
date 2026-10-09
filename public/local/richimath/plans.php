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
 * Site administration > Plans: the plans and the appearance each one wears.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_richimath\plans\plan_service;

$service = new plan_service();

$action = optional_param('action', '', PARAM_ALPHA);
$planid = optional_param('id', 0, PARAM_INT);

admin_externalpage_setup('local_richimath_plans');
$url = \local_richimath\routes::url('plans');

if ($action === 'delete' && $planid && confirm_sesskey()) {
    $deleted = $service->delete_plan($planid);
    redirect(
        $url,
        get_string($deleted ? 'plandeleted' : 'plandeletedefault', 'local_richimath'),
        null,
        $deleted ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING
    );
}

if ($action === 'edit' || $action === 'add') {
    $form = new \local_richimath\form\plan_form($url->out(false) . '?action=' . $action . '&id=' . $planid);
    if ($form->is_cancelled()) {
        redirect($url);
    }
    if ($data = $form->get_data()) {
        $service->save_plan($data);
        redirect($url, get_string('plansaved', 'local_richimath'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($planid && ($existing = $DB->get_record('local_richimath_plan', ['id' => $planid]))) {
        $form->set_data($existing);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($action === 'add' ? 'planadd' : 'planedit', 'local_richimath'));
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

$rows = [];
foreach ($service->all_plans() as $item) {
    $actions = html_writer::link(
        new moodle_url($url, ['action' => 'edit', 'id' => $item->id]),
        get_string('edit'),
        ['class' => 'btn btn-sm btn-secondary']
    );
    if (!$item->isdefault) {
        // The fallback plan cannot go: its users would point nowhere.
        $actions .= ' ' . html_writer::link(
            new moodle_url($url, ['action' => 'delete', 'id' => $item->id, 'sesskey' => sesskey()]),
            get_string('delete'),
            [
                'class' => 'btn btn-sm btn-outline-danger',
                'data-richimath-confirm' => get_string('plandeleteconfirm', 'local_richimath', $item->name),
            ]
        );
    }

    $rows[] = [
        s($item->name) . ($item->isdefault
            ? ' ' . html_writer::span(get_string('plandefaultbadge', 'local_richimath'), 'badge badge-primary')
            : ''),
        html_writer::tag('code', s($item->shortname)),
        s($item->appearance_name()) .
            html_writer::div(s(\local_richimath\plans\appearance::design($item->appearance)), 'small text-muted'),
        html_writer::link(
            new moodle_url('/local/richimath/userplans.php', ['planid' => $item->id]),
            $service->user_count($item->id)
        ),
        $actions,
    ];
}

$PAGE->requires->js_amd_inline("
document.addEventListener('click', function(e) {
    var link = e.target.closest('[data-richimath-confirm]');
    if (link && !window.confirm(link.dataset.richimathConfirm)) {
        e.preventDefault();
    }
});");

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('plans', 'local_richimath'));
echo html_writer::tag('p', get_string('plansintro', 'local_richimath'));

$table = new html_table();
$table->attributes['class'] = 'generaltable';
$table->head = [
    get_string('planname', 'local_richimath'),
    get_string('planshortname', 'local_richimath'),
    get_string('appearance', 'local_richimath'),
    get_string('planusers', 'local_richimath'),
    '',
];
$table->data = $rows;
echo html_writer::table($table);

echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'add']), get_string('planadd', 'local_richimath'), 'get');
echo $OUTPUT->footer();
