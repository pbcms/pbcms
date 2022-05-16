<?php
    namespace Helper;

    /**
     * Takes an object or array and prints it out as json in a standard format.
     */
    class ApiResponse {

        /**
         * Provides the client with a successfull response and will include the following properties:
         *   - success: true
         *   - message: STRING         //Only if non-empty string or number has been provided.
         *   - mixed    properties     //If object or array has been provided, it will be merged with initial response object. Success will be overwritten.
         * 
         * @param   string|array|object|int     $data=''    If string will be provided as message attribute, if object or array, will be merged. See above.
         * @return  void                                    Will print json to output and set json response header.
         */
        public static function success($data = ''): void {
            $res = (object) array(
                "success" => true
            );

            if (is_string($data) || is_numeric($data)) {
                if (!empty($data)) $res->message = $data;
            } else {
                $res = (object) $data;
                $res->success = true;
            }

            Respond::JSON($res);
        }

        /**
         * Provides the client with a error response and will include the following properties:
         *   - success: false
         *   - error:   STRING         
         *   - message: STRING         //Only if non-empty string or number has been provided.
         *   - mixed    properties     //If object or array has been provided, it will be merged with initial response object. Success will be overwritten.
         * 
         * @param   string                      $error      The error code. (example_error_code)
         * @param   string|array|object|int     $data=''    If string will be provided as message attribute, if object or array, will be merged. See above.
         * @return  void                                    Will print json to output and set json response header.
         */
        public static function error(string $error, $data = ''): void {
            $res = (object) array(
                "success" => false,
                "error" => $error
            );

            if (is_string($data) || is_numeric($data)) {
                if (!empty($data)) $res->message = $data;
            } else {
                $res = (object) $data;
                $res->success = false;
                $res->error = $error;
            }

            Respond::JSON($res);
        }
    }