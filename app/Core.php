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
    
                    if (self::DefaultOperationMode()) {
                        $router->processRequest();
                        Event::trigger('request-processed', $router->documentRequest());
        
                        $router->executeRequest();
                        Event::trigger('request-executed', $router->documentRequest());
                    } else {
                        switch(OPERATION_MODE) {
                            case "CLI": 
                                core_initialized();
                                break;
                            default:
                                die("Illegal operation mode defined.");
                                break;
                        }
                    }
                }
            }
    
            public static function Safemode() {
                return self::$inSafemode;
            }

            public static function InCli() {
                return OPERATION_MODE == "CLI";
            }

            public static function DefaultOperationMode() {
                return OPERATION_MODE == "DEFAULT";
            }

            public static function Print($text) {
                if (self::InCli()) {
                    fwrite(STDOUT, $text);
                } else {
                    echo $text;
                }
            }

            public static function PrintLine($text) {
                self::Print($text . PHP_EOL);
            }

            public static function SystemAssets($forceAuth = false) {
                if (self::$assetsLoaded) return false;
                self::$assetsLoaded = true;

                $assets = new \Library\Assets;
                $controller = new \Library\Controller;

                $assets->registerBody('style', "pb-modal.css", array("origin" => "pubfiles", "permanent" => true));

                $assets->registerBody('script', 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js', array("permanent" => true));
                $assets->registerBody('script', "pb-modal.js", array("origin" => "pubfiles", "permanent" => true));
                $assets->registerBody('script', "
                    const SITE_LOCATION = '" . SITE_LOCATION . "';
                    const PB_API = axios.create({
                        baseURL: SITE_LOCATION + 'pb-api/'
                    });
                ", array("permanent" => true));

                if ($controller->__model('user')->signedin()) {
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
                $controller = new \Library\Controller;
                $sessions = new \Library\Sessions;
                $session = $controller->__model('session')->info(true);
                if ($session->success) {
                    $sessions->refresh($session->info->uuid);
                }
            }
        }
    }