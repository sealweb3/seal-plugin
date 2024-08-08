<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/seal/lib.php'); // Include the separate file for fetching nonce
require_login();

header('Content-Type: application/json; charset=utf-8');

$userAddress = required_param('userAddress', PARAM_TEXT);

if (empty($userAddress)) {
    http_response_code(400);
    echo json_encode(['error' => 'User address is required']);
    exit;
}

try {
    // Log the user address for debugging
    error_log("Fetching nonce for user address: $userAddress");

    // Fetch the nonce from the external API
    $nonce = fetch_nonce_from_api($userAddress);

    // Log the nonce for debugging
    error_log("Fetched nonce: $nonce");

    // Return the nonce as a JSON response
    echo json_encode(['nonce' => $nonce]);
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error fetching nonce: " . $e->getMessage());

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;