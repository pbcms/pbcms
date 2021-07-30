<?php
    namespace Library;

    use Registry\Store;

    class Router {
        protected static $preferredLanguage = NULL;
        protected static $virtualPath = NULL;
        protected static $currentController;

        protected static $controller = 'System';
        protected static $method = 'Index';
        protected static $params = [];
        protected static $url = '/';
        protected $db;

        private static $executed = false;
        private static $initialized = false;

        public function __construct($url = '') {
            $this->db = new Database;
            if (!self::$initialized) {
                self::$initialized = true;
                if (isset($_GET['url'])) self::$url = $_GET['url'];
                if ($url != '') self::$url = $url;

                $this->validateUrl();
                $this->fillProperties();
            }
        }

        public function refactorRequest($url) {
            if (self::$executed) return false;
            if (self::$controller == 'System') {
                if (self::$method == 'PbApi' || self::$method == 'PbDashboard' || self::$method == 'PbAuth') {
                    return false;
                }
            } else if (self::$controller == 'PbLoader') {
                return false;
            }
            
            self::$preferredLanguage = NULL;
            self::$virtualPath = NULL;
            self::$controller = 'System';
            self::$method = 'Index';
            self::$params = [];
            self::$url = $url;

            $this->validateUrl();
            $this->fillProperties();
            $this->processRequest();
            return true;
        }

        public function langPref() {
            return self::$preferredLanguage;
        }

        public function virtPath() {
            return self::$virtualPath;
        }

        public function documentRequest() {
            return (object) array(
                "controller" => self::$controller,
                "method" => self::$method,
                "params" => self::$params,
                "url" => self::$url,

                "preferred-language" => self::$preferredLanguage,
                "virtual-path" => self::$virtualPath
            );
        }

        public function processRequest($virtualIndex = 0) {
            if (self::$executed) return false;

            $this->loadController(self::$controller);
            $class = 'Controller\\' . self::$controller;
            self::$currentController = new $class;

            if (method_exists(self::$currentController, $this->prepareFunctionNaming(self::$method))) {
                return;
            } else {
                $virtualPaths = $this->matchVirtualPath();
                if (count($virtualPaths) > 0 && isset($virtualPaths[$virtualIndex])) {
                    self::$controller = 'System';
                    self::$method = 'Index';
                    self::$params = [];

                    $virtual = $virtualPaths[$virtualIndex]['path'];
                    $target = $virtualPaths[$virtualIndex]['target'];
                    $rest = array_slice($this->processUrl(self::$url), count($this->processUrl($virtual)));
                    $final = join('/', $this->processUrl($target)) . '/' . join('/', $rest);

                    self::$virtualPath = $virtualPaths[$virtualIndex]['id'];
                    self::$preferredLanguage = $virtualPaths[$virtualIndex]['lang'];
                    Store::set('router-virtual-path', $virtualPaths[$virtualIndex]['id']);
                    Store::set('router-preferred-language', $virtualPaths[$virtualIndex]['lang']);
                    $this->fillProperties($final);
                    $this->processRequest($virtualIndex + 1);
                } else {
                    self::$method = NULL;
                    self::$virtualPath = NULL;
                    self::$preferredLanguage = NULL;
                    Store::delete('router-virtual-path');
                    Store::delete('router-virtual-path');
                    return;
                }
            }
        }

        public function executeRequest() {
            if (self::$executed) return false;
            self::$executed = true;
            if (!self::$method) {
                if (method_exists(self::$currentController, '__error')) {
                    self::$currentController->__error(404);
                } else {
                    $this->errorRoute(404);
                }
            } else {
                self::$currentController->{$this->prepareFunctionNaming(self::$method)}(self::$params);
            }            
        }

        private function fillProperties($orig = '') {
            if (self::$executed) return false;
            $index = 0;
            if ($orig == '') $orig = self::$url;
            $url = $this->processUrl($orig);
            if (count($url) < 1) return NULL;

            foreach($url as $segment) if (substr($segment, 0, 2) == '__') $this->errorRoute(403);

            if ($this->controllerExists($url[$index])) {
                self::$controller = $this->prepareFunctionNaming($url[$index]);
                unset($url[$index]);
                $index++;
            }

            if (count($url) < 1) return;
            self::$method = $this->prepareFunctionNaming($url[$index]);
            unset($url[$index]);
            $index++;
            self::$params = array_values($url);
            return;
        }
        
        public function matchVirtualPath($orig = '') {
            if ($orig == '') $orig = self::$url;
            $url = $this->processUrl($orig);

            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE";

            if (count($url) == 0) {
                $sql .= " `path`='/'";
            }

            while(count($url) > 0) {
                $sql .= " `path`='" . join('/', $url) . "'";
                $sql .= " OR `path`='/" . join('/', $url) . "'";
                $sql .= " OR `path`='/" . join('/', $url) . "/'";
                $sql .= " OR `path`='" . join('/', $url) . "/'";
                $url = array_slice($url, 0, -1);

                if (count($url) > 0) {
                    $sql .= ' OR';
                }
            }

            $result = $this->db->query($sql);
            $fetched = $result->fetch_all(MYSQLI_ASSOC);

            usort($fetched, function($a, $b) {
                return strlen($b['path']) <=> strlen($a['path']);
            });

            return $fetched;
        }

        public function matchReversedVirtualPath($orig = '', $lang = '') {
            if ($orig == '' || !$orig) $orig = self::$url;
            $url = $this->processUrl($orig);
            if (count($url) < 1) return array();

            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE (";

            while(count($url) > 0) {
                $sql .= " `target`='" . join('/', $url) . "'";
                $url = array_slice($url, 0, -1);

                if (count($url) > 0) {
                    $sql .= ' OR';
                }
            }

            $sql .= ')';

            if ($lang !== '') {
                $sql .= " AND `lang`='${lang}'";
            }

            $result = $this->db->query($sql);
            $fetched = $result->fetch_all(MYSQLI_ASSOC);

            usort($fetched, function($a, $b) {
                return strlen($b['path']) <=> strlen($a['path']);
            });

            return $fetched;
        }

        public function urlTranslations($orig = '', $lang = '') {
            if ($orig == '' || !$orig) $orig = self::$url;
            $url = $this->processUrl($orig);
            if (count($url) < 1) return array();

            $matches = $this->matchReversedVirtualPath($orig, $lang);
            $results = array();

            foreach($matches as $match) {
                $virtual = $match['path'];
                $rest = array_slice($url, count($this->processUrl($virtual)));
                $match['translation'] = join('/', $this->processUrl($virtual)) . '/' . join('/', $this->processUrl(join('/', $rest)));
                array_push($results, $match);
            }

            return $results;
        }

        public function attemtUrlTranslation($orig = '', $lang = '') {
            $translations = self::$urlTranslations($orig, $lang);
            if (count($translations) == 0) return $orig;
            return $translations[0]['translation'];
        }

        public function processUrl($url) {
            $url = explode('/', $url);
            $url = array_filter($url);
            return $url;
        }

        private function validateUrl() {
            if (substr(self::$url, 0, 1) == '/' && strlen(self::$url) > 1) {
                self::$url = substr(self::$url, 1);
            }
        }

        public function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }

        public function controllerExists($controller) {
            if (\strtolower($controller) == 'error') $controller = '';
            if (file_exists(APP_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function loadController($controller) {
            if (file_exists(APP_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php')) {
                require_once APP_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php';
            } else if (file_exists(DYNAMIC_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php')) {
                require_once DYNAMIC_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php';
            } else {
                return false;
            }
            
        }

        public function errorRoute($error) {
            if (self::$executed) return false;
            self::$executed = true;
            $this->loadController('System');

            $errorController = new \Controller\PbError;
            $errorController->Display($error);
            die;
        }
    }