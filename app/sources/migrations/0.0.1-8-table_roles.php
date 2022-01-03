<?php
    namespace DatabaseMigrator;

    class TableRoles__8__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "roles` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(32) NOT NULL,
                `description` varchar(1024) NOT NULL,
                `weight` int(11) NOT NULL UNIQUE,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated` timestamp NOT NULL DEFAULT current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "roles` (`name`, `description`, `weight`) VALUES
                ('Administrator', 'An administrator can manage and control every aspect of the website.', 1);");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "roles`");
        }
    }