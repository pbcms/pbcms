<?php
    namespace Controller;

    class System {
        
    }

    class PbError extends \Library\Controller {
        public function Display($error) {
            $lang = new \Library\Language();
            $lang->detectLanguage();
            $lang->load();

            $this->view('pages/error', array(
                "errorCode" => $error,
                "errorMessage" => ($lang->get("error-pages.messages." . $error) ? $lang->get("error-pages.messages." . $error) : $lang->get("error-pages.messages.0")),
                "errorShort" => ($lang->get("error-pages.short." . $error) ? $lang->get("error-pages.short." . $error) : $lang->get("error-pages.short.0"))
            ));

            $this->template('pb-default', array(
                "title" => (isset($short[$error]) ? $short[$error] : 'Onbekende fout') . " - Birkje.nl",
                "head" => '<link rel="stylesheet" href="' . SITE_LOCATION . 'pb-pubfiles/css/siteFront_errorPage.css">',
                "scripts" => '<script src="' . SITE_LOCATION . 'pb-pubfiles/js/siteFront_errorPage.js"></script>',
                "navbarNoShadow" => true
            ));
        }
    }