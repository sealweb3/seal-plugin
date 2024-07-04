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

$seal_admin = $DB->get_records('seal_admin');
if($seal_admin[1]->enabledcreate == '1'){
    $mensaje = 'false';
}
else{
    $mensaje = 'true';
}
//$mensaje = var_dump($seal_admin[1]);

/*if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_seal_settings', new lang_string('pluginname', 'mod_seal'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf*/
if ($ADMIN->fulltree) {
    if(is_null($seal_admin[1])){
        $settings->add(new admin_setting_heading('uno', 'Iniciovacio', 'Se muestra botón para cargar wallet con firma, se explica la solución'));
    }
    else if ($seal_admin[1]->enabledcreate == '0'){
        $settings->add(new admin_setting_heading('uno', 'No esta habilitado', 'Se explica como inscribirse se da posibilidad de creditos gratuitos se deja botón para modificar wallet con firma para recibir variables'));
    }
    else if ($seal_admin[1]->enabledattestation == '0'){
        $settings->add(new admin_setting_heading('uno', 'Habilitado para atestiguar compañia', 'formulario para atestiguar compañia'));
    }
    else{
        $settings->add(new admin_setting_heading('uno', 'Cree su certificado', 'Menu donde se crea el certificado para ser utilizado por los profesores y usuarios. Después de creado queda un botón para solicitar el attestation'));

    }
    //die;

    }
//}