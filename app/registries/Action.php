<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Action {
        private static $actions = [];
    
        public static function register($action, $callback, $overwrite = false) {
            if (isset(self::$actions[$action]) && !$overwrite) {
                return false;
            } else {
                self::$actions[$action] = $callback;
                return true;
            }
        }
    
        public static function call($action) {
            if (isset(self::$actions[$action])) {
                $callback = self::$actions[$action];
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

        public static function exists($action) {
            if (isset(self::$actions[$action])) {
                return true;
            } else {
                return false;
            }
        }
    }