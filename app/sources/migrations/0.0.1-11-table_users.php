<?php
    namespace DatabaseMigrator;

    class TableUsers__11__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "users` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `firstname` varchar(255) NOT NULL,
                `lastname` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL UNIQUE,
                `username` varchar(500) DEFAULT NULL UNIQUE,
                `password` varchar(128) NOT NULL,
                `status` varchar(128) NOT NULL DEFAULT 'UNVERIFIED',
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "users`");
        }
    }