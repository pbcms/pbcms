<?php
    use Helper\Request;
    use Helper\Validate as Validator;
    use Helper\ApiResponse as Respond;
    use Library\Users;
    use Library\Media;

    $this->__registerMethod('create', function() {
        if (!Request::requireMethod('post')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('user.create')) {
            $users = new Users;
            $postdata = Request::parseBody();
            $result = $users->create($postdata);

            if ($result->success) {
                Respond::success($result);
            } else {
                Respond::error($result->error, $result);
            }
        } else {
            Respond::error("missing_privileges", $this->lang->get("messages.api-user.create.error-missing_privileges", "You are lacking the permission to create a new user."));
        }
    });

    $this->__registerMethod('info', function($params) {
        if (!Request::requireAuthentication()) die();
        if ($this->user->check('user.info')) {
            if (isset($params[0])) {
                $users = new Users;
                $result = $users->info($params[0], (isset($params[1]) ? ($params[1] == 'true' || intval($params[1]) == 1 ? true : false) : true));
            } else {
                $result = $this->user->info();
            }

            if ($result) unset($result->password);
            Respond::success((object) array(
                "user" => $result
            ));
        } else {
            if (count($params) > 0) {
                Respond::error("missing_privileges", $this->lang->get("messages.api-user.info.error-missing_privileges", "You are lacking the permission to obtain information about a different user."));
            } else {
                $result = $this->user->info();
                if ($result) unset($result->password);
                if ($result) unset($result->meta);
                Respond::success((object) array(
                    "user" => $result
                ));
            }
        }
    });

    $this->__registerMethod('list', function() {
        if (!Request::requireAuthentication()) die();
        if ($this->user->check('user.list')) {
            $users = new Users;
            $postdata = Request::parseBody();
            $result = $users->list($postdata);

            Respond::success((object) array(
                "users" => array_map(function($user) {
                    unset($user['password']);
                    return $user;
                }, $result)
            ));
        } else {
            Respond::error("missing_privileges", $this->lang->get("messages.api-user.list.error-missing_privileges", "You are lacking the permission to list users."));
        }
    });

    $this->__registerMethod('update', function($params) {
        if (!Request::requireMethod('patch')) die();
        if (!Request::requireAuthentication()) die();
        $postdata = Request::parseBody();
        $users = new Users;

        if ($this->user->check('user.update')) {
            if (isset($params[0])) {
                $result = $users->update($params[0], $postdata);
            } else {
                $result = $users->update($this->user->info()->id, $postdata);
            }

            if ($result->success) {
                Respond::success($result);
            } else {
                Respond::error($result->error, $result);
            }
        } else {
            if (count($params) > 0) {
                Respond::error("missing_privileges", $this->lang->get("messages.api-user.update.error-missing_privileges", "You are lacking the permission to update a different user."));
            } else {
                $result = $users->update($this->user->info()->id, $postdata);

                if ($result->success) {
                    Respond::success($result);
                } else {
                    Respond::error($result->error, $result);
                }
            }
        }
    });

    $this->__registerMethod('delete', function() {
        if (!Request::requireMethod('delete')) die();
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('user.delete')) {
            if (isset($params[0])) {
                if (intval($params[0]) === intval($this->user->info()->id)) {
                    Respond::error("self_protection", $this->lang->get("messages.api-user.delete.error-missing_privileges", "You cannot delete your own account from your own session."));
                } else {
                    $users = new Users;
                    $result = $users->delete($params[0]);
                    if ($result->success) {
                        Respond::success($result);
                    } else {
                        Respond::error($result->error, $result);
                    }
                }
            } else {
                Respond::error("missing_identifier", $this->lang->get("messages.api-user.delete.error-missing_privileges", "You are missing the user identifier in the URL."));
            }
        } else {
            Respond::error("missing_privileges", $this->lang->get("messages.api-user.delete.error-missing_privileges", "You are lacking the permission to delete a user."));
        }
    });

    $this->__registerMethod('get-id', function() {
        if (!Request::requireAuthentication()) die();
        $postdata = Request::parseBody();

        if ($this->user->check('user.info')) {
            if (isset($params[0])) {
                $users = new Users;
                $result = $users->getId($params[0]);
            } else {
                $result = $this->user->info()->id;
            }

            if ($result->success) {
                Respond::success($result);
            } else {
                Respond::error($result->error, $result);
            }
        } else {
            if (count($params) > 0) {
                Respond::error("missing_privileges", "You are lacking the permission to obtain information about a different user.");
            } else {
                $result = $this->user->info()->id;

                if ($result->success) {
                    Respond::success($result);
                } else {
                    Respond::error($result->error, $result);
                }
            }
        }
    });

    $this->__registerMethod('profile-picture', function($params) {
        if (!Request::requireAuthentication()) die();
        if (Request::method() == "DELETE") {
            $user = $this->user->info();
            $users = new Users;
            $users->metaDelete('profile-picture');
            Respond::success();
        } else if (Request::method() == "GET") {
            $user = $this->user->info();
            Respond::success(array(
                "picture" => $user->picture
            ));
        } else if (Request::method() == "PATCH") {
            if (isset($params[0])) {
                $user = $this->user->info();
                $users = new Users;
                $media = new Media;
                $mediaItem = $media->info($params[0]);

                if ($mediaItem) {
                    if ($mediaItem->owner == $user->id) {
                        $users->metaSet('profile-picture', $params[0]);
                        Respond::success();
                    } else {
                        Respond::error('forbidden_media', "You are not the owner of this media item.");
                    }
                } else {
                    Respond::error('unknown_media', "No media item exists with the provided uuid.");
                }
            } else {
                Respond::error('missing_information', "No uuid of a media item was provided.");
            }
        } else {
            Respond::error('invalid_request_method', "The request was made with an incorrect request method. (The 'GET', 'PATCH' or 'DELETE' method is required)");
        }
    });