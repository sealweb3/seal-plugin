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
require_once($CFG->libdir . '/completionlib.php');


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

$PAGE->set_url('/mod/seal/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

if(has_capability('moodle/site:config',$modulecontext)){
    $PAGE->requires->js(new moodle_url('/mod/seal/js/web3manager.js'));
    $users = $DB->get_records('seal_user', array('course' => $COURSE->id));

    // Convertir el objeto de usuarios en un array.
    $usersArray = [];
    foreach ($users as $user) {
        $nameuser = $DB->get_record('user', array('id' => $user->iduser));
        // Aquí puedes añadir lógica para formatear los datos del usuario si es necesario.
        $usersArray[] = [
            'id' => $user->id,
            'name' => $nameuser->firstname.' '.$nameuser->lastname,
            'wallet' => $user->wallethash,
            'ipfs' => $user->ipfs,
        ];
    }
    $templatecontext = (object)[
        'table' => $usersArray,
        'var1' => $COURSE->id,
    ];
    echo $OUTPUT->render_from_template('mod_seal/viewmanager', $templatecontext);
}
else if(has_capability('moodle/course:manageactivities', $modulecontext)){
    $context = context_course::instance($COURSE->id);
    $enrolledusers = get_enrolled_users($context);

    $nonteachers = array_filter($enrolledusers, function($user) use ($context) {
        return !has_capability('moodle/course:manageactivities', $context, $user->id);
    });

    $users = array();
    foreach ($nonteachers as $user) {
        $walletid = 'wallet_' . $user->id;
        $waluser = $DB->get_record('seal_user', array('iduser' => $user->id));
        $wallet = isset($waluser->wallethash) ? $waluser->wallethash : get_string('notavailable', 'mod_seal');

        $completion = new completion_info($course);
        $iscomplete = $completion->is_course_complete($user->id);

        // Calculate the course completion percentage
        $criteria = $completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
        $completedcriteria = 0;

        foreach ($criteria as $criterion) {
            if ($criterion->is_complete($user->id)) {
                $completedcriteria++;
            }
        }
        $totalcriteria = count($criteria);
        $percentage = $totalcriteria ? ($completedcriteria / $totalcriteria) * 100 : 0;

        $users[] = array(
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'wallet' => $wallet,
            'percentage' => round($percentage) . "%",
            'aprobe' => $iscomplete ? get_string('yes') : get_string('no'),
            'id' => $user->id,
        );
    }
    $templatecontext = (object)[
        'table' => $users
    ];
    echo $OUTPUT->render_from_template('mod_seal/viewteacher', $templatecontext);

}
else
{
    $userview = $DB->get_record('seal_user', array('iduser' => $USER->id));
    if(!$userview || empty((array)$userview)){
        $PAGE->requires->js(new moodle_url('/mod/seal/js/web3student.js'));
        $otra = "no wallet";
        $templatecontext = (object)[
            'var1' => $otra,
        ];
        echo $OUTPUT->render_from_template('mod_seal/viewstudentone', $templatecontext);
    }
    else if(is_null($userview->url)){
        $PAGE->requires->js(new moodle_url('/mod/seal/js/web3student.js'));
        $templatecontext = (object)[
            'wallet' => $userview->wallethash,
            'signature' => $userview->signaturehash,
        ];
        echo $OUTPUT->render_from_template('mod_seal/viewstudenttwo', $templatecontext);
    }
    else
    {
        $templatecontext = (object)[
            'wallet' => $userview->wallethash,
            'signature' => $userview->signaturehash,
            'ipfs' => $userview->ipfs,
            'url' => $userview->url,
        ];
        echo $OUTPUT->render_from_template('mod_seal/viewstudentend', $templatecontext);
    }
}

echo $OUTPUT->footer();

