<?php
defined('MOODLE_INTERNAL') || die();

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
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

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


/**
 * Fetch nonce from an external API.
 *
 * @param string $userAddress The user address.
 * @return string The nonce.
 * @throws moodle_exception If the API call fails or the response is invalid.
 */
function fetch_nonce_from_api($userAddress) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php'); // Ensure the core curl class is included

    $curl = new \curl(); // Use the correct class name
    $url = 'https://run.mocky.io/v3/6af96e2e-a7ca-4bfb-8ce7-25ceeaeb5ec9?userAddress=' . urlencode($userAddress);
    error_log('URL: ' . $url);
    $response = $curl->get($url);

    if ($response === false) {
        $errorMessage = 'Failed to fetch nonce from external API';
        error_log($errorMessage);
        throw new moodle_exception($errorMessage);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMessage = 'Invalid JSON response from external API';
        error_log($errorMessage);
        throw new moodle_exception($errorMessage);
    }

    if (!isset($data['nonce'])) {
        $errorMessage = 'Nonce not found in external API response';
        error_log($errorMessage);
        throw new moodle_exception($errorMessage);
    }

    return $data['nonce'];
}



function send_data_to_external_api($signature, $userAddress, $messageWithNonce) {
    // $url = 'https://run.mocky.io/v3/cd8d2524-e7db-4ad6-8a3f-dd765864048b'; // FALSE,false
    // $url = 'https://run.mocky.io/v3/5835b23c-bd0e-46fc-8800-2e450efd96cf'; // TRUE,false
    $url = 'https://run.mocky.io/v3/45129c60-a1b3-4a12-b3ab-cf4df423ce81'; // TRUE,true
// 
    $data = array('signature' => $signature, 'userAddress' => $userAddress, 'messageWithNonce' => $messageWithNonce);
    $options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // Log the response for debugging
    error_log("API Response: " . $result);

    if ($result === FALSE) {
        $errorMessage = 'Error sending data to external API';
        error_log($errorMessage);
        throw new Exception($errorMessage);
    }

    $response = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMessage = 'Invalid JSON response from external API';
        error_log($errorMessage);
        throw new Exception($errorMessage);
    }

    return $response;
}