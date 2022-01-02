<?php

    use Library\Objects;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('create', function($params) {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        $body = Request::parseBody();
        $required = array("type", "name");

        if ($this->user->check('object.create')) {
            $missing = Validate::listMissing($required, $body);
            if (count($missing) > 0) {
                Respond::error('missing_information', array(
                    "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                    "missing_info" => $missing
                ));
            } else {
                if (empty($body->type) || empty($body->name)) {
                    Respond::error('empty_data', "Either the type or name was provided empty.");
                } else {
                    $objects = new Objects();
                    if (!$objects->validateNaming($body->type) || !$objects->validateNaming($body->name)) {
                        Respond::error('invalid_naming', "Either the type or name was given an illegal name. Cannot contain just a number and only contain a-z, A-Z, 0-9, '-', '_' or '.' as characters.");
                    } else {
                        if ($objects->create($body->type, $body->name)) {
                            Respond::success();
                        } else {
                            Respond::error('object_exists', "The object you are trying to create already exists.");
                        }
                    }
                }
            } 
        } else {
            Respond::error('missing_privileges', "You are not allowed to create a new object.");
        }
    });

    $this->__registerMethod('exists', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.exists')) {
            if (count($params) < 1) {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/exists/OBJECT_TYPE/OBJECT_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/exists/OBJECT_ID\".");
            } else {
                $objects = new Objects();
                if (isset($params[1])) {
                    Respond::success(array(
                        "object_type" => $params[0],
                        "object_name" => $params[1],
                        "exists" => $objects->exists($params[0], $params[1])
                    ));
                } else {
                    Respond::success(array(
                        "object_type" => $params[0],
                        "object_name" => null,
                        "exists" => $objects->exists($params[0])
                    ));
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to check if an object exists.");
        }
    });

    $this->__registerMethod('info', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.info')) {
            if (count($params) < 1) {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/info/OBJECT_TYPE/OBJECT_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/info/OBJECT_ID\".");
            } else {
                $objects = new Objects();
                if (isset($params[1])) {
                    Respond::success(array(
                        "object_type" => $params[0],
                        "object_name" => $params[1],
                        "object_id" => null,
                        "info" => $objects->info($params[0], $params[1])
                    ));
                } else {
                    Respond::success(array(
                        "object_type" => null,
                        "object_name" => null,
                        "object_id" => $params[0],
                        "info" => $objects->info($params[0])
                    ));
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to obtain information about an object.");
        }
    });

    $this->__registerMethod('list', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.list')) {
            $objects = new Objects();
            if (isset($params[2])) {
                Respond::success(array(
                    "list" => $objects->list($params[0], $params[1], $params[2])
                ));
            } else if (isset($params[1])) {
                Respond::success(array(
                    "list" => $objects->list($params[0], $params[1])
                ));
            } else if (isset($params[0])) {
                Respond::success(array(
                    "list" => $objects->list($params[0])
                ));
            } else {
                Respond::success(array(
                    "list" => $objects->list()
                ));
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to list objects.");
        }
    });

    $this->__registerMethod('properties', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.properties')) {
            $objects = new Objects();
            if (isset($params[2])) {
                $type = $params[0];
                $name = $params[1];
                $parse = (strtolower($params[2]) == 'parsed' || strtolower($params[2]) == 'parse' ? true : false);
            } else if (isset($params[1])) {
                if (is_numeric($params[0])) {
                    $type = $params[0];
                    $name = (strtolower($params[1]) == 'parsed' || strtolower($params[1]) == 'parse' ? true : false);
                    $parse = null;
                } else {
                    $type = $params[0];
                    $name = $params[1];
                    $parse = false;
                }
            } else if (isset($params[0])) {
                $type = $params[0];
                $name = '';
                $parse = false;
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/properties/OBJECT_TYPE/OBJECT_NAME/parsed\" OR like: \"" . SITE_LOCATION . "pb-api/objects/exists/OBJECT_ID/parsed\" where \"parsed\" is optional.");
            }

            Respond::success(array(
                "properties" => $objects->properties($type, $name, $parse)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to list properties of an object.");
        }
    });

    $this->__registerMethod('purge', function($params) {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.purge')) {
            if (count($params) < 1) {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/purge/OBJECT_TYPE/OBJECT_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/purge/OBJECT_ID\".");
            } else {
                $objects = new Objects();
                $res = $objects->purge($params[0], $params[1]);
                if (isset($params[1])) {
                    $resdata = array(
                        "object_type" => $params[0],
                        "object_name" => $params[1],
                        "object_id" => null,
                    );
                } else {
                    $resdata = array(
                        "object_type" => null,
                        "object_name" => null,
                        "object_id" => $params[0],
                    );
                }

                if (!$res) {
                    $resdata['message'] = "Object does not exist.";
                    Respond::error('unknown_object', $resdata);
                } else {
                    Respond::success($resdata);
                }
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to delete an object.");
        }
    });

    $this->__registerMethod('get', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.get-property')) {
            $objects = new Objects();
            if (isset($params[2])) {
                $type = $params[0];
                $name = $params[1];
                $property = $params[2];
            } else if (isset($params[1])) {
                $type = $params[0];
                $name = $params[1];
                $property = '';
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/get/OBJECT_TYPE/OBJECT_NAME/PROPERTY_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/get/OBJECT_ID/PROPERTY_NAME\".");
            }

            Respond::success(array(
                "value" => $objects->get($type, $name, $property)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to obtain object properties.");
        }
    });

    $this->__registerMethod('property-exists', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.property-exists')) {
            $objects = new Objects();
            if (isset($params[2])) {
                $type = $params[0];
                $name = $params[1];
                $property = $params[2];
            } else if (isset($params[1])) {
                $type = $params[0];
                $name = $params[1];
                $property = '';
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/get/OBJECT_TYPE/OBJECT_NAME/PROPERTY_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/get/OBJECT_ID/PROPERTY_NAME\".");
            }

            Respond::success(array(
                "exists" => $objects->propertyExists($type, $name, $property)
            ));
        } else {
            Respond::error('missing_privileges', "You are not allowed to check if a property exists within an object.");
        }
    });

    $this->__registerMethod('set', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.set-property')) {
            $objects = new Objects();
            if (isset($params[3])) {
                $type = $params[0];
                $name = $params[1];
                $property = $params[2];
                $value = $params[3];
            } else if (isset($params[2])) {
                $type = $params[0];
                $name = $params[1];
                $property = $params[2];
                $value = '';
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/set/OBJECT_TYPE/OBJECT_NAME/PROPERTY_NAME/PROPERTY_VALUE\" OR like: \"" . SITE_LOCATION . "pb-api/objects/set/OBJECT_ID/PROPERTY_NAME/PROPERTY_VALUE\".");
            }

            if ($objects->set($type, $name, $property, $value)) {
                Respond::success();
            } else {
                Respond::error("unknown_object", "The object you are trying to set a property for does not exist.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to set object properties.");
        }
    });

    $this->__registerMethod('delete', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('object.get-property')) {
            $objects = new Objects();
            if (isset($params[2])) {
                $type = $params[0];
                $name = $params[1];
                $property = $params[2];
            } else if (isset($params[1])) {
                $type = $params[0];
                $name = $params[1];
                $property = '';
            } else {
                Respond::error('missing_information', "Format your url like: \"" . SITE_LOCATION . "pb-api/objects/delete/OBJECT_TYPE/OBJECT_NAME/PROPERTY_NAME\" OR like: \"" . SITE_LOCATION . "pb-api/objects/delete/OBJECT_ID/PROPERTY_NAME\".");
            }

            if ($objects->delete($type, $name, $property)) {
                Respond::success();
            } else {
                Respond::error("unknown_object", "The object you are trying to set a property for does not exist.");
            }
        } else {
            Respond::error('missing_privileges', "You are not allowed to delete object properties.");
        }
    });
