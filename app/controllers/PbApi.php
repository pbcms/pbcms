<?php
    namespace Controller;

    class PbApi {
        public function Auth($params) {
            require_once APP_DIR . '/api/Auth.php';
        }

        public function User($params) {
            require_once APP_DIR . '/api/User.php';
        }

        public function Language($params) {
            require_once APP_DIR . '/api/Language.php';
        }

        public function Media($params) {
            require_once APP_DIR . '/api/Media.php';
        }

        public function Modules($params) {
            require_once APP_DIR . '/api/Modules.php';
        }

        public function Objects($params) {
            require_once APP_DIR . '/api/Objects.php';
        }

        public function Policy($params) {
            require_once APP_DIR . '/api/Policy.php';
        }

        public function Session($params) {
            require_once APP_DIR . '/api/Session.php';
        }

        public function Token($params) {
            require_once APP_DIR . '/api/Token.php';
        }
    }