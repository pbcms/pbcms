<?php
    namespace Controller;

    use Library\Modules;

    class PbLoader extends \Library\Controller {
        public function __construct() {
            
        }

        public function Module($params) {
            $modules = new Modules;
            $module = $params[0];

            array_shift($params);
            $executionLog = (array) $modules->forwardRequest($module, $params);

            if (isset($executionLog['error'])) switch($executionLog['error']) {
                case 'unknown_module':
                    $this->__displayError(500, 'internal server error', 'unknown module');
                    break;
                case 'module_disabled': 
                    $this->__displayError(500, 'internal server error', 'module disabled');
                    break;
                case 'module_unloaded': 
                    $this->__displayError(500, 'internal server error', 'module unloaded');
                    break;
                case 'no_request_handler':
                    $this->__displayError(500, 'internal server error', 'no request handler');
                    break;
                default:
                    $this->__displayError(500, 'internal server error', 'unknown error');
            }
        }

        public function ModuleStatic($params) {
            $modules = new Modules;
            $module = $params[0];

            if ($modules->exists($module)) {
                array_shift($params);
                $path = implode('/', $params);
                $path = str_replace('.../', './', $path);
                $path = str_replace('../', './', $path);

                if (file_exists(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path)) {
                    $mime = mime_content_type(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path);
                    if (explode('.', $path)[count(explode('.', $path)) - 1] == 'css') $mime = 'text/css';
                    if (explode('.', $path)[count(explode('.', $path)) - 1] == 'js') $mime = 'text/javascript';
                    
                    header("Content-Type: " . $mime);
                    print_r(file_get_contents(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path));
                } else {
                    $this->__displayError(404, 'not found', 'not found');
                }
            } else {
                $this->__displayError(500, 'internal server error', 'unknown module');
            }
        }
    }