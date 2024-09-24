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
$url = get_config('mod_seal', 'url');
echo '<script type="text/javascript">var url = "';
echo get_config('mod_seal', 'url');
echo '";';
echo 'var dirurl = "';
echo new moodle_url('/mod/seal/');
echo '";';
echo '</script>';
$environments = array(
    'https://c860-2600-3c04-00-f03c-94ff-fe6d-53e8.ngrok-free.app' => array(
        'name' => 'Arbitrum Sepolia',
        'api_key' => '81c9f2e5739df1248ef4acada223c21f98364d170af61049d15ad3ef280e5038'
    ),
    'https://62b0-2600-3c04-00-f03c-94ff-fe6d-53e8.ngrok-free.app' => array(
        'name' => 'Arbitrum One',
        'api_key' => 'f84c27e5749e32149f2d9b91409c82e2c34d29a37ae893f9e1947ba2847c5147'
    ),
    'https://e9b0-2600-3c04-00-f03c-94ff-fe6d-53e8.ngrok-free.app' => array(
        'name' => 'CeloMainnet',
        'api_key' => '91b3b7e5638e00184b4fcac1ad281a03f73a9f87da8b924d93d0b8fa024f4569'
    )
);
foreach ($environments as $url5 => $data) {
    $combobox_options[$url5] = $data['name'];
}
if(get_config('mod_seal', 'url')=='')
{
    $settings->add(new admin_setting_heading('uno', get_string('settings_start', 'seal'), ''));
    $otra = new moodle_url('/mod/seal/pix/seal-logo.jpg');
    $templatecontext = (object)[
        'var1' => $otra,
    ];
    $combobox_options = array();
    $settings->add(new admin_setting_description('seal/intro_screen', '', $OUTPUT->render_from_template('mod_seal/setting_one', $templatecontext))); 
    $settings->add(new admin_setting_configselect('mod_seal/url',
        get_string('url_label', 'seal'), 
        get_string('url_desc', 'seal'), 
        '',  // Valor predeterminado: primera URL
        $combobox_options
    ));
}
else if($isAuthorized == ''){
    $selected_url = get_config('mod_seal', 'url');
    if (array_key_exists($selected_url, $environments)) {
        $api_key = $environments[$selected_url]['api_key'];
    }
    set_config('api_key', $api_key, 'mod_seal');    
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

    //$settings->add(new admin_setting_configtext('mod_seal/profid', get_string('profileid', 'seal'), '', '', PARAM_URL));
    
    $settings->add(new admin_setting_configtextarea('mod_seal/adressList', get_string('adressList', 'seal'), '', '', PARAM_TEXT));
    
    //$settings->add(new admin_setting_configtextarea('mod_seal/temp', 'temp', '', '', PARAM_TEXT));

    

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