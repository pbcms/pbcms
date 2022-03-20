<?php
    namespace Registry;

    use Helper\Random;
    use Source\DefaultPermissions;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */
    
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