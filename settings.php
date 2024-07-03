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

/*if ($hassiteconfig) {
    $settings = new admin_settingpage('mod_seal_settings', new lang_string('pluginname', 'mod_seal'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf*/
if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.
    $settings->add(new admin_setting_heading('uno', 'Iniciovacio', 'Se explica como certificarse se da posibilidad de creditos gratuitos se pide wallet con firma para recibir variables'));
    $settings->add(new admin_setting_heading('dos', 'Habilitado para atestiguar compañia', 'formulario para atestiguar compañia'));
    $settings->add(new admin_setting_heading('tres', 'Cree su certificado', 'Menu donde se crea el certificado para ser utilizado por los profesores y usuarios. Después de creado queda un botón para solicitar el attestation'));
    $settings->add(new admin_setting_heading('cuatro', 'prueba acceso a base', 'var_dump($DB->seal)'));

    }
//}
