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
require_once(__DIR__.'/lib.php');
$environments = require(__DIR__.'/environments.php');
global $DB;

$PAGE->requires->js(new moodle_url('/mod/seal/dist/metamask.bundle.js'));

$isAuthorized = get_config('mod_seal', 'isAuthorized');
$name = get_config('mod_seal', 'name');
$url = get_config('mod_seal', 'url');
echo '<script type="text/javascript">var url = "';
echo get_config('mod_seal', 'url');
echo '";';
echo 'var dirurl = "';
echo new moodle_url('/mod/seal/');
echo '";';
echo 'var var_chain = "';
echo get_config('mod_seal', 'var_chain');
echo '";';
echo 'var name_web3 = "';
echo get_config('mod_seal', 'name_web3');
echo '";';
echo 'var evmchain = "';
echo get_config('mod_seal', 'evmchain');
echo '";';
echo '</script>';

$testurl = 'https://dbab-2600-3c04-00-f03c-94ff-fe6d-53e8.ngrok-free.app';
$combobox_options = array();
foreach ($environments as $url5 => $data2) {
    $combobox_options[$url5] = $data2['name'];
}
if(get_config('mod_seal', 'url')=='')
{
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
    $settings->add(new admin_setting_configselect('mod_seal/url',
        get_string('url_label', 'seal'), 
        get_string('url_desc', 'seal'), 
        $testurl,  // Valor predeterminado: primera URL
        $combobox_options
    ));
}
else if($isAuthorized == ''){
    $selected_url = get_config('mod_seal', 'url');
    if($selected_url==$testurl)set_config('url_student', 'https://main--seal-frontend-vite-test.netlify.app/', 'mod_seal');
    else set_config('url_student', 'https://sealweb3.com/', 'mod_seal');
    if (array_key_exists($selected_url, $environments)) {
        $api_key = $environments[$selected_url]['api_key'];
        $name_web3 = $environments[$selected_url]['name'];
        $var_chain = $environments[$selected_url]['var_chain'];
        $evmchain = $environments[$selected_url]['evmchain'];
    }
    set_config('api_key', $api_key, 'mod_seal');    
    set_config('name_web3', $name_web3, 'mod_seal');    
    set_config('var_chain', $var_chain, 'mod_seal');    
    set_config('evmchain', $evmchain, 'mod_seal');    
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
    $settings->add(new admin_setting_configselect('mod_seal/url',
        get_string('url_label', 'seal'), 
        get_string('url_desc', 'seal'), 
        $selected_url,  // Valor predeterminado: primera URL
        $combobox_options
    ));
}
else if ($isAuthorized == '0' && $name == ''){
    $selected_url = get_config('mod_seal', 'url');
    if($selected_url==$testurl)set_config('url_student', 'https://main--seal-frontend-vite-test.netlify.app/', 'mod_seal');
    else set_config('url_student', 'https://sealweb3.com/', 'mod_seal');
    if (array_key_exists($selected_url, $environments)) {
        $api_key = $environments[$selected_url]['api_key'];
        $name_web3 = $environments[$selected_url]['name'];
        $var_chain = $environments[$selected_url]['var_chain'];
        $evmchain = $environments[$selected_url]['evmchain'];
    }
    set_config('api_key', $api_key, 'mod_seal'); 
    $settings->add(new admin_setting_heading('uno', get_string('settings_Unlicensed', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
        $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_two', $templatecontext)));
        $settings->add(new admin_setting_configselect('mod_seal/url',
        get_string('url_label', 'seal'), 
        get_string('url_desc', 'seal'), 
        $testurl,  // Valor predeterminado: primera URL
        $combobox_options
    ));
}
else if ($isAuthorized == '0' && $name != ''){
    $PAGE->requires->js(new moodle_url('/mod/seal/js/setting.js'));
    $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));
    // Nombre de la Entidad
    $settings->add(new admin_setting_configtext('mod_seal/name', get_string('entityname', 'seal'), '', '',  PARAM_TEXT));
    // Descripción de la Entidad
    if (get_config('mod_seal', 'program')== '')$settings->add(new admin_setting_configtextarea('mod_seal/description', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
    //webste
    $settings->add(new admin_setting_configtext('mod_seal/website', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));

    //$settings->add(new admin_setting_configtext('mod_seal/profid', get_string('profid', 'seal'), '', '', PARAM_URL));
    //$settings->add(new admin_setting_description('tres', 'Program', get_config('mod_seal', 'program')));
    //$settings->add(new admin_setting_description('cuatro', 'bantest', get_config('mod_seal', 'bantest')));

    
    if (get_config('mod_seal', 'program')== '')$settings->add(new admin_setting_configtextarea('mod_seal/adressList', get_string('adressList', 'seal'), '', '', PARAM_TEXT));
    
    //$settings->add(new admin_setting_configtextarea('mod_seal/temp', 'temp', '', '', PARAM_TEXT));
    $program = json_decode(get_program(), true);  // Convert JSON string to array
    $program_option = array(
        'new' => 'new'
    );
    foreach ($program as $data2) {
        if (isset($data2['attestation']['id']) && isset($data2['attestation']['data']['name'])) {
            $program_option[$data2['attestation']['id']] = $data2['attestation']['data']['name'];
        }
    }
    //cargar Array

    if (get_config('mod_seal', 'program')!= 'new'){
        $settings->add(new admin_setting_configselect('mod_seal/program',
            get_string('program_label', 'seal'), 
            get_string('program_desc', 'seal'), 
            'nuevo',  // Valor predeterminado: primera URL
            $program_option
        ));
        $PAGE->requires->js(new moodle_url('/mod/seal/js/settingprogra.js'));
    }
    else
    {
        $settings->add(new admin_setting_description('dos', get_string('programtitle', 'seal'), get_string('programtitledesc', 'seal')));
        $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_form_enabled', 'seal'), ''));
    }

    if (get_config('mod_seal', 'program')!= ''){
        if(get_config('mod_seal', 'program')!='new')
        {
            foreach ($program as $data2) {
                if (isset($data2['attestation']['id']) && $data2['attestation']['id'] == get_config('mod_seal', 'program')) {
                    // If the attestation ID matches the config value, store the attestation name
                    $attestation_sel = $data2['attestation']['data'];
                    break;  // Exit the loop once the match is found
                }
            }
            set_config('nameprogram', $attestation_sel['name'], 'mod_seal');
            set_config('descprogram', $attestation_sel['description'], 'mod_seal');
            set_config('reqprogram', $attestation_sel['programRequirements'], 'mod_seal');
            set_config('programmod', $attestation_sel['modality'], 'mod_seal');
        }
        $settings->add(new admin_setting_configtext('mod_seal/nameprogram', get_string('programname', 'seal'), '', '',  PARAM_TEXT));
        $settings->add(new admin_setting_configtextarea('mod_seal/descprogram', get_string('programdesc', 'seal'), '', '',  PARAM_TEXT));
        $settings->add(new admin_setting_configtext('mod_seal/reqprogram', get_string('programrequirement', 'seal'), '', '',  PARAM_TEXT));
        $settings->add(new admin_setting_configselect('mod_seal/programmod', get_string('programodality', 'seal'), '', 'Online',
            array('Online'=>'Online', 'In person'=>'In person', 'Mixed'=>'Mixed',)));
        
        if ($data = data_submitted() && confirm_sesskey()) {
            if(get_config('mod_seal','bantest')==2 && get_config('mod_seal', 'program')=='new') set_config('bantest', 3, 'mod_seal');
            if(get_config('mod_seal','bantest')==0 && get_config('mod_seal', 'program')=='new') set_config('bantest', 2, 'mod_seal');
        }
    }
    if (get_config('mod_seal', 'bantest') == 3) {
        echo '<script type="text/javascript">';
        echo 'var nameProgram = "' . get_config('mod_seal', 'nameprogram') . '";';
        echo 'var descProgram = "' . get_config('mod_seal', 'descprogram') . '";';
        echo 'var reqProgram = "' . get_config('mod_seal', 'reqprogram') . '";';
        echo 'var programMod = "' . get_config('mod_seal', 'programmod') . '";';
        echo 'var profileId = "' . get_config('mod_seal', 'profid') . '";';
        echo '</script>';
        
        $PAGE->requires->js(new moodle_url('/mod/seal/dist/attestprogram.bundle.js'));        
    }
}
else if ($isAuthorized == '1' && $name == ''){  //revisar ingreso de managers descripción
    $settings->add(new admin_setting_heading('uno', get_string('enable_certificates', 'seal'), ''));
    
    // Nombre de la Entidad
    $settings->add(new admin_setting_configtext('mod_seal/name', get_string('entityname', 'seal'), '', '', PARAM_TEXT));
    // Descripción de la Entidad
    $settings->add(new admin_setting_configtextarea('mod_seal/description', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
    //webste
    $settings->add(new admin_setting_configtext('mod_seal/website', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
    
    $settings->add(new admin_setting_configtextarea('mod_seal/adressList', get_string('adressList', 'seal'), get_string('adressList_desc', 'seal'), '', PARAM_TEXT));    
    
    $terms_url = new moodle_url('/mod/seal/terms.php');
    $terms_link = html_writer::link($terms_url, get_string('view_terms', 'seal'), ['target' => '_blank']);
    
    $settings->add(new admin_setting_configcheckbox('mod_seal/agree_terms', 
    get_string('agree_terms', 'seal'), 
    get_string('agree_terms_desc', 'seal') . ' ' . $terms_link, 
    0
    ));
    if ($data = data_submitted() && confirm_sesskey()) {
    set_config('bantest', 1, 'mod_seal');              
    }
}
else {

    if (get_config('mod_seal', 'bantest')==1) {
        echo '<script type="text/javascript">var institutionName = "';
        echo get_config('mod_seal', 'name');
        echo '";var institutionDescription = "';
        echo get_config('mod_seal', 'description');
        echo '";var institutionWebsite = "';
        echo get_config('mod_seal', 'website');
        echo '";var institutionWallets = "';
        echo get_config('mod_seal', 'adressList');
        echo '";</script>';
        $PAGE->requires->js(new moodle_url('/mod/seal/dist/attestation.bundle.js'));        
    }
    $PAGE->requires->js(new moodle_url('/mod/seal/js/setting.js'));
    $settings->add(new admin_setting_heading('uno', get_string('settings_attestation_enabled', 'seal'), ''));
    // Nombre de la Entidad
    $settings->add(new admin_setting_configtext('mod_seal/name', get_string('entityname', 'seal'), '', '',  PARAM_TEXT));
    // Descripción de la Entidad
    $settings->add(new admin_setting_configtextarea('mod_seal/description', get_string('entitydescription', 'seal'), '', '', PARAM_TEXT));
    //webste
    $settings->add(new admin_setting_configtext('mod_seal/website', get_string('contactwebsite', 'seal'), '', '', PARAM_URL));
    
    $settings->add(new admin_setting_configtextarea('mod_seal/adressList', get_string('adressList', 'seal'), '', '', PARAM_TEXT));
}