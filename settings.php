<?php
require_once(__DIR__ . '/../../config.php'); 
require_login();

// Start the session using Moodle's session manager
if (session_status() == PHP_SESSION_NONE) {
    \core\session\manager::start();
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log('input: ' . json_encode($input, JSON_PRETTY_PRINT));
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
$PAGE->requires->js(new moodle_url('/mod/seal/dist/attestation.bundle.js'));
$PAGE->requires->js(new moodle_url('/mod/seal/dist/metamask.bundle.js'));

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/externallib.php');
global $DB;


if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('seal_settings', get_string('pluginname', 'seal'), ''));
}

$input = json_decode(get_config('mod_seal', 'input'), true);

$name = get_config('mod_seal', 'name');
$profileid = get_config('mod_seal', 'profileid');
$agree = get_config('mod_seal', 'agree_terms');

$input = isset($_SESSION['input']) ? $_SESSION['input'] : null;

if (!isset($input[0])) {
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    // $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
} else if ($input[0] == false && count($input[1]) > 0) {
    $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));

    foreach ($input[1] as $index => $data) {
        $userData = array(
            'entityname' => $data['nonce'],
            'entitydescription' => $data['name'],
            'contactwebsite' => $data['credits'],
            'adressList' => $data['managers'],
        );
        $settings->add(new admin_setting_description("seal/spacer_$index", '', '<br>'));

        $settings->add(new admin_setting_description("seal/entityname_$index", get_string('entityname', 'seal') . " " . $index, $userData['entityname']));
        $settings->add(new admin_setting_description("seal/entitydescription_$index", get_string('entitydescription', 'seal') . " " . $index, $userData['entitydescription']));
        $settings->add(new admin_setting_description("seal/contactwebsite_$index", get_string('contactwebsite', 'seal') . " " . $index, $userData['contactwebsite']));
        $settings->add(new admin_setting_description("seal/adressList_$index", get_string('adressList', 'seal') . " " . $index, implode(', ', $userData['adressList'])));
        
        // Add a space between lists
        $settings->add(new admin_setting_description("seal/spacer_$index", '', '<br>'));
    }
    
    $courses = $DB->get_records('course', null, '', 'id, fullname');
    $course_options = array();
    foreach ($courses as $course) {
        $course_options[$course->id] = $course->fullname;
    }
} else if ($input[0] == true) {
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));

    $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT, 60, 5));
    $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL, 50));
    $settings->add(new admin_setting_configtextarea('seal/adressList', 
        get_string('adressList', 'seal'), 
        get_string('adressList_format', 'seal'), 
        '', 
        PARAM_TEXT, 
        60, 
        5
    ));
    $PAGE->requires->js(new moodle_url('/mod/seal/settings_validation.js'));
    $settings->add(new admin_setting_description('seal/attestation_button', '', '<button type="button" id="attestationButton" class="btn btn-primary">' . get_string('attestation_button', 'seal') . '</button>'));
    $terms_url = new moodle_url('/mod/seal/terms.php');
    $terms_link = html_writer::link($terms_url, get_string('view_terms', 'seal'), ['target' => '_blank']);
    $settings->add(new admin_setting_configcheckbox('seal/agree_terms', 
        get_string('agree_terms', 'seal'), 
        get_string('agree_terms_desc', 'seal') . ' ' . $terms_link, 
        0
    ));

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
} else {
    $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
    $settings->add(new admin_setting_heading('uno', get_string('settings_not_enabled', 'seal'), ''));
}