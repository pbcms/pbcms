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
    
    class Request {
        public static function sessionInfo($fromRefreshToken = false) {
            if ($fromRefreshToken) {
                if (isset($_COOKIE['pb-refresh-token'])) {
                    $token = new \Library\Token;
                    $decoded = $token->decode('refresh-token', $_COOKIE['pb-refresh-token']);
                    if ($decoded->success) {
                        $sessionUUID = $decoded->payload->session;
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => $decoded->error,
                            "message" => "An error occured while decoding the refresh token."
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "missing_refresh_token",
                        "message" => "No refresh token present."
                    );
                }
            } else {
                $headers = Header::Authorization();
                if (!empty($headers)) {
                    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                        $token = new \Library\Token;
                        $decoded = $token->decode('access-token', $matches[1]);
                        if ($decoded->success) {
                            $sessionUUID = $decoded->payload->session;
                        } else {
                            return (object) array(
                                "success" => false,
                                "error" => $decoded->error,
                                "message" => "An error occured while decoding the access token."
                            );
                        }
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "invalid_authorization_header",
                            "message" => "Invalid authorization header present, expecting Bearer token."
                        );
                    }
                } else {
                    return (object) array(
                        "success" => false,
                        "error" => "missing_authorization_header",
                        "message" => "No authorization header present."
                    );
                }
            }
            
            $sessions = new \Library\Sessions;
            $session = $sessions->info($sessionUUID);
            if ($session) {
                if ($session->expired) {
                    return (object) array(
                        "success" => false,
                        "error" => "session_expired",
                        "message" => "The requested session has since expired."
                    );
                } else {
                    $users = new \Library\Users;
                    $user = $users->info($session->user);

                    if ($user != NULL) {
                        if ($user->status == "LOCKED") return (object) array(
                            "success" => false,
                            "error" => "user_locked",
                            "message" => "The user you are trying to request an access token for has been locked by the system or an administrator."
                        );

                        return (object) array(
                            "success" => true,
                            "info" => $session
                        );
                    } else {
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_user",
                            "message" => "The user you are trying to request an access token for does not exist anymore."
                        );
                    }
                }
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_session",
                    "message" => "The requested session does not exist."
                );
            }
        }

        
        public static function signedin() {
            return self::sessionInfo(true)->success;
        }

        public static function authenticated() {
            return self::sessionInfo(false)->success;
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

        public static function request($url, $execute = true) {
            $router = new Router;
            if ($router->refactorRequest($url)) {
                if ($execute) $router->executeRequest();
            } else {
                return false;
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