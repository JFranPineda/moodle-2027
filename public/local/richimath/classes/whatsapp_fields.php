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
 * Creates the native fields the WhatsApp button reads.
 *
 * Two on the user profile (the teacher's number and their own switch) and one
 * on the course (the per-course override). Idempotent: an administrator who
 * renames the labels keeps them, and running the upgrade twice creates
 * nothing twice.
 *
 * @package   local_richimath
 * @copyright 2026 Richi Math
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class whatsapp_fields {

    /**
     * Create the profile fields and the course field if they are missing.
     */
    public static function install(): void {
        self::profile_fields();
        self::course_field();
    }

    /**
     * The teacher's number and their global switch, on the user profile.
     */
    private static function profile_fields(): void {
        global $DB;

        $categoryid = $DB->get_field('user_info_category', 'id',
            ['name' => get_string('whatsappcategory', 'local_richimath')]);
        if (!$categoryid) {
            $sortorder = (int) $DB->get_field_sql(
                'SELECT COALESCE(MAX(sortorder), 0) FROM {user_info_category}') + 1;
            $categoryid = $DB->insert_record('user_info_category', (object) [
                'name' => get_string('whatsappcategory', 'local_richimath'),
                'sortorder' => $sortorder,
            ]);
        }

        $fields = [
            whatsapp::FIELD_NUMBER => [
                'datatype' => 'text',
                'name' => get_string('whatsappnumber', 'local_richimath'),
                'description' => get_string('whatsappnumber_help', 'local_richimath'),
                'param1' => 30, 'param2' => 30, 'param3' => 0,
                'defaultdata' => '',
            ],
            whatsapp::FIELD_ENABLED => [
                'datatype' => 'checkbox',
                'name' => get_string('whatsappenabled', 'local_richimath'),
                'description' => get_string('whatsappenabled_help', 'local_richimath'),
                'param1' => '', 'param2' => '', 'param3' => '',
                'defaultdata' => 0,
            ],
        ];

        $sortorder = 1;
        foreach ($fields as $shortname => $field) {
            if ($DB->record_exists('user_info_field', ['shortname' => $shortname])) {
                continue;
            }
            $DB->insert_record('user_info_field', (object) array_merge([
                'shortname' => $shortname,
                'categoryid' => $categoryid,
                'descriptionformat' => FORMAT_HTML,
                'sortorder' => $sortorder++,
                'required' => 0,
                'locked' => 0,
                // Visible to everyone: a student has to be able to see that
                // their teacher is reachable.
                'visible' => 2,
                'forceunique' => 0,
                'signup' => 0,
                'defaultdataformat' => FORMAT_MOODLE,
            ], $field));
        }
    }

    /**
     * The per-course override, as a three-way course custom field.
     *
     * Three values and not a checkbox: "inherit" has to exist, or a teacher
     * who keeps the button off everywhere could never turn it on for one
     * course, and one who keeps it on could never silence a single course.
     */
    private static function course_field(): void {
        $handler = \core_course\customfield\course_handler::create();

        // `configdata` has to reach api::save_field_configuration() as a real
        // ARRAY. Flattened form keys like 'configdata[options]' are silently
        // dropped — the field is created, looks fine in the UI, and every
        // value reads back as NULL because it has no options.
        $configdata = [
            'required' => 0,
            'uniquevalues' => 0,
            'options' => get_string('whatsappcourseoptions', 'local_richimath'),
            'defaultvalue' => '',
            'locked' => 0,
            'visibility' => 2,
        ];

        foreach ($handler->get_categories_with_fields() as $category) {
            foreach ($category->get_fields() as $field) {
                if ($field->get('shortname') !== whatsapp::FIELD_COURSE) {
                    continue;
                }
                // Repair a field created before the array fix.
                if (!$field->get_configdata_property('options')) {
                    $handler->save_field_configuration($field,
                        (object) ['configdata' => $configdata]);
                }
                return;
            }
        }

        $categoryid = $handler->create_category(
            get_string('whatsappcategory', 'local_richimath'));
        $category = \core_customfield\category_controller::create($categoryid);

        $field = \core_customfield\field_controller::create(0,
            (object) ['type' => 'select'], $category);
        $handler->save_field_configuration($field, (object) [
            'name' => get_string('whatsappcourse', 'local_richimath'),
            'shortname' => whatsapp::FIELD_COURSE,
            'description' => get_string('whatsappcourse_help', 'local_richimath'),
            'descriptionformat' => FORMAT_HTML,
            'type' => 'select',
            'configdata' => $configdata,
        ]);
    }
}
