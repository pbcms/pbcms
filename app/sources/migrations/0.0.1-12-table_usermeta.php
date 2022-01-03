<?php
    namespace DatabaseMigrator;

    class TableUsermeta__12__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "usermeta` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user` int(11) NOT NULL,
                `name` varchar(128) NOT NULL,
                `value` longtext NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "usermeta`");
        }
    }