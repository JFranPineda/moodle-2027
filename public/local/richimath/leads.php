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
 * Everyone who registered for an open session, and the CSV of the ones who
 * agreed to be written to.
 *
 * The export deliberately carries only the consented rows: the list on screen
 * is the attendance record, the file is the mailing list, and they are not the
 * same thing.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_richimath\lead;

global $PAGE, $OUTPUT, $DB;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$download = optional_param('download', 0, PARAM_BOOL);

if ($download) {
    require_sesskey();
    $rows = lead::all(true);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="richimath-leads-'
        . date('Ymd') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Nombres', 'Apellidos', 'Universidad', 'Correo',
                   'Sesiones', 'Primer registro', 'Último ingreso']);
    foreach ($rows as $r) {
        fputcsv($out, [$r->firstname, $r->lastname, $r->university, $r->email,
            $r->joincount, userdate($r->timecreated), userdate($r->timelastjoined)]);
    }
    fclose($out);
    exit;
}

$PAGE->set_url('/local/richimath/leads.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('leadstitle', 'local_richimath'));
$PAGE->set_heading(get_string('leadstitle', 'local_richimath'));

[$total, $contactable] = lead::counts();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('leadstitle', 'local_richimath'));
echo html_writer::tag('p', get_string('leadscount', 'local_richimath',
    (object) ['total' => $total, 'contactable' => $contactable]));

if ($total) {
    echo html_writer::link(
        new moodle_url('/local/richimath/leads.php',
            ['download' => 1, 'sesskey' => sesskey()]),
        get_string('leadsexport', 'local_richimath'),
        ['class' => 'btn btn-primary mb-3']);

    $table = new html_table();
    $table->head = [
        get_string('leadfirstname', 'local_richimath'),
        get_string('leadlastname', 'local_richimath'),
        get_string('leaduniversity', 'local_richimath'),
        get_string('leademail', 'local_richimath'),
        get_string('leadssessions', 'local_richimath'),
        get_string('leadsconsent', 'local_richimath'),
        get_string('leadsfirstseen', 'local_richimath'),
    ];
    $table->attributes['class'] = 'generaltable';

    foreach (lead::all() as $r) {
        $table->data[] = [
            s($r->firstname),
            s($r->lastname),
            s($r->university),
            s($r->email),
            $r->joincount,
            $r->marketingconsent
                ? html_writer::tag('span', get_string('yes'), ['class' => 'badge badge-success'])
                : html_writer::tag('span', get_string('no'), ['class' => 'badge badge-secondary']),
            userdate($r->timecreated, get_string('strftimedatefullshort')),
        ];
    }
    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('leadsempty', 'local_richimath'), 'info');
}

echo $OUTPUT->footer();
