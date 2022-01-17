<?php

    use Library\VirtualPath;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('create', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('router.create-virtual-path')) {
            $body = Request::parseBody();
            $required = array('path', 'target', 'lang');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                $vPaths = new VirtualPath;
                if ($vPaths->create($body->path, $body->target, $body->lang)) {
                    Respond::success();
                } else {
                    Respond::error("virtual_path_exists", "This virtual path already exists.");
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to create a virtual path.");
        }
    });

    $this->__registerMethod('delete', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('router.delete-virtual-path')) {
            $vPaths = new VirtualPath;
            if (isset($params[0])) {
                $res = $vPaths->delete(intval($params[0]));
            } else {
                $body = Request::parseBody();
                $required = array('path', 'target', 'lang');
                $missing = Validate::listMissing($required, $body);

                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also format your URL like: "' . SITE_LOCATION . 'pb-api/virtual-path/delete/VIRTUAL_PATH_IDENTIFIER/".',
                        "missing_info" => $missing
                    ));

                    die();
                } else {
                    $res = $vPaths->delete($body->path, $body->target, $body->lang);
                }
            }

            if ($res) {
                Respond::success();
            } else {
                Respond::error("unknown_virtual_path", "This virtual path does not exists.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to delete a virtual path.");
        }
    });

    $this->__registerMethod('find', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('router.find-virtual-path')) {
            $vPaths = new VirtualPath;
            if (isset($params[0])) {
                $res = $vPaths->find(intval($params[0]));
            } else {
                $body = Request::parseBody();
                $required = array('path', 'target', 'lang');
                $missing = Validate::listMissing($required, $body);

                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also format your URL like: "' . SITE_LOCATION . 'pb-api/virtual-path/delete/VIRTUAL_PATH_IDENTIFIER/".',
                        "missing_info" => $missing
                    ));

                    die();
                } else {
                    $res = $vPaths->find($body->path, $body->target, $body->lang);
                }
            }

            Respond::success(array(
                "virtual_path" => $res
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to find a virtual path.");
        }
    });

    $this->__registerMethod('list', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('router.list-virtual-path')) {
            $body = Request::parseBody();
            $vPaths = new VirtualPath;
            Respond::success(array(
                "virtual_paths" => $vPaths->list($body)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to list virtual paths.");
        }
    });