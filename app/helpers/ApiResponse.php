<?php
    namespace Helper;

    class ApiResponse {
        public static function success($data = '') {
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

        public static function error($error, $data = '') {
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