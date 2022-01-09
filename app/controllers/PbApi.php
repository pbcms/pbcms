<?php
    namespace Controller;

    use Registry\Action;
    use Library\Router;
    use Library\Language;

    class PbApi extends \Library\ApiController {
        private $user;

        public function __construct() {
            $this->user = $this->__model('user');
            $this->lang = new Language;
            $this->lang->detectLanguage();
            $this->lang->load();
        }

        public function __index($params) {
            $this->__apiError("missing_api");
        }

        public function __error($error) {
            if ($error == 404) {
                $router = new Router;
                $request = $router->documentRequest();
                $method = $request->params[0];
                if (Action::exists('external_api_method:' . $method)) {
                    $using = function($name) { $this->__usingApi($name); };
                    $register = function($name, $func) { $this->__registerMethod($name, $func); };
                    Action::call('external_api_method:' . $method, $using, $register);

                    $params = $request->params;
                    array_shift($params);
                    $this->__execute($params);
                } else {
                    http_response_code(404);
                    $this->__apiError("unknown_api");
                }
            } else {
                $this->__displayError($error);
            }
        }

        public function __execute($params) {
            if (!isset($params[0]) || empty($params[0])) { 
                $this->__apiError("missing_method"); 
                http_response_code(404); 
            }

            $method = $params[0];
            array_shift($params);
            if (!$this->__callMethod($method, $params)) { 
                $this->__apiError("unknown_method"); 
                http_response_code(404); 
            }
        }

        public function Auth($params) {
            $this->__usingApi("Auth");
            require_once APP_DIR . '/api/Auth.php';
            $this->__execute($params);
        }

        public function User($params) {
            $this->__usingApi("User");
            require_once APP_DIR . '/api/User.php';
            $this->__execute($params);
        }

        public function Language($params) {
            $this->__usingApi("Language");
            require_once APP_DIR . '/api/Language.php';
            $this->__execute($params);
        }

        public function Media($params) {
            $this->__usingApi("Media");
            require_once APP_DIR . '/api/Media.php';
            $this->__execute($params);
        }

        public function Modules($params) {
            $this->__usingApi("Modules");
            require_once APP_DIR . '/api/Modules.php';
            $this->__execute($params);
        }

        public function Roles($params) {
            $this->__usingApi("Roles");
            require_once APP_DIR . '/api/Roles.php';
            $this->__execute($params);
        }

        public function Permissions($params) {
            $this->__usingApi("Permissions");
            require_once APP_DIR . '/api/Permissions.php';
            $this->__execute($params);
        }

        public function Objects($params) {
            $this->__usingApi("Objects");
            require_once APP_DIR . '/api/Objects.php';
            $this->__execute($params);
        }

        public function Policy($params) {
            $this->__usingApi("Policy");
            require_once APP_DIR . '/api/Policy.php';
            $this->__execute($params);
        }

        public function Relation($params) {
            $this->__usingApi("Relation");
            require_once APP_DIR . '/api/Relation.php';
            $this->__execute($params);
        }

        public function Site($params) {
            $this->__usingApi("Site");
            require_once APP_DIR . '/api/Site.php';
            $this->__execute($params);
        }
    }