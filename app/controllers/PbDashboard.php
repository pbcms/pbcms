<?php
    namespace Controller;

    use Library\Language;
    use Helper\Header;
    use Helper\Request;

    class PbDashboard extends \Library\Controller {
        public function __construct() {
            $this->session = $this->__model('session')->info(true);
            if (!$this->session->success) {
                $router = new \Library\Router;
                $request = $router->documentRequest();
                Header::Location(SITE_LOCATION . 'pb-auth/signin?forced&followup=' . $request->url);
                die();
            }

            $this->lang = new Language();
            $this->lang->detectLanguage();
            $this->lang->load();
        }

        public function __index($params) {
            Header::Location(SITE_LOCATION . 'pb-dashboard/overview');
        } 

        public function Overview($params) {
            $this->__view("dashboard/overview");
            $this->__template("pb-dashboard", array(
                "title" => "overview",
                "section" => "overview"
            ));
        } 

        public function Updates($params) {
            $this->__view("dashboard/updates");
            $this->__template("pb-dashboard", array(
                "title" => "updates",
                "section" => "updates"
            ));
        } 

        public function Media($params) {
            $this->__view("dashboard/media");
            $this->__template("pb-dashboard", array(
                "title" => "media",
                "section" => "media"
            ));
        } 

        public function VirtualPaths($params) {
            $this->__view("dashboard/virtual-paths");
            $this->__template("pb-dashboard", array(
                "title" => "virtual-paths",
                "section" => "virtual-paths"
            ));
        } 

        public function Profile($params) {
            $this->__view("dashboard/profile");
            $this->__template("pb-dashboard", array(
                "title" => "profile",
                "section" => "profile"
            ));
        } 

        public function Users($params) {
            $this->__view("dashboard/users");
            $this->__template("pb-dashboard", array(
                "title" => "users",
                "section" => "users"
            ));
        } 

        public function Modules($params) {
            $this->__view("dashboard/modules");
            $this->__template("pb-dashboard", array(
                "title" => "modules",
                "section" => "modules"
            ));
        } 

        public function Roles($params) {
            $this->__view("dashboard/roles");
            $this->__template("pb-dashboard", array(
                "title" => "roles",
                "section" => "roles"
            ));
        } 

        public function Permissions($params) {
            $this->__view("dashboard/permissions");
            $this->__template("pb-dashboard", array(
                "title" => "permissions",
                "section" => "permissions"
            ));
        } 

        public function Objects($params) {
            $this->__view("dashboard/objects");
            $this->__template("pb-dashboard", array(
                "title" => "objects",
                "section" => "objects"
            ));
        } 

        public function Policies($params) {
            $this->__view("dashboard/policies");
            $this->__template("pb-dashboard", array(
                "title" => "policies",
                "section" => "policies",
                "body" => array(
                    ['script', 'pb-pages-dashboard-policies.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function ModuleConfig($params) {
            $modules = new \Library\Modules();

            if (isset($params[0])) {
                $res = $modules->loadConfigurator($params[0], array_slice($params, 1));
                if (!$res->success) {
                    echo $res->error;
                }

                $this->__template("pb-dashboard", array(
                    "title" => "module - " . $params[0],
                    "section" => "module-config-" . $params[0]
                ));
            } else {
                echo 'No module requested.';
                $this->__template("pb-dashboard", array(
                    "title" => "unknown module" . $params,
                    "section" => "module-config-" . $params[0]
                ));
            }
        }
    }