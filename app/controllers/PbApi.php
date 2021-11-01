<?php
    namespace Controller;

    use Library\Router;

    class PbApi extends \Library\ApiController {
        private $user;

        public function __construct() {
            $this->user = $this->__model('user');
        }

        public function __index($params) {
            $this->__apiError("missing_api");
        }

        public function __error($error) {
            if ($error == 404) {
                http_response_code(404);
                $this->__apiError("unknown_api");
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
            $this->__execute($params, );
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

        public function Session($params) {
            $this->__usingApi("Session");
            require_once APP_DIR . '/api/Session.php';
            $this->__execute($params);
        }

        public function Token($params) {
            $this->__usingApi("Token");
            require_once APP_DIR . '/api/Token.php';
            $this->__execute($params);
        }
    }