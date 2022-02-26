<?php
    /**
     * Contains the initial Loader class that prepares the request or starts installation.
     */

    /**
     * Initiates all library inclusions, makes system definitions, loads pre-core modules and loads the core or starts installation.
     */
    class Loader {
        private static $initialized = false;
        
        public function __construct() {
            $this->initialize();
        }
        
        /**
         * Initializes the request. Prevents itself from running a second time with the static $initialized variable.
         *
         * @return void
         */
        private function initialize() {
            if (!self::$initialized) {
                self::$initialized = true;
                ob_start();
                
                $this->definitions();
                $this->prepareConfiguration();
                $this->requireLibraries();
                $this->requireSources();

                $modules = new \Library\Modules();
                $preCoreModules = $modules->list('pre-core');
                foreach($preCoreModules as $module) $modules->load($module);
                \Registry\Action::call('register-core-extention');
                \Registry\Action::call('register-custom-core');
                \Registry\Event::trigger('pre-core-loaded');
                
                require_once 'Core.php';
                new Core();
            }
        }
        
        /**
         * Creates system definitions such as directories, etc.
         *
         * @return void
         */
        private function definitions() {
            define('KB', 1024);
            define('MB', 1048576);
            define('GB', 1073741824);
            define('TB', 1099511627776);

            define("ROOT_DIR", dirname(__DIR__));
            define("APP_DIR", ROOT_DIR . '/app');
            define("PUBLIC_DIR", ROOT_DIR . '/public');
            define("DYNAMIC_DIR", ROOT_DIR . '/dynamic');
            define("PUBFILES_DIR", ROOT_DIR . '/public/pb-pubfiles');
            define("REQUEST_PROTOCOL", (isset($_SERVER["SERVER_PROTOCOL"]) ? strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://' : NULL));
            define("REQUEST_HTTP_HOST", (isset($_SERVER["HTTP_HOST"]) ? $_SERVER['HTTP_HOST'] : NULL));
            define("REQUEST_BASE", (!REQUEST_PROTOCOL || !REQUEST_HTTP_HOST ? NULL : REQUEST_PROTOCOL . REQUEST_HTTP_HOST));
        }
        
        /**
         * Loads the configuration file or starts installation if it doesn't exist.
         *
         * @return void
         */
        private function prepareConfiguration() {
            if (!file_exists(ROOT_DIR . '/config.php')) {
                require_once APP_DIR . '/libraries/Installation.php';
                die;
            }

            require_once ROOT_DIR . '/config.php';
            if (PBCMS_DEBUG_MODE) ini_set('display_errors', 1);
        }
        
        /**
         * Requires all libraries.
         *
         * @return void
         */
        private function requireLibraries() {
            require_once 'libraries/Registries.php';
            require_once 'libraries/JWT.php';
            require_once 'libraries/Meta.php';
            require_once 'libraries/Assets.php';
            require_once 'libraries/Controller.php';
            require_once 'libraries/Database.php';
            require_once 'libraries/VirtualPath.php';
            require_once 'libraries/Policy.php';
            require_once 'libraries/Objects.php';
            require_once 'libraries/Media.php';
            require_once 'libraries/Mailer.php';
            require_once 'libraries/Helpers.php';
            require_once 'libraries/Roles.php';
            require_once 'libraries/Users.php';
            require_once 'libraries/Relations.php';
            require_once 'libraries/Permissions.php';
            require_once 'libraries/Sessions.php';
            require_once 'libraries/Language.php';
            require_once 'libraries/Token.php';
            require_once 'libraries/Router.php';
            require_once 'libraries/Modules.php';
            require_once 'libraries/Cli.php';
        }

        /**
         * Require scripts from sources.
         * 
         * @return void
         */
        private function requireSources() {
            require_once 'sources/dashboard-sidebar.php';
        }
    }

    // Start the Loader and thus, process the request.
    new Loader();
