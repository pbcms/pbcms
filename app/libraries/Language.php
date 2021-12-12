<?php
    namespace Library;

    use Helper\JSON;
    use Library\Policy;
    use Registry\Action;

    class Language {
        private $loaded = false;
        protected $language = NULL;
        protected static $defaultlang = NULL;
        protected static $list = array();
        private static $inmemory = array();
        private static $updates = array();

        public function __construct($preflang = '') {
            if (!self::$defaultlang) {
                $policy = new Policy;
                self::$defaultlang = $policy->get('default-language');
                if (!self::$defaultlang) self::$defaultlang = 'en';
            }

            $this->language = self::$defaultlang;
            if (count(self::$list) == 0) self::$list = JSON::decode(\file_get_contents(APP_DIR . '/sources/languages/list.json'));
            $this->setLanguage($preflang);
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
            if ($this->loaded) return false;
            if (!isset(self::$inmemory[$this->language])) self::$inmemory[$this->language] = json_decode(\file_get_contents(APP_DIR . '/sources/languages/' . $this->language . '.json'), true);
            if (isset(self::$updates[$this->language])) {
                self::$inmemory[$this->language] = array_replace_recursive(self::$inmemory[$this->language], self::$updates[$this->language]);
                unset(self::$updates[$this->language]);
            }

            $this->loaded = true;
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
                return self::$inmemory[$this->language];
            } else {
                $current = (array) self::$inmemory[$this->language];
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
                self::$inmemory[$short] = json_decode(json_encode($data), true);
                array_push(self::$list, array(
                    "name" => $name,
                    "short" => $short,
                    "listed" => $listed
                ));

                return true;
            }
        }

        public function update($lang, $selector, $value = null) {
            if (!isset(self::$inmemory[$lang]) && !isset(self::$updates[$lang])) self::$updates[$lang] = array();
            if (!$value) {
                if (is_array($selector)) {
                    $local = $selector;
                } else {
                    return false;
                }
            } else if (!$selector) {
                if (is_array($value)) {
                    $local = $value;
                } else {
                    return false;
                }
            } else {
                $local = array();
                $current = &$local;
                foreach(explode('.', $selector) as $item) {
                    $current[$item] = array();
                    $current = &$current[$item];
                }

                $current = $value;
            }

            if (isset(self::$inmemory[$lang])) {
                self::$inmemory[$lang] = array_replace_recursive(json_decode(json_encode(self::$inmemory[$lang]), true), json_decode(json_encode($local), true));
            } else {
                self::$updates[$lang] = array_replace_recursive(json_decode(json_encode(self::$updates[$lang]), true), json_decode(json_encode($local), true));
            }

            return true;
        }
    }