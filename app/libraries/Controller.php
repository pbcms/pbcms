<?php
    namespace Library;

    class Controller {
        public function model($model) {
            $model = ucwords($model);
            if ($this->modelExists($model)) {
                require_once APP_DIR . '/models/' . $model . '.php';
                $class = 'Model\\' . $model;
                return new $class;
            } else {
                return null;
            }
        }

        public function view($view, $data = []) {
            if ($this->viewExists($view)) {
                include_once APP_DIR . '/views/' . $view . '.php';
            } else {
                $this->displayError(500, "Unknown View", "Interne server fout");
            }
        }

        public function template($template, $data = []) {
            $content = ob_get_contents();
            ob_end_clean();

            if ($this->templateExists($template)) {
                include_once APP_DIR . '/templates/' . $template . '.php';
            } else {
                $this->displayError(500, "Unknown Template", "Interne server fout");
            }
        }

        public function modelExists($model) {
            return file_exists(APP_DIR . '/models/' . $model . '.php');
        }

        public function viewExists($view) {
            return file_exists(APP_DIR . '/views/' . $view . '.php');
        }

        public function templateExists($template) {
            return file_exists(APP_DIR . '/templates/' . $template . '.php');
        }

        public function displayError($error, $short, $message) {
            $data = array(
                "errorCode" => $error,
                "errorMessage" => $message,
                "errorShort" => $short
            );

            include_once APP_DIR . '/views/pages/error.php';

            $content = ob_get_contents();
            ob_end_clean();

            $data = array(
                "title" => $short . " - Birkje.nl",
                "head" => '<link rel="stylesheet" href="' . SITE_LOCATION . 'pb-pubfiles/css/siteFront_errorPage.css">',
                "scripts" => '<script src="' . SITE_LOCATION . 'pb-pubfiles/js/siteFront_errorPage.js"></script>',
                "navbarNoShadow" => true
            );

            include_once APP_DIR . '/templates/pb-default.php';
            http_response_code($error);

            die();
        }
    }