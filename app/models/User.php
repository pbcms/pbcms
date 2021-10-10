<?php
    namespace Model;

    use \Library\Users;
    use \Library\Controller;
    use \Library\Permissions;
    use \Library\UserPermissions;

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
                return $session->user;
            } else {
                return null;
            }
        }

        public function check($permission, $opt = null) {
            if (!$opt) {
                $user = $this->info();
            } else {
                $users = new Users;
                $user = $users->find($permission);
                $permission = $opt;
            }

            if (!$user) {
                return null;
            } else {
                $permissions = new UserPermissions;
                return $permissions->check($user->id, $permission);
            }
        }
    }