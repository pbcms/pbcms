<?php
    namespace Controller;

    class PbLoader extends \Library\Controller {
        public function __construct() {
            
        }

        public function Theme($params) {
            $path = implode('/', $params);
            $path = str_replace('.../', './', $path);
            $path = str_replace('../', './', $path);

            if (file_exists(DYNAMIC_DIR . '/themes/' . $path)) {
                header("Content-Type: " . mime_content_type(DYNAMIC_DIR . '/themes/' . $path));
                print_r(file_get_contents(DYNAMIC_DIR . '/themes/' . $path));
            } else {
                $this->displayError(404, 'not found', 'not found');
            }
        }

        public function Module($params) {
            global $modules;
            $module = $params[0];

            array_shift($params);
            $executionLog = $modules->forwardRequest($module, $params);

            if (isset($executionLog['error'])) switch($executionLog['error']) {
                case 'unknown_module':
                    $this->displayError(500, 'internal server error', 'unknown module');
                    break;
                case 'module_disabled': 
                    $this->displayError(500, 'internal server error', 'module disabled');
                    break;
                case 'module_unloaded': 
                    $this->displayError(500, 'internal server error', 'module unloaded');
                    break;
                case 'no_request_handler':
                    $this->displayError(500, 'internal server error', 'no request handler');
                    break;
                default:
                    $this->displayError(500, 'internal server error', 'unknown error');
            }
        }

        public function ModuleStatic($params) {
            global $modules;
            $module = $params[0];

            if ($modules->exists($module)) {
                array_shift($params);
                $path = implode('/', $params);
                $path = str_replace('.../', './', $path);
                $path = str_replace('../', './', $path);

                if (file_exists(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path)) {
                    header("Content-Type: " . mime_content_type(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path));
                    print_r(file_get_contents(DYNAMIC_DIR . '/modules/' . $module . '/static/' . $path));
                } else {
                    $this->displayError(404, 'not found', 'not found');
                }
            } else {
                $this->displayError(500, 'internal server error', 'unknown module');
            }
        }
    }