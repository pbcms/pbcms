<?php

    use Library\Roles;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('create', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.create')) {
            $body = Request::parseBody();
            $required = array('name', 'description');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                $roles = new Roles;
                if ($roles->create($body->name, $body->description, (isset($body->weight) ? intval($body->weight) : null))) {
                    Respond::success();
                } else {
                    Respond::error("role_exists", "A role with the given name already exists.");
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to create a role.");
        }
    });

    $this->__registerMethod('find', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.find')) {
            if (isset($params[0])) {
                $roles = new Roles;
                Respond::success(array(
                    "result" => $roles->find($params[0], (isset($params[1]) && strtolower($params[1]) == 'by-id-allowed'))
                ));
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/roles/find/ROLE_IDENTIFIER/by-id-allowed\" where \"by-id-allowed\" is optional.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to find a role.");
        }
    });

    $this->__registerMethod('list', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.list')) {
            $body = Request::parseBody();
            $roles = new Roles;
            Respond::success(array(
                "roles" => $roles->list($body)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to list roles.");
        }
    });

    $this->__registerMethod('update', function($params) {
        if (!Request::requireMethod('patch')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.update')) {
            if (isset($params[0])) {
                $body = Request::parseBody();
                $roles = new Roles;
                $res = $roles->update($params[0], $body);
                if ($res->success) {
                    Respond::success();
                } else {
                    Respond::error($res->error, $res);
                }
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/roles/update/ROLE_IDENTIFIER/\".");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to update a role.");
        }
    });

    $this->__registerMethod('delete', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.delete')) {
            if (isset($params[0])) {
                $roles = new Roles;
                $res = $roles->delete($params[0]);
                if ($res->success) {
                    Respond::success();
                } else {
                    Respond::error($res->error, $res);
                }
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/roles/delete/ROLE_IDENTIFIER/\".");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to delete a role.");
        }
    });

    $this->__registerMethod('get-id', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('role.get-id')) {
            if (isset($params[0])) {
                $roles = new Roles;
                Respond::success(array(
                    "result" => $roles->getId($params[0], (isset($params[1]) && strtolower($params[1]) == 'by-id-allowed'))
                ));
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/roles/get-id/ROLE_IDENTIFIER/by-id-allowed\" where \"by-id-allowed\" is optional.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to find a role.");
        }
    });