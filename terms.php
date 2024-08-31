<?php
require_once('../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('terms_and_conditions', 'seal'));

echo $OUTPUT->header();

echo '<h2>' . get_string('terms_and_conditions', 'seal') . '</h2>';

echo '<p>' . get_string('terms_content', 'seal') . '</p>';

echo $OUTPUT->footer();
