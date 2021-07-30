<?php
    namespace Library;

    use Helper\JSON;

    class Language {
        private $loaded = NULL;
        protected $language = 'nl';
        protected $accepted = ['nl', 'en'];

        public function detectLanguage() {
            $this->setLanguage(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2), 'en');
            if (isset($_COOKIE['language'])) {
                $this->setLanguage($_COOKIE['language']);
            }

            $router = new Router;
            $rPref = $router->langPref();
            $rPref = (!$rPref && !$router->virtPath() && isset($_GET['url']) ? 'en' : $rPref);

            $this->setLanguage($rPref, $this->language);
            $this->saveLanguage();
        }

        public function setLanguage($language, $fallback = NULL) {
            if ($this->loaded) return false;
            $fallback = $fallback ? (in_array($fallback, $this->accepted) ? $fallback : 'en') : $this->language;
            $this->language = in_array($language, $this->accepted) ? $language : $fallback;
        }

        public function saveLanguage($language = '') {
            if ($language == '') $language = $this->language;
            setcookie("language", $language, time() + (10 * 365 * 24 * 60 * 60), "/");
        }

        public function load() {
            if ($this->loaded !== NULL) return false;
            $this->loaded = JSON::decode(\file_get_contents(APP_DIR . '/sources/languages/' . $this->language . '.json'));
            return true;
        }

        public function selected() {
            return $this->language;
        }

        public function current() {
            return !$this->loaded ? NULL : $this->language;
        }

        public function get($selector = NULL) {
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

                return $current;
            }
        } 
    }