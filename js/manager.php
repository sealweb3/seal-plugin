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

    $atest = isset($data['atest']) ? $data['atest'] : '';
    $ids = isset($data['ids']) ? $data['ids'] : '';    
    $courseId = isset($data['courseNow']) ? $data['courseNow'] : '';    

    if (empty($atest) || empty($ids)) {
        throw new Exception('Missing required data');
    }

    foreach ($ids as $iduser) {
        $user = $DB->get_record('seal_user', array('id' => $iduser));
        $user->ipfs = $atest;
        $user->url = get_config('mod_seal', 'url_student');
        $DB->update_record('seal_user', $user);
    }

    $seals = $DB->get_record('seal', array('course' => $courseId));
    $seals->enabled = 0;
    $DB->update_record('seal', $seals);

    $response->success = true;
    $response->id = $id;        

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
