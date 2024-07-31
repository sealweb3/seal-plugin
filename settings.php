<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     mod_seal
 * @category    admin
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
//require(__DIR__.'/../../config.php');
global $DB;

$PAGE->requires->js(new moodle_url('/mod/seal/js/web3.js'));

$seal_admin = $DB->get_records('seal_admin');

/*if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_seal_settings', new lang_string('pluginname', 'mod_seal'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf*/
    if(is_null($seal_admin[1])){
        $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
        $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="firstButton">' . get_string('wallet_button', 'seal') . '</button>'));
        $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
        $templatecontext = (object)[
            'var1' => $otra,
        ];
        $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
    }
    else if ($seal_admin[1]->enabledcreate == '0' && $seal_admin[1]->enabledattestation == '0'){
        $settings->add(new admin_setting_heading('uno', get_string('settings_Unlicensed', 'seal'), ''));
        $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="firstButton">' . get_string('wallet_button', 'seal') . '</button>'));
        $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
        $templatecontext = (object)[
            'var1' => $otra,
        ];
        $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_two', $templatecontext)));

    }
    else if ($seal_admin[1]->enabledcreate == '0' &&$seal_admin[1]->enabledattestation == '1'){
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
    }
    else if ($seal_admin[1]->enabledcreate == '1' &&$seal_admin[1]->enabledattestation == '0'){
        $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));
        
        // Nombre de la Entidad
        $settings->add(new admin_setting_configtext('mod_seal/entityname', get_string('entityname', 'seal'), '', '', PARAM_TEXT));
        // Descripción de la Entidad
        $settings->add(new admin_setting_configtextarea('mod_seal/entitydescription', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
        //webste
        $settings->add(new admin_setting_configtext('mod_seal/contactwebsite', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
        
        $settings->add(new admin_setting_configtextarea('mod_seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    
        $settings->add(new admin_setting_configcheckbox('mod_seal/agree_terms', get_string('agree_terms', 'seal'), get_string('agree_terms_desc', 'seal'), 0));


    }
//}