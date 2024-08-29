<?php
require_once('../../config.php');
require_login();

header('Content-Type: application/json');

function send_hardcoded_attestation() {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');

    // Hardcoded attestation data
    $attestationDto = [
        "attestationDto" => [
            "schemaId" => "0xb4",
            "linkedAttestationId" => 0,
            "attester" => "0x92388d12744B418eFac8370B266D31fd9C4c5F0e",
            "validUntil" => 0,
            "revoked" => false,
            "dataLocation" => 0,
            "attestTimestamp" => 0,
            "revokeTimestamp" => 0,
            "recipients" => [
                "0x0000000000000000000000000000000000000000000000000000000000000020000000000000000000000000000000000000000000000000000000000000002a30783932333838643132373434423431386546616338333730423236364433316664394334633546306500000000000000000000000000000000000000000000"
            ],
            "data" => "0x000000000000000000000000000000000000000000000000000000000000008000000000000000000000000000000000000000000000000000000000000000c00000000000000000000000000000000000000000000000000000000000000100000000000000000000000000000000000000000000000000000000000000014000000000000000000000000000000000000000000000000000000000000000016100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000162000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001630000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000053078313938000000000000000000000000000000000000000000000000000000",
            "indexingValue" => ""
        ],
        "signatureDto" => "0xf4f70536f86017b424a02392a75eae4ee94da7d652989f98c594032778d4c009242255a5a0af8fc734dae8bf0683d3e3690e7417bc1e964ae589b71e383383491b",
        "profileDto" => [
            "name" => "Hardcoded Name",
            "managers" => ["Hardcoded Manager"]
        ]
    ];

    $url = 'http://192.46.223.247:4000/attestations/attestOrganizationInDelegationMode';
    $jwt_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJzZWFsLWF0dGVzdGF0aW9uLXRlc3QiLCJpYXQiOjE3MjM3MjcyMjEsImV4cCI6MTc1NTI2MzIyMX0.ryv_FGvy711GcUpRk9CXJZMU5oI5v8v1KPcxMCjGRpo';

    $curl = new curl();
    $curl->setHeader([
        'Accept: application/json',
        'Authorization: Bearer ' . $jwt_token,
        'Content-Type: application/json'
    ]);

    // Enable verbose output for cURL
    $curl->setopt(array('CURLOPT_VERBOSE' => 1));
    $verbose = fopen('php://temp', 'w+');
    $curl->setopt(array('CURLOPT_STDERR' => $verbose));

    $response = $curl->post($url, json_encode($attestationDto));

    // Get verbose information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);

    if ($curl->errno != CURLE_OK) {
        error_log('Error sending attestation: ' . $curl->error);
        return [
            'success' => false, 
            'error' => 'Curl error: ' . $curl->error,
            'verbose_log' => $verboseLog
        ];
    }

    // Log the raw response for debugging
    error_log('Raw API response: ' . $response);

    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Invalid JSON response from attestation API: ' . json_last_error_msg());
        return [
            'success' => false, 
            'error' => 'Invalid JSON response',
            'raw_response' => $response,
            'json_error' => json_last_error_msg(),
            'verbose_log' => $verboseLog
        ];
    }

    return [
        'success' => true, 
        'data' => $decoded_response,
        'verbose_log' => $verboseLog
    ];
}

// Execute the function and echo the result
$result = send_hardcoded_attestation();
echo json_encode($result);