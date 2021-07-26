<?php
    namespace DatabaseMigrator;

    class TableVirtualPaths__13__0_0_1 {
        public function up($db) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "virtual-paths` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `path` varchar(512) NOT NULL UNIQUE,
                `target` varchar(1024) NOT NULL,
                `lang` varchar(2) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "virtual-paths`");
        }
    }