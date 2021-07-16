<?php
    namespace Library;

    class Modules {
        private $loaded = array();

        public function initialize($data = array()) {
            $modules = $this->list('enabled');
            foreach($modules as $module) {
                $this->load($module, $data);
            }
        }

        public function list($type = 'enabled') {
            $scanned = scandir(DYNAMIC_DIR . '/modules');
            $modules = array();

            foreach($scanned as $item) {
                if ($this->exists($item)) {
                    switch($type) {
                        case 'all':
                            array_push($modules, $item);
                            break;
                        case 'disabled':
                            if ($this->disabled($item)) {
                                array_push($modules, $item);
                            }

                            break;
                        default:
                            if ($this->enabled($item)) {
                                array_push($modules, $item);
                            }
                    }
                }
            }

            return $modules;
        }

        public function load($module, $data = array()) {
            if ($this->exists($module)) {
                if ($this->enabled($module) || $force) {
                    require DYNAMIC_DIR . '/modules/' . $module . '/pb_entry.php';
                    $class = 'Module\\' . $this->prepareFunctionNaming($module);
                    $this->loaded[$module] = new $class($data);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        public function forwardRequest($module, $params) {
            if ($this->exists($module)) {
                if ($this->enabled($module)) {
                    if (isset($this->loaded[$module])) {
                        if (method_exists($this->loaded[$module], 'requestHandler')) {
                            $this->loaded[$module]->requestHandler($params);
                        } else {
                            return array(
                                "error" => "no_request_handler"
                            );
                        }
                    } else {
                        return array(
                            "error" => "module_unloaded"
                        );
                    }
                } else {
                    return array(
                        "error" => "module_disabled"
                    );
                }
            } else {
                return array(
                    "error" => "unknown_module"
                );
            }
        }

        public function exists($module) {
            return file_exists(DYNAMIC_DIR . '/modules/' . $module . '/pb_entry.php');
        }

        public function enable($module) {
            unlink(DYNAMIC_DIR . '/modules/' . $module . '/.disabled') or die("Unable to enabled module: Lacking permission");
        }

        public function enabled($module) {
            return !file_exists(DYNAMIC_DIR . '/modules/' . $module . '/.disabled');
        }

        public function disable($module) {
            $disabler = fopen(DYNAMIC_DIR . '/modules/' . $module . '/.disabled', "w") or die("Unable to disable module: Lacking permissions");
            fwrite($disabler, "MODULE_DISABLED");
            fclose($disabler);           
        }

        public function disabled($module) {
            return file_exists(DYNAMIC_DIR . '/modules/' . $module . '/.disabled');
        }

        public function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }
    }

    class ModuleConfig extends ObjectPropertyWorker {
        public function __construct($plugin) {
            $this->init('module-config', $this->prepareFunctionNaming($plugin));
        }

        private function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }
    }