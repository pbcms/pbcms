<?php
    namespace Controller;

    use Helper\Header;
    use Helper\Request;

    class PbDashboard extends \Library\Controller {
        public function __construct() {
            if (!Request::signedin()) {
                $router = new \Library\Router;
                $request = $router->documentRequest();
                Header::Location(SITE_LOCATION . 'pb-auth/signin?forced&followup=' . $request->url);
                die();
            }
        }

        public function Index($params) {
            Header::Location(SITE_LOCATION . 'pb-dashboard/overview');
        } 

        public function Overview($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard", array(
                "title" => "overview",
                "section" => "overview"
            ));
        } 

        public function Updates($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function Media($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function VirtualPaths($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function Users($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function Modules($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function Objects($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        } 

        public function Policies($params) {
            $this->view("dashboard/overview");
            $this->template("pb-dashboard");
        }

        public function ModuleConfig($params) {
            $modules = new \Library\Modules();

            if (isset($params[0])) {
                $res = $modules->loadConfigurator($params[0], array_slice($params, 1));
                if (!$res->success) {
                    echo $res->error;
                }
            } else {
                echo 'No module requested.';
            }

            $this->template("pb-dashboard");
        }
    }