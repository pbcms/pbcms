<?php
    namespace Model;

    class Session {
        private $db;

        public function __construct() {
            $this->db = new \Core\Database;
        }
    }