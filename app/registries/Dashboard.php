<?php
    namespace Registry;

    use Helper\Random;
    use Source\DashboardSidebar;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */

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