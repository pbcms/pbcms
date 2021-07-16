<?php
    namespace Library;

    class Token {
        protected $db;

        public function __construct() {
            $this->db = new Database;
            $this->policy = new Policy;
        }

        public function create($type, $payload, $expiration = 86400) {
            $payload = (object) $payload;
            $payload->issued = time();
            $payload->exp = time() + $expiration;

            if (!$expiration) unset($payload->exp);
            $secret = $this->retrieveSecret($type);
            $token = \Library\JWT::encode($payload, $secret);

            return (object) array(
                "success" => true,
                "expiration" => (isset($payload->exp) ? $payload->exp : NULL),
                "token" => $token
            );
        }

        public function decode($type, $token) {
            $secret = $this->retrieveSecret($type);
            try {
                $res = \Library\JWT::decode($token, $secret, array('HS256'));
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

        private static function generateSecret($length = 100) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        private function retrieveSecret($type, $length = 100) {
            $res = $this->db->query("SELECT * FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
            if ($res->num_rows > 0) {
                $res = (object) $res->fetch_assoc();
                return $res->secret;
            } else {
                $secret = $this->generateSecret($length);
                $this->db->query("INSERT INTO `" . $this->db->table("token-secrets") . "` (`type`, `secret`) VALUES ('${type}', '${secret}')");
                return $secret;
            }
        }

        public function renewSecret($type) {
            $res = $this->db->query("SELECT * FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
            $secret = \Helper\Secret::String(100);

            if ($res->num_rows > 0) {
                $this->db->query("UPDATE `" . $this->db->table("token-secrets") . "` SET `secret`='${secret}' WHERE `type`='${type}'");
            } else {
                $this->db->query("INSERT INTO `" . $this->db->table("token-secrets") . "` (`type`, `secret`) VALUES ('${type}', '${secret}')");
            }
        }

        private function destroySecret($type) {
            $this->db->query("DELETE FROM `" . $this->db->table("token-secrets") . "` WHERE `type`='${type}'");
        }

        public function requestClientToken($user, $expires = true) {
            $user = $this->validateUser($user);
            if ($user != NULL) {
                $payload = (object) array(
                    "id" => $user,
                    "issued" => time(),
                    "exp" => time() + 60 * 60 * 24
                );

                if (!$expires) unset($payload->exp);
                $clientTokenKey = $this->policy->get('clientTokenKey');
                $clientToken = \Library\JWT::encode($payload, $clientTokenKey);

                return (object) array(
                    "success" => true,
                    "expiration" => (isset($payload->exp) ? $payload->exp : NULL),
                    "token" => $clientToken
                );
            } else {
                return (object) array(
                    "success" => false,
                    "error" => "unknown_user"
                );
            }
        }
    }