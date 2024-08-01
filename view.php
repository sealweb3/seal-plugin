<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_seal.
 *
 * @package     mod_seal
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $USER, $DB;
// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('seal', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('seal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('seal', array('id' => $s), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('seal', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);


$modulecontext = context_module::instance($cm->id);

/*$event = \mod_seal\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('seal', $moduleinstance);
$event->trigger();
*/
$PAGE->set_url('/mod/seal/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

if(has_capability('mod/seal:manage', $modulecontext)){
    $otra = "tiene acceso";
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    echo $OUTPUT->render_from_template('mod_seal/viewsteacher', $templatecontext);

}
else
{
    $otra = "sin acceso";
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    echo $OUTPUT->render_from_template('mod_seal/viewstudent', $templatecontext);

}

echo $OUTPUT->footer();

/*global $DB, $USER;

// ID del curso que deseas verificar
$courseid = 2; // Reemplazar con el ID del curso real

// Obtén el contexto del curso
$context = context_course::instance($courseid);

// IDs de los roles que deseas verificar
$roleid_teacher = $DB->get_field('role', 'id', array('shortname' => 'teacher'));
$roleid_student = $DB->get_field('role', 'id', array('shortname' => 'student'));

// Verifica si el usuario es un teacher
$is_teacher = user_has_role_assignment($USER->id, $roleid_teacher, $context->id);

// Verifica si el usuario es un student
$is_student = user_has_role_assignment($USER->id, $roleid_student, $context->id);

if ($is_teacher) {
    echo "El usuario es un profesor.";
} elseif ($is_student) {
    echo "El usuario es un estudiante.";
} else {
    echo "El usuario no es ni profesor ni estudiante en este curso.";
}
*/