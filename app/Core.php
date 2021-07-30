<?php
    use Registry\Event;
    use Library\Router;
    use Library\Policy;
    use Library\Modules;

    class Core {
        private static $loaded = false;
        private static $inSafemode = false;

        public function __construct() {
            if (!self::$loaded) {
                self::$loaded = true;
                $this->definedVariables();

                $modules = new Modules;
                $router = new Router;
                $policy = new Policy;

                if (PBCMS_SAFE_MODE || intval($policy->get('pbcms-safe-mode')) == 1) {
                    Event::trigger('pbcms-safe-mode');
                    self::$inSafemode = true;
                } else {
                    $modules->initialize();
                    Event::trigger('modules-initialized', $router->documentRequest());
                }

                $router->processRequest();
                Event::trigger('request-processed', $router->documentRequest());

                $router->executeRequest();
                Event::trigger('request-executed', $router->documentRequest());
            }
        }

        public static function Safemode() {
            return self::$inSafemode;
        }

        public static function RewriteRequest($url, $execute = true) {
            $router = new Router;
            if ($router->refactorRequest($url)) {
                if ($execute) $router->executeRequest();
            } else {
                return false;
            }
        }

        private function definedVariables() {
            $policy = new Policy;
            define("SITE_TITLE", $policy->get('site-title'));
            define("SITE_DESCRIPTION", $policy->get('site-description'));
            define("SITE_LOCATION", $policy->get('site-location') . (substr($policy->get('site-location'), -1) == '/' ? '' : '/'));
            define("SITE_INDEXING", (intval($policy->get('site-indexing')) == 1 ? true : false));
        }
    }