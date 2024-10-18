<?php
require_once('../../config.php');
require_once('lib.php');
require_login();

// Log the raw input data
$rawInput = file_get_contents('php://input');

// Decode the JSON input
$inputData = json_decode($rawInput, true);

// Access the parameters from the decoded JSON
$nonce = $inputData['nonce'] ?? '';
$userAddress = $inputData['userAddress'] ?? '';
$fullMessage = $inputData['fullMessage'] ?? '';
$signature = $inputData['signature'] ?? '';
try {
    $response = send_data_to_external_api($nonce, $userAddress, $fullMessage, $signature);
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}
exit;