<?php
    namespace Controller;

    use Library\Router;
    use Library\Controller;

    class PbPubfiles extends Controller {
        public function __error($error) {
            if ($error == 404) {
                $router = new Router;
                $path = join('/', $router->documentRequest()->params);
                $path = str_replace('.../', './', $path);
                $path = str_replace('../', './', $path);

                if (file_exists(DYNAMIC_DIR . '/static/' . $path)) {
                    $mime = mime_content_type(DYNAMIC_DIR . '/static/' . $path);
                    if (explode('.', $path)[count(explode('.', $path)) - 1] == 'css') $mime = 'text/css';
                    if (explode('.', $path)[count(explode('.', $path)) - 1] == 'js') $mime = 'text/javascript';
                    
                    header("Content-Type: " . $mime);
                    print_r(file_get_contents(DYNAMIC_DIR . '/static/' . $path));
                } else {
                    $this->__displayError(404, 'not found', 'not found');
                }
            } else {
                $this->__displayError($error);
            }
        }

        public function Media($params, $unlockKey) {
            $router = new Router;
            $router->refactorRequest('/pb-api/media/get/' . join('/', $params), $unlockKey);
            $router->executeRequest();
        }
    }