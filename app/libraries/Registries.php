<?php
    namespace Registry;

    use Helper\Random;
    use Source\DashboardSidebar;
    use Source\DefaultPermissions;

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
        private static $permit = "";
        private static $sections = array();
        private static $categories = array(
            "no_category" => array(),
            "content" => array(),
            "configuration" => array(),
            "other" => array()
        );

        private static function validator($permit = null) {
            if (self::$permit == "") self::$permit = Random::String(6);
            if (DashboardSidebar::initialized()) return true;
            if ($permit == self::$permit) return true;
            DashboardSidebar::initialize(self::$permit);
            return true;
        }

        public static function initialize() {
            self::validator();
        }
    
        public static function register($section, $options) {
            self::validator((isset($options['permit']) ? $options['permit'] : null));
            if (isset($options['permit'])) unset($options['permit']);
            if (isset(self::$sections[$section])) {
                return false;
            } else {
                $options = (array) $options;
                $options['section'] = $section;
                $category = (isset($options['category']) ? $options['category'] : 'no_category');
                if (!isset(self::$categories[$category])) $category = 'other';
                $options['category'] = $category;
                if (isset($options['permissions']) && !is_array($options['permissions'])) $options['permissions'] = [$options['permissions']];

                self::$sections[$section] = $options;
                array_push(self::$categories[$category], $section);
                return true;
            }            
        }
    
        public static function get($section) {
            self::validator();
            if (isset(self::$sections[$section])) {
                return self::$sections[$section];
            } else {
                return false;
            }
        }

        public static function list($category = null) {
            self::validator();
            if ($category === true) {
                return self::$sections;
            } else {
                if (!$category) $category = "no_category";
                if (!isset(self::$categories[$category])) return false;
                $result = [];
                
                foreach(self::$categories[$category] as $section) {
                    $result[$section] = self::$sections[$section];
                }

                return $result;
            }
        }

        public static function exists($section) {
            self::validator();
            if (isset(self::$sections[$section])) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    class PermissionHints {
        private static $permit = "";
        private static $permissions = array();

        private static function validator($permit = null) {
            if (self::$permit == "") self::$permit = Random::String(6);
            if (DefaultPermissions::initialized()) return true;
            if ($permit == self::$permit) return true;
            DefaultPermissions::initialize(self::$permit);
            return true;
        }

        public static function initialize() {
            self::validator();
        }
    
        public static function register($permission, $description = null, $permit = null) {
            self::validator($permit);
            if (isset(self::$permissions[$permission])) {
                return false;
            } else {
                self::$permissions[$permission] = $description;
                return true;
            }            
        }
    
        public static function get($permission) {
            self::validator();
            if (isset(self::$permissions[$permission])) {
                return self::$permissions[$permission];
            } else {
                return false;
            }     
        }

        public static function list() {
            self::validator();
            return self::$permissions;
        }

        public static function exists($permission) {
            self::validator();
            return isset(self::$permissions[$permission]);
        }

        public static function search($typed, $extendedSearch = false) {
            $result = array();
            foreach(array_keys(self::$permissions) as $perm) {
                if (!$extendedSearch) {
                    if (substr($perm, 0, strlen($typed)) == $typed) {
                        $result[$perm] = self::$permissions[$perm];
                    } else if (substr($perm, -1) == '%' && substr($perm, 0, -1) == substr($typed, 0, strlen($perm) - 1)) {
                        $result[$perm] = self::$permissions[$perm];
                    }
                } else if (is_int(strpos($perm, $typed))) {
                    $result[$perm] = self::$permissions[$perm];
                }
            }

            return $result;
        }
    }