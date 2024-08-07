<?php
require_once('../../../config.php');
require_login();

$response = new stdClass();
global $USER, $DB, $COURSE;

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

    $action = isset($data['action']) ? $data['action'] : '';
    $signature = isset($data['signature']) ? $data['signature'] : '';
    $userAddress = isset($data['userAddress']) ? $data['userAddress'] : '';
    $singMessage = isset($data['singMessage']) ? $data['singMessage'] : '';
    

    if (empty($action) || empty($signature) || empty($userAddress) || empty($singMessage)) {
        throw new Exception('Missing required data');
    }

    if ($action === 'student') {

        $moduleinstance->timecreated = time();
        $moduleinstance->iduser = $USER->id;
        $moduleinstance->course = $COURSE->id;
        $moduleinstance->wallethash = $userAddress;
        $moduleinstance->signaturehash = $signature;

        $id = $DB->insert_record('seal_user', $moduleinstance);

        //pensar en un foreach para insertar todos

        $response->success = true;
        $response->id = $id;        

    } elseif ($action === 'reset') {
        unset($_SESSION['signature']);
        unset($_SESSION['matching_record']);
        $response->success = true;

    } else {
        throw new Exception('Invalid action');
    }

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
