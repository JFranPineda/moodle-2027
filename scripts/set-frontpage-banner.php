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
 * Install or replace the site home banner: one "Text and media area"
 * (mod_label) holding a single image, in section 1 of the front page.
 *
 * Usage, as www-data from the Moodle root:
 *   php scripts/set-frontpage-banner.php assets/frontpage-banner.jpg [--alt="Alt text"]
 *
 * Idempotent: the banner is found back by its course module idnumber. The
 * first run adopts a lone label already sitting in that section, so a banner
 * added by hand is replaced, not duplicated. Later swaps can be done here or
 * by editing the label in the browser — both end in the same place.
 *
 * @package   theme_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

const BANNER_IDNUMBER = 'richimath-frontpage-banner';

[$options, $args] = cli_get_params(['alt' => '', 'help' => false], ['h' => 'help']);
$path = $args[0] ?? '';
if ($options['help'] || $path === '') {
    cli_writeln('Usage: php scripts/set-frontpage-banner.php <image> [--alt="Alt text"]');
    exit($options['help'] ? 0 : 1);
}
$size = is_readable($path) ? getimagesize($path) : false;
if (!$size) {
    cli_error("Not a readable image: $path");
}
[$width, $height] = $size;

$filename = clean_filename(basename($path));
$site = get_site();
$alt = $options['alt'] !== '' ? $options['alt'] : format_string($site->fullname);
$html = html_writer::tag('p', html_writer::empty_tag('img', [
    'src' => '@@PLUGINFILE@@/' . rawurlencode($filename),
    'class' => 'img-fluid',
    'alt' => $alt,
    'width' => $width,
    'height' => $height,
]));

// The draft area and the module events need a real user.
\core\session\manager::set_user(get_admin());

$cm = null;
$labels = get_fast_modinfo($site)->get_instances_of('label');
foreach ($labels as $candidate) {
    if ($candidate->idnumber === BANNER_IDNUMBER) {
        $cm = $candidate;
        break;
    }
}
if (!$cm) {
    $insection = array_filter($labels, fn($c) => $c->sectionnum == 1 && !$c->deletioninprogress);
    if (count($insection) === 1) {
        $cm = reset($insection);
    }
}

$fs = get_file_storage();
if ($cm) {
    $context = context_module::instance($cm->id);
    $fs->delete_area_files($context->id, 'mod_label', 'intro');
    $fs->create_file_from_pathname([
        'contextid' => $context->id,
        'component' => 'mod_label',
        'filearea' => 'intro',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $filename,
    ], $path);
    $DB->update_record('label', (object) [
        'id' => $cm->instance,
        'name' => $alt,
        'intro' => $html,
        'introformat' => FORMAT_HTML,
        'timemodified' => time(),
    ]);
    $DB->set_field('course_modules', 'idnumber', BANNER_IDNUMBER, ['id' => $cm->id]);
    rebuild_course_cache($site->id, true);
    cli_writeln("Banner replaced in course module {$cm->id}: $filename ({$width}x{$height}).");
    exit(0);
}

// No label yet: the image goes through a draft area and add_moduleinfo()
// files it under mod_label/intro together with the new course module.
$draftid = file_get_unused_draft_itemid();
$fs->create_file_from_pathname([
    'contextid' => context_user::instance($USER->id)->id,
    'component' => 'user',
    'filearea' => 'draft',
    'itemid' => $draftid,
    'filepath' => '/',
    'filename' => $filename,
], $path);
$moduleinfo = (object) [
    'modulename' => 'label',
    'module' => $DB->get_field('modules', 'id', ['name' => 'label'], MUST_EXIST),
    'course' => $site->id,
    'section' => 1,
    'visible' => 1,
    'visibleoncoursepage' => 1,
    'cmidnumber' => BANNER_IDNUMBER,
    'introeditor' => ['text' => $html, 'format' => FORMAT_HTML, 'itemid' => $draftid],
];
$moduleinfo = add_moduleinfo($moduleinfo, $site);
$DB->set_field('label', 'name', $alt, ['id' => $moduleinfo->instance]);
rebuild_course_cache($site->id, true);
cli_writeln("Banner created as course module {$moduleinfo->coursemodule}: $filename ({$width}x{$height}).");
