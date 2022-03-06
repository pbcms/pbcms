<?php
    namespace Registry;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Event {
        private static $events = [];
    
        public static function listen($name, $callback) {
            self::$events[$name][] = $callback;
        }
    
        public static function trigger($name) {
            $results = array();
            $arguments = func_get_args();
            array_shift($arguments);

            if (isset(self::$events[$name])) foreach (self::$events[$name] as $event => $callback) {
                if (count($arguments) > 0) {
                    $res = call_user_func_array($callback, $arguments);
                } else {
                    $res = call_user_func($callback);
                }

                array_push($results, $res);
            }

            return $results;
        }
    }