<?php
    namespace Helper;

    /**
     * Encode and decode JSON data. Very useless class but still used in some places.
     */
    class Json {

        /**
         * Encodes a PHP Object or Array into JSON data. Utilizes PHP's json_encode function.
         * 
         * @param   object|array    $data   The Object or Array to be converted into JSON data.
         * @return  string                  The resulted JSON data.
         */
        public static function encode($data): string {
            return \json_encode($data);
        }

        /**
         * Decodes JSON data into a PHP Object or Array. Utilizes PHP's json_decode function.
         * 
         * @param   string          $json   The JSON data to be converted into a PHP Object or Array.
         * @return  object|array            The resulted PHP Object or Array.
         */
        public static function decode($json) {
            return \json_decode($json);
        }
    }