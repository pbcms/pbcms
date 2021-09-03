<?php
    namespace Library;

    use Library\Router;
    use Library\Policy;

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

        public function displayError($error, $short = null, $message = null) {
            $policy = new Policy;
            $router = new Router;
            $request = $router->documentRequest();
            if ($error == 404 && ($request->url == '' || $request->url == '/') && intval($policy->get('show-welcome-page')) === 1) {
                include_once APP_DIR . '/views/pages/error-welcome-page.php';
            } else {
                if (file_exists(APP_DIR . '/views/pages/error-' . $error . '.php')) {
                    include_once APP_DIR . '/views/pages/error-' . $error . '.php';
                } else {
                    include_once APP_DIR . '/views/pages/error-unknown.php';
                }
            }

            $content = ob_get_contents();
            ob_end_clean();

            $data['copyright'] = "&copy; " . SITE_TITLE . " " . date("Y");

            include_once APP_DIR . '/templates/pb-error.php';
            http_response_code($error);

            die();
        }
    }