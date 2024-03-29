<?php
    use Library\Cli;
    use Library\Users;
    use Library\DatabaseMigrator;
    use Helper\ApiResponse as Respond;
    use Helper\Validate;
    use Helper\Request;

    $this->__registerMethod('migrate-database', function() {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check("site.migrate-database")) {
            $dbmig = new DatabaseMigrator(array("shout" => false));
            $dbmig->migrate();
            Respond::success(array(
                "logs" => $dbmig->retrieveLogs()
            ));
        } else {
            Respond::error('missing_privileges', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('execute-command', function() {
        if (!Request::requireAuthentication()) die();

        if ($this->user->check("site.execute-command")) {
            $body = Request::parseBody();
            $required = array('input');
            if (!Request::requireData($required, $body)) die();

            $cli = new Cli();
            $cli->process($body->input);
            $output = ob_get_contents();
            ob_end_clean();

            if (!defined("SITE_TITLE")) define("SITE_TITLE", "Rescue shell");
            $prompt = SITE_TITLE . "@PBCMS ~> ";

            Respond::success(array(
                "prompt" => $prompt,
                "output" => $output
            ));
        } else {
            Respond::error('missing_privileges', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('dashboard', function($params) {
        if (!Request::requireAuthentication()) die();

        if (isset($params[0])) {
            switch(strtolower($params[0])) {
                case 'create-shortcut':
                    if (!Request::requireMethod('post')) die();
                    $body = Request::parseBody();
                    $required = array('shortcut-type', 'title', 'icon', 'target');
                    $missing = Validate::listMissing($required, $body);
                    if (count($missing) > 0) {
                        Respond::error('missing_information', array(
                            "message" => 'The following information is missing from the request: ' . join(',', $missing) . '.',
                            "missing_info" => $missing
                        ));
                    } else {
                        $body = Validate::removeUnlisted($required, $body);
                        $users = new Users;
                        $user = $this->user->info();
                        $shortcuts = json_decode($users->metaGet($user->id, 'dashboard-shortcuts'));
                        if (!$shortcuts) $shortcuts = array();
                        array_push($shortcuts, $body);
                        $users->metaSet($user->id, 'dashboard-shortcuts', json_encode($shortcuts));
                        Respond::success();
                    }

                    break;
                case 'delete-shortcut':
                    if (isset($params[1])) {
                        $index = intval($params[1]) - 1;
                        $users = new Users;
                        $user = $this->user->info();
                        $shortcuts = json_decode($users->metaGet($user->id, 'dashboard-shortcuts'));
                        if (!$shortcuts) $shortcuts = array();
                        if (isset($shortcuts[$index])) {
                            unset($shortcuts[$index]);
                            $users->metaSet($user->id, 'dashboard-shortcuts', json_encode($shortcuts));
                            Respond::success();
                        } else {
                            Respond::error('unknown_shortcut', "The requested shortcut does not exist.");    
                        }
                    } else {
                        Respond::error('missing_shortcut', "No shortcut index was defined in the URL.");     
                    }
                    
                    break;
                case 'shortcuts':
                    $users = new Users;
                    $user = $this->user->info();
                    $shortcuts = json_decode($users->metaGet($user->id, 'dashboard-shortcuts'));
                    if (!$shortcuts) $shortcuts = array();
                    Respond::success(array(
                        "shortcuts" => $shortcuts
                    ));
                    break;
                case 'toggle-advanced': 
                    $users = new Users;
                    if ($this->user->check('site.advanced-mode')) {
                        $user = $this->user->info();
                        $current = $users->metaGet($user->id, 'pb-dashboard-advanced-mode');
                        $users->metaSet($user->id, 'pb-dashboard-advanced-mode', ($current ? '0' : '1'));
                        Respond::success();
                    } else {
                        Respond::error('missing_privileges', "You are not allowed to enter advanced mode.");  
                    }

                    break;
                default:
                    Respond::error('unknown_action', "An unknown action was requested.");
            }
        } else {
            Respond::error('missing_action', "No action was defined.");     
        }
    });



    $this->__registerMethod('search', function($params) {

    });