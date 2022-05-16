<?php
    namespace Helper;

    /**
     * Helper to encode and decode query strings.
     */
    class Query {

        /**
         * Encode a data object into a query string.
         * 
         * @param   object  $data   The data object to be converted into a query string.
         * @return  string          The resulting query string.
         */
        public static function encode($data) {
            $res = '';
            foreach ($data as $key => $value) {
                if ($res != '') $res .= '&';
                $res .= $key . '=' . $value;
            }
            return $res;
        }

        public static function decode($query) {
            $res = new \stdClass;
            $query = explode('&', $query);
            foreach ($query as $segment) {
                $segment = explode('=', $segment);
                if (!isset($segment[1])) $segment[1] = '';
                $res->{$segment[0]} = $segment[1];
            }

            return $res;
        }
    }