<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Store {
        private static $store = [];
    
        public static function set($key, $value) {
            self::$store[$key] = $value;
        }

        public static function get($key) {
            if (isset(self::$store[$key])) {
                return self::$store[$key];
            } else {
                return;
            }
        }

        public static function delete($key) {
            if (isset(self::$store[$key])) unset(self::$store[$key]);
        }
    }