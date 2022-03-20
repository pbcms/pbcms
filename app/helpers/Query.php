<?php
    namespace Helper;

    class Query {
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