<?php
    use Library\Language;
    use Helper\ApiResponse;
    use Helper\Respond;

    $this->__registerMethod('accepted', function() {
        $lang = new Language;
        Respond::JSON($lang->accepted());
    });

    $this->__registerMethod('listed', function() {
        $lang = new Language;
        Respond::JSON($lang->listed());
    });
    
    $this->__registerMethod('default', function() {
        $lang = new Language;
        Respond::JSON(array("default" => $lang->default()));
    });
    
    $this->__registerMethod('detected', function($params) {
        $lang = new Language;
        if (isset($params[0])) {
            switch($params[0]) {
                case 'stock':
                    $lang->detectLanguage(true);
                    break;
                default:
                    $lang->detectLanguage();
            }
        } else {
            $lang->detectLanguage();
        }

        Respond::JSON(array("detected" => $lang->selected()));
    });
    
    $this->__registerMethod('set', function($params) {
        if (isset($params[0])) {
            $lang = new Language;
            $lang->saveLanguage($params[0]);
            ApiResponse::success();
        } else {
            ApiResponse::error('no_language_given', "The language to be set should be specified in the url.");
        }
    });
    
    $this->__registerMethod('get', function($params) {
        if (isset($params[1])) {
            $lang = new Language($params[0]);
            $lang->load();
            Respond::JSON(array(
                "language" => $lang->current(),
                "result" => $lang->get($params[1])
            ));
        } else if (isset($params[0])) {
            $lang = new Language();
            $lang->detectLanguage();
            $lang->load();

            if (in_array($params[0], $lang->accepted()) && !$lang->get($params[0])) {
                $lang = new Language($params[0]);
                $lang->load();

                Respond::JSON(array(
                    "language" => $lang->current(),
                    "result" => $lang->get()
                ));
            } else {
                Respond::JSON(array(
                    "language" => $lang->current(),
                    "result" => $lang->get($params[0])
                ));
            }            
        } else {
            $lang = new Language();
            $lang->detectLanguage();
            $lang->load();
            Respond::JSON(array(
                "language" => $lang->current(),
                "result" => $lang->get()
            ));
        }
    });