<?php
    namespace Helper;

    class Query {
        public static function encode($data) {
            $res = '';
            foreach ($data as $key => $value) {
                if ($res != '') $res .= '&';
                $res .= $key . '=' . $value;
            }
            return $res;
        }

        public static function decode($query) {
            $res = new \stdClass;
            $query = explode('&', $query);
            foreach ($query as $segment) {
                $segment = explode('=', $segment);
                if (!isset($segment[1])) $segment[1] = '';
                $res->{$segment[0]} = $segment[1];
            }

            return $res;
        }
    }

    class Json {
        public static function encode($data) {
            return \json_encode($data);
        }

        public static function decode($json) {
            return \json_decode($json);
        }
    }

    class Validate {
        public static function removeUnlisted($allowed, $data) {
            $data = (array) $data;
            foreach (\array_keys($data) as $key) if (!\in_array($key, (array) $allowed)) unset($data[$key]);
            return $data;
        }

        public static function listMissing($required, $data) {
            $missing = [];
            foreach ($required as $requirement) if (!\property_exists((object) $data, $requirement)) $missing[] = $requirement;
            return $missing;
        }

        public static function trimObject($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data->{$key} = trim($value);
            return $data;
        }

        public static function trimArray($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data[$key] = trim($value);
            return (array) $data;
        }

        public static function secureRequest() {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        }
     }

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
    }
    
    class Request {
        public static function authenticated($redirect = false, $followup = '') {
            if (!isset($_COOKIE['pb-refresh-token'])) {
                return false;
            } else {
                $clientToken = $_COOKIE['clientToken'];
                $decoded = $this->token->decodeClientToken($clientToken);
                if ($decoded->success) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        public static function austhenticated($userModel, $redirect = false, $followup = '') {
            if (!$userModel->authenticated()) {
                if ($redirect) {
                    $followup = ($followup == '' ? '' : '?followup=' . $followup);
                    Header::Location('/account/signin' . $followup);
                } else {
                    Header::JSON();
                    print_r(json_encode(array(
                        "success" => false,
                        "error" => "not_authenticated",
                        "message" => "Je bent niet aangemeld."
                    )));
                }

                exit();
            }
        }

        public static function privileged($userModel, $message = "Je hebt niet het recht om deze actie uit te voeren.") {
            if (!$userModel->privileged()) {
                Respond::JSON(array(
                    "success" => false,
                    "error" => "insufficient_permissions",
                    "message" => $message
                )); 

                exit();
            }
        }

        public static function method($lowercase = false) {
            return ($lowercase ? strtolower($_SERVER['REQUEST_METHOD']) : strtoupper($_SERVER['REQUEST_METHOD']));
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
    }

    class Respond {
        public static function JSON($response) {
            Header::JSON();
            print_r(json_encode($response, JSON_NUMERIC_CHECK));
        }
    }

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

    class SEO {
        public static function urlify($string){
            $string = str_replace(array('[\', \']'), '', $string);
            $string = preg_replace('/\[.*\]/U', '', $string);
            $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
            $string = htmlentities($string, ENT_COMPAT, 'utf-8');
            $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
            $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
            return strtolower(trim($string, '-'));
        }
    }

    class Random {
        
        /**
         * Source: https://stackoverflow.com/a/4356295
         */
        public static function String($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        public static function Number($min = PHP_INT_MIN, $max = PHP_INT_MAX) {
            try {
                $r = random_int($min, $max);
            } catch(Exception $e) {
                $r = rand($min, $max);
            }

            return $r;
        }
    }

    function uuidv4() {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }