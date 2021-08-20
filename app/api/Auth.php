<?php
    use Library\Policy;
    use Library\Users;
    use Library\Token;
    use Library\Sessions;
    use Library\Database;
    use Library\PasswordPolicies;
    use Helper\ApiResponse as Respond;
    use Helper\Request as Request;
    use Helper\Validate as Validate;

    if (isset($params[0])) {
        switch ($params[0]) {
            case 'create-session': 
                $db = new Database();
                $required = array("identifier", "password");
                $postdata = Request::parsePost();

                if (!Request::requireMethod('post')) die();
                if (!Request::requireData($required)) die();

                $users = new Users;
                $info = $users->find($postdata->identifier, false);
                if ($info) {
                    if (password_verify($postdata->password, $info->password)) {
                        $tokens = new Token;
                        $sessions = new Sessions;
                        $policy = new Policy;

                        if ($info->status == "LOCKED") {
                            Respond::error('user_locked', "The user you are trying to create a session for has been locked by the system or an administrator.");
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
                            Respond::success();
                        } else {
                            Respond::error($token->error, "An error occured while creating the refresh-token");
                        }
                    } else {
                        Respond::error('invalid_password', "An invalid password has been provided for the user identified by $postdata->identifier.");
                    }
                } else {
                    Respond::error('unknown_user', "A user identified by $postdata->identifier does not exist.");
                }
                
                break;
            case 'access-token':
                if (isset($_COOKIE['pb-refresh-token'])) {
                    $token = new Token;
                    $decoded = $token->decode('refresh-token', $_COOKIE['pb-refresh-token']);
                    if ($decoded->success) {
                        $sessions = new Sessions;
                        $session = $sessions->info($decoded->payload->session);
                        if ($session) {
                            if ($session->expired) {
                                Respond::error("session_expired", "The requested session has since expired.");
                            } else {
                                $users = new Users;
                                $user = $users->info($session->user);

                                if ($user->success) {
                                    if ($user->status == "LOCKED") {
                                        Respond::error('user_locked', "The user you are trying to request an access token for has been locked by the system or an administrator.");
                                        die();
                                    }

                                    $sessions->refresh($session->uuid);
                                    $refreshToken = $token->create('refresh-token', array("session" => $decoded->payload->session), (isset($decoded->expirationTime) ? $decoded->expirationTime : NULL));
                                    $accessToken = $token->create('access-token', array("session" => $decoded->payload->session), 3600);

                                    if ($refreshToken->success) {
                                        $secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? true : false);
                                        $url = parse_url(SITE_LOCATION);
                                        setcookie('pb-refresh-token', $refreshToken->token, 2147483647, $url['path'], $url['host'], $secure, true);
                                    }

                                    if ($accessToken->success) {
                                        Respond::success($accessToken);
                                    } else {
                                        Respond::error($accessToken->error, "An error occured while creating the access-token");
                                    }
                                } else {
                                    Respond::error('unknown_user', "The user you are trying to request an access token for does not exist anymore.");
                                    die();
                                }
                            }
                        } else {
                            Respond::error("unknown_session", "The requested session does not exist.");
                        }
                    } else {
                        Respond::error($decoded->error, "An error occured while decoding the refresh token.");
                    }
                } else {
                    Respond::error('missing_refresh_token', "No refresh token present.");    
                }

                break;
            case 'authenticated':
                

                break;
            case 'password-policy':
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

                break;
            case 'validate-password':
                $users = new Users;
                $required = array("password");
                $postdata = Request::parsePost();

                if (!Request::requireMethod('post')) die();
                if (!Request::requireData($required)) die();

                if (isset($postdata->policy)) {
                    $res = $users->validatePassword($postdata->password, $postdata->policy);
                } else {
                    $res = $users->validatePassword($postdata->password);
                }

                $res->valid = $res->success;
                Respond::success($res);

                break;
        }
    }
