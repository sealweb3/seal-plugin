<?php
// Este archivo es parte de Moodle - https://moodle.org/
//
// Moodle es un software libre: puedes redistribuirlo y/o modificarlo
// bajo los términos de la Licencia Pública General de GNU publicada por
// la Free Software Foundation, ya sea la versión 3 de la Licencia, o
// (a tu elección) cualquier versión posterior.
//
// Moodle se distribuye con la esperanza de que sea útil,
// pero SIN NINGUNA GARANTÍA; sin siquiera la garantía implícita de
// COMERCIABILIDAD o APTITUD PARA UN PROPÓSITO PARTICULAR. Consulte el
// Licencia pública general de GNU para más detalles.
//
// Debe haber recibido una copia de la Licencia Pública General de GNU
// junto con Moodle. Si no, vea <https://www.gnu.org/licenses/>.

require_once(__DIR__.'/../../config.php');
require_login();

header('Content-Type: application/json; charset=utf-8');

global $DB, $COURSE;

$courseid = required_param('courseid', PARAM_INT);

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Verifica que el usuario tenga permiso para acceder a esta página.
$context = context_course::instance($courseid);
require_capability('moodle/course:view', $context);

// Inicializar arrays para almacenar los datos.
$wallets = [];
$seals = [];

foreach ($data as $userId) {
    // Do something with each user ID
}

// Consulta para obtener las wallets desde la tabla seal_user.
$wallets = $DB->get_records_sql("SELECT wallethash, iduser, id FROM {seal_user} WHERE course = ?", [$courseid]);

// Consulta para obtener los nombres y descripciones desde la tabla seal.
$seals = $DB->get_records_sql("SELECT * FROM {seal} WHERE course = ?", [$courseid]);

// Formatear los resultados en arrays simples.
$wallets_array = [];
$ids_array = [];
foreach ($wallets as $wallet) {
    $wallets_array[] = $wallet->wallethash;
    $ids_array[] = $wallet->id;
}
foreach ($wallets as $wallet) {
    $user = $DB->get_record('user', array('id' => $wallet->iduser));
    $names_array[] = $user->firstname.' '.$user->lastname;
}


$seals_array = [];
foreach ($seals as $seal) {
    $seals_array[] = [
        'name' => $seal->name,
        'description' => strip_tags($seal->intro),
        'duration' => $seal->duration,
        'location' => $seal->location,
        'partners' => $seal->partners,
    ];
}

// Crear un array de respuesta JSON.
$response = [
    'wallets' => $wallets_array,
    'seals' => $seals_array,
    'names' => $names_array,
    'ids'   => $ids_array,
];

// Enviar la respuesta en formato JSON.
header('Content-Type: application/json');
echo json_encode($response);

exit;
