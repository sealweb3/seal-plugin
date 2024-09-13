<?php
// Este archivo es parte de Moodle - https://moodle.org/
//
// Moodle es un software libre: puedes redistribuirlo y/o modificarlo
// bajo los términos de la Licencia Pública General de GNU publicada por
// la Free Software Foundation, ya sea la versión 3 de la Licencia, o
// (a tu elección) cualquier versión posterior.
//
// Moodle se distribuye con la esperanza de que sea útil,
// pero SIN NINGUNA GARANTÍA; sin siquiera la garantía implícita de
// COMERCIABILIDAD o APTITUD PARA UN PROPÓSITO PARTICULAR. Consulte el
// Licencia pública general de GNU para más detalles.
//
// Debe haber recibido una copia de la Licencia Pública General de GNU
// junto con Moodle. Si no, vea <https://www.gnu.org/licenses/>.

require_once(__DIR__.'/../../config.php');
require_login();

global $DB, $COURSE;

$isAuthorized = get_config('mod_seal', 'isAuthorized');
$name = get_config('mod_seal', 'name');
$profileid = get_config('mod_seal', 'profileid');
$agree = get_config('mod_seal', 'agree_terms');

echo $isAuthorized;
echo " BR ";
echo $name;

set_config('isAuthorized', '', 'mod_seal');
set_config('name', '', 'mod_seal');