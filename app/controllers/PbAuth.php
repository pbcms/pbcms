<?php
    namespace Controller;

    use Helper\Header;
    use Helper\Respond;
    use Helper\Request;
    use Library\Language;
    use Registry\Auth as Callback;

    class PbAuth extends \Library\Controller {
        public function __construct() {
            $this->lang = new Language;
            $this->lang->detectLanguage();
            $this->lang->load();
        }

        public function Index() {
            Header::Location(SITE_LOCATION . 'pb-auth/signin', 301);
        }

        public function Signin($params) {
            if (Request::signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->view('auth/page-signin');
            $this->template('pb-portal', array(
                "title" => $this->lang->get('pages.pb-auth.signin.title', "Signin"),
                "subtitle" => $this->lang->get('pages.pb-auth.signin.subtitle', "Signin to your account"),
                "description" => str_replace('{{SITE_TITLE}}', SITE_TITLE, $this->lang->get('pages.pb-auth.signin.description', "Signin to your account on {{SITE_TITLE}}")),
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                "body" => array(
                    ['script', 'pb-pages-auth-signin.js', array("origin" => "pubfiles")]
                )
            ));
        } 

        public function Signup($params) {
            if (Request::signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->view('auth/page-signup');
            $this->template('pb-portal', array(
                "title" => "Signup",
                "subtitle" => "Make your new account.",
                "description" => "Make a new account on " . SITE_TITLE,
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                "body" => array(
                    ['script', 'pb-pages-auth-signup.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function forgotPassword() {
            if (Request::signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->view('auth/page-forgot-password');
            $this->template('pb-portal', array(
                "title" => "Forgot your password",
                "subtitle" => "Recover your password.",
                "description" => "Recover your account for " . SITE_TITLE,
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                "body" => array(
                    ['script', 'pb-pages-auth-forgot-password.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function ResetPassword($params) {
            if (isset($params[0])) {
                
            } else {
                $this->view('auth/page-reset-password-error', array(
                    "message" => "The password reset verifier is missing from your request."
                ));

                $this->template('pb-error', array(
                    "title" => "Missing reset verifier",
                    "description" => "The password reset verifier is missing from your request",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y")
                ));
            }
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