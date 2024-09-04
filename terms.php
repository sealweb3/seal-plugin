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
 * Prints an instance of mod_seal.
 *
 * @package     mod_seal
 * @copyright   2024 Pablo Vesga <pablovesga@outlook.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_login();

$PAGE->set_url('/mod/seal/terms.php');
$PAGE->set_title(get_string('terms_and_conditions', 'seal'));
$PAGE->set_heading(get_string('terms_and_conditions', 'seal'));
$PAGE->set_context(context_system::instance());


echo $OUTPUT->header();

echo '<p>' . get_string('terms_content', 'seal') . '</p>';

echo $OUTPUT->footer();