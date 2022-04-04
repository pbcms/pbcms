<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class ErrorPage {
        private static $handlers = [];
    
        public static function register($error, $callback, $overwrite = false) {
            $error = intval($error);
            if (isset(self::$handlers[$error]) && !$overwrite) {
                return false;
            } else {
                self::$handlers[$error] = $callback;
                return true;
            }
        }
    
        public static function call($error) {
            $error = intval($error);
            if (isset(self::$handlers[$error])) {
                $callback = self::$handlers[$error];
                $arguments = func_get_args();
                array_shift($arguments);
                
                if (count($arguments) > 0) {
                    return call_user_func_array($callback, $arguments);
                } else {
                    return call_user_func($callback);
                }
            } else {
                return NULL;
            }
        }

        public static function exists($error) {
            $error = intval($error);
            if (isset(self::$handlers[$error])) {
                return true;
            } else {
                return false;
            }
        }
    }