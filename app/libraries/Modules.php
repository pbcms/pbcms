<?php
    namespace Library;

    class Modules {
        private static $loaded = array();

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
                        case 'pre-core':
                            if ($this->preCore($item)) {
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
            if ($this->exists($module) && !$this->isLoaded($module)) {
                if ($this->enabled($module) || $force) {
                    require DYNAMIC_DIR . '/modules/' . $module . '/pb_entry.php';
                    $class = 'Module\\' . $this->prepareFunctionNaming($module);
                    self::$loaded[$module] = new $class($data);
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
                    if (isset(self::$loaded[$module])) {
                        if (method_exists(self::$loaded[$module], 'requestHandler')) {
                            return (object) array(
                                "success" => true,
                                "response" => self::$loaded[$module]->requestHandler($params)
                            );
                        } else {
                            return (object) array(
                                "success" => false,
                                "error" => "no_request_handler"
                            );
                        }
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "module_unloaded"
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "module_disabled"
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_module"
                );
            }
        }

        public function loadConfigurator($module, $params) {
            if ($this->exists($module)) {
                if ($this->enabled($module)) {
                    if (isset(self::$loaded[$module])) {
                        if (method_exists(self::$loaded[$module], 'configurator')) {
                            return (object) array(
                                "success" => true,
                                "response" => self::$loaded[$module]->configurator($params)
                            );
                        } else {
                            return (object) array(
                                "success" => false,
                                "error" => "no_configurator_available"
                            );
                        }
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "module_unloaded"
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "module_disabled"
                    );
                }
            } else {
                return (object) array(
                    "success" => false,
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

        public function preCore($module) {
            return file_exists(DYNAMIC_DIR . '/modules/' . $module . '/.pre-core');
        }

        public function isLoaded($module) {
            return isset(self::$loaded[$module]);
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

        public function defaults($properties) {
            $properties = (array) $properties;
            foreach($properties as $key => $value) {
                if (!$this->existsInDatabase($key)) $this->set($key, $value);
            }
        }

        private function prepareFunctionNaming($str) {
            $str = str_replace('-', ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            return $str;
        }
    }