<?php

    use Registry\Action;
    use Registry\PermissionHints;
    use Library\Permissions;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('grant', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.grant')) {
            $body = Request::parseBody();
            $required = array('target_type', 'target_value', 'permission');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                $permissions = new Permissions();
                $permissions->grant($body->target_type, $body->target_value, $body->permission);
                Respond::success();
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to grant permissions.");
        }
    });

    $this->__registerMethod('reject', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.reject')) {
            $body = Request::parseBody();
            $required = array('target_type', 'target_value', 'permission');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                $permissions = new Permissions();
                $permissions->reject($body->target_type, $body->target_value, $body->permission);
                Respond::success();
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to reject permissions.");
        }
    });

    $this->__registerMethod('clear', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.clear')) {
            $body = Request::parseBody();
            
            if (isset($body->permission_id)) {
                $permissions = new Permissions();
                $res = $permissions->clear($body->permission_id);
                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("unknown_permission", 'The permission you are trying to clear does not exist.');
                }
            } else {
                $required = array('target_type', 'target_value', 'permission');
                $missing = Validate::listMissing($required, $body);
                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also reference the permission by it\'s id with "permission_id"',
                        "missing_info" => $missing
                    ));
                } else {
                    $permissions = new Permissions();
                    $res = $permissions->clear($body->target_type, $body->target_value, $body->permission);
                    if ($res) {
                        Respond::success();
                    } else {
                        Respond::error("unknown_permission", 'The permission you are trying to clear does not exist.');
                    }
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to clear permissions.");
        }
    });

    $this->__registerMethod('check', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.check')) {
            $body = Request::parseBody();
            $required = array('target_type', 'target_value', 'permission');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                if (isset($params[0])) {
                    if (Action::exists('external_permission_validator:' . $params[0])) {
                        Respond::success(array(
                            "result" => Action::call('external_permission_validator:' . $params[0], $body->target_type, $body->target_value, $body->permission, (isset($body->extended_result) && $body->extended_result ? true : false))
                        ));
                    } else {
                        Respond::error("unknown_validator", "The requested permission validator does not exist.");
                    }
                } else {
                    $permissions = new Permissions();
                    Respond::success(array(
                        "result" => $permissions->check($body->target_type, $body->target_value, $body->permission, (isset($body->extended_result) && $body->extended_result ? true : false))
                    ));
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to check permissions.");
        }
    });

    $this->__registerMethod('list', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.list')) {
            $body = Request::parseBody();
            $checkWildcards = (isset($body->check_wildcards) && !$body->check_wildcards ? false : true);

            if (isset($params[0])) {
                if (Action::exists('external_permission_list:' . $params[0])) {
                    Respond::success(array(
                        "list" => Action::call('external_permission_list:' . $params[0], (array) $body, $checkWildcards)
                    ));
                } else {
                    Respond::error("unknown_validator", "The requested permission validator does not exist.");
                }
            } else {
                $permissions = new Permissions();
                Respond::success(array(
                    "list" => $permissions->list((array) $body, $checkWildcards)
                ));
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to list permissions.");
        }
    });

    $this->__registerMethod('find', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.find')) {
            $body = Request::parseBody();
            
            if (isset($body->permission_id)) {
                $permissions = new Permissions();
                Respond::success(array(
                    "result" => $permissions->find($body->permission_id)
                ));
            } else {
                $required = array('target_type', 'target_value', 'permission');
                $missing = Validate::listMissing($required, $body);
                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also reference the permission by it\'s id with "permission_id"',
                        "missing_info" => $missing
                    ));
                } else {
                    $permissions = new Permissions();                        
                    Respond::success(array(
                        "result" => $permissions->find($body->target_type, $body->target_value, $body->permission)
                    ));
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to find permissions.");
        }
    });

    $this->__registerMethod('hints', function($params) {
        if (!Request::requireMethod('get')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('permission.hints')) {
            if (count($params) > 1) {
                $extendedSearch = strpos($params[1], 'extended-search');
                Respond::success(array(
                    "result" => PermissionHints::search($params[0], $extendedSearch)
                ));
            } else if (count($params) > 0) {
                Respond::success(array(
                    "result" => PermissionHints::search($params[0])
                ));
            } else {
                Respond::success(array(
                    "result" => PermissionHints::list()
                ));
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to retrieve permission hints.");
        }
    });