<?php
    namespace Library;

    use Library\Router;
    use Library\Policy;
    use Library\Language;
    use Registry\ErrorPage;
    use Helper\ApiResponse as Respond;

    class Controller {
        public function __model($model) {
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

        public function __view($view, $data = []) {
            if (file_exists(APP_DIR . '/views/' . $view . '.php')) {
                include_once APP_DIR . '/views/' . $view . '.php';
            } else if (file_exists(DYNAMIC_DIR . '/views/' . $view . '.php')) {
                include_once DYNAMIC_DIR . '/views/' . $view . '.php';
            } else {
                $this->__displayError(500, "Unknown View", "Interne server fout");
            }
        }

        public function __template($template, $data = []) {
            $content = ob_get_contents();
            ob_end_clean();

            if (file_exists(APP_DIR . '/templates/' . $template . '.php')) {
                include_once APP_DIR . '/templates/' . $template . '.php';
                return;
            }
            
            if (explode(':', $template)[0] == 'module' && isset(explode(':', $template)[1]) && isset(explode(':', $template)[2])) {
                $requestedModule = explode(':', $template)[1];
                $modules = new Modules;
                if ($modules->isLoaded($requestedModule)) {
                    if (file_exists(DYNAMIC_DIR . '/modules/' . $requestedModule . '/templates/' . explode(':', $template)[2] . '.php')) {
                        include_once DYNAMIC_DIR . '/modules/' . $requestedModule . '/templates/' . explode(':', $template)[2] . '.php';
                        return;
                    }
                }
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
                $this->__displayError(500, "Unknown Template", "Interne server fout");
            }
        }

        public function __modelExists($model) {
            if (file_exists(APP_DIR . '/models/' . $model . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/models/' . $model . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function __viewExists($view) {
            if (file_exists(APP_DIR . '/views/' . $view . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/views/' . $view . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function __templateExists($template) {
            if (file_exists(APP_DIR . '/templates/' . $template . '.php')) {
                return true;
            } else if (file_exists(DYNAMIC_DIR . '/templates/' . $template . '.php')) {
                return true;
            } else {
                return false;
            }
        }

        public function __displayError($error, $data = []) {
            $policy = new Policy;
            $router = new Router;
            $stock = true;
            $request = $router->documentRequest();

            if ($error == 404 && ($request->url == '' || $request->url == '/') && intval($policy->get('show-welcome-page')) === 1) {
                include_once APP_DIR . '/views/pages/error-welcome-page.php';
            } else {
                if (ErrorPage::exists($error)) {
                    $stock = false;
                    ErrorPage::call($error, $data);
                } else if (file_exists(APP_DIR . '/views/pages/error-' . $error . '.php')) {
                    include_once APP_DIR . '/views/pages/error-' . $error . '.php';
                } else {
                    include_once APP_DIR . '/views/pages/error-unknown.php';
                }
            }

            if ($stock) {
                $content = ob_get_contents();
                ob_end_clean();

                $data['copyright'] = "&copy; " . SITE_TITLE . " " . date("Y");

                include_once APP_DIR . '/templates/pb-error.php';
            }

            http_response_code($error);
            die();
        }
    }

    class ApiController extends Controller {
        private $api;
        private $methods = array();

        public function __usingApi($api) {
            $this->api = $api;
        }

        public function __registerMethod($method, $callback) {
            if (isset($this->methods[$method])) {
                return false;
            } else {
                $this->methods[$method] = $callback;
                return true;
            }
        }

        public function __callMethod($method) {
            if (isset($this->methods[$method])) {
                $callback = $this->methods[$method];
                $arguments = func_get_args();
                array_shift($arguments);
                
                if (count($arguments) > 0) {
                    call_user_func_array($callback, $arguments);
                } else {
                    call_user_func($callback);
                }
                
                return true;
            } else {
                return false;
            }
        }
            
        public function __apiError($error, $preflang = '') {
            $lang = new Language($preflang);
            if (!$preflang) $lang->detectLanguage();
            $lang->load();

            Respond::error($error, $lang->get("messages.__api-controller.error-" . $error, $lang->get("messages.__api-controller.__unknown-error", "An error occured while retrieving the error message!")));
            die();
        }
    }