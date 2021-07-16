<?php
    namespace Controller;

    class System extends \Library\Controller {
        public function PbDashboard($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        }

        public function PbAuth($params) {
            
        }

        public function PbApi($params) {
            if (isset($params[0])) {
                $api = $params[0];
                array_shift($params);

                if (\Registry\Api::exists($api)) {
                    \Registry\Api::call($api, $params);
                } else {
                    \Helper\Respond::JSON((object) array(
                        "success" => false,
                        "error" => "unknown_api",
                        "message" => "The requested API does not exist."
                    ));
                }
            } else {
                \Helper\Respond::JSON((object) array(
                    "success" => false,
                    "error" => "missing_api",
                    "message" => "No API has been requested."
                ));
            }
        }

        public function Index($params) {
            echo 'This is my website';
        }

        public function Token() {
            $token = new \Library\Token;
            echo '<pre>';

            $u = new \Library\Users;
            print_r(\Helper\Json::encode($u->validatePassword('Password1!!!', 'STRONG')));
        }
    }

    class PbError extends \Library\Controller {
        public function Display($error) {
            $lang = new \Library\Language();
            $lang->detectLanguage();
            $lang->load();

            $this->view('pages/error', array(
                "errorCode" => $error,
                "errorMessage" => ($lang->get("error-pages.messages." . $error) ? $lang->get("error-pages.messages." . $error) : $lang->get("error-pages.messages.0")),
                "errorShort" => ($lang->get("error-pages.short." . $error) ? $lang->get("error-pages.short." . $error) : $lang->get("error-pages.short.0"))
            ));

            $this->template('pb-default', array(
                "title" => (isset($short[$error]) ? $short[$error] : 'Onbekende fout') . " - Birkje.nl",
                "head" => '<link rel="stylesheet" href="' . SITE_LOCATION . 'pb-pubfiles/css/siteFront_errorPage.css">',
                "scripts" => '<script src="' . SITE_LOCATION . 'pb-pubfiles/js/siteFront_errorPage.js"></script>',
                "navbarNoShadow" => true
            ));
        }
    }