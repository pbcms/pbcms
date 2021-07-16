<?php
    namespace Library;

    class Policy {
        private $db = NULL;

        public function __construct() {
            $this->db = new Database();
        }

        public function get($name) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='${name}'");
            if ($res->num_rows > 0) {
                return $res->fetch_assoc()['value'];
            } else {
                return NULL;
            }
        }

        public function list($limit = 10) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "policies` LIMIT ${limit}");
            if ($res->num_rows > 0) {
                return $res->fetch_all(MYSQLI_ASSOC);
            } else {
                return NULL;
            }
        }

        public function exists($name) {
            $res = $this->db->query("SELECT * FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='${name}'");
            if ($res->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function set($name, $value) {
            $value = strval($value);
            if ($this->exists($name)) {
                $this->db->query("UPDATE `" . DATABASE_TABLE_PREFIX . "policies` SET `value`='${value}' WHERE `name`='${name}'");
            } else {
                $this->db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "policies` (`name`, `value`) VALUES ('${name}', '${value}')");
            }
        }

        public function delete($name) {
            $this->db->query("DELETE FROM `" . DATABASE_TABLE_PREFIX . "policies` WHERE `name`='${name}'");
        }
    }