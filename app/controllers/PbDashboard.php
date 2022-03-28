<?php
    namespace Controller;

    use Library\Users;
    use Library\Modules;
    use Library\ModuleManager;
    use Library\Language;
    use Library\Policy;
    use Helper\Header;
    use Helper\Request;
    use Registry\Store;

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

            $this->user = $this->__model('user');
            if (!$this->user->check('site.dashboard')) {
                $policy = new Policy;
                $alternative = $policy->get('alternative-dashboard');
                if ($alternative) {
                    if (substr($alternative, 0, 1) == '/') $alternative = substr($alternative, 1);
                    Header::Location(SITE_LOCATION . $alternative);
                    die();
                } else {
                    $this->__displayError(403);
                }
            }
        }

        private function __useTemplate() {
            if (Store::get('pb-dashboard-template')) {
                return Store::get('pb-dashboard-template');
            } else {
                return 'pb-dashboard';
            }
        }

        public function __index($params) {
            Header::Location(SITE_LOCATION . 'pb-dashboard/overview');
        } 

        public function Overview($params) {
            $this->__view("dashboard/overview");
            $this->__template($this->__useTemplate(), array(
                "title" => "overview",
                "section" => "overview",
                "body" => array(
                    ['script', 'pb-pages-dashboard-overview.js', array("origin" => "pubfiles")]
                )
            ));
        } 

        public function Updates($params) {
            $this->__view("dashboard/updates");
            $this->__template($this->__useTemplate(), array(
                "title" => "updates",
                "section" => "updates",
                "head" => array(
                    ["style", "pb-pages-dashboard-updates.css", array("origin" => "pubfiles")],
                ),
                "body" => array(
                    ['script', 'pb-pages-dashboard-updates.js', array("origin" => "pubfiles", "properties" => "type=\"module\"")]
                )
            ));
        } 

        public function Media($params) {
            $this->__view("dashboard/media");
            $this->__template($this->__useTemplate(), array(
                "title" => "media",
                "section" => "media"
            ));
        } 

        public function VirtualPaths($params) {
            $this->__view("dashboard/virtual-paths");
            $this->__template($this->__useTemplate(), array(
                "title" => "virtual-paths",
                "section" => "virtual-paths"
            ));
        } 

        public function Profile($params) {
            $this->__view("dashboard/profile");
            $this->__template($this->__useTemplate(), array(
                "title" => "profile",
                "section" => "profile",
                "head" => array(
                    ["style", "pb-pages-dashboard-profile.css", array("origin" => "pubfiles")],
                    ["style", "pbcms-system-pages.css", array("origin" => "pubfiles")]
                ),
                "body" => array(
                    ['script', 'pb-pages-dashboard-profile.js', array("origin" => "pubfiles", "properties" => "type=\"module\"")]
                )
            ));
        } 

        public function Users($params) {
            if (isset($params[0])) {
                $users = new Users;
                $user = $users->info(intval($params[0]));
                if ($user) {
                    $this->__view("dashboard/view-user", array("user" => $user));
                    $this->__template($this->__useTemplate(), array(
                        "title" => "User - " . $user->fullname,
                        "section" => "users",
                        "head" => array(
                            ['style', 'pb-pages-dashboard-view-user.css', array("origin" => "pubfiles")]
                        ),
                        "body" => array(
                            ['script', 'pb-pages-dashboard-view-user.js', array("origin" => "pubfiles")]
                        )
                    ));
                } else {
                    $this->__view("dashboard/unknown-user");
                    $this->__template($this->__useTemplate(), array(
                        "title" => "Unknown user",
                        "section" => "users"
                    ));
                }
            } else {
                $this->__view("dashboard/users");
                $this->__template($this->__useTemplate(), array(
                    "title" => "users",
                    "section" => "users",
                    "body" => array(
                        ['script', 'pb-pages-dashboard-users.js', array("origin" => "pubfiles")]
                    )
                ));
            }
        } 

        public function Modules($params) {
            function getParameter($item, $param, $alternative = null) {
                $local = ($item->local && isset(((array) $item->local)[$param]) ? ((array) $item->local)[$param] : null);
                $repo = ($item->repo && isset(((array) $item->repo)[$param]) ? ((array) $item->repo)[$param] : null);
                return ($local ? $local : ($repo ? $repo : $alternative));
            }

            if (isset($params[0])) {
                $modman = new ModuleManager;
                $module = $modman->moduleSummary($params[0]);
                if ($module) {
                    $this->__view("dashboard/view-module", array("module" => $module));
                    $this->__template($this->__useTemplate(), array(
                        "title" => "Module - " . getParameter($module, 'name', $module->module),
                        "section" => "modules",
                        "head" => array(
                            ['style', 'pb-pages-dashboard-view-module.css', array("origin" => "pubfiles")]
                        ),
                        "body" => array(
                            ['script', 'pb-pages-dashboard-view-module.js', array("origin" => "pubfiles")]
                        )
                    ));
                } else {
                    $this->__view("dashboard/unknown-module");
                    $this->__template($this->__useTemplate(), array(
                        "title" => "Unknown module",
                        "section" => "modules"
                    ));
                }
            } else {
                $this->__view("dashboard/modules");
                $this->__template($this->__useTemplate(), array(
                    "title" => "modules",
                    "section" => "modules",
                    "head" => array(
                        ['style', 'pb-pages-dashboard-modules.css', array("origin" => "pubfiles")]
                    ),
                    "body" => array(
                        ['script', 'pb-pages-dashboard-modules.js', array("origin" => "pubfiles")]
                    )
                ));
            }
        } 

        public function Roles($params) {
            $this->__view("dashboard/roles");
            $this->__template($this->__useTemplate(), array(
                "title" => "roles",
                "section" => "roles",
                "body" => array(
                    ['script', 'pb-pages-dashboard-roles.js', array("origin" => "pubfiles", "properties" => 'type="module"')]
                )
            ));
        } 

        public function Permissions($params) {
            $this->__view("dashboard/permissions");
            $this->__template($this->__useTemplate(), array(
                "title" => "permissions",
                "section" => "permissions",
                "body" => array(
                    ['script', 'pb-pages-dashboard-permissions.js', array("origin" => "pubfiles", "properties" => 'type="module"')]
                )
            ));
        } 

        public function Objects($params) {
            $this->__view("dashboard/objects");
            $this->__template($this->__useTemplate(), array(
                "title" => "objects",
                "section" => "objects"
            ));
        } 

        public function Policies($params) {
            $this->__view("dashboard/policies");
            $this->__template($this->__useTemplate(), array(
                "title" => "policies",
                "section" => "policies",
                "body" => array(
                    ['script', 'pb-pages-dashboard-policies.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function Shortcuts($params) {
            $this->__view("dashboard/shortcuts");
            $this->__template($this->__useTemplate(), array(
                "title" => "shortcuts",
                "section" => "shortcuts",
                "head" => array(
                    ['style', 'pb-pages-dashboard-shortcuts.css', array("origin" => "pubfiles")]
                ),
                "body" => array(
                    ['script', 'pb-pages-dashboard-shortcuts.js', array("origin" => "pubfiles")]
                )
            ));
        }

        public function ModuleConfig($params) {
            $modules = new Modules;

            if (isset($params[0])) {
                $res = $modules->loadConfigurator($params[0], array_slice($params, 1));
                if (!$res->success) {
                    echo $res->error;
                }

                $this->__template($this->__useTemplate(), array(
                    "title" => "module - " . $params[0],
                    "section" => "module-config-" . $params[0],
                    "backup_section" => "modules"
                ));
            } else {
                echo 'No module requested.';
                $this->__template($this->__useTemplate(), array(
                    "title" => "unknown module" . $params,
                    "section" => "module-config-" . $params[0],
                    "backup_section" => "modules"
                ));
            }
        }
    }