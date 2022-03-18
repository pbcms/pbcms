<?php
    namespace Registry;

    use Library\Router;
    use Helper\Request;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

    class Route {
        private static $routes = [];
    
        public static function register($controller, $method, $callback, $overwrite = false) {
            $controller = self::prepareFunctionNaming($controller);
            $method = self::prepareFunctionNaming($method);

            if (in_array($controller, Router::lockedControllers())) return false;
            if (!isset(self::$routes[$controller])) self::$routes[$controller] = [];
            if (isset(self::$routes[$controller][$method]) && !$overwrite) {
                return false;
            } else {
                self::$routes[$controller][$method] = $callback;
                return true;
            }
        }
    
        public static function call($controller, $method, $params = array()) {
            $controller = self::prepareFunctionNaming($controller);
            $method = self::prepareFunctionNaming($method);

            if (in_array($controller, Router::lockedControllers())) return false;
            if (!isset(self::$routes[$controller])) self::$routes[$controller] = [];
            if (isset(self::$routes[$controller][$method])) {
                $callback = self::$routes[$controller][$method];
                if (is_string($callback)) {
                    Request::rewrite($callback);
                } else {
                    return call_user_func($callback, $params);
                }
            } else {
                return NULL;
            }
        }

        public static function exists($controller, $method = null) {
            $controller = self::prepareFunctionNaming($controller);
            $method = self::prepareFunctionNaming($method);

            if (in_array($controller, Router::lockedControllers())) return false;
            if (isset(self::$routes[$controller])) {
                if ($method) {
                    return isset(self::$routes[$controller][$method]);
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }

        private static function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }
    }