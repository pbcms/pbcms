<?php
    namespace DatabaseMigrator;

    class TableMediaTypes__2__0_0_1 {
        public function up($db) {
            $db->query("CREATE TABLE`" . DATABASE_TABLE_PREFIX . "media-types` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(1024) NOT NULL UNIQUE,
                `extensions` varchar(8192) NOT NULL,
                `max-size` varchar(128) DEFAULT '2M'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $db->query("INSERT INTO `" . DATABASE_TABLE_PREFIX . "media-types` (`type`, `extensions`, `max-size`) VALUES ('profilepicture', 'jpg,jpeg,png,gif', '2M');");
        }

        public function down($db) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "media-types`");
        }
    }