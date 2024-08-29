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

function seal_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('seal', $moduleinstance);
}


function seal_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('seal', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('seal', array('id' => $id));

    return true;
}


function fetch_nonce_from_api($userAddress) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php'); // Ensure the core curl class is included

    $ch = curl_init();
    $url = 'http://192.46.223.247:4000/auth/getNonce/' . urlencode($userAddress);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        $errorMessage = 'Failed to fetch nonce from external API: ' . curl_error($ch);
        error_log($errorMessage);
        curl_close($ch);
        throw new moodle_exception($errorMessage);
    }
    curl_close($ch);
    error_log('DATA: ' . $response);
    if (!$response) {
        $errorMessage = 'Invalid response from external API';
        error_log($errorMessage);
        throw new moodle_exception($errorMessage);
    }
    return $response;
}

function send_data_to_external_api($nonce, $userAddress, $fullMessage, $signature) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');

    $data = array('nonce' => $nonce, 'address' => $userAddress, 'message' => $fullMessage, 'signature' => $signature);
    error_log("DATA: " . json_encode($data));

    $url = 'http://192.46.223.247:4000/auth/login';
    $curl = new curl();
    $curl->setHeader('Content-Type: application/json');
    $response = $curl->post($url, json_encode($data));

    error_log("API Response: " . $response);

    if ($curl->errno != CURLE_OK) {
        throw new moodle_exception('error:api_request_failed', 'mod_seal', '', $curl->error);
    }

    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new moodle_exception('error:invalid_json_response', 'mod_seal');
    }

    return $decoded_response;
}

function create_attestation($data, $schemaId) {
    $attestationInfo = json_encode([
        'schemaId' => $schemaId,
        'data' => $data,
        'indexingValue' => "",
        'recipients' => ['0x92388d12744B418eFac8370B266D31fd9C4c5F0e'],
        'validUntil' => 0
    ]);

    // This is where you'd normally call delegateSignAttestation
    // For now, we'll simulate its response
    $info = [
        'attestation' => json_decode($attestationInfo, true),
        'delegationSignature' => 'simulated_signature'
    ];

    return $info;
}

function send_attestation($attestationDto) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');

    $url = 'http://192.46.223.247:4000/attestations/attestOrganizationInDelegationMode';
    $jwt_token = 'your_jwt_token_here'; // Replace with actual JWT token

    $curl = new curl();
    $curl->setHeader([
        'Accept: application/json',
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
    ]);

    $response = $curl->post($url, json_encode($attestationDto));

    if ($curl->errno != CURLE_OK) {
        throw new Exception('Error sending attestation: ' . $curl->error);
    }

    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from attestation API');
    }

    return $decoded_response;
}