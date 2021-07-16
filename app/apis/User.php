<?php
    namespace Api;

    class User {
        public function __construct() {
            $users = new \Library\Users;
            \Helper\Header::JSON();
            print_r(json_encode($users->validateUsername('kearfy')));
        }
    }