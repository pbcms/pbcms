<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Api {
        private static $apis = [];
    
        public static function register($api, $callback) {
            if (isset(self::$apis[$api])) {
                return false;
            } else {
                self::$apis[$api] = $callback;
                return true;
            }
        }
    
        public static function call($api) {
            if (isset(self::$apis[$api])) {
                $callback = self::$apis[$api];
                $arguments = func_get_args();
                array_shift($arguments);
                
                if (count($arguments) > 0) {
                    return call_user_func_array($callback, $arguments);
                } else {
                    return call_user_func($callback);
                }
            } else {
                return false;
            }
        }

        public static function exists($api) {
            if (isset(self::$apis[$api])) {
                return true;
            } else {
                return false;
            }
        }
    }