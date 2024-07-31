<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'   => '\\core\\event\\config_updated',
        'callback'    => 'mod_seal_observer::config_updated',
    ),
);