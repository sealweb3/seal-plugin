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
 * @package     mod_cer
 * @category    admin
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$ADMIN->add('modsettings', new admin_category('mod_cer_settings', new lang_string('pluginname', 'mod_cer')));
$settings = new admin_settingpage('managemod_cer', new lang_string('pluginname', 'mod_cer'));


if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'mod_cer/showinnavigation',
        new lang_string('showinnavigation', 'mod_cer'),
        new lang_string('showinnavigation_desc', 'mod_cer'),
        1
    ));
    $name = get_string('defaultsettings', 'mod_cer');
    $description = new lang_string('defaultsettings_help', 'mod_cer');
    $settings->add(new admin_setting_heading('defaultsettings', $name, $description));
}

$ADMIN->add('modsettings', $settings);


