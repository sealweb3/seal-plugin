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
        $logourl = new moodle_url('/mod/certifieth/pix/seal-logo.svg');
        global $CFG, $DB, $COURSE;
        $profileId = get_config('mod_seal', 'profileId');
        if($profileId=='')
            {
                $mform = $this->_form;
                $mform->addElement('header', 'general', get_string('noidprofile', 'mod_seal'));
                
                $this->standard_coursemodule_elements();
                
            }   
            else
            {
                $mform = $this->_form;

                $view = true; 
                //código donde aparecen usuarios para que el manager los ingrese
                if ($this->_instance) {
                    $view = false;
                    $mform->addElement('header', 'users', get_string('users', 'mod_seal'));
                    
                    $context = context_course::instance($COURSE->id);
                    $enrolledusers = get_enrolled_users($context);
                    $nonteachers = array_filter($enrolledusers, function($user) use ($context) {
                        return !has_capability('moodle/course:manageactivities', $context, $user->id);
                    });

                    foreach ($nonteachers as $user) {
                        $exist = $DB->get_record('seal_user', array('iduser' => $user->id, 'course' => $COURSE->id));
                        if(!$exist){
                            $walletid = 'wallet_' . $user->id;
                            $userall = $user->firstname.' '.$user->lastname;
                            $walUser = $DB->get_record('user', array('id' => $user->id));
                            if(empty($walUser->wallet)){
                                $mform->addElement('text', $walletid, $userall, array('size' => '64'));
                                if (!empty($CFG->formatstringstriptags)) {
                                    $mform->setType($walletid, PARAM_TEXT);
                                } else {
                                    $mform->setType($walletid, PARAM_CLEANHTML);
                                }
                            }
                            else{
                                $mform->addElement('html', '<div>'.$userall.' '.$walUser->wallet.'</div>');                    
                            }

                        }
                    }
                }
            
                // Adding the "general" fieldset, where all the common settings are shown.
                $mform->addElement('header', 'general', get_string('certificate', 'mod_seal'));
                
                // Adding the standard "name" field.
                $mform->addElement('text', 'name', get_string('sealname', 'mod_seal'), array('size' => '64'));
                
                if (!empty($CFG->formatstringstriptags)) {
                    $mform->setType('name', PARAM_TEXT);
                } else {
                    $mform->setType('name', PARAM_CLEANHTML);
                }
                
                $mform->addRule('name', null, 'required', null, 'client');
                $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
                $mform->addHelpButton('name', 'sealname', 'mod_seal');
                if ($view) {
                    $mform->addElement('filepicker', 'batchfile', get_string('batch', 'mod_seal'), null, array('accepted_types' => array('image'), 'maxfiles' => 1));
                    $mform->addRule('batchfile', null, 'required', null, 'client');

                    $mform->addElement('filepicker', 'imagefile', get_string('image', 'mod_seal'), null, array('accepted_types' => array('.jpg', '.png', '.jpeg'), 'maxfiles' => 1));
                    $mform->addRule('imagefile', null, 'required', null, 'client');
                }
                // Adding the standard "intro" and "introformat" fields.
                if ($CFG->branch >= 29) {
                    $this->standard_intro_elements();
                } else {
                    $this->add_intro_editor();
                }
                $mform->addElement('text', 'duration', get_string('duration', 'mod_seal'), array('size' => '64'));
                $mform->setType('duration', PARAM_TEXT);
                $mform->addRule('duration', null, 'required', null, 'client');
                $mform->addElement('text', 'location', get_string('location', 'mod_seal'), array('size' => '64'));
                $mform->setType('location', PARAM_TEXT);
                $mform->addRule('location', null, 'required', null, 'client');
                $mform->addElement('text', 'partners', get_string('partners', 'mod_seal'), array('size' => '64'));
                $mform->setType('partners', PARAM_TEXT);
                $mform->addRule('partners', null, 'required', null, 'client');
                
                
                // Adding the rest of mod_seal settings, spreading all them into this fieldset
                // ... or adding more fieldsets ('header' elements) if needed for better logic.
                //$mform->addElement('static', 'label1', 'sealsettings', get_string('sealsettings', 'mod_seal'));
                //$mform->addElement('header', 'sealfieldset', get_string('sealfieldset', 'mod_seal'));
                
                // Add standard elements.
                $this->standard_coursemodule_elements();
                
                // Add standard buttons.
                $this->add_action_buttons();
            }
        }
    }
    
    
    