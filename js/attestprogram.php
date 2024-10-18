<?php
require_once('../../../config.php');
require_login();

$response = new stdClass();

try {
    // Capturar datos de la solicitud
    $rawData = file_get_contents("php://input");
    if ($rawData === false) {
        throw new Exception('Error reading input data');
    }

    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Depuración de los datos recibidos
    error_log('Received data: ' . print_r($data, true));

    $attestID = isset($data['attestID']) ? $data['attestID'] : null;

    if ($attestID === null) {
        throw new Exception('Missing authorization data');
    }

    set_config('bantest', 0, 'mod_seal');  
    set_config('program', $attestID['attestation']['id'], 'mod_seal');   
     
    $response->success = true;
    $response->message = 'Data processed successfully';

} catch (Exception $e) {
    $response->success = false;
    $response->error = $e->getMessage();
    error_log('Error in web3.php: ' . $e->getMessage());
    http_response_code(500);
}

// Asegurar que no haya salida previa
if (ob_get_contents()) {
    ob_clean();
}
header('Content-Type: application/json');
echo json_encode($response);
exit;

