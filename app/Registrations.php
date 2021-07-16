<?php
    namespace Registry;

    Api::register('user', function() {
        $users = new \Library\Users;
        \Helper\Header::JSON();
        print_r(json_encode($users->validateUsername('2004A')));
    });