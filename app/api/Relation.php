<?php

    use Library\Relations;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('create', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('relation.create')) {
            $body = Request::parseBody();
            $required = array('type', 'origin', 'target');
            $missing = Validate::listMissing($required, $body);

            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                $relations = new Relations;
                if ($relations->create($body->type, $body->origin, $body->target)) {
                    Respond::success();
                } else {
                    Respond::error("relation_exists", "This relation already exists.");
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to create a relation.");
        }
    });

    $this->__registerMethod('delete', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('relation.delete')) {
            $relations = new Relations;
            if (isset($params[0])) {
                $res = $relations->delete(intval($params[0]));
            } else {
                $body = Request::parseBody();
                $required = array('type', 'origin', 'target');
                $missing = Validate::listMissing($required, $body);

                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also format your URL like: "' . SITE_LOCATION . 'pb-api/relation/delete/RELATION_IDENTIFIER/".',
                        "missing_info" => $missing
                    ));

                    die();
                } else {
                    $res = $relations->delete($body->type, $body->origin, $body->target);
                }
            }

            if ($res) {
                Respond::success();
            } else {
                Respond::error("unknown_relation", "This relation does not exists.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to delete a relation.");
        }
    });

    $this->__registerMethod('find', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('relation.find')) {
            $relations = new Relations;
            if (isset($params[0])) {
                $res = $relations->find(intval($params[0]));
            } else {
                $body = Request::parseBody();
                $required = array('type', 'origin', 'target');
                $missing = Validate::listMissing($required, $body);

                if (count($missing) > 0) {
                    Respond::error('missing_information', array(
                        "message" => 'The following information is missing from the request: ' . join(',', $missing) . '. You can also format your URL like: "' . SITE_LOCATION . 'pb-api/relation/find/RELATION_IDENTIFIER/".',
                        "missing_info" => $missing
                    ));

                    die();
                } else {
                    $res = $relations->find($body->type, $body->origin, $body->target);
                }
            }

            Respond::success(array(
                "relation" => $res
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to find a relation.");
        }
    });

    $this->__registerMethod('list', function($params) {
        //if (!Request::requireAuthentication()) die();

        if ($this->user->check('relation.list')) {
            $body = Request::parseBody();
            $relations = new Relations;
            Respond::success(array(
                "relations" => $relations->list($body)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to list relations.");
        }
    });