<?php
    namespace Registry;

    use Helper\Random;
    use Source\CronJobs;

    /**
     * Registries were inspired by the following two gists:
     * - https://gist.github.com/im4aLL/548c11c56dbc7267a2fe96bda6ed348b
     * - https://gist.github.com/nicolasegp/0c6707774b5d8bb8a0edb7426d512d3d
     */
    
    class Cron {
        private static $permit = "";
        private static $jobs = array();

        private static function validator($permit = null) {
            if (self::$permit == "") self::$permit = Random::String(6);
            if (CronJobs::initialized()) return true;
            if ($permit == self::$permit) return true;
            CronJobs::initialize(self::$permit);
            return true;
        }

        public static function initialize() {
            self::validator();
        }
    
        public static function register($job, $permit = null) {
            self::validator($permit);
            if (in_array($job, self::$jobs)) {
                return false;
            } else {
                array_push(self::$jobs, $job);
                return true;
            }            
        }

        public static function list() {
            self::validator();
            return self::$jobs;
        }

        public static function exists($job) {
            self::validator();
            return in_array($job, self::$jobs);
        }
    }