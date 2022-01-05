<?php

    use Library\Modules;
    use Library\ModuleManager;
    use Helper\ApiResponse as Respond;
    use Helper\Request;
    use Helper\Validate;
    use Helper\Header;

    $this->__registerMethod('list', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.list')) {
            $modules = new Modules;
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $list = $modules->list($params[0]);
            } else {
                $list = $modules->list();
            }

            for ($i = 0; $i < count($list); $i++) {
                $list[$i] = $moduleManager->moduleSummary($list[$i]);
            }

            Respond::success(array("list" => $list));
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to list modules.");
        }
    });

    $this->__registerMethod('list-repository-modules', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.list')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0]) && $params[0] == 'include-disabled') {
                $list = $moduleManager->listModules(true);
            } else {
                $list = $moduleManager->listModules();
            }

            Respond::success(array("list" => $list));
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to list modules.");
        }
    });

    $this->__registerMethod('exists', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.exists')) {
            if (isset($params[0])) {
                $modules = new Modules;
                $moduleManager = new ModuleManager;

                Respond::success(array(
                    "local" => $modules->exists($params[0]),
                    "repositories" => $moduleManager->moduleExists($params[0])
                )); 
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to check if a module exists.");
        }
    });

    $this->__registerMethod('installed', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.installed')) {
            if (isset($params[0])) {
                $moduleManager = new ModuleManager;

                Respond::success(array(
                    "installed" => $moduleManager->moduleInstalled($params[0])
                )); 
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to check if a module is installed.");
        }
    });

    $this->__registerMethod('enable', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.enable')) {
            if (isset($params[0])) {
                $modules = new Modules;
                if ($modules->exists($params[0])) {
                    if ($modules->disabled($params[0])) $modules->enable($params[0]);
                    Respond::success(); 
                } else {
                    http_response_code(400);
                    Respond::error("unknown_module", "The provided module is not installed.");
                }
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to enable a module.");
        }
    });

    $this->__registerMethod('disable', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.disable')) {
            if (isset($params[0])) {
                $modules = new Modules;
                if ($modules->exists($params[0])) {
                    if ($modules->enabled($params[0])) $modules->disable($params[0]);
                    Respond::success(); 
                } else {
                    http_response_code(400);
                    Respond::error("unknown_module", "The provided module is not installed.");
                }
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to disable a module.");
        }
    });

    $this->__registerMethod('install', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.install')) {
            if (isset($params[0])) {
                $moduleManager = new ModuleManager;
                switch($moduleManager->installModule($params[0])) {
                    case 1:
                        Respond::success();
                        break;
                    case -1:
                        Respond::error("already_installed", "The module you are trying to install is already installed.");
                        break;
                    case -2:
                        Respond::error("unknown_module", "The requested module could not be found in the enabled repositories.");
                        break;
                    case -3:
                        Respond::error("archive_read_error", "An unexpected error occured while reading the module's archive.");
                        break;
                    case -4:
                        Respond::error("missing_entry", "The pb_entry.php file is missing from the module.");
                        break;
                    case -5:
                        Respond::error("missing_module_file", "The module.json file is missing from the module.");
                        break;
                    case -6:
                        Respond::error("archive_extraction_error", "An unexpected error occured while extracting the module's archive.");
                        break;
                    case -7:
                        Respond::error("module_move_error", "An error occured while moving the module to the correct directory.");
                        break;
                    case -8:
                        Respond::error("containment_deletion_error", "The folder that the module was contained in could not be deleted.");
                        break;
                    default:
                        Respond::error("unknown_error", "An unknown error occured while installing the module.");
                        break;
                }
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to install a module.");
        }
    });

    $this->__registerMethod('summary', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.summary')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->moduleSummary($params[0]);
                if ($res) {
                    Respond::success(array("summary" => $res));
                } else {
                    Respond::error("unknown_module", "The requested module could neither locally nor remotely be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to list modules.");
        }
    });

    $this->__registerMethod('update', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.update')) {
            if (isset($params[0])) {
                $moduleManager = new ModuleManager;

                if (isset($params[1])) {
                    if ($params[1] == 'force') {
                        if ($this->user->check('module.update.force')) {
                            $res = $moduleManager->updateModule($params[0], true);
                        } else {
                            http_response_code(403);
                            Respond::error("missing_privileges", "You are not allowed to update a module by force.");
                            return;
                        }
                    } else {
                        $res = $moduleManager->updateModule($params[0]);
                    }
                } else {
                    $res = $moduleManager->updateModule($params[0]);
                }

                switch($res) {
                    case 1:
                        Respond::success();
                        break;
                    case -1:
                        Respond::error("not_installed", "The module you are trying to update is not installed.");
                        break;
                    case -2:
                        Respond::error("unknown_module", "The requested module could not be found in the enabled repositories.");
                        break;
                    case -3:
                        Respond::error("missing_information", "Either the local or repo information is missing about the module.");
                        break;
                    case -4:
                        Respond::error("not_updated", "The latest version is already installed.");
                        break;
                    case -5:
                        Respond::error("not_updatable", "The module is restricted from further updates.");
                        break;
                    default:
                        Respond::error("unknown_error", "An unknown error occured while installing the module.");
                        break;
                }
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to update a module.");
        }
    });

    $this->__registerMethod('remove', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.remove')) {
            if (isset($params[0])) {
                $moduleManager = new ModuleManager;

                switch($moduleManager->removeModule($params[0])) {
                    case 1:
                        Respond::success();
                        break;
                    case -1:
                        Respond::error("not_installed", "The module you are trying to remove is not installed.");
                        break;
                    default:
                        Respond::error("unknown_error", "An unknown error occured while installing the module.");
                        break;
                }
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to remove a module.");
        }
    });

    $this->__registerMethod('module-repository-info', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.info')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->moduleRepoInfo($params[0]);
                if ($res) {
                    Respond::success(array("info" => $res));
                } else {
                    Respond::error("unknown_module", "The requested module could neither locally nor remotely be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to obtain info about modules.");
        }
    });

    $this->__registerMethod('module-local-info', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.info')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->moduleLocalInfo($params[0]);
                if ($res) {
                    Respond::success(array("info" => $res));
                } else {
                    Respond::error("unknown_module", "The requested module could neither locally nor remotely be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No module name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to obtain info about modules.");
        }
    });

    $this->__registerMethod('add-repository', function($params) {
        if (!Request::requireMethod("post")) die();
        if (!Request::requireAuthentication()) die();

        $body = (object) Request::parseBody();
        $required = array("name", "url");

        if ($this->user->check('module.add-repository')) {
            $moduleManager = new ModuleManager;
            $missing = Validate::listMissing($required, $body);
            if (count($missing) > 0) {
                Respond::error("missing_information", "The following information is missing from the request's body: " . join(', ', $missing));
            } else {
                if (isset($body->enabled)) {
                    $enabled = (strtolower($body->enabled) == 'enabled' || strtolower($body->enabled) == 'true' || intval($body->enabled) === 1 ? true : false);
                    $res = $moduleManager->addRepository($body->name, $body->url, $enabled);
                } else {
                    $res = $moduleManager->addRepository($body->name, $body->url);
                }

                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("repository_exists", "The provided repository already exists.");
                }
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to add a repository.");
        }
    });

    $this->__registerMethod('enable-repository', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.enable-repository')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->enableRepository($params[0]);
                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to enable a repository.");
        }
    });

    $this->__registerMethod('disable-repository', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.disable-repository')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->disableRepository($params[0]);
                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to disable a repository.");
        }
    });

    $this->__registerMethod('remove-repository', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.remove-repository')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->removeRepository($params[0]);
                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to remove a repository.");
        }
    });

    $this->__registerMethod('get-repository', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.get-repository')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                if (isset($params[1])) {
                    if ($params[1] == 'force-refresh') {
                        if ($this->user->check('module.refresh-repository')) {
                            $res = $moduleManager->getRepository($params[0], true);
                        } else {
                            http_response_code(403);
                            Respond::error("missing_privileges", "You don't have the permission to refresh a repository with force.");
                            return;
                        }
                    } else {
                        $res = $moduleManager->getRepository($params[0]);
                    }
                } else {
                    $res = $moduleManager->getRepository($params[0]);
                }

                if ($res) {
                    Respond::success(array("repository" => $res));
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to obtain a repository.");
        }
    });

    $this->__registerMethod('list-repositories', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.list-repositories')) {
            $moduleManager = new ModuleManager;
            $res = $moduleManager->listRepositories();
            Respond::success(array("repositories" => $res));
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to list repositories.");
        }
    });

    $this->__registerMethod('repository-info', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.repository-info')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->repositoryInfo($params[0]);
                if ($res) {
                    Respond::success(array("info" => $res));
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to obtain info about a repository.");
        }
    });

    $this->__registerMethod('refresh-repository', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.refresh-repository')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $res = $moduleManager->refreshRepository($params[0]);
                if ($res) {
                    Respond::success();
                } else {
                    Respond::error("unknown_repository", "The requested repository could not be found.");
                }        
            } else {
                http_response_code(400);
                Respond::error("missing_information", "No repository name was defined in the URL.");
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to refresh a repository.");
        }
    });

    $this->__registerMethod('refresh-repositories', function($params) {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check('module.refresh-repositories')) {
            $moduleManager = new ModuleManager;
            if (isset($params[0])) {
                $moduleManager->refreshRepositories($params[0] == 'include-disabled');
                Respond::success(); 
            } else {
                $moduleManager->refreshRepositories();
                Respond::success(); 
            }
        } else {
            http_response_code(403);
            Respond::error("missing_privileges", "You don't have the permission to refresh all repositories.");
        }
    });