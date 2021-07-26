<?php
    namespace Model;

    //TODO 1: Send email verification token to user.

    class User {
        private $user = NULL;
        private $policy = NULL;
        private $token = NULL;

        public function __construct() {
            $this->user = new \Core\User;
            $this->policy = new \Core\Policy;
            $this->token = new \Core\Token;
        }

        public function authenticate($identifier, $password, $byIdAllowed = false) {
            $user = $this->find($identifier, $byIdAllowed);
            if ($user == NULL) {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_user",
                    "message" => "Deze gebruiker bestaat niet."
                );
            } else {
                if (password_verify($password, $user->password)) {
                    return (object) array(
                        "success" => true,
                        "userId" => $user->id
                    );
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "incorrect_password",
                        "message" => "De gegeven combinatie van inloggegevens is incorrect."
                    );
                }
            }
        }

        public function authenticated($clientToken = NULL) {
            if ($clientToken == NULL && isset($_COOKIE['clientToken'])) $clientToken = $_COOKIE['clientToken'];
            if ($clientToken == NULL) {
                return false;
            } else {
                $decoded = $this->token->decodeClientToken($clientToken);
                if ($decoded->success) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        public function extractToken($clientToken = NULL) {
            if ($clientToken == NULL && isset($_COOKIE['clientToken'])) $clientToken = $_COOKIE['clientToken'];
            if ($clientToken == NULL) {
                return (object) array(
                    "success" => false,
                    "error" => "no_client_token_provided",
                    "message" => "No client token has been provided, thus unable to retrieve user information."
                );
            } else {
                $decoded = $this->token->decodeClientToken($clientToken);
                if ($decoded->success) {
                    $user = $this->info($decoded->payload->id);
                    if ($user == NULL) {
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_user"
                        );
                    } else {
                        return (object) array(
                            "success" => true,
                            "info" => (object) $user
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "client_token_expired",
                        "message" => "The provided client token has expired."
                    );
                }
            }
        }

        public function privileged($user = false) {
            if (!$user) {
                $user = $this->info();
                if ($user->success) {
                    $user = $user->info;
                } else {
                    return NULL;
                }
            } else {
                $user = $this->find($user);
                if ($user == NULL) return NULL;
            }

            return $user->type == 'administrator';
        }
    }