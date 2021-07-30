<?php
    ob_start();

    define("ROOT_DIR", dirname(__DIR__));
    define("APP_DIR", ROOT_DIR . '/app');
    define("PUBLIC_DIR", ROOT_DIR . '/public');
    define("DYNAMIC_DIR", ROOT_DIR . '/dynamic');
    define("PUBFILES_DIR", ROOT_DIR . '/public/pb-pubfiles');
    define("REQUEST_PROTOCOL", strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://');
    define("REQUEST_HTTP_HOST", $_SERVER['HTTP_HOST']);
    define("REQUEST_BASE", REQUEST_PROTOCOL . REQUEST_HTTP_HOST);

    if (!file_exists(ROOT_DIR . '/config.php')) {
        require_once APP_DIR . '/libraries/Installation.php';
        die;
    }

    require_once ROOT_DIR . '/config.php';

    if (PBCMS_DEBUG_MODE) {
        ini_set('display_errors', 1);
    }

    define('KB', 1024);
    define('MB', 1048576);
    define('GB', 1073741824);
    define('TB', 1099511627776);

    require_once 'libraries/Registries.php';
    require_once 'libraries/JWT.php';
    require_once 'libraries/Meta.php';
    require_once 'libraries/Controller.php';
    require_once 'libraries/Database.php';
    require_once 'libraries/Policy.php';
    require_once 'libraries/Objects.php';
    require_once 'libraries/Helpers.php';
    require_once 'libraries/Users.php';
    require_once 'libraries/Sessions.php';
    require_once 'libraries/Language.php';
    require_once 'libraries/Token.php';
    require_once 'libraries/Router.php';
    require_once 'libraries/Modules.php';
    
    require_once 'Core.php';
    new Core();