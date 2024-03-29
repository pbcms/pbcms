<?php
    use Library\Policy;
    use Library\Users;
    use Library\Token;
    use Library\Sessions;
    use Library\Database;
    use Library\PasswordPolicies;
    use Library\Mailer;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Header;
    use Helper\Validate;
    use Registry\Event;

    $this->__registerMethod('create-session', function() {
        $required = array("identifier", "password");
        $postdata = Request::parsePost();

        Event::trigger('post-create-session', (object) array(
            "postdata" => $postdata
        ));

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required)) die();

        $users = new Users;
        $info = $users->find($postdata->identifier, false);
        if ($info) {
            if ($info->type == 'local') {
                if (password_verify($postdata->password, $info->password)) {
                    $tokens = new Token;
                    $sessions = new Sessions;
                    $policy = new Policy;
    
                    if ($info->status == "LOCKED") {
                        Respond::error('user_locked', $this->lang->get('messages.api-auth.create-session.error-user_locked', "The user you are trying to create a session for has been locked by the system or an administrator."));
                        die();
                    }
    
                    if (intval($policy->get('allow-stay-signedin')) == 1 && isset($postdata->stay_signedin) && intval($postdata->stay_signedin) == 1) {
                        $session = $sessions->create($info->id, false);
                        $token = $tokens->create('refresh-token', array("session" => $session), false);
                    } else {
                        $session = $sessions->create($info->id);
                        $token = $tokens->create('refresh-token', array("session" => $session));
                    }
    
                    if ($token->success) {
                        $secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? true : false);
                        $url = parse_url(SITE_LOCATION);
                        setcookie('pb-refresh-token', $token->token, 2147483647, $url['path'], $url['host'], $secure, true);
                        Event::trigger('post-create-session', (object) array(
                            "user" => $info,
                            "session" => $session,
                            "token" => $token
                        ));

                        Respond::success();
                    } else {
                        Respond::error($token->error, $this->lang->get('messages.api-auth.create-session.error-token_error', "An error occured while creating the refresh-token."));
                    }
                } else {
                    Respond::error('invalid_password', str_replace('{{IDENTIFIER}}', $postdata->identifier, $this->lang->get('messages.api-auth.create-session.error-invalid_password', "An invalid password has been provided for the user identified by {{IDENTIFIER}}.")));
                }
            } else {
                Respond::error('invalid_plugin', array(
                    "message" => $this->lang->get('messages.api-auth.create-session.error-invalid_plugin', "You are trying to authenticate a user against the local database while it's provided by external sources."),
                    "plugin" => $info->type
                ));
            }
        } else {
            Respond::error('unknown_user', str_replace("{{IDENTIFIER}}", $postdata->identifier, $this->lang->get('messages.api-auth.create-session.error-unknown_user', "A user identified by {{IDENTIFIER}} does not exist.")));
        }
    });

    $this->__registerMethod('access-token', function() {
        if (isset($_COOKIE['pb-refresh-token'])) {
            $token = new Token;
            $decoded = $token->decode('refresh-token', $_COOKIE['pb-refresh-token']);
            if ($decoded->success) {
                $sessions = new Sessions;
                $session = $sessions->info($decoded->payload->session);
                if ($session) {
                    if ($session->expired) {
                        Respond::error("session_expired", $this->lang->get('messages.api-auth.access-token.error-session_expired', "The requested session has since expired."));
                    } else {
                        $users = new Users;
                        $user = $users->info($session->user);

                        if ($user != NULL) {
                            if ($user->status == "LOCKED") {
                                Respond::error('user_locked', $this->lang->get('messages.api-auth.access-token.error-user_locked', "The user you are trying to request an access token for has been locked by the system or an administrator."));
                                die();
                            }

                            $policy = new Policy;
                            $expiration = intval($policy->get('access-token-expiration'));
                            if (!$expiration || $expiration < 1) $expiration = 3600;
                            
                            $sessions->refresh($session->uuid);
                            $refreshToken = $token->create('refresh-token', array("session" => $decoded->payload->session), (isset($decoded->expirationTime) ? $decoded->expirationTime : NULL));
                            $accessToken = $token->create('access-token', array("session" => $decoded->payload->session), $expiration);

                            if ($refreshToken->success) {
                                $secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? true : false);
                                $url = parse_url(SITE_LOCATION);
                                setcookie('pb-refresh-token', $refreshToken->token, 2147483647, $url['path'], $url['host'], $secure, true);
                            }

                            if ($accessToken->success) {
                                Respond::success($accessToken);
                            } else {
                                Respond::error($accessToken->error, $this->lang->get('messages.api-auth.access-token.error-token_error', "An error occured while creating the access-token."));
                            }
                        } else {
                            Respond::error('unknown_user', $this->lang->get('messages.api-auth.access-token.error-unknown_user', "The user you are trying to request an access token for does not exist anymore."));
                            die();
                        }
                    }
                } else {
                    Respond::error("unknown_session", $this->lang->get('messages.api-auth.access-token.error-unknown_session', "The requested session does not exist."));
                }
            } else {
                Respond::error($decoded->error, $this->lang->get('messages.api-auth.access-token.error-decode_error', "An error occured while decoding the refresh token."));
            }
        } else {
            Respond::error('missing_refresh_token', $this->lang->get('messages.api-auth.access-token.error-missing_refresh_token', "No refresh token present."));    
        }
    });

    $this->__registerMethod('signout', function($params) {
        Event::trigger('pre-signout');

        unset($_COOKIE['pb-refresh-token']); 
        $secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? true : false);
        $url = parse_url(SITE_LOCATION);
        setcookie('pb-refresh-token', null, -1, $url['path'], $url['host'], $secure, true);

        Event::trigger('post-signout');

        if (isset($_GET['followup'])) {
            Header::Location(SITE_LOCATION . $_GET['followup']);
        } else {
            Respond::success();
        }
    });

    $this->__registerMethod('retrieve-plugin', function($params) {
        $required = array("identifier");
        $body = Request::parseBody();

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required, $body)) die();

        $users = new Users;
        $user = $users->info($body->identifier);
        $plugin = (!$user ? "local" : $user->type);
        Respond::success(array(
            "plugin" => $plugin
        ));
    });

    $this->__registerMethod('reset-password', function($params) {
        $policy = new Policy;
        $resetPolicy = $policy->get("password-reset-policy");
        if ($resetPolicy == "NONE") {
            Respond::error('no_reset_policy', $this->lang->get('messages.api-auth.reset-password.error-no_reset_policy', "Unfortunately, the administrator of this site hasn't configured a password reset policy. We are unable to reset your password at this moment."));
            die();
        }

        $required = array("identifier");
        $postdata = Request::parseBody();

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required, $postdata)) die();

        $users = new Users;
        $info = $users->find($postdata->identifier, false);
        if ($info) {
            $tokens = new Token;
            if ($info->status == "LOCKED") {
                Respond::error('user_locked', $this->lang->get('messages.api-auth.reset-password.error-user_locked', "The user you are trying to create a session for has been locked by the system or an administrator."));
                die();
            }

            switch($resetPolicy) {
                case "EMAIL": 
                    if (!isset($params[0])) {
                        $uuid = \Helper\uuidv4();
                        $users->metaSet($info->id, "password-reset-identifier", time() . ":" . $uuid);
                        $mailer = new Mailer;
                        $content = file_get_contents(APP_DIR . '/sources/templates/password-reset-email.template.html');
                        $content = str_replace("{{RESET_LINK}}", SITE_LOCATION . 'pb-auth/reset-password/' . $uuid, $content);
                        $content = str_replace("{{SITE_LOCATION}}", SITE_LOCATION, $content);

                        $res = $mailer->send(array(
                            "recipient" => $info->email, 
                            "subject" => SITE_TITLE . ": Request to reset your password.", 
                            "message" => $content, 
                            "headers" => array(
                                'Mime-Version' => '1.0',
                                'Content-Type' => 'text/html;charset=UTF-8'
                            ),

                            //Optimal options for common mailer plugins.
                            "isHTML" => true
                        ));
                                    
                        if ($res) {
                            Respond::success();
                        } else {
                            Respond::error("email_error", $this->lang->get('messages.api-auth.reset-password.error-email_error', "An error occured while sending the password reset email."));
                        }
                    } else if ($params[0] == 'validate-request') {
                        $storedVerification = $users->metaGet($info->id, "password-reset-identifier");
                        if ($storedVerification) {
                            $timeStarted = explode(':', $storedVerification)[0];
                            $storedVerification = explode(':', $storedVerification)[1];
                            $timespan = intval($policy->get('password-reset-timespan'));
                            if (!$timespan || !is_int($timespan)) $timespan = 600;
                            
                            if (($timeStarted + $timespan) > time()) {
                                if (isset($postdata->verification)) {
                                    if ($postdata->verification === $storedVerification) {
                                        Respond::success();
                                    } else {
                                        Respond::error("invalid_reset_verification", "The provided reset verification token does not match the stored token.");
                                    }
                                } else {
                                    Respond::error("missing_reset_verification", "Missing the reset verification token from the postdata.");
                                }
                            } else {
                                Respond::error("request_expired", "The password reset request has expired.");
                            }
                        } else {
                            Respond::error("no_reset_available", "No password reset has yet been initialized for this user or it has already been completed.");
                        }
                    } else if ($params[0] == 'process-request') {
                        $storedVerification = $users->metaGet($info->id, "password-reset-identifier");
                        if ($storedVerification) {
                            $timeStarted = explode(':', $storedVerification)[0];
                            $storedVerification = explode(':', $storedVerification)[1];
                            $timespan = intval($policy->get('password-reset-timespan'));
                            if (!$timespan || !is_int($timespan)) $timespan = 600;
                            
                            if (($timeStarted + $timespan) > time()) {
                                if (isset($postdata->verification)) {
                                    if ($postdata->verification === $storedVerification) {
                                        if (isset($postdata->password)) {
                                            if ($users->update($info->id, array( "password" => $postdata->password ))) {
                                                $users->metaDelete($info->id, "password-reset-identifier");

                                                if ($postdata->end_sessions) {
                                                    $sessionManager = new Sessions;
                                                    foreach($sessionManager->list($info->id) as $session) {
                                                        $sessionManager->end($session->uuid);
                                                    }
                                                }

                                                Respond::success();
                                            } else {
                                                Respond::error("unknown_error", "An unknown error occured while updating your password in the database.");
                                            }
                                        } else {
                                            Respond::error("missing_password", "New password is missing.");
                                        }
                                    } else {
                                        Respond::error("invalid_reset_verification", "The provided reset verification token does not match the stored token.");
                                    }
                                } else {
                                    Respond::error("missing_reset_verification", "Missing the reset verification token from the postdata.");
                                }
                            } else {
                                Respond::error("request_expired", "The password reset request has expired.");
                            }
                        } else {
                            Respond::error("no_reset_available", "No password reset has yet been initialized for this user or it has already been completed.");
                        }
                    } else {
                        Respond::error("unknown_request", "An invalid request was made.");
                    }

                    break;
                case "REQUESTADMIN":
                    Respond::error('policy_unavailable', $this->lang->get('messages.api-auth.reset-password.error-policy_unavailable', "Unfortunately, the requested policy is currently unavailable. We are unable to reset your password at this moment."));
                    break;
                default:
                    $found = false;
                    $results = Event::trigger('custom-password-reset-policy', $resetPolicy);
                    foreach($results as $result) {
                        if (!$found && is_callable($result)) {
                            $found = true;
                            call_user_func_array($result, array($postdata, $info));
                        }
                    }

                    if (!$found) Respond::error('unknown_policy', $this->lang->get('messages.api-auth.reset-password.error-unknown_policy', "The configured password reset policy is unknown to this site. We are unable to reset your password at this moment."));
                    break;
            }
        } else {
            Respond::error('unknown_user', str_replace("{{IDENTIFIER}}", $postdata->identifier, $this->lang->get('messages.api-auth.reset-password.error-unknown_user', "A user identified by {{IDENTIFIER}} does not exist.")));
        }
    });

    $this->__registerMethod('signedin', function() {
        $session = $this->__model('session')->info(true);
        if ($session->success) {
            Respond::success(array(
                "result" => true
            ));
        } else {
            $session->result = false;
            Respond::success($session);
        }
    });

    $this->__registerMethod('authenticated', function() {
        $session = $this->__model('session')->info(false);
        if ($session->success) {
            Respond::success(array(
                "result" => true
            ));
        } else {
            $session->result = false;
            Respond::success($session);
        }
    });

    $this->__registerMethod('status', function() {
        Respond::success(array(
            "signedin" => Request::signedin(),
            "authenticated" => Request::authenticated()
        ));
    });

    $this->__registerMethod('password-policy', function() {
        $policy = new Policy;
        $passwordPolicies = new PasswordPolicies();

        $result = $policy->get('password-policy');
        $policies = (array) $passwordPolicies->properties();
        if (explode(':', $result)[0] == "CUSTOM") {
            $type = explode(':', $result)[0];
            $result = explode(':', $result)[1];
        } else {
            $type = $result;
            $result = $passwordPolicies->get($result);
        }

        Respond::success(array(
            "type" => $type,
            "policy" => $result
        ));
    });

    $this->__registerMethod('validate-password', function() {
        $users = new Users;
        $required = array("password");
        $postdata = Request::parseBody();

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required, $postdata)) die();

        if (isset($postdata->policy)) {
            $res = $users->validatePassword($postdata->password, $postdata->policy);
        } else {
            $res = $users->validatePassword($postdata->password);
        }

        $res->valid = $res->success;
        Respond::success($res);
    });

    $this->__registerMethod('account-policies', function() {
        if (!Request::requireMethod('get')) die();

        $policy = new Policy;
        Respond::success(array(
            "policies" => array(
                "usernames-enabled" => $policy->get("usernames-enabled") == 1,
                "usernames-required" => $policy->get("usernames-required") == 1,
                "usernames-minimum-length" => $policy->get("usernames-minimum-length"),
                "usernames-maximum-length" => $policy->get("usernames-maximum-length"),
                "password-policy" => $policy->get("password-policy")
            )
        ));
    });
