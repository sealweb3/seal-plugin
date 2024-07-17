<?php
require_once(__DIR__ . '/../../config.php'); // Use __DIR__ to get the directory of the current file
require_login();

// Start the session using Moodle's session manager
if (session_status() == PHP_SESSION_NONE) {
    \core\session\manager::start();
}
$PAGE->requires->css(new moodle_url('/mod/seal/styles.css'));
$PAGE->requires->js(new moodle_url('/mod/seal/metamask.js'));

// Fetch records and check for matching signature
global $DB;
$seal_admin = $DB->get_records('seal_admin');
$signature = isset($_SESSION['signature']) ? $_SESSION['signature'] : ''; // Get the signature from the session
$matching_record = isset($_SESSION['matching_record']) ? $_SESSION['matching_record'] : null; // Get the matching record from the session

foreach ($seal_admin as $record) {
    if (isset($record->signaturehash) && $record->signaturehash === $signature) {
        $matching_record = $record;
        break;
    }
}

    $logourl = new moodle_url('/mod/seal/pix/LogoCertifiEth.svg');
    $arbitrumurl = new moodle_url('/mod/seal/pix/arbitrum.png');
    $signurl = new moodle_url('/mod/seal/pix/sign.svg');
    $witnessurl = new moodle_url('/mod/seal/pix/witness.png');

    $landing_page_html = "";
    $landing_page_html .= '<div class="landing-page-container">'; // New div start
    $landing_page_html .= '<div id="shortDescription">' . get_string('shortDescription', 'seal') . '</div>';
    $landing_page_html .= '<img src="' . $logourl . '" alt="CertifiEth Logo" class="certifi-logo">';
    $landing_page_html .= get_string('description', 'seal');
    $landing_page_html .= '<div class="sponsor-logos-container">';
    $landing_page_html .= '<img src="' . $arbitrumurl . '" alt="Arbitrum Logo" class="sponsor-logo1">';
    $landing_page_html .= '<img src="' . $signurl . '" alt="Sign Logo" class="sponsor-logo">';
    $landing_page_html .= '<img src="' . $witnessurl . '" alt="Witness Logo" class="sponsor-logo">';
    $landing_page_html .= '';
    $landing_page_html .= '</div>';
    $landing_page_html .= '</div>';


if (isset($ADMIN) && $ADMIN->fulltree) {
    if ($matching_record == null) {
        $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
        $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
        $settings->add(new admin_setting_description('seal/intro_screen', '', $landing_page_html)); 
    } else if ($matching_record->enabledcreate == '0') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
        $settings->add(new admin_setting_heading('uno', get_string('settings_not_enabled', 'seal'), ''));
    } else if ($matching_record->enabledcreate == '1' && $matching_record->enabledattestation == '1') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
        $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));

        // Predefined values
        $predefined_values = array(
            'entityname' => 'Predefined Entity Name',
            'entitydescription' => 'Predefined Entity Description',
            'contactwebsite' => 'https://example.com'
        );

        // Nombre de la Entidad
        $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', $predefined_values['entityname'], PARAM_TEXT));
        // Descripción de la Entidad
        $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', $predefined_values['entitydescription'], PARAM_TEXT));
        
        $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', $predefined_values['contactwebsite'], PARAM_URL));

$settings->add(new admin_setting_configtextarea('seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    } else if ($matching_record->enabledcreate == '1' && $matching_record->enabledattestation == '0') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
        $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));

        // Nombre de la Entidad
        $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT));
        // Descripción de la Entidad
        $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
        //webste
        $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));

$settings->add(new admin_setting_configtextarea('seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    }
}