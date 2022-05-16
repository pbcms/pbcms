<?php
    namespace Helper;

    /**
     * Shortcuts to set of response headers.
     */
    class Header {

        /**
         * Sets a location header with a specified location and status to redirect the client to a different page.
         * 
         * @param   string  $location   The location the client should redirect to.
         * @param   int     $status=302 The response code, should be in the 3XX range.
         * @return  void                Redirects the client to the targeted location.
         */
        public static function Location(string $location, int $status = 302): void {
            if ($status < 300 || $status > 399) $status = 302;
            http_response_code($status);
            header("Location: ${location}");
        }

        /**
         * Set a JSON Content-Type header.
         * 
         * @return  void
         */
        public static function Json(): void {
            header("Content-Type: application/json");
        }

        /**
         * Set a Content-Type header with the specified type.
         * 
         * @param   string  $type   The Content-Type that should be set and will be returned.
         * @return  void
         */
        public static function ContentType(string $type): void {
            header("Content-Type: ${type}");
        }

        /**
         * Retrieve authorization headers.
         * 
         * @return string|null  The contents of the authorization header.
         */
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