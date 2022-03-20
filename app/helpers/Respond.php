<?php
    namespace Helper;

    class Respond {
        public static function JSON($response) {
            Header::JSON();
            print_r(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }