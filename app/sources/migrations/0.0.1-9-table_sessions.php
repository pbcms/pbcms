<?php
    namespace DatabaseMigrator;

    class TableSessions__9__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "sessions` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user` int(11) NOT NULL,
                `uuid` varchar(36) NOT NULL UNIQUE,
                `lastSeen` timestamp NOT NULL DEFAULT current_timestamp(),
                `expiration` int(64) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "sessions`");
        }
    }