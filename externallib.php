php
Copiar código
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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class mod_seal_external {

    /**
     * The function itself.
     * @param stdClass $inputdata
     * @return stdClass
     */

    public static function attestation_student($user, $certify) {
        foreach ($user as $key => $object) {
            $result[$key]->iduser=$object->id;
            $result[$key]->course=$certify->idcourse;
            $result[$key]->wallethash=$object->wallet;
            $result[$key]->ipfs="direipfs";
            $result[$key]->url="dirurl";
        }
        return $result;
    }

    public static function attestation_certify($certify, $wallet) {
        // Perform the function's logic here.
        $output = new stdClass();
        $output->result = 'Success';
        $output->data = $inputdata;

        return $output;
    }

    public static function attestation_organization() {
        /*
        //attestOrganizationInDelegationMode(): string (profileId)

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://adjusted-weekly-cattle.ngrok-free.app/certificate/certify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $postData = [
            schemaId: '0x96' // HARDCORED,
             validUntil: 0,
             recipients: [address],
             indexingValue: '',
             data: [
               name: get_config('mod_seal', 'name'),
               description: get_config('mod_seal', 'description'),
               website: get_config('mod_seal', 'website'),
               linkedAttestationId: get_config('mod_seal', 'linkedattestationId'),
             ]

        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
*///        'accept: */*',
/*          'Content-Type: multipart/form-data',
        ]);

        // Ejecuta la solicitud y almacena la respuesta
        $responsedata = curl_exec($ch);

        $output = $responsedata->profileid;
        */
        
        $output = "AE2034";

        return $output;
    }
}