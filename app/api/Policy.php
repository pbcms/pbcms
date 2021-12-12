<?php
    use Library\Policy;
    use Library\UserPermissions;
    use Library\Language;
    use Helper\ApiResponse as Respond;
    use Helper\Request;

    $lang = new Language;
    $lang->detectLanguage();
    $lang->load();

    $this->__registerMethod('update', function() {
        $postdata = Request::parseBody();
        $policies = new Policy;

        if (!$this->user->authenticated()) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if (!Request::requireMethod('post')) die();
        if ($this->user->check("policy.update")) {
            foreach($postdata as $policy => $value) {
                if ($policies->exists($policy)) {
                    $policies->set($policy, $value);
                }
            }

            Respond::success();
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('set', function() {
        $required = array("policy", "value");
        $postdata = Request::parseBody();
        $session = Request::sessionInfo();
        $permission = new UserPermissions;
        $policy = new Policy;

        if (!$session->success) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required)) die();

        if ($permission->check($session->user->id, "policy.set")) {
            $policy->set($postdata->policy, $postdata->value);
            Respond::success();
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('get', function() {
        $required = array("policy");
        $postdata = Request::parseBody();
        $session = Request::sessionInfo();
        $permission = new UserPermissions;
        $policy = new Policy;

        if (!$session->success) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required)) die();

        if ($permission->check($session->user->id, "policy.get")) {
            $result = $policy->get($postdata->policy);
            if (!$result) {
                Respond::error('unknown_policy', "The requested policy does not exist.");
            } else {
                Respond::success(array(
                    "policy" => $postdata->policy,
                    "value" => $result
                ));
            }
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('exists', function() {
        $required = array("policy");
        $postdata = Request::parseBody();
        $session = Request::sessionInfo();
        $permission = new UserPermissions;
        $policy = new Policy;

        if (!$session->success) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required)) die();

        if ($permission->check($session->user->id, "policy.exists")) {
            $result = $policy->exists($postdata->policy);
            Respond::success(array(
                "policy" => $postdata->policy,
                "exists" => $result
            ));
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('delete', function() {
        $required = array("policy");
        $postdata = Request::parseBody();
        $session = Request::sessionInfo();
        $permission = new UserPermissions;
        $policy = new Policy;

        if (!$session->success) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if (!Request::requireMethod('post')) die();
        if (!Request::requireData($required)) die();

        if ($permission->check($session->user->id, "policy.delete")) {
            $result = $policy->delete($postdata->policy);
            Respond::success();
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });

    $this->__registerMethod('list', function() {
        $postdata = Request::parseBody();
        $session = Request::sessionInfo();
        $permission = new UserPermissions;
        $policy = new Policy;

        if (!$session->success) {
            Respond::error('not_authenticated', "You must be authenticated to perform this request!");
            die();
        }

        if ($permission->check($session->user->id, "policy.list")) {
            $limit = (isset($postdata->limit) ? $postdata->limit : 0);
            $offset = (isset($postdata->offset) ? $postdata->offset : 0);
            $result = $policy->list($limit, $offset);
            Respond::success(array(
                "policies" => $result
            ));
        } else {
            Respond::error('no_permission', "You are lacking the permissions to perform this action.");
        }
    });
    