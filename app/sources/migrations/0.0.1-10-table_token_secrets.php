<?php
    namespace DatabaseMigrator;

    class TableTokenSecrets__10__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "token-secrets` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(500) NOT NULL UNIQUE,
                `secret` varchar(1024) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "token-secrets`");
        }
    }