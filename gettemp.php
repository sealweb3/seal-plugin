<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/seal/lib.php'); 
require_login();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');  // Permite solicitudes desde cualquier dominio (útil para CORS)
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');  // Permite los métodos POST, GET, y OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization');  // Permite los encabezados de Content-Type y Authorization

set_config('bantest', 0, 'mod_seal');  
set_config('program', '0x25b', 'mod_seal');  
try {
    $program = json_decode(get_program(), true);  // Convert JSON string to array
    $var = $program[0]['attestation']['data']['name']; 
    $program_option = array(
        'new' => 'new'
    );
    foreach ($program as $data2) {
        if (isset($data2['attestation']['id']) && isset($data2['attestation']['data']['name'])) {
            $program_option[$data2['attestation']['id']] = $data2['attestation']['data']['name'];
        }
    }
    echo json_encode(['program' => $program_option]);
} catch (Exception $e) {
    error_log("Error fetching nonce: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;