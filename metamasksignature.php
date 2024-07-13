<?php
require_once('../../config.php');
require_login();
\core\session\manager::start(); // Start the session using Moodle's session manager

// Log raw input data for debugging
$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);
$action = isset($data['action']) ? $data['action'] : '';
$signature = isset($data['signature']) ? $data['signature'] : '';
$userAddress = isset($data['userAddress']) ? $data['userAddress'] : '';

// Log parsed data for debugging

$response = new stdClass();
if ($action === 'verifyAdmin') {
    // Log the received data
    debugging("Received signature: $signature", DEBUG_DEVELOPER);
    debugging("Received userAddress: $userAddress", DEBUG_DEVELOPER);

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
        // Log the matching record
        debugging("Matching record found: " . json_encode($matching_record), DEBUG_DEVELOPER);
        $_SESSION['matching_record'] = $matching_record; // Store the matching record in the session
      } else {
        // Log no matching record found
        debugging("No matching record found.", DEBUG_DEVELOPER);
        unset($_SESSION['matching_record']); // Clear the session if no matching record is found
    }

    $response->success = !is_null($matching_record);
} elseif ($action === 'verifyUser') {
  // Log the received data
    debugging("Received signature: $signature", DEBUG_DEVELOPER);
    debugging("Received userAddress: $userAddress", DEBUG_DEVELOPER);

    global $DB;
    $seal_user = $DB->get_records('seal_user');
    $matching_record = null;

    foreach ($seal_user as $record) {
        if (isset($record->signaturehash) && $record->signaturehash === $signature) {
            $matching_record = $record;
            break;
        }
    }

    if ($matching_record) {
        // Log the matching record
        debugging("Matching record found: " . json_encode($matching_record), DEBUG_DEVELOPER);
        $_SESSION['matching_record'] = $matching_record; // Store the matching record in the session
        error_log("Matching record found: " . json_encode($_SESSION['matching_record']));
    } else {
        // Log no matching record found
        debugging("No matching record found.", DEBUG_DEVELOPER);
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