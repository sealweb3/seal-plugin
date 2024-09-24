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

    if (!$id) {
        throw new moodle_exception('Unable to insert seal instance');
    }

    $moduleinstance->id = $id; 
    //insert images batch and certificate

    // Obtener el contexto del módulo
    $context = context_module::instance($moduleinstance->coursemodule);

    // Mover el archivo batch desde el área de borradores al área final
    $draftitemid = file_get_submitted_draft_itemid('batchfile');
    file_save_draft_area_files(
        $draftitemid,         // El ID del área de borradores
        $context->id,         // El contexto del módulo
        'mod_seal',           // El componente
        'batch',              // El área de archivo destino
        $moduleinstance->id,  // El ID del elemento (id del registro)
        array('subdirs' => 0, 'maxfiles' => 1) // Opciones de guardado
    );

    // Actualizar el registro del módulo en la base de datos con el nombre del archivo
    if ($draftitemid) {
        $moduleinstance->batch = $mform->get_new_filename('batchfile');
    }

    $draftitem2id = file_get_submitted_draft_itemid('imagefile');
    file_save_draft_area_files(
        $draftitem2id,         // El ID del área de borradores
        $context->id,         // El contexto del módulo
        'mod_seal',           // El componente
        'image',              // El área de archivo destino
        $moduleinstance->id,  // El ID del elemento (id del registro)
        array('subdirs' => 0, 'maxfiles' => 1) // Opciones de guardado
    );

    // Actualizar el registro del módulo en la base de datos con el nombre del archivo
    if ($draftitem2id) {
        $moduleinstance->image = $mform->get_new_filename('imagefile');
    }
    $DB->update_record('seal', $moduleinstance);

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
    global $DB, $COURSE;

    // Actualizar la marca de tiempo de modificación
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Obtener el contexto del módulo
    $context = context_module::instance($moduleinstance->coursemodule);

    $enrolledusers = get_enrolled_users($context);
    $nonteachers = array_filter($enrolledusers, function($user) use ($context) {
        return !has_capability('moodle/course:manageactivities', $context, $user->id);
    });
    
    
    foreach ($nonteachers as $user) {
        $walletid = 'wallet_' . $user->id;
        if (isset($moduleinstance->{$walletid})) {
            $walletdata = $moduleinstance->{$walletid};
            
            $newrecord = new stdClass();
            $newrecord->iduser = $user->id;
            $newrecord->wallethash = $walletdata;
            $newrecord->course = $COURSE->id;

            if($walletdata!='')$DB->insert_record('seal_user', $newrecord);
        }
    }

    // Mover el archivo batch desde el área de borradores al área final
    /*
    $draftitemid = file_get_submitted_draft_itemid('batchfile');
    file_save_draft_area_files(
        $draftitemid,         // El ID del área de borradores
        $context->id,         // El contexto del módulo
        'mod_seal',           // El componente
        'batch',              // El área de archivo destino
        $moduleinstance->id,  // El ID del elemento (id del registro)
        array('subdirs' => 0, 'maxfiles' => 1) // Opciones de guardado
    );

    // Actualizar el registro del módulo en la base de datos con el nombre del archivo
    if ($draftitemid) {
        $moduleinstance->batch = $mform->get_new_filename('batchfile');
    }

    $draftitem2id = file_get_submitted_draft_itemid('imagefile');
    file_save_draft_area_files(
        $draftitem2id,         // El ID del área de borradores
        $context->id,         // El contexto del módulo
        'mod_seal',           // El componente
        'image',              // El área de archivo destino
        $moduleinstance->id,  // El ID del elemento (id del registro)
        array('subdirs' => 0, 'maxfiles' => 1) // Opciones de guardado
    );

    // Actualizar el registro del módulo en la base de datos con el nombre del archivo
    if ($draftitem2id) {
        $moduleinstance->image = $mform->get_new_filename('imagefile');
    }
    */
    // Guardar el registro actualizado en la base de datos
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


// function attest

function fetch_nonce_from_api($userAddress) {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php'); // Ensure the core curl class is included

    $ch = curl_init();
    $url = get_config('mod_seal', 'url').'/auth/getNonce/' . urlencode($userAddress);
    $api_key = get_config('mod_seal', 'api_key');
    

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $headers = [
        'ngrok-skip-browser-warning: true',
        'Authorization:'. $api_key  // Enviar el API key como parte de los headers
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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

    $url = get_config('mod_seal', 'url').'/auth/login';
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

    $url = get_config('mod_seal', 'url').'/attestations/attestOrganizationInDelegationMode';
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

function mod_seal_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);


    $itemid = array_shift($args);  // Item ID
    $filename = array_pop($args);  // Nombre del archivo
    $filepath = '/' . implode('/', $args) . '/';  // Ruta del archivo

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_seal', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
