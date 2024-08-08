<?php
$data = json_decode(file_get_contents('php://input'), true);
require_once('../../config.php');
require_login();
global $COURSE, $USER, $DB;

$curso = 2;
$coursedb = $DB->get_record('course', ['id'=>$curso]);


$dirimage='/var/www/html/moodle/mod/certifieth/pix/';
$diripfs='/var/www/html/moodle/mod/certifieth/ipfs_storage/';
$studentname = $USER->firstname.' '.$USER->lastname;
$coursename = $coursedb->fullname;
$coursedescription= $coursedb->summary;
$signature = $data['signature'] ?? '';
$userAddress = $data['userAddress'] ?? '';
$hash = $data['hash'] ?? '';
$record = $DB->get_record('certifieth', ['course' => $coursedb->id]);
$teacheradress = $record->teacherhash;
$lighthousegateway = 'https://gateway.lighthouse.storage/ipfs/';


error_log('POSTED');
$ch = curl_init();

// Configura la URL de destino
curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/certificate/certify');

// Configura el método HTTP como POST
curl_setopt($ch, CURLOPT_POST, 1);

// Configura la opción para que curl devuelva la respuesta como un string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Configura los datos a enviar en el formulario
$postData = [
    'file' => new CURLFile($dirimage.'test.jpg', 'image/jpeg'),
    'name' => $studentname,
    'course' => $coursename,
];
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// Configura las cabeceras
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: */*',
    'Content-Type: multipart/form-data',
]);

// Ejecuta la solicitud y almacena la respuesta
$response = curl_exec($ch);

// Verifica si hubo algún error en la solicitud
if ($response === false) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Guarda la respuesta en un archivo
    file_put_contents($diripfs.'response.jpg', $response);
}

// Cierra la sesión cURL
curl_close($ch);

exec($diripfs.'ipfs_storage.sh response.jpg', $salida, $retorno);

$nombre_archivo = $diripfs.'response.json'; // Reemplaza con el nombre de tu archivo
$archivo = fopen($nombre_archivo, 'r');
$datosipfs=fgets($archivo);
//echo $datosipfs;
$datosjsonipfs = json_decode($datosipfs);

$imageurl = $lighthousegateway.$datosjsonipfs->Hash;


$ch = curl_init();

// Configura la URL de destino
curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/attestation/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Configura el método HTTP como POST
curl_setopt($ch, CURLOPT_POST, 1);

// Prepara los datos a enviar
$postData = [
    "to" => "$userAddress",
    "signature" => "$signature",
    "hash" => "$hash",
    "type" => "course",
    "attestation" => [
        "schemaId" => "0x23",
        "linkedAttestationId" => "0x14",
        "indexingValue" => "Beta",
        "recipients" => ["$userAddress"],
        "data" => [
            "Teacher address" => "$teacheradress",
            "Witness proof" => null
        ]
    ]
];

// Convierte los datos a JSON
$jsonData = json_encode($postData);

// Configura los datos a enviar
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

// Configura las cabeceras para enviar JSON
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'accept: */*'
]);

$response = curl_exec($ch);
curl_close($ch);

$responseObj = json_decode($response);

$signAttestationId = $responseObj->attestationId;
$signScanAttestation = "https://testnet-scan.sign.global/attestation/onchain_evm_421614_";
$signAttestationUrl = "$signScanAttestation$signAttestationId";
$witnessAttestionProof = $responseObj->attestation->data->{'Witness proof'};


$metadata = [
  "description" => $coursedescription,
  "external_url" => $signAttestationUrl,
  "image" => $imageurl,
  "name" => $coursename,
  "attributes" => [
      [
          "trait_type" => "SignETH Attestation",
          "value" => $signAttestationUrl
      ],
      [
          "trait_type" => "Witness Attestation",
          "value" => $witnessAttestionProof
      ]
  ]
];

$metadatajson = json_encode($metadata);

$nombre_archivo = $diripfs . 'metadata.json'; 
$archivo = fopen($nombre_archivo, 'w');
fwrite($archivo, $metadatajson);
fclose($archivo);

$nombre_archivo = $diripfs . 'meta.json'; 
$archivo = fopen($nombre_archivo, 'w');
fwrite($archivo, $metadatajson);
fclose($archivo);

exec($diripfs.'ipfs_storage.sh metadata.json', $salida, $retorno);

$nombre_archivo = $diripfs.'metadata.json'; 
$archivo = fopen($nombre_archivo, 'r');
$datosipfs=fgets($archivo);
$datosjsonipfs = json_decode($datosipfs);
$url = $lighthousegateway.$datosjsonipfs->Hash;

error_log('META: '.json_encode($metadata));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/nft/mint');
curl_setopt($ch, CURLOPT_POST, true);

$postData = [
    "to" => $userAddress,
    "signature" => $signature,
    "hash" => $hash,
    "uri" => $url,
];

$jsonData = json_encode($postData);

curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Accept: */*'
]);

curl_exec($ch);
curl_close($ch);



$customData = new stdClass();
$customData->certifiethid = $record->id;
$customData->userid = $USER->id;
$customData->addresuser = $userAddress;
$customData->hashfileips = $imageurl;
$customData->attestation = $signAttestationUrl;

$insertedId = $DB->insert_record('certifieth_user', $customData);

sleep(15);