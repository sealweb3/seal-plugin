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
 * Library of interface functions and constants.
 *
 * @package     mod_seal
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
require_once(__DIR__.'/../../config.php');

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['userid'])) {
    $userid = required_param('userid', PARAM_INT);
    delete_user_wallet($userid);
}

function seal_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_seal into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_seal_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function seal_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance->enabled = 1;

    $id = $DB->insert_record('seal', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_seal in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_seal_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function seal_update_instance($moduleinstance, $mform = null) {
    global $DB,$COURSE;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    $context = context_course::instance($COURSE->id);
    $enrolledusers = get_enrolled_users($context);
    $nonteachers = array_filter($enrolledusers, function($user) use ($context) {
        return !has_capability('moodle/course:manageactivities', $context, $user->id);
    });
    foreach ($nonteachers as $user) {
        $walletid = 'wallet_' . $user->id;
        if (isset($moduleinstance->{$walletid})) {
            $walletdata = $moduleinstance->{$walletid};

            // Insert or update the wallet information in your `seal_user` table
            $walletrecord = $DB->get_record('seal_user', array('iduser' => $user->id));
            if ($walletrecord) {
                $walletrecord->wallet = $walletdata;
                $DB->update_record('seal_user', $walletrecord);
            } else {
                $newrecord = new stdClass();
                $newrecord->iduser = $user->id;
                $newrecord->wallet = $walletdata;
                $DB->insert_record('seal_user', $newrecord);
            }
        }
    }

    return $DB->update_record('seal', $moduleinstance);
}

/**
 * Removes an instance of the mod_seal from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function seal_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('seal', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('seal', array('id' => $id));

    return true;
}

function delete_user_wallet($userid) {
    global $DB, $COURSE;

    // Validar que el usuario tenga permisos para eliminar.
    $context = context_course::instance($COURSE->id);
    require_capability('moodle/course:manageactivities', $context);
    
    $userview = $DB->get_record('seal_user', array('iduser' => $userid));
        if($userview && !empty((array)$userview)){
            // Eliminar el registro de la base de datos.
            $DB->delete_records('seal_user', array('id' => $userview->id));

        }

    // Redireccionar de vuelta a la página principal o de configuración.
    //var_dump($COURSE);
    //die;
    redirect(new moodle_url('/course/view.php', ['id' => $COURSE->id]), 'Wallet eliminado exitosamente');
}
