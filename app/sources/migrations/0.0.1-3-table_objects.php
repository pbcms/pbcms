<?php
    namespace DatabaseMigrator;

    class TableObjects__3__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "objects` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(1024) NOT NULL,
                `name` varchar(1024) NOT NULL,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            
            $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "objects` (`type`, `name`) VALUES
                ('pb-password', 'policies');");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "objects`");
        }
    }