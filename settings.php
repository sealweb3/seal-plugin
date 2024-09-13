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
require_once(__DIR__.'/externallib.php');
global $DB;

$PAGE->requires->js(new moodle_url('/mod/seal/dist/metamask.bundle.js'));

$isAuthorized = get_config('mod_seal', 'isAuthorized');
$name = get_config('mod_seal', 'name');
$profileid = get_config('mod_seal', 'profileid');
$agree = get_config('mod_seal', 'agree_terms');

set_config('url', 'https://9acc-2600-3c04-00-f03c-94ff-fe6d-53e8.ngrok-free.app', 'mod_seal'); //servidor quemado revisar código para cambiarlo

$url = get_config('mod_seal', 'url');
echo '<script type="text/javascript">var url = "';
echo get_config('mod_seal', 'url');
echo '";';
echo '</script>';
if($isAuthorized == ''){
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
}
else if ($isAuthorized == '0' && $name == ''){
    $settings->add(new admin_setting_heading('uno', get_string('settings_Unlicensed', 'seal'), ''));
    $settings->add(new admin_setting_description('seal/wallet_button', '', '<button type="button" class="btn btn-primary" id="metamaskButton">' . get_string('wallet_button', 'seal') . '</button>'));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
        $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_two', $templatecontext)));

    }
    else if ($isAuthorized == '0' && $name != ''){
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
    else if ($isAuthorized == '1' && $name == ''){
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
        debugging('Form submitted and sesskey confirmed.');
        //$profileid=mod_seal_external::attestation_organization();
        //set_config('profileId', $profileid, 'mod_seal');
        //set_config('isAuthorized', '0', 'mod_seal');
    }
    
}