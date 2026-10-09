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
 * Writes the site's pretty URLs, and the Moodle router rule, into public/.htaccess
 * ($CFG->dirroot is public/ since 5.1).
 *
 * Reads local/richimath/routes.php and replaces the generated block, leaving
 * anything else in the file alone. Idempotent: run it after every change to
 * the map.
 *
 * Usage: php scripts/build-routes.php [--print]
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../config.php');

$rules = \local_richimath\routes::apache_rules();

if (in_array('--print', $argv ?? [], true)) {
    echo $rules;
    exit(0);
}

$htaccess = $CFG->dirroot . '/.htaccess';
$current = file_exists($htaccess) ? file_get_contents($htaccess) : '';

$start = \local_richimath\routes::BLOCK_START;
$end = \local_richimath\routes::BLOCK_END;
$pattern = '/' . preg_quote($start, '/') . '.*?' . preg_quote($end, '/') . '\n?/s';

if (preg_match($pattern, $current)) {
    $updated = preg_replace($pattern, $rules, $current);
} else {
    $updated = ($current === '' ? '' : rtrim($current) . "\n\n") . $rules;
}

if (file_put_contents($htaccess, $updated) === false) {
    // Said "written" even when the web server user could not write public/.
    fwrite(STDERR, "{$htaccess}: no se pudo escribir (¿permisos? prueba --print > public/.htaccess)\n");
    exit(1);
}

$count = count(\local_richimath\routes::all());
echo "{$htaccess}: {$count} rutas escritas\n";
