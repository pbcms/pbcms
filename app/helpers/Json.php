<?php
    namespace Helper;

    class Json {
        public static function encode($data) {
            return \json_encode($data);
        }

        public static function decode($json) {
            return \json_decode($json);
        }
    }