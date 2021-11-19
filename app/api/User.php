<?php
    use Helper\Request;
    use Helper\ApiResponse as Respond;
    use Library\Users;

    $this->__registerMethod('create', function() {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('user.create')) {
            $users = new Users;
            $postdata = Request::parsePost();
            $result = $users->create($postdata);

            if ($result->success) {
                Respond::success($result);
            } else {
                Respond::error($result->error, $result);
            }
        } else {
            Respond::error("missing_privileges", $this->lang->get("messages.api-user.error_missing_privileges", "You are lacking the permission to create a new user."));
        }
    });

    $this->__registerMethod('find', function() {

    });

    $this->__registerMethod('info', function() {

    });

    $this->__registerMethod('list', function() {

    });

    $this->__registerMethod('update', function() {

    });

    $this->__registerMethod('delete', function() {

    });

    $this->__registerMethod('get-id', function() {

    });

    $this->__registerMethod('meta-get', function() {

    });

    $this->__registerMethod('meta-list', function() {

    });

    $this->__registerMethod('meta-exists', function() {

    });

    $this->__registerMethod('meta-set', function() {

    });

    $this->__registerMethod('meta-delete', function() {

    });

    $this->__registerMethod('purge-meta', function() {

    });