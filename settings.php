<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$PAGE->requires->js('/mod/seal/metamask.js');

// Fetch records and check for matching signature
global $DB;
$seal_admin = $DB->get_records('seal_admin');
$signature = optional_param('signature', '', PARAM_TEXT); // Get the signature from the request
$matching_record = isset($_SESSION['matching_record']) ? $_SESSION['matching_record'] : null; // Get the matching record from the session
foreach ($seal_admin as $record) {
    if (isset($record->signaturehash) && $record->signaturehash === $signature) {
        $matching_record = $record;
        break;
    }
}

if (!is_null($matching_record)) {
    $mensaje = 'false';
    // Use $matching_record to access the contents of the matching row
} else {
    $mensaje = 'true';
}


if ($ADMIN->fulltree) {
    if ($matching_record == null) {
        $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
        $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    } else if ($matching_record->enabledcreate == '0') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">Disconnect Wallet</button>'));
        $settings->add(new admin_setting_heading('uno', get_string('settings_not_enabled', 'seal'), ''));
    } else if ($matching_record->enabledcreate == '1' && $matching_record->enabledattestation == '1') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">Disconnect Wallet</button>'));
        $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));

        // Predefined values
        $predefined_values = array(
            'entityname' => 'Predefined Entity Name',
            'entitydescription' => 'Predefined Entity Description',
            'entitytype' => 'universidad',
            'geolocation' => 'Predefined Location',
            'foundationyear' => 2000,
            'contactemail' => 'contact@example.com',
            'contactphone' => '123456789',
            'contactaddress' => 'Predefined Address',
            'contactwebsite' => 'https://example.com'
        );

        // Nombre de la Entidad
        $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', $predefined_values['entityname'], PARAM_TEXT));

        // Descripción de la Entidad
        $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', $predefined_values['entitydescription'], PARAM_TEXT));

        // Tipo de Entidad
        $options = array(
            'universidad' => get_string('university', 'seal'),
            'organizacion' => get_string('organization', 'seal'),
            'plataforma' => get_string('educationalplatform', 'seal')
        );
        $settings->add(new admin_setting_configselect('seal/entitytype', get_string('entitytype', 'seal'), '', $predefined_values['entitytype'], $options));

        // Ubicación Geográfica
        $settings->add(new admin_setting_configtext('seal/geolocation', get_string('geolocation', 'seal'), '', $predefined_values['geolocation'], PARAM_TEXT));

        // Año de Fundación
        $settings->add(new admin_setting_configtext('seal/foundationyear', get_string('foundationyear', 'seal'), '', $predefined_values['foundationyear'], PARAM_INT));

        // Información de Contacto
        $settings->add(new admin_setting_configtext('seal/contactemail', get_string('contactemail', 'seal'), '', $predefined_values['contactemail'], PARAM_EMAIL));
        $settings->add(new admin_setting_configtext('seal/contactphone', get_string('contactphone', 'seal'), '', $predefined_values['contactphone'], PARAM_TEXT));
        $settings->add(new admin_setting_configtext('seal/contactaddress', get_string('contactaddress', 'seal'), '', $predefined_values['contactaddress'], PARAM_TEXT));
        $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', $predefined_values['contactwebsite'], PARAM_URL));

        // Include the JavaScript module
    } else if ($matching_record->enabledcreate == '1' && $matching_record->enabledattestation == '0') {
        $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">Disconnect Wallet</button>'));

        $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));

        // Nombre de la Entidad
        $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT));

        // Descripción de la Entidad
        $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));

        // Tipo de Entidad
        $options = array(
            'university' => get_string('university', 'seal'),
            'organization' => get_string('organization', 'seal'),
            'educationalplatform' => get_string('educationalplatform', 'seal')
        );
        $settings->add(new admin_setting_configselect('seal/entitytype', get_string('entitytype', 'seal'), '', '', $options));

        // Ubicación Geográfica
        $settings->add(new admin_setting_configtext('seal/geolocation', get_string('geolocation', 'seal'), '', '', PARAM_TEXT));

        // Año de Fundación
        $settings->add(new admin_setting_configtext('seal/foundationyear', get_string('foundationyear', 'seal'), '', '', PARAM_INT));

        // Logo de la Entidad
        $settings->add(new admin_setting_configstoredfile('seal/entitylogo', get_string('entitylogo', 'seal'), get_string('entitylogodesc', 'seal'), 'entitylogo'));

        // Información de Contacto
        $settings->add(new admin_setting_configtext('seal/contactemail', get_string('contactemail', 'seal'), '', '', PARAM_EMAIL));
        $settings->add(new admin_setting_configtext('seal/contactphone', get_string('contactphone', 'seal'), '', '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('seal/contactaddress', get_string('contactaddress', 'seal'), '', '', PARAM_TEXT));
        $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
    }
}