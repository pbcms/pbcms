<?php
    namespace DatabaseMigrator;

    class TableRelations__7__0_0_1 {
        public function up($db, $log) {
            $db->query("CREATE TABLE `" . DATABASE_TABLE_PREFIX . "relations` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(128) NOT NULL,
                `origin` int(11) NOT NULL,
                `target` int(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        public function down($db, $log) {
            $db->query("DROP TABLE `" . DATABASE_TABLE_PREFIX . "relations`");
        }
    }