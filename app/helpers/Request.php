<?php
    namespace Helper;

    use Library\Controller;
    
    class Request {
        public static function method($lowercase = false) {
            return ($lowercase ? strtolower($_SERVER['REQUEST_METHOD']) : strtoupper($_SERVER['REQUEST_METHOD']));
        }

        public static function decodeBody() {
            $body = file_get_contents("php://input");
            if (!$body || empty($body)) return array();

            $decoded = json_decode($body);
            if ($decoded && json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else if (urlencode(urldecode($body)) === $body) {
                return urldecode($body);
            } else {
                $result = [];
                self::parse_raw_http_request($result);
                return $result;
            }
        }

        public static function parseBody() {
            $db = new \Library\Database;
            return $db->escapeObject(self::decodeBody());
        }

        public static function parsePost() {
            $db = new \Library\Database;
            return $db->escapeObject($_POST);
        }

        public static function requireMethod($method) {
            if (self::method() !== strtoupper($method)) {
                ApiResponse::error('invalid_request_method', "The request was made with an incorrect request method. (The '" . strtoupper($method) . "' method is required)");
                return false;
            } else {
                return true;
            }
        }

        public static function requireData($properties, $origin = NULL) {
            if ($origin == NULL) $origin = $_POST;
            $missing = Validate::listMissing($properties, $origin);

            if (count($missing) > 0) {
                ApiResponse::error('missing_information', 'The following post information is missing from the request: ' . join(',', $missing) . '.');
                return false;
            } else {
                return true;
            }
        }

        public static function requireAuthentication() {
            $controller = new Controller;
            if (!$controller->__model('user')->authenticated()) {
                ApiResponse::error('not_authenticated', 'You must be authenticated to perform this request.');
                return false;
            } else {
                return true;
            }
        }

        public static function rewrite($url, $execute = true) {
            $router = new \Library\Router;
            if ($router->refactorRequest($url)) {
                if ($execute) $router->executeRequest();
            } else {
                return false;
            }
        }


        //Props to: https://stackoverflow.com/a/5488449
        public static function parse_raw_http_request(array &$a_data) {
            // read incoming data
            $input = file_get_contents('php://input');

            // grab multipart boundary from content type header
            preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
            $boundary = $matches[1];

            // split content by boundary and get rid of last -- element
            $a_blocks = preg_split("/-+$boundary/", $input);
            array_pop($a_blocks);

            // loop data blocks
            foreach ($a_blocks as $id => $block) {
                if (empty($block)) continue;

                // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

                // parse uploaded files
                if (strpos($block, 'application/octet-stream') !== FALSE) {
                    // match "name", then everything after "stream" (optional) except for prepending newlines 
                    preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                // parse all other fields
                } else { 
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                }

                $a_data[$matches[1]] = $matches[2];
            }        
        }
    }