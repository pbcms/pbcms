<?php
    namespace Controller;

    use Helper\Header;
    use Helper\Respond;
    use Helper\Request;
    use Library\Language;
    use Registry\Auth as AuthPlugin;

    class PbAuth extends \Library\Controller {
        private $lang;
        private $user;

        public function __construct() {
            $this->user = $this->__model('user');
            $this->lang = new Language;
            $this->lang->detectLanguage();
            $this->lang->load();
        }

        public function __index() {
            Header::Location(SITE_LOCATION . 'pb-auth/signin', 301);
        }

        public function Signin($params) {
            if ($this->user->signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->__view('auth/page-signin');
            $this->__template('pb-portal', array(
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
            if ($this->user->signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->__view('auth/page-signup');
            $this->__template('pb-portal', array(
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
            if ($this->user->signedin()) {
                Header::Location(SITE_LOCATION . (isset($_GET['followup']) ? $_GET['followup'] : 'pb-dashboard'));
                die();
            }

            $this->__view('auth/page-forgot-password');
            $this->__template('pb-portal', array(
                "title" => $this->lang->get('pages.pb-auth.forgot-password.title', "Forgot password"),
                "subtitle" => $this->lang->get('pages.pb-auth.forgot-password.subtitle', "Reset your password."),
                "description" => str_replace('{{SITE_TITLE}}', SITE_TITLE, $this->lang->get('pages.pb-auth.forgot-password.description', "Reset your password on {{SITE_TITLE}}")),
                "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                "body" => array(
                    ['script', 'pb-pages-auth-forgot-password.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function ResetPassword($params) {


            if (Request::method() == "POST") {
                if (!isset($params[0])) Respond::error("missing_verifier", "The password reset verifier is missing from your request.");
            } else {
                if (!isset($params[0])) {
                    $this->__view('auth/page-reset-password-error', array(
                        "message" => "The password reset verifier is missing from your request."
                    ));

                    $this->__template('pb-error', array(
                        "title" => "Missing reset verifier.",
                        "description" => "The password reset verifier is missing from your request.",
                        "copyright" => "&copy; " . SITE_TITLE . " " . date("Y")
                    ));
                } else {
                    
                    $this->__view('auth/page-reset-password');
                    $this->__template('pb-error', array(
                        "title" => "Reset your password",
                        "description" => "Reset your password.",
                        "copyright" => "&copy; " . SITE_TITLE . " " . date("Y")
                    ));
                }
            }
        }

        public function Signout($params) {
            Header::Location(SITE_LOCATION);
        }

        public function Plugin($params) {
            if (isset($params[0])) {
                if (AuthPlugin::exists($params[0])) {
                    AuthPlugin::call($params[0], array_slice($params, 1));
                } else {
                    http_response_code(404);
                    $this->__view('auth/auth-options');
                    $this->__template('pb-portal', array(
                        "title" => "Unknown plugin",
                        "subtitle" => "An unknown authentication plugin was requested!",
                        "description" => "Unknown plugin requested!",
                        "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                        "meta" => array(
                            "robots" => "noindex, nofollow"
                        )
                    ));
                }
            } else {
                http_response_code(400);
                $this->__view('auth/auth-options');
                $this->__template('pb-portal', array(
                    "title" => "Invalid request",
                    "subtitle" => "No authentication plugin was requested!",
                    "description" => "No plugin requested!",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                    "meta" => array(
                        "robots" => "noindex, nofollow"
                    )
                ));
            }
        }

        public function __error($code) {
            http_response_code($code);
            $this->__view('auth/auth-options');

            if ($code == 404) {
                $this->__template('pb-portal', array(
                    "title" => "Unknown action",
                    "subtitle" => "An unknown action was requested!",
                    "description" => "Unknown action requested!",
                    "copyright" => "&copy; " . SITE_TITLE . " " . date("Y"),
                    "meta" => array(
                        "robots" => "noindex, nofollow"
                    )
                ));
            } else {
                $this->__template('pb-portal', array(
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