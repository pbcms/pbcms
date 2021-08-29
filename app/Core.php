<?php
    use Registry\Event;
    use Library\Router;
    use Library\Policy;
    use Library\Modules;
    use Helper\Request;

    if (!class_exists('CoreExtention')) {
        class CoreExtention{}
    }

    if (!class_exists('Core')) {
        class Core extends CoreExtention {
            private static $loaded = false;
            private static $inSafemode = false;
            private static $assetsLoaded = false;
    
            public function __construct() {
                if (!self::$loaded) {
                    self::$loaded = true;
                    $this->definedVariables();
                    $this->sessionWorker();
    
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

            public static function SystemAssets($forceAuth = false) {
                if (self::$assetsLoaded) return false;
                self::$assetsLoaded = true;

                $assets = new \Library\Assets;
                $assets->registerBody('script', 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js', array("permanent" => true));
                $assets->registerBody('script', "
                    const SITE_LOCATION = '" . SITE_LOCATION . "';
                    const PB_API = axios.create({
                        baseURL: SITE_LOCATION + 'pb-api/'
                    });
                ", array("permanent" => true));

                if (Request::signedin()) {
                    $assets->registerBody('script', "pb-auth.js", array("origin" => "pubfiles", "permanent" => true));
                }
            }
    
            private function definedVariables() {
                $policy = new Policy;
                define("SITE_TITLE", $policy->get('site-title'));
                define("SITE_DESCRIPTION", $policy->get('site-description'));
                define("SITE_LOCATION", $policy->get('site-location') . (substr($policy->get('site-location'), -1) == '/' ? '' : '/'));
                define("SITE_INDEXING", (intval($policy->get('site-indexing')) == 1 ? true : false));
            }

            private function sessionWorker() {
                $sessions = new \Library\Sessions;
                $session = Request::sessionInfo();
                if ($session->success) {
                    $sessions->refresh($session->info->uuid);
                }
            }
        }
    }