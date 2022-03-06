<?php
    use Registry\PermissionHints;
    use Registry\Dashboard;
    use Registry\Event;
    use Library\Language;
    use Library\Router;
    use Library\Policy;
    use Library\Modules;
    use Helper\Request;

    if (!class_exists('CoreExtention')) {        
        /**
         * Can be used to append non-existant functions to the Core class, as the Core class extends this class.
         * 
         * You can safely define this class in a pre-core module once the 'register-core-extention' Action is triggered, as it will only be registered if it does not yet exist.
         * Please listen to the action as only one action will be triggered, preventing multiple modules from defining a core extention.
         */
        class CoreExtention{}
    }

    if (!class_exists('Core')) {
        /**
         * Makes connection to database, initiates the request, handles system assets and starts the router.
         * 
         * You can safely define a replacement Core class in a pre-core module on the 'register-custom-core' Action is triggered, as it will only be registered if it does not yet exist.
         * Please listen to the action as only one action will be triggered, preventing multiple modules from defining a custom core.
         */
        class Core extends CoreExtention {
            private static $loaded = false;
            private static $inSafemode = false;
            private static $assetsLoaded = false;
                
            /**
             * Loads modules, defines constants from the database and starts the router. Contains some system utitlities in static methods.
             *
             * @return void
             */
            public function __construct() {
                if (!self::$loaded) {
                    self::$loaded = true;
                    $this->definedVariables();
                    $this->sessionWorker();
                    $this->languageWorker();

                    //Initialize registries.
                    PermissionHints::initialize();
                    Dashboard::initialize();
    
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
            
            /**
             * States wheter pbcms is in safe mode or not.
             * 
             * If you try to use this within a module, modules don't load in safe mode ;)
             *
             * @return bool
             */
            public static function Safemode() {
                return self::$inSafemode;
            }
            
            /**
             * States wheter pbcms is in CLI or not.
             *
             * @return bool
             */
            public static function InCli() {
                return OPERATION_MODE == "CLI";
            }
            
            /**
             * States wheter pbcms is in the default operation mode (web) or not.
             *
             * @return void
             */
            public static function DefaultOperationMode() {
                return OPERATION_MODE == "DEFAULT";
            }
            
            /**
             * Prints accordingly to wheter pbcms is in CLI or in it's default operation mode.
             *
             * @param  mixed $text
             * @return void
             */
            public static function Print($text) {
                if (self::InCli()) {
                    fwrite(STDOUT, $text);
                } else {
                    echo $text;
                }
            }
            
            /**
             * Prints accordingly to wheter pbcms in in CLI or in it's default operation mode and appends a newline.
             *
             * @param  mixed $text
             * @return void
             */
            public static function PrintLine($text) {
                self::Print($text . (self::InCli() ? PHP_EOL : '<br>'));
            }
            
            /**
             * Assigns system assets to the asset loader. Duplicate entries are prevented with the static class variable $assetsLoaded.
             *
             * @param  bool $forceAuth Forces auth library to be included even when the user is not signed in.
             * @return void
             */
            public static function SystemAssets(bool $forceAuth = false) {
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

                if ($controller->__model('user')->signedin() || $forceAuth) {
                    $assets->registerBody('script', "pb-auth.js", array("origin" => "pubfiles", "permanent" => true));
                }
            }
                
            /**
             * Defines contants from the database.
             *
             * @return void
             */
            private function definedVariables() {
                $policy = new Policy;
                define("SITE_TITLE", $policy->get('site-title'));
                define("SITE_DESCRIPTION", $policy->get('site-description'));
                define("SITE_LOCATION", $policy->get('site-location') . (substr($policy->get('site-location'), -1) == '/' ? '' : '/'));
                define("SITE_INDEXING", (intval($policy->get('site-indexing')) == 1 ? true : false));
            }
            
            /**
             * Renews session if it exists.
             *
             * @return void
             */
            private function sessionWorker() {
                $controller = new \Library\Controller;
                $sessions = new \Library\Sessions;
                $session = $controller->__model('session')->info(true);
                if ($session->success) {
                    $sessions->refresh($session->info->uuid);
                }
            }

            private function languageWorker() {
                if (!Language::detectedLanguage()) {
                    $lang = new Language(true);
                    $lang->detectLanguage(false, true);
                    $lang->load();
                }
            }
        }
    }