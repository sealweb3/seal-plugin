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

    $logourl = new moodle_url('/mod/seal/pix/seal-logo.jpg');

    $landing_page_html = "";
    $landing_page_html .= '<div class="landing-page-container">'; // New div start
    $landing_page_html .= '<div id="shortDescription">' . get_string('shortDescription', 'seal') . '</div>';
    $landing_page_html .= '<img src="' . $logourl . '" alt="Seal Logo" class="seal-logo">';
    $landing_page_html .= get_string('description', 'seal');
    $landing_page_html .= '<div class="sponsor-logos-container">';
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

        $settings->add(new admin_setting_heading('dropdown_section', get_string('dropdown_section', 'seal'), ''));
        $dropdown_setting = new admin_setting_configselect('seal/course_dropdown', get_string('dropdown_label', 'seal'), '', key($course_options), $course_options);
        $settings->add($dropdown_setting);

        if ($data = data_submitted() && confirm_sesskey()) {
            $selected_course_id = get_config('seal', 'course_dropdown');
            if ($selected_course_id) {
                $course = $DB->get_record('course', array('id' => $selected_course_id), 'fullname');
                $record = new stdClass();
                $record->name = $course->fullname;
                $record->enabled = 1;
                $record->modality = 1;
                $record->certifyhash = '0x12345';   
                $record->timecreated = time();
                $record->timemodified = time();
                $record->intro = 'Placeholder intro';
                $record->introformat = 1;

                $DB->insert_record('seal_course_certify', $record);
            } 
        } 



    } else if ($matching_record->enabledcreate == '1' && $matching_record->enabledattestation == '0') {
            $settings->add(new admin_setting_description('seal/disconnect_button', '', '<button type="button" id="disconnectButton">' . get_string('disconnect_button', 'seal') . '</button>'));
            $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));
            
            // Nombre de la Entidad
            $settings->add(new admin_setting_configtext('seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT));
            // Descripción de la Entidad
            $settings->add(new admin_setting_configtextarea('seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
            //webste
            $settings->add(new admin_setting_configtext('seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
            
            $settings->add(new admin_setting_configtextarea('seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    
            $settings->add(new admin_setting_configcheckbox('seal/agree_terms', get_string('agree_terms', 'seal'), get_string('agree_terms_desc', 'seal'), 0));
    }

}