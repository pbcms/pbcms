<?php
    namespace Library;

    class Controller {
        public function model($model) {
            $model = ucwords($model);
            if (file_exists(APP_DIR . '/models/' . $model . '.php')) {
                require_once APP_DIR . '/models/' . $model . '.php';
                $class = 'Model\\' . $model;
                return new $class;
            } else if (file_exists(DYNAMIC_DIR . '/models/' . $model . '.php')) {
                require_once DYNAMIC_DIR . '/models/' . $model . '.php';
                $class = 'Model\\' . $model;
                return new $class;
            } else {
                return null;
            }
        }

        public function view($view, $data = []) {
            if (file_exists(APP_DIR . '/views/' . $view . '.php')) {
                include_once APP_DIR . '/views/' . $view . '.php';
            } else if (file_exists(DYNAMIC_DIR . '/views/' . $view . '.php')) {
                include_once DYNAMIC_DIR . '/views/' . $view . '.php';
            } else {
                $this->displayError(500, "Unknown View", "Interne server fout");
            }
        }

        public function template($template, $data = []) {
            $content = ob_get_contents();
            ob_end_clean();

            if (file_exists(APP_DIR . '/templates/' . $template . '.php')) {
                include_once APP_DIR . '/templates/' . $template . '.php';
                return;
            }

            $policy = new Policy;
            $templateProvider = $policy->get('default-template-provider');
            if (is_string($templateProvider)) {
                if (explode(':', $templateProvider)[0] == 'module' && isset(explode(':', $templateProvider)[1])) {
                    $requestedModule = explode(':', $templateProvider)[1];
                    $modules = new Modules;
                    if ($modules->isLoaded($requestedModule)) {
                        if (file_exists(DYNAMIC_DIR . '/modules/' . $requestedModule . '/templates/' . $template . '.php')) {
                            include_once DYNAMIC_DIR . '/modules/' . $requestedModule . '/templates/' . $template . '.php';
                            return;
                        }
                    }
                }
            }
            
            if (file_exists(DYNAMIC_DIR . '/templates/' . $template . '.php')) {
                include_once DYNAMIC_DIR . '/templates/' . $template . '.php';
            } else {
                $this->displayError(500, "Unknown Template", "Interne server fout");
            }
        }

        public function modelExists($model) {
            if (file_exists(APP_DIR . '/models/' . $model . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/models/' . $model . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function viewExists($view) {
            if (file_exists(APP_DIR . '/views/' . $view . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/views/' . $view . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function templateExists($template) {
            if (file_exists(APP_DIR . '/templates/' . $template . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/templates/' . $template . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function displayError($error, $short, $message) {
            $data = array(
                "errorCode" => $error,
                "errorMessage" => $message,
                "errorShort" => $short
            );

            include_once APP_DIR . '/views/pages/error.php';

            $content = ob_get_contents();
            ob_end_clean();

            $data = array(
                "title" => $short . " - Birkje.nl",
                "head" => '<link rel="stylesheet" href="' . SITE_LOCATION . 'pb-pubfiles/css/siteFront_errorPage.css">',
                "scripts" => '<script src="' . SITE_LOCATION . 'pb-pubfiles/js/siteFront_errorPage.js"></script>',
                "navbarNoShadow" => true
            );

            include_once APP_DIR . '/templates/pb-default.php';
            http_response_code($error);

            die();
        }
    }