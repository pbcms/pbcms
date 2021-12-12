<?php
    namespace Model;

    use \Library\Users;
    use \Library\Token;
    use \Library\Sessions;
    use \Helper\Header;

    class Session {
        private $users;
        private $token;
        private $sessions;

        public function __construct() {
            $this->users = new Users;
            $this->token = new Token;
            $this->sessions = new Sessions;
        }

        public function info($fromRefreshToken = false) {
            if ($fromRefreshToken) {
                if (isset($_COOKIE['pb-refresh-token'])) {
                    $decoded = $this->token->decode('refresh-token', $_COOKIE['pb-refresh-token']);
                    if ($decoded->success) {
                        $sessionUUID = $decoded->payload->session;
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => $decoded->error,
                            "message" => "An error occured while decoding the refresh token."
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "missing_refresh_token",
                        "message" => "No refresh token present."
                    );
                }
            } else {
                $headers = Header::Authorization();
                if (!empty($headers)) {
                    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                        $decoded = $this->token->decode('access-token', $matches[1]);
                        if ($decoded->success) {
                            $sessionUUID = $decoded->payload->session;
                        } else {
                            return (object) array(
                                "success" => false,
                                "error" => $decoded->error,
                                "message" => "An error occured while decoding the access token."
                            );
                        }
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "invalid_authorization_header",
                            "message" => "Invalid authorization header present, expecting Bearer token."
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "missing_authorization_header",
                        "message" => "No authorization header present."
                    );
                }
            }
            
            $session = $this->sessions->info($sessionUUID);
            if ($session) {
                if ($session->expired) {
                    return (object) array(
                        "success" => false,
                        "error" => "session_expired",
                        "message" => "The requested session has since expired."
                    );
                } else {
                    $user = $this->users->info($session->user);
                    if ($user != NULL) {
                        if ($user->status == "LOCKED") return (object) array(
                            "success" => false,
                            "error" => "user_locked",
                            "message" => "The user you are trying to request an access token for has been locked by the system or an administrator."
                        );

                        return (object) array(
                            "success" => true,
                            "info" => $session,
                            "user" => $user
                        );
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_user",
                            "message" => "The user you are trying to request an access token for does not exist anymore."
                        );
                    }
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_session",
                    "message" => "The requested session does not exist."
                );
            }
        }
    }