<?php
require_once(__DIR__ . '/../../config.php'); // Use __DIR__ to get the directory of the current file
require_login();

// Start the session using Moodle's session manager
if (session_status() == PHP_SESSION_NONE) {
    \core\session\manager::start();
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        if (isset($input['action']) && $input['action'] === 'reset') {
            unset($_SESSION['input']);
            echo json_encode(['status' => 'success', 'message' => 'Session cleared successfully']);
        } else {
            if (isset($input['success'])) {
                $input['success'] = $input['success'] ? 'true' : 'false';
            }
            $_SESSION['input'] = $input;
            echo json_encode(['status' => 'success', 'message' => 'Data received and stored in session']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No data received']);
    }
    exit;
}

$PAGE->requires->css(new moodle_url('/mod/seal/styles.css'));
$PAGE->requires->js(new moodle_url('/mod/seal/metamask.js'));
$PAGE->requires->js(new moodle_url('/mod/seal/dist/attestation.bundle.js'));

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/externallib.php');
global $DB;


if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('seal_settings', get_string('pluginname', 'seal'), ''));

    // Add your settings fields here
    // $settings->add(new admin_setting_configtext('seal/schemaid', get_string('schemaid', 'seal'), '', '', PARAM_TEXT));
    // $settings->add(new admin_setting_configtext('seal/name', get_string('name', 'seal'), '', '', PARAM_TEXT));
    // $settings->add(new admin_setting_configtext('seal/indexingvalue', get_string('indexingvalue', 'seal'), '', '', PARAM_TEXT));
}


// $PAGE->requires->js(new moodle_url('/mod/seal/js/web3.js'));

$input = json_decode(get_config('mod_seal', 'input'), true);

$name = get_config('mod_seal', 'name');
$profileid = get_config('mod_seal', 'profileid');
$agree = get_config('mod_seal', 'agree_terms');

$settings->add(new admin_setting_description('seal/intro', '', 'agree: '.$agree)); 
$settings->add(new admin_setting_description('seal/intro2', '', 'prof: '.$profileid));
$settings->add(new admin_setting_description('seal/intro3', '', 'name: '.$name));

$input = isset($_SESSION['input']) ? $_SESSION['input'] : null;

if (!isset($input['success'])) {
    // Condition: if !index
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
} else if ($input['success'] === 'false') {
    // Condition: if index success is false
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_heading('uno', get_string('settings_not_enabled', 'seal'), ''));
} else if ($input['success'] === 'true' && count($input['data']) > 0) {
    // Condition: if index success is true and index data array length is 0
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));

    $predefined_values = array(
        'entityname' => 'Predefined Entity Name',
        'entitydescription' => 'Predefined Entity Description',
        'contactwebsite' => 'https://example.com',
        'adressList' => '0x12345,0x6789a,0xbcdef',
    );

    $settings->add(new admin_setting_description('seal/entityname', get_string('entityname', 'seal'), $predefined_values['entityname']));
    $settings->add(new admin_setting_description('seal/entitydescription', get_string('entitydescription', 'seal'), $predefined_values['entitydescription']));
    $settings->add(new admin_setting_description('seal/contactwebsite', get_string('contactwebsite', 'seal'), $predefined_values['contactwebsite']));
    $settings->add(new admin_setting_description('seal/adressList', get_string('adressList', 'seal'), $predefined_values['adressList']));

    $courses = $DB->get_records('course', null, '', 'id, fullname');
    $course_options = array();
    foreach ($courses as $course) {
        $course_options[$course->id] = $course->fullname;
    }
} else if ($input['success'] === 'true' && count($input['data']) === 0) {
    // Condition: if index success is true and index data array length is 1
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));
    // Nombre de la Entidad
    $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT));
    // Descripción de la Entidad
    $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
    // Website
    $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
    
    $settings->add(new admin_setting_configtextarea('seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    
    $settings->add(new admin_setting_description('seal/attestation_button', '', '<button type="button" id="attestationButton" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px;">' . get_string('attestation_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_configcheckbox('seal/agree_terms', get_string('agree_terms', 'seal'), get_string('agree_terms_desc', 'seal'), 0));

    if ($data = data_submitted() && confirm_sesskey()) {
        $entityname = get_config('seal', 'entityname');
        $entitydescription = get_config('seal', 'entitydescription');
        $contactwebsite = get_config('seal', 'contactwebsite');
        $agree_terms = get_config('seal', 'agree_terms');

        $payload = array(
            'schemaId' => '0x96',
            'validUntil' => 0,
            'recipients' => explode(',', $adressList),
            'indexigngValue' => '',
            'data' => array(
                'name' => $entityname,
                'description' => $entitydescription,
                'website' => $contactwebsite,
                'linkedAttestationId' => '0x11e'
            )
        );

        send_to_external_api($payload);
    }
}
