<?php
    namespace Library;

    use Helper\Random;

    class Token {
        protected $db;

        public function __construct() {
            $this->db = new Database;
            $this->policy = new Policy;
        }

        public function create($type, $payload, $expiration = NULL) {
            $payload = (object) $payload;
            $payload->issued = time();

            if (is_int($expiration)) {
                if ($expiration < 0) {
                    unset($payload->exp);
                    unset($payload->expirationTime);
                } else {
                    $payload->exp = time() + $expiration;
                    $payload->expirationTime = $expiration;
                }        
            } else {
                if ($expiration == NULL) {
                    $expiration = intval($this->policy->get('token-default-expiration'));
                    $payload->exp = time() + $expiration;
                    $payload->expirationTime = $expiration;
                } else {
                    unset($payload->exp);
                    unset($payload->expirationTime);
                }
            }

            $secret = $this->retrieveSecret($type);
            $token = JWT::encode($payload, $secret);

            return (object) array(
                "success" => true,
                "expiration" => (isset($payload->exp) ? $payload->exp : NULL),
                "token" => $token
            );
        }

        public function decode($type, $token) {
            $secret = $this->retrieveSecret($type);
            try {
                $res = JWT::decode($token, $secret, array('HS256'));
            } catch(\Exception $e) {
                switch($e->getMessage()) {
                    case 'Signature verification failed':
                        return (object) array(
                            "success" => false,
                            "error" => "invalid_signature"
                        );
                    case 'Expired token':
                        return (object) array(
                            "success" => false,
                            "error" => "token_expired"
                        );
                    case 'Wrong number of segments':
                    case 'Invalid header encoding':
                    case 'Invalid claims encoding':
                    case 'Invalid signature encoding':
                        return (object) array(
                            "success" => false,
                            "error" => "invalid_token"
                        );
                    default:
                        return (object) array(
                            "success" => false,
                            "error" => "unknown_exception",
                            "exception" => $e->getMessage()
                        );
                }
            }

            return (object) array(
                "success" => true,
                "payload" => $res
            );
        }

        private function retrieveSecret($type, $length = 100) {
            $res = $this->db->query("SELECT * FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                return $res->secret;
            } else {
                $secret = Random::String($length);
                $this->db->query("INSERT INTO `" . $this->db->table("token-secrets") . "` (`type`, `secret`) VALUES ('${type}', '${secret}')");
                return $secret;
            }
        }

        public function renewSecret($type) {
            $res = $this->db->query("SELECT * FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
            $secret = Random::String(100);

            if ($res->num_rows > 0) {
                $this->db->query("UPDATE `" . $this->db->table("token-secrets") . "` SET `secret`='${secret}' WHERE `type`='${type}'");
            } else {
                $this->db->query("INSERT INTO `" . $this->db->table("token-secrets") . "` (`type`, `secret`) VALUES ('${type}', '${secret}')");
            }
        }

        private function destroySecret($type) {
            $this->db->query("DELETE FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
        }
    }