<?php
require_once('../../config.php');
require_login();
session_start(); // Start the session

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$action = isset($data['action']) ? $data['action'] : '';
$signature = isset($data['signature']) ? $data['signature'] : '';
$userAddress = isset($data['userAddress']) ? $data['userAddress'] : '';

$response = new stdClass();

if ($action === 'verify') {
    error_log("Received signature: $signature");
    error_log("Received userAddress: $userAddress");

    global $DB;
    $seal_admin = $DB->get_records('seal_admin');
    $matching_record = null;

    foreach ($seal_admin as $record) {
        if (isset($record->signaturehash) && $record->signaturehash === $signature) {
            $matching_record = $record;
            break;
        }
    }

    if ($matching_record) {
        error_log("Matching record found: " . json_encode($matching_record));
        $_SESSION['matching_record'] = $matching_record; // Store the matching record in the session
    } else {
        error_log("No matching record found.");
        unset($_SESSION['matching_record']); // Clear the session if no matching record is found
    }

    $response->success = !is_null($matching_record);
} elseif ($action === 'reset') {
    unset($_SESSION['signature']);
    unset($_SESSION['matching_record']);

    $response->success = true;
} else {
    $response->success = false;
    $response->message = 'Invalid action';
}

// Ensure no additional output
ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit;