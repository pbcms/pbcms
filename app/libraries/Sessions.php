<?php
    namespace Library;

    use function Helper\uuidv4 as UuidGenerator;

    class Sessions {
        private $db;
        private $policy;
        private $user;

        public function __construct() {
            $this->db = new Database;
            $this->policy = new Policy;
            $this->user = new Users;
        }

        public function create($user, $expiration = NULL) {
            $userId = $this->user->getId($user);
            if (!$userId) {
                return false;
            } else {
                $uuid = UuidGenerator();
                $lastSeen = time();
                if (is_int($expiration)) {
                    if ($expiration < 0) $expiration = "NULL";
                } else {
                    if ($expiration == NULL) {
                        $expiration = intval($this->policy->get('session-default-expiration'));
                    } else {
                        $expiration = "NULL";
                    }
                }

                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "sessions` (`user`, `uuid`, `lastSeen`, `expiration`) VALUES ('$userId', '$uuid', '$lastSeen', '$expiration')");
                return $uuid;
            }
        }

        public function exists($uuid) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "sessions` WHERE `uuid`='$uuid'");
            return $res->num_rows > 0;
        }

        public function info($uuid) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "sessions` WHERE `uuid`='$uuid'");
            if ($res->num_rows > 0) {
                $row = (object) $res->fetch_assoc();
                if ($row->expiration == NULL) {
                    $row->expired = false;
                } else {
                    $row->expired = time() > (intval($row->lastSeen) + intval($row->expiration));
                }

                return $row;
            } else {
                return NULL;
            }
        }

        public function list($user) {
            $userId = $this->user->getId($user);
            if (!$userId) {
                return false;
            } else {
                $processResult = function($row) { 
                    $row = (object) $row;
                    if ($row->expiration == NULL) {
                        $row->expired = false;
                    } else {
                        $row->expired = time() > $row->lastSeen + $row->expiration;
                    }

                    return $row;
                };

                $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "sessions` WHERE `user`='$userId'");
                return array_map($processResult, $res->fetch_all(MYSQLI_ASSOC));
            }
        }

        public function refresh($uuid) {
            $info = $this->info($uuid);
            if (!$info) return false;
            if ($info->expired) return false;
            $currentTime = time();
            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "sessions` SET `lastSeen`=$currentTime WHERE `uuid`='$uuid'");
            return true;
        }

        public function expired($uuid) {
            $res = $this->db->query("SELECT `lastSeen`, `expiration` FROM `" . DATABASE_TABLE_PREFIX . "sessions` WHERE `uuid`='$uuid'");
            $row = (object) $res->fetch_assoc();
            if ($row->expiration == NULL) {
                return false;
            } else {
                return time() > $row->lastSeen + $row->expiration;
            }
        }

        public function end($uuid) {
            $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "sessions` SET `expiration`=0 WHERE `uuid`='$uuid'");
        }

        public function delete($uuid) {
            $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "sessions` WHERE `uuid`='$uuid'");
        }
    }