<?php
    namespace Helper;

    class Header {
        public static function Location($loc, $status = 302) {
            if ($status < 300 || $status > 399) $status = 302;
            http_response_code($status);
            header("Location: ${loc}");
        }

        public static function Json() {
            header("Content-Type: application/json");
        }

        public static function ContentType($type) {
            header("Content-Type: ${type}");
        }

        public static function Authorization() {        //Credit: https://stackoverflow.com/a/40582472
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } else if (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if (isset($requestHeaders['Authorization'])) $headers = trim($requestHeaders['Authorization']);
            }
            
            return $headers;
        }
    }