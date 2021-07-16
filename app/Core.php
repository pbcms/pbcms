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
    require_once 'libraries/Controller.php';
    require_once 'libraries/Database.php';
    require_once 'libraries/Policy.php';
    require_once 'libraries/Objects.php';
    require_once 'libraries/Helpers.php';
    require_once 'libraries/Users.php';
    require_once 'libraries/Language.php';
    require_once 'libraries/Token.php';
    require_once 'libraries/Router.php';
    require_once 'libraries/Modules.php';
    
    $policy = new \Library\Policy;
    $object = new \Library\Objects;
    $modules = new \Library\Modules;
    $router = new \Library\Router;

    define("SITE_TITLE", $policy->get('site-title'));
    define("SITE_DESCRIPTION", $policy->get('site-description'));
    define("SITE_LOCATION", $policy->get('site-location') . (substr($policy->get('site-location'), -1) == '/' ? '' : '/'));
    define("SITE_INDEXING", (intval($policy->get('site-indexing')) == 1 ? true : false));

    require_once 'Registrations.php';

    $modules->initialize();
    \Registry\Event::trigger('modules-initialized', $router->documentRequest());

    $router->processRequest();
    \Registry\Event::trigger('request-processed', $router->documentRequest());

    $router->executeRequest();
    \Registry\Event::trigger('request-executed', $router->documentRequest());