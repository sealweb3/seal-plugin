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

    if (empty($action) || empty($signature) || empty($userAddress)) {
        throw new Exception('Missing required data');
    }

    global $DB;
    if ($action === 'action') {
/*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/certificate/certify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $postData = [
            'file' => new CURLFile($dirimage.'test.jpg', 'image/jpeg'),
            'name' => $studentname,
            'course' => $coursename,
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
*///        'accept: */*',
/*          'Content-Type: multipart/form-data',
        ]);

        // Ejecuta la solicitud y almacena la respuesta
        $responsedata = curl_exec($ch);

        $seal_admin->enabledcreate=$responsedata->enabledcreate;
        $seal_admin->enabledattestation=$responsedata->enabledattestation;
        $seal_admin->location=$responsedata->location;
        $seal_admin->type=$responsedata->type;
        $seal_admin->year=$responsedata->year;
        $seal_admin->email=$responsedata->email;
        $seal_admin->phone=$responsedata->phone;
        $seal_admin->address=$responsedata->address;
        $seal_admin->website=$responsedata->website;
        $seal_admin->allowedwallets=$responsedata->allowedwallets;
        $seal_admin->timecreated=time();
        $seal_admin->wallethash=$userAddress;
        $seal_admin->signaturehash=$signature;
        */

//valores simulados
        
        $seal_admin->enabledcreate=1;
        $seal_admin->enabledattestation=0;
        $seal_admin->location="";
        $seal_admin->type=0;
        $seal_admin->year=2000;
        $seal_admin->email="";
        $seal_admin->phone="";
        $seal_admin->address="";
        $seal_admin->website="";
        $seal_admin->allowedwallets="";
        $seal_admin->timecreated=time();
        $seal_admin->wallethash=$userAddress;
        $seal_admin->signaturehash=$signature;


        //función borrado base
        //pensar en un foreach para insertar todos
        $DB->delete_records('seal_admin');
        $id = $DB->insert_record('seal_admin', $seal_admin);

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
    error_log('Error in metamasksignature.php: ' . $e->getMessage());
    http_response_code(500);
}

// Asegurar que no haya salida previa
if (ob_get_contents()) {
    ob_clean();
}
header('Content-Type: application/json');
echo json_encode($response);
exit;
