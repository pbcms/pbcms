<?php
    namespace Module;

    class PbMaintenance {
        public function __construct($data) {
            $modconf = new \Library\ModuleConfig('pb-maintenance');
            if ($modconf->get('enabled') == '1') {
                \Registry\Event::listen('request-processed', function($request) {
                    global $router;
    
                    if ($request->controller == 'PbLoader') {
                        return;
                    } else if ($request->controller == 'System' && $request->method == 'PbDashboard') {
                        return;
                    } else if ($request->controller == 'System' && $request->method == 'PbAuth') {
                        return;
                    } else if ($request->controller == 'System' && $request->method == 'PbApi') {
                        return;
                    } else {
                        $router->refactorRequest('/pb-loader/module/pb-maintenance/show-page');
                        $router->executeRequest();
                    }
                });
            }

            \Registry\Api::register('maintenance', function($params) {
                global $modules;
                $modules->forwardRequest('pb-maintenance', $params);
            });
        }

        public function requestHandler($params) {
            $modconf = new \Library\ModuleConfig('pb-maintenance');
            if (count($params) > 0 && $params[0] == 'enable') {
                $modconf->set('enabled', 1);
                \Helper\ApiResponse::success("Maintenance mode enabled.");
            } else if (count($params) > 0 && $params[0] == 'disable') {
                $modconf->set('enabled', 0);
                \Helper\ApiResponse::success("Maintenance mode disabled.");
            } else if (count($params) > 0 && $params[0] == 'show-page') {
                $static = $modconf->get('static');
                if ($static) {
                    print_r($static);
                } else {
                    include __DIR__ . '/maintenance.php';
                }
            } else {
                \Helper\ApiResponse::error('missing_method');
            }
        }
    }