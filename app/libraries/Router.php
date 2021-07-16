<?php
    namespace Library;

    class Router {
        protected $preferredLanguage = NULL;
        protected $virtualPath = NULL;
        protected $currentController;

        protected $controller = 'System';
        protected $method = 'Index';
        protected $params = [];
        protected $url = '/';
        protected $db;

        private $executed = false;

        public function __construct($url = '') {
            $this->db = new Database;
            if (isset($_GET['url'])) $this->url = $_GET['url'];
            if ($url != '') $this->url = $url;

            $this->validateUrl();
            $this->fillProperties();
        }

        public function refactorRequest($url) {
            if ($this->executed) return false;
            
            $this->preferredLanguage = NULL;
            $this->virtualPath = NULL;
            $this->controller = 'System';
            $this->method = 'Index';
            $this->params = [];
            $this->url = $url;

            $this->validateUrl();
            $this->fillProperties();
            $this->processRequest();
            return true;
        }

        public function langPref() {
            return $this->preferredLanguage;
        }

        public function virtPath() {
            return $this->virtualPath;
        }

        public function documentRequest() {
            return (object) array(
                "controller" => $this->controller,
                "method" => $this->method,
                "params" => $this->params,
                "url" => $this->url,

                "preferred-language" => $this->preferredLanguage,
                "virtual-path" => $this->virtualPath
            );
        }

        public function processRequest($virtualIndex = 0) {
            if ($this->executed) return false;

            $this->loadController($this->controller);
            $class = 'Controller\\' . $this->controller;
            $this->currentController = new $class;

            if (method_exists($this->currentController, $this->prepareFunctionNaming($this->method))) {
                return;
            } else {
                $virtualPaths = $this->matchVirtualPath();
                if (count($virtualPaths) > 0 && isset($virtualPaths[$virtualIndex])) {
                    $this->controller = 'System';
                    $this->method = 'Index';
                    $this->params = [];

                    $virtual = $virtualPaths[$virtualIndex]['path'];
                    $target = $virtualPaths[$virtualIndex]['target'];
                    $rest = array_slice($this->processUrl($this->url), count($this->processUrl($virtual)));
                    $final = join('/', $this->processUrl($target)) . '/' . join('/', $rest);

                    $this->virtualPath = $virtualPaths[$virtualIndex]['id'];
                    $this->preferredLanguage = $virtualPaths[$virtualIndex]['lang'];
                    \Registry\Store::set('router-virtual-path', $virtualPaths[$virtualIndex]['id']);
                    \Registry\Store::set('router-preferred-language', $virtualPaths[$virtualIndex]['lang']);
                    $this->fillProperties($final);
                    $this->processRequest($virtualIndex + 1);
                } else {
                    $this->method = NULL;
                    $this->virtualPath = NULL;
                    $this->preferredLanguage = NULL;
                    \Registry\Store::delete('router-virtual-path');
                    \Registry\Store::delete('router-virtual-path');
                    return;
                }
            }
        }

        public function executeRequest() {
            if ($this->executed) return false;
            $this->executed = true;
            if (!$this->method) {
                if (method_exists($this->currentController, '__error')) {
                    $this->currentController->__error(404);
                } else {
                    $this->errorRoute(404);
                }
            } else {
                $this->currentController->{$this->prepareFunctionNaming($this->method)}($this->params);
            }            
        }

        private function fillProperties($orig = '') {
            if ($this->executed) return false;
            $index = 0;
            if ($orig == '') $orig = $this->url;
            $url = $this->processUrl($orig);
            if (count($url) < 1) return NULL;

            foreach($url as $segment) if (substr($segment, 0, 2) == '__') $this->errorRoute(403);

            if ($this->controllerExists($url[$index])) {
                $this->controller = $this->prepareFunctionNaming($url[$index]);
                unset($url[$index]);
                $index++;
            }

            if (count($url) < 1) return;
            $this->method = $this->prepareFunctionNaming($url[$index]);
            unset($url[$index]);
            $index++;
            $this->params = array_values($url);
            return;
        }
        
        public function matchVirtualPath($orig = '') {
            if ($orig == '') $orig = $this->url;
            $url = $this->processUrl($orig);

            $sql = "SELECT * FROM `" . DATABASE_TABLE_PREFIX . "virtual-paths` WHERE";

            if (count($url) == 0) {
                $sql .= " `path`='/'";
            }

            while(count($url) > 0) {
                $sql .= " `path`='" . join('/', $url) . "'";
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
            if ($orig == '' || !$orig) $orig = $this->url;
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
            if ($orig == '' || !$orig) $orig = $this->url;
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
            $translations = $this->urlTranslations($orig, $lang);
            if (count($translations) == 0) return $orig;
            return $translations[0]['translation'];
        }

        public function processUrl($url) {
            $url = explode('/', $url);
            $url = array_filter($url);
            return $url;
        }

        private function validateUrl() {
            if (substr($this->url, 0, 1) == '/' && strlen($this->url) > 1) {
                $this->url = substr($this->url, 1);
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
            return file_exists(APP_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php');
        }

        public function loadController($controller) {
            require_once APP_DIR . '/controllers/' . $this->prepareFunctionNaming($controller) . '.php';
        }

        public function errorRoute($error) {
            if ($this->executed) return false;
            $this->executed = true;
            $this->loadController('System');

            $errorController = new \Controller\PbError;
            $errorController->Display($error);
            die;
        }
    }