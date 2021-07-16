<?php
    namespace Library;

    class Database {
        private $hostname = DATABASE_HOSTNAME;
        private $username = DATABASE_USERNAME;
        private $password = DATABASE_PASSWORD;
        private $database = DATABASE_DATABASE;
        protected $conn = NULL;

        public function __construct() {
            $this->insert_id = NULL;
            $this->conn = new \mysqli($this->hostname, $this->username, $this->password, $this->database);

            if ($this->conn->connect_error) {
                die("Error 500: Connection to the database failed!");
            }
        }

        public function table($name) {
            return DATABASE_TABLE_PREFIX . $name;
        }

        public function query($sql) {
            $res = $this->conn->query($sql);
            $this->insert_id = $this->conn->insert_id;
            return $res;
        }
        
        public function close() {
            return $this->conn->close();
        }

        public function escape($input) {
            return $this->conn->real_escape_string($input);
        }

        public function escapeObject($data) {
            $data = (object) $data;
            foreach($data as $key => $value) $data->{$key} = $this->conn->real_escape_string($value);
            return $data;
        }
    }