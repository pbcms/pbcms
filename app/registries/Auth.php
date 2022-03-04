<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Auth {
        private static $providers = [];
    
        public static function register($provider, $callback) {
            if (isset(self::$providers[$provider])) {
                return false;
            } else {
                self::$providers[$provider] = $callback;
                return true;
            }
        }
    
        public static function call($provider) {
            if (isset(self::$providers[$provider])) {
                $callback = self::$providers[$provider];
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

        public static function exists($provider) {
            if (isset(self::$providers[$provider])) {
                return true;
            } else {
                return false;
            }
        }
    }