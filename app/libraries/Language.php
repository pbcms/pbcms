<?php
    namespace Library;

    use Helper\JSON;
    use Library\Policy;
    use Registry\Action;

    class Language {
        private $loaded = NULL;
        protected $language = NULL;
        protected static $defaultlang = NULL;
        protected static $list = array();
        private static $inmemory = array();

        public function __construct() {
            if (!self::$defaultlang) {
                $policy = new Policy;
                self::$defaultlang = $policy->get('default-language');
                if (!self::$defaultlang) self::$defaultlang = 'en';
            }

            $this->language = self::$defaultlang;
            if (count(self::$list) == 0) self::$list = JSON::decode(\file_get_contents(APP_DIR . '/sources/languages/list.json'));
        }

        public function accepted() {
            $result = array();
            foreach(self::$list as $lang) {
                $lang = (object) $lang;
                array_push($result, $lang->short);
            }
            return $result;
        }

        public function listed() {
            return self::$list;
        }

        public function default() {
            return self::$defaultlang;
        }

        public function detectLanguage($stockDetection = false) {
            if ($this->loaded) return false;
            if (Action::exists('detect_language') && !$stockDetection) {
                $res = Action::call('detect_language');
                $this->setLanguage($res);
            } else {
                $sources = (object) array(
                    "headers" => null,
                    "cookie" => null,
                    "router" => null
                );

                if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    $langsFromHeaders = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                    foreach($langsFromHeaders as $lang) {
                        if (!$sources->headers) {
                            if (in_array(substr($lang, 0, 2), $this->accepted())) {
                                $sources->headers = substr($lang, 0, 2);
                            }
                        }
                    }
                }

                if (isset($_COOKIE['language'])) {
                    $sources->cookie = $_COOKIE['language'];
                }

                $router = new Router;
                $sources->router = $router->langPref();

                if ($sources->headers) $this->setLanguage($sources->headers);
                if ($sources->cookie) $this->setLanguage($sources->cookie);
                if ($sources->router) $this->setLanguage($sources->router);
            }

            $this->saveLanguage();
        }

        public function setLanguage($language, $fallback = NULL) {
            if ($this->loaded) return false;
            $fallback = $fallback ? (in_array($fallback, $this->accepted()) ? $fallback : $this->language) : $this->language;
            $this->language = in_array($language, $this->accepted()) ? $language : $fallback;
        }

        public function saveLanguage($language = '') {
            if ($language == '') $language = $this->language;
            $secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? true : false);
            $url = parse_url(SITE_LOCATION);
            setcookie("language", $language, 2147483647, $url['path'], $url['host'], $secure);
        }

        public function load() {
            if ($this->loaded !== NULL) return false;
            if (!isset(self::$inmemory[$this->language])) self::$inmemory[$this->language] = JSON::decode(\file_get_contents(APP_DIR . '/sources/languages/' . $this->language . '.json'));
            $this->loaded = self::$inmemory[$this->language];
            return true;
        }

        public function selected() {
            return $this->language;
        }

        public function current() {
            return !$this->loaded ? NULL : $this->language;
        }

        public function get($selector = NULL, $fallback = NULL) {
            if (!$selector) {
                return $this->loaded;
            } else {
                $current = $this->loaded;
                $selector = explode('.', $selector);

                foreach($selector as $item) {
                    if ($current !== NULL) {
                        if (isset(((array) $current)[$item])) {
                            $current = ((array) $current)[$item];
                        } else {
                            $current = NULL;
                        }
                    }
                }

                return (!$current ? $fallback : $current);
            }
        } 

        public function virtual($name, $short, $data, $listed = true) {
            if (in_array($short, $this->accepted())) {
                return false;
            } else {
                self::$inmemory[$this->language] = $data;
                array_push(self::$list, array(
                    "name" => $name,
                    "short" => $short,
                    "listed" => $listed
                ));

                return true;
            }
        }
    }