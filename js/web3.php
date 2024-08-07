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

    $action = isset($data['action']) ? $data['action'] : '';
    $signature = isset($data['signature']) ? $data['signature'] : '';
    $userAddress = isset($data['userAddress']) ? $data['userAddress'] : '';
    $singMessage = isset($data['singMessage']) ? $data['singMessage'] : '';
    $messagehash = isset($data['messagehash']) ? $data['messagehash'] : '';


    if (empty($action) || empty($signature) || empty($userAddress) || empty($singMessage) || empty($messagehash)) {
        throw new Exception('Missing required data');
    }

    if ($action === 'action') {

/*
        //IsOwnerInProfiles(message, hashMessage, signature, address): boolean, any[]

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/certificate/certify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $postData = [
            'message' => $singMessage,
            'hashMessage' => $hashMesssage,
            'signature' => $signature,
            'address' => $userAddress,
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
*///        'accept: */*',
/*          'Content-Type: multipart/form-data',
        ]);

        // Ejecuta la solicitud y almacena la respuesta
        $responsedata = curl_exec($ch);

        set_config('isAuthorized', $responsedata->isAuthorized, 'mod_seal');
        set_config('name', $responsedata->organizations->name, 'mod_seal');
        set_config('profileId', $responsedata->organization->profileTd, 'mod_seal');
        set_config('description', $responsedata->organization->description, 'mod_seal');
        set_config('website', $responsedata->organization->website, 'mod_seal');
        set_config('addressList', $responsedata->organization->managers, 'mod_seal');
        set_config('address', $userAddress, 'mod_seal');
        set_config('signature', $signature, 'mod_seal');

        */

//valores simulados
set_config('isAuthorized', '1', 'mod_seal');
set_config('name', '', 'mod_seal');
set_config('profileId', '', 'mod_seal');
set_config('description', '', 'mod_seal');
set_config('website', '', 'mod_seal');
set_config('addressList', '', 'mod_seal');
set_config('address', $userAddress, 'mod_seal');
set_config('signature', $signature, 'mod_seal');


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
