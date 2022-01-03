<?php
    use Library\DatabaseMigrator;
    use Helper\ApiResponse as Respond;
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