<?php
require_once('../../../config.php');
require_login();

$response = new stdClass();

try {
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
/*        'Content-Type: multipart/form-data',
    ]);

// Ejecuta la solicitud y almacena la respuesta
$responsedata = curl_exec($ch);
    */
    
    // Obtener el nonce, aquí simulado como '102030'
    
    $nonce = '102030'; 

    // Crear la respuesta
    $response->success = true;
    $response->nonce = $nonce;

} catch (Exception $e) {
    $response->success = false;
    $response->error = $e->getMessage();
    error_log('Error in nonce.php: ' . $e->getMessage());
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
