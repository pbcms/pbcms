<?php
    /**
     * Reason for this class is to make sure that the system cron jobs will be registered first and executed before any possible failing jobs will execute. 
     * The Cron registry will check if this class has been initialized before, otherwise it will let this class register it's cron jobs first with a special code called the "permit".
     */

    namespace Source;

    use Registry\Cron;

    class CronJobs {
        private static $isReady = false;

        public static function initialized() {
            return self::$isReady;
        }

        public static function initialize($permit) {
            if (self::$isReady) return false;

            $cronjobs = array(
                "updater"
            );

            foreach($cronjobs as $job) {
                $job = \Helper\prepareFunctionNaming($job);
                require_once APP_DIR . "/sources/cron/$job.php";
                Cron::register($job, $permit);
            }

            self::$isReady = true;
            return true;
        }
    }
    

