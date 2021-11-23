<?php
    namespace DatabaseMigrator;

    class UpdateTypeMediaTable__1__0_0_11 {
        public function up($db) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "media` MODIFY COLUMN `type` int(11) NOT NULL");
        }

        public function down($db) {
            $res = $db->query("ALTER TABLE `" . DATABASE_TABLE_PREFIX . "media` MODIFY COLUMN `type` varchar(1024) NOT NULL");
        }
    }