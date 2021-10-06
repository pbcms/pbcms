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
                    if (method_exists(self::$loaded[$module], 'initialize')) {
                        self::$loaded[$module]->initialize($data);
                    }

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

        public function updateable($module) {
            return !(file_exists(DYNAMIC_DIR . '/modules/' . $module . '/.no-update'));
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

    class ModuleManager {
        public function moduleSummary($name) {
            $modules = new Modules;
            $found = false;
            $result = (object) array(
                "module" => $name,
                "functionNaming" => $modules->prepareFunctionNaming($name),
                "enabled" => false,
                "preCore" => false,
                "loaded" => false,
                "updateable" => false,
                "repo" => $this->moduleRepoInfo($name),
                "local" => $this->moduleLocalInfo($name)
            );

            if ($modules->exists($name)) {
                $found = true;
                $result->enabled = $modules->enabled($name);
                $result->preCore = $modules->preCore($name);
                $result->loaded = $modules->isLoaded($name);
                $result->updateable = $modules->updateable($name);
            }

            if (!$found && !$repo) return null;
            return $result;
        }

        public function installModule($name) {
            if (!$this->moduleInstalled($name)) {
                $module = $this->moduleRepoInfo($name);
                if ($module) {
                    $filename = tempnam(sys_get_temp_dir(), 'pbmodule_' . $module->module);
                    $file = fopen($filename, 'w+');
                    fwrite($file, $this->retrieveFile($module->latest, true));
                    fclose($file);

                    $zip = new \ZipArchive();
                    if ($zip->open($filename) === true) {
                        $root = '';
                        if ($zip->statIndex(0)['name'] == $module->module . '-' . $module->version . '/') $root = $module->module . '-' . $module->version . '/';
                        if ($zip->locateName($root . 'pb_entry.php') === false) {
                            unlink($filename);
                            return -4;
                        }
                        
                        if ($zip->locateName($root . 'module.json') === false) {
                            unlink($filename);
                            return -5;
                        }

                        mkdir(DYNAMIC_DIR . '/modules/' . $module->module, 0775, true);
                        fclose(fopen(DYNAMIC_DIR . '/modules/' . $module->module . '/.disabled', 'w+'));
                        if ($zip->extractTo(DYNAMIC_DIR . '/modules/' . $module->module)) {
                            unlink($filename);
                            if ($root !== '') {
                                $success = true;
                                $files = scandir(DYNAMIC_DIR . '/modules/' . $module->module . '/' . $root);
                                foreach ($files as $file) {
                                    if (in_array($file, array(".",".."))) continue;
                                    if (!copy(DYNAMIC_DIR . '/modules/' . $module->module . '/' . $root . $file, DYNAMIC_DIR . '/modules/' . $module->module . '/' . $file)) {
                                        $success = false;
                                    }
                                }

                                if ($success) {
                                    return ($this->deleteDirectory(DYNAMIC_DIR . '/modules/' . $module->module . '/' . $root) ? 1 : -8);
                                } else {
                                    return -7;
                                }
                            } else {
                                return 1;
                            }
                        } else {
                            unlink($filename);
                            return -6;
                        }
                    } else {
                        return -3;
                    }
                } else {
                    return -2;
                }
            } else {
                return -1;
            }
        }

        public function removeModule($name) {
            if ($this->moduleInstalled($name)) {
                $this->deleteDirectory(DYNAMIC_DIR . '/modules/' . $name);
            } else {
                return -1;
            }
        }

        public function updateModule($name, $force = false) {
            if ($this->moduleInstalled($name)) {
                $module = $this->moduleRepoInfo($name);
                if ($module) {
                    $repo = $this->moduleRepoInfo($name);
                    $local = $this->moduleLocalInfo($name);
                    if (!$repo || !$local) return -3;
                    if (version_compare($local->version, $repo->version) >= 0) return -4;
                    if (file_exists(DYNAMIC_DIR . '/modules/' . $name . '/.no-update') && $force !== true) return -5;
                
                    $modules = new Modules;
                    $enabled = $modules->enabled($name);
                    $this->deleteDirectory(DYNAMIC_DIR . '/modules/' . $name);
                    $this->installModule($name);
                    if ($enabled) {
                        $modules->enable($name);
                    } else {
                        $modules->disable($name);
                    }

                    return 1;
                } else {
                    return -2;
                }
            } else {
                return -1;
            }
        }

        public function moduleExists($name) {
            $exists = false;
            $modules = $this->listModules();
            foreach($modules as $item) {
                if ($item['module'] == $name) {
                    $exists = true;
                    break;
                }
            }

            return $exists;
        }

        public function moduleRepoInfo($name) {
            $module = null;
            $modules = $this->listModules();
            foreach($modules as $item) {
                if ($item->module == $name) {
                    if (!$module) {
                        $module = (object) $item;
                    } else if (version_compare($item->version, $module->version) === 1) {
                        $module = (object) $item;
                    }
                }
            }

            return $module;
        }

        public function moduleLocalInfo($name) {
            if (file_exists(DYNAMIC_DIR . '/modules/' . $name . '/module.json')) {
                return (object) json_decode(file_get_contents(DYNAMIC_DIR . '/modules/' . $name . '/module.json'));
            } else {
                return null;
            }
        }

        public function moduleInstalled($name) {
            return file_exists(DYNAMIC_DIR . '/modules/' . $name . '/pb_entry.php');
        }

        public function listModules($includeDisabled = false) {
            $result = array();
            $repositories = $this->listRepositories();
            foreach($repositories as $repository) {
                if ($includeDisabled || intval($repository->enabled) == 1) {
                    $result = array_replace_recursive($result, $this->getRepository($repository->name));
                }
            }

            return $result;
        }

        public function addRepository($name, $url, $enabled = true) {
            if ($this->repositoryExists($name)) {
                return false;
            } else {
                $objects = new Objects;
                $objects->create('modules-repository', $name);
                $objects->set('modules-repository', $name, 'url', $url);
                $objects->set('modules-repository', $name, 'enabled', ($enabled ? 1 : 0));
            }
        }

        public function enableRepository($name) {
            if ($this->repositoryExists($name)) {
                return false;
            } else {
                $objects = new Objects;
                $objects->set('modules-repository', $name, 'enabled', 1);
            }
        }

        public function disableRepository($name) {
            if ($this->repositoryExists($name)) {
                return false;
            } else {
                $objects = new Objects;
                $objects->set('modules-repository', $name, 'enabled', 0);
            }
        }

        public function removeRepository($name) {
            if ($this->repositoryExists($name)) {
                $objects = new Objects;
                $objects->purge('modules-repository', $name);
            } else {
                return false;
            }
        }

        public function repositoryExists($name) {
            $objects = new Objects;
            return ($objects->info('modules-repository', $name) ? true : false);
        }

        public function getRepository($name, $forceRefresh = false) {
            $objects = new Objects;
            if ($this->repositoryExists($name)) {
                if (!file_exists(APP_DIR . '/sources/repositories/modules-' . $name . '.json') || $forceRefresh) {
                    $repository = (object) $objects->info('modules-repository', $name);
                    $properties = (object) $objects->properties('modules-repository', $repository->name, true);
                    $file = fopen(APP_DIR . '/sources/repositories/modules-' . $repository->name . '.json', 'w+');
                    fputs($file, $this->retrieveFile($properties->url, true));
                    fclose($file);

                    $objects->set('modules-repository', $repository->name, 'last-refreshed', time());
                }

                return json_decode(file_get_contents(APP_DIR . '/sources/repositories/modules-' . $name . '.json'));
            } else {
                return false;
            }
        }

        public function listRepositories() {
            $objects = new Objects;
            $repositories = $objects->list('modules-repository', 0);
            $result = array();
            foreach($repositories as $repository) {
                $repository = (object) $repository;
                $properties = (object) $objects->properties('modules-repository', $repository->name, true);
                array_push($result, (object) array(
                    "name" => $repository->name,
                    "url" => $properties->url,
                    "enabled" => (intval($properties->enabled) === 1 ? true : false)
                ));
            }

            return $result;
        }

        public function repositoryInfo($name) {
            $objects = new Objects;
            if ($this->repositoryExists($name)) {
                $repository = (object) $objects->info('modules-repository', $name);
                $properties = (object) $objects->properties('modules-repository', $repository->name, true);
                return (object) array(
                    "name" => $repository->name,
                    "url" => $properties->url,
                    "enabled" => (intval($properties->enabled) === 1 ? true : false)
                );
            } else {
                return false;
            }
        }

        public function refreshRepository($name) {
            $objects = new Objects;
            if ($this->repositoryExists($name)) {
                $repository = (object) $objects->info('modules-repository', $name);
                $properties = (object) $objects->properties('modules-repository', $repository->name, true);
                $file = fopen(APP_DIR . '/sources/repositories/modules-' . $repository->name . '.json', 'w+');
                fputs($file, $this->retrieveFile($properties->url, true));
                fclose($file);

                $objects->set('modules-repository', $repository->name, 'last-refreshed', time());
                return true;
            } else {
                return false;
            }
        }

        public function refreshRepositories($includeDisabled = false) {
            $objects = new Objects;
            $repositories = $this->listRepositories();
            foreach($repositories as $repository) {
                if ($includeDisabled || intval($repository->enabled) == 1) {
                    $file = fopen(APP_DIR . '/sources/repositories/modules-' . $repository->name . '.json', 'w+');
                    fputs($file, $this->retrieveFile($repository->url, true));
                    fclose($file);

                    $objects->set('modules-repository', $repository->name, 'last-refreshed', time());
                }
            }
        }

        private function retrieveFile($url, $raw = false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec ($ch);
            curl_close ($ch);

            if ($raw) return $data;
            $file = tmpfile();
            fwrite($file, $data);
            fseek($file, 0);
            return $file;
        }

        // https://stackoverflow.com/a/1653776
        protected function deleteDirectory($dir) {
            if (!file_exists($dir)) return true;
            if (!is_dir($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
            }
        
            return rmdir($dir);
        }
    }