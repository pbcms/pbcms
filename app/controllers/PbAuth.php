<?php
    namespace Controller;

    use Helper\Header;
    use Helper\Respond;
    use Registry\Auth as Callback;

    class PbAuth extends \Library\Controller {
        public function Index() {
            Header::Location(SITE_LOCATION . 'pb-auth/signin', 301);
        }

        public function Signin($params) {
            $this->view('auth/page-signin');
            $this->template('pb-portal', array(
                "title" => "Signin",
                "subtitle" => "Signin to your account.",
                "description" => "Sign into your account on " . SITE_TITLE,
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y")
            ));
        } 

        public function Signup($params) {
            $this->view('auth/page-signup');
            $this->template('pb-portal', array(
                "title" => "Signup",
                "subtitle" => "Make your new account.",
                "description" => "Make a new account on " . SITE_TITLE,
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y")
            ));
        }

        public function Signout($params) {
            Header::Location(SITE_LOCATION);
        }

        public function Callback($params) {
            if (isset($params[0])) {
                if (Callback::exists($params[0])) {
                    Callback::call($params[0], array_slice($params, 1));
                } else {
                    http_response_code(404);
                    $this->view('auth/auth-options');
                    $this->template('pb-portal', array(
                        "title" => "Unknown callback",
                        "subtitle" => "An unknown callback was requested!",
                        "description" => "Unknown callback requested!",
                        "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                        "meta" => array(
                            "robots" => "noindex, nofollow"
                        )
                    ));
                }
            } else {
                http_response_code(400);
                $this->view('auth/auth-options');
                $this->template('pb-portal', array(
                    "title" => "Invalid request",
                    "subtitle" => "No callback was requested!",
                    "description" => "No callback requested!",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                    "meta" => array(
                        "robots" => "noindex, nofollow"
                    )
                ));
            }
        }

        public function __error($code) {
            http_response_code($code);
            $this->view('auth/auth-options');

            if ($code == 404) {
                $this->template('pb-portal', array(
                    "title" => "Unknown action",
                    "subtitle" => "An unknown action was requested!",
                    "description" => "Unknown action requested!",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                    "meta" => array(
                        "robots" => "noindex, nofollow"
                    )
                ));
            } else {
                $this->template('pb-portal', array(
                    "title" => "Error $code",
                    "subtitle" => "Error $code has occured!",
                    "description" => "Error $code.",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                    "meta" => array(
                        "robots" => "noindex, nofollow"
                    )
                ));
            }
        }
    }