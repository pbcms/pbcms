<?php
    namespace Registry;

    /**
     * Libraries were inspired by the following two gists:
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

    class Dashboard {
        private static $sections = array();
        private static $categories = array(
            "no_category" => array(),
            "content" => array(),
            "configuration" => array(),
            "other" => array()
        );
    
        public static function register($section, $options) {
            if (isset(self::$sections[$section])) {
                return false;
            } else {
                $options = (array) $options;
                $options['section'] = $section;
                $category = (isset($options['category']) ? $options['category'] : 'no_category');
                if (!isset(self::$sections[$category])) $category = 'other';
                $options['category'] = $category;

                self::$sections[$section] = $options;
                array_push(self::$categories[$category], $section);
                return true;
            }

            
        }
    
        public static function get($section) {
            if (isset(self::$sections[$section])) {
                $callback = self::$sections[$section];
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

        public static function list($category = null) {
            return self::$sections;
        }

        public static function exists($section) {
            if (isset(self::$sections[$section])) {
                return true;
            } else {
                return false;
            }
        }
    }