<?php
namespace mod_seal;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Evento cuando la configuración es actualizada.
     *
     * @param \core\event\config_updated $event
     */
    public static function config_updated(\core\event\config_updated $event) {
        // Aquí puedes llamar a la función que desees ejecutar
        self::update_seal_admin_settings();
    }

    /**
     * Función personalizada para actualizar la base de datos.
     */
    protected static function update_seal_admin_settings() {
        global $DB;

        // Ejemplo de actualización de la tabla seal_admin
        $name = get_config('mod_seal', 'location');
        $intro = get_config('mod_seal', 'intro');
        $website = get_config('mod_seal', 'website');
        $allowedwallets = get_config('mod_seal', 'allowedwallets');
        $enabledattestation = get_config('mod_seal', 'enabledattestation');

        $record = new \stdClass();
        $record->id = 1; // Asumiendo que estás actualizando un registro con ID específico
        $record->name = $entityname;
        $record->intro = $entitydescription;
        $record->website = $contactwebsite;
        $record->allowedwallets = $adressList;
        $record->enabledcreate = 0;
        $record->enabledattestation = 1;


        if ($DB->record_exists('seal_admin', ['id' => $record->id])) {
            $DB->update_record('seal_admin', $record);
        } else {
            $DB->insert_record('seal_admin', $record);
        }
    }
}