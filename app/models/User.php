<?php
    namespace Model;

    use \Library\Controller;

    class User {
        private $session;

        public function __construct() {
            $controller = new Controller;
            $this->session = $controller->__model("session");
        }

        public function signedin() {
            return $this->session->info(true)->success;
        }

        public function authenticated() {
            return $this->session->info(false)->success;
        }

        public function info() {
            $session = $this->session->info(true);
            if ($session->success) {
                return $section->user;
            } else {
                return null;
            }
        }
    }