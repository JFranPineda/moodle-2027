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
 * Quiz strings new in Moodle 5.x that the Spanish language pack did not
 * translate yet when we moved to 5.3, and that students or teachers do see:
 * left alone they show in English. Imported with admin.php (see there).
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitydate:due'] = 'Fecha de entrega:';
$string['attemptsubmitted'] = 'Intento enviado.';
$string['duedate_help'] = 'Es la fecha de entrega del cuestionario. Después de ella se seguirán permitiendo intentos.';
$string['duedateafterclose'] = 'La fecha de entrega debe ser anterior a la fecha de cierre.';
$string['duedatebeforeopen'] = 'La fecha de entrega debe ser posterior a la fecha de apertura.';
$string['noduedate'] = 'Sin fecha de entrega';
$string['privacy:metadata:quiz_overrides:duedate'] = 'La nueva fecha de entrega del cuestionario.';
$string['quizclose_help'] = 'Después de la fecha de cierre, los estudiantes no podrán comenzar nuevos intentos.';
$string['quizduein'] = 'El cuestionario vence en {$a}';
$string['quizeventduedate'] = 'Vence {$a}';
$string['quizfinishedearly'] = 'Cuestionario terminado {$a} antes de la fecha';
$string['quizfinishedlate'] = 'Cuestionario terminado {$a} después de la fecha';
$string['quizoverdue'] = 'El cuestionario está vencido';
