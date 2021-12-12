<?php
    namespace DatabaseMigrator;

    class TableMedia__1__0_0_1 {
        public function up($db) {
            //CREATE MEDIA TABLE
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "media` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uuid` varchar(128) NOT NULL UNIQUE,
                `ext` varchar(16) NOT NULL,
                `type` varchar(1024) NOT NULL,
                `owner` int(11) NOT NULL,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "media`");
        }
    }