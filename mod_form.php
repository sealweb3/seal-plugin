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
 * The main mod_seal configuration form.
 *
 * @package     mod_seal
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_seal
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_seal_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        $logourl = new moodle_url('/mod/certifieth/pix/LogoCertifiEth.svg');
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $certifications = $DB->get_records_menu('seal_course_certify', null, '', 'id, name');

        // Adding the standard "name" field.
        $mform->addElement('select', 'idcoursecertify', get_string('sealname', 'mod_seal'), $certifications);

        //$mform->addElement('text', 'name', get_string('sealname', 'mod_seal'), array('size' => '64'));
        $mform->addElement('static', 'my_text', '', 'En este especaio se coloca lo necesario para solicitar la billetera');

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addRule('certification', null, 'required', null, 'client');
        $mform->addRule('certification', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('certification', 'sealname', 'mod_seal');

        $mform->addElement('header', 'user', get_string('usersneed', 'mod_seal'));
        $mform->setExpanded('user');

        // Fetch enrolled users in the current course.
        $context = context_course::instance($COURSE->id);
        $enrolledusers = get_enrolled_users($context);


        foreach ($enrolledusers as $user) {
            $walletid = 'wallet_' . $user->id;
            $mform->addElement('text', $walletid, $user->firstname.' '.$user->lastname, array('size' => '20'));
            $mform->setType($walletid, PARAM_TEXT);
        }


        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    
}
